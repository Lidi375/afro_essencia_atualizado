<?php

class AuthController extends BaseController
{
    private $authService;
    private $usuarioService;

    public function __construct()
    {
        $this->authService   = new AuthService();
        $this->usuarioService = new UsuarioService();
    }

    // ─── LOGIN ────────────────────────────────────────────────────────────────
    public function login()
    {
        // Já logado? redireciona
        if (isset($_SESSION['user']['id'])) {
            $this->redirect('dashboard');
            return;
        }

        $erro = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->verifyCsrf();

            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';

            if (empty($email) || empty($senha)) {
                $erro = "Preencha e-mail e senha.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erro = "E-mail inválido.";
            } else {
                $result = $this->authService->login($email, $senha);

                if ($result['success']) {
                    // Regenera ID de sessão para prevenir fixação
                    session_regenerate_id(true);
                    $this->redirect('dashboard');
                    return;
                } elseif (!empty($result['locked'])) {
                    // Bloqueio por excesso de tentativas: aqui é seguro
                    // mostrar a mensagem específica, pois o usuário já
                    // demonstrou saber (ou estar tentando) esse e-mail.
                    $erro = $result['message'];
                } else {
                    // Mensagem genérica — não revela se email existe
                    $erro = "E-mail ou senha incorretos.";
                }
            }
        }

        $sucesso = $_SESSION['success'] ?? null;
        $erroSessao = $_SESSION['erro'] ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $this->view('auth/login', [
            'erro'    => $erro ?? $erroSessao,
            'sucesso' => $sucesso,
        ]);
    }

    // ─── CADASTRO ─────────────────────────────────────────────────────────────
    public function register()
    {
        if (isset($_SESSION['user']['id'])) {
            $this->redirect('dashboard');
            return;
        }

        $erro = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->verifyCsrf();

            $dados = [
                'nome'  => trim(strip_tags($_POST['nome']  ?? '')),
                'email' => trim($_POST['email'] ?? ''),
                'senha' => $_POST['senha'] ?? '',
                'confirmar_senha' => $_POST['confirmar_senha'] ?? ($_POST['confirmarSenha'] ?? ''),
            ];

            if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
                $erro = "Preencha todos os campos obrigatórios.";
            } elseif (strlen($dados['nome']) < 3) {
                $erro = "Nome deve ter pelo menos 3 caracteres.";
            } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                $erro = "E-mail inválido.";
            } elseif (strlen($dados['senha']) < 6) {
                $erro = "A senha deve ter pelo menos 6 caracteres.";
            } elseif ($dados['senha'] !== $dados['confirmar_senha']) {
                $erro = "As senhas não coincidem.";
            } else {
                try {
                    $this->usuarioService->register($dados);
                    $_SESSION['success'] = "Cadastro realizado com sucesso! Faça login para continuar.";
                    $this->redirect('auth/login');
                    return;
                } catch (Exception $e) {
                    $erro = $e->getMessage();
                }
            }
        }

        $this->view('auth/register', ['erro' => $erro]);
    }

    // ─── LOGOUT ───────────────────────────────────────────────────────────────
    public function logout()
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }
        session_destroy();
        $base = defined('APP_BASE') ? APP_BASE : '/afro_essencia';
        header("Location: {$base}/");
        exit;
    }

    // ─── RECUPERAR SENHA (etapa 1) ────────────────────────────────────────────
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->verifyCsrf();

            $email = trim($_POST['email'] ?? '');

            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Invalida tokens anteriores
                $this->usuarioService->invalidarTokensAnteriores($email);

                $usuario = $this->usuarioService->findByEmail($email);

                if ($usuario) {
                    $token    = bin2hex(random_bytes(32));
                    $expiraEm = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    $this->usuarioService->salvarToken($email, $token, $expiraEm);

                    $emailService = new EmailService();
                    $emailService->enviarLinkRedefinicao($email, $usuario['nome'], $token);
                }
            }

            // Mensagem genérica (não revela se e-mail existe)
            $_SESSION['success'] = "Se este e-mail estiver cadastrado, você receberá um link para redefinir sua senha em breve.";
            $this->redirect('auth/forgotPassword');
            return;
        }

        $sucesso = $_SESSION['success'] ?? null;
        $erro    = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $this->view('auth/forgot-password', ['sucesso' => $sucesso, 'erro' => $erro]);
    }

    // ─── REDEFINIR SENHA (etapa 2) ────────────────────────────────────────────
    public function resetPassword($tokenParam = null)
    {
        // O link enviado por e-mail é /auth/resetPassword/{token} (segmento
        // de URL, não query string) — por isso $tokenParam vem primeiro.
        $token = $tokenParam ?? $_GET['token'] ?? $_POST['token'] ?? null;

        if (!$token || strlen($token) !== 64 || !ctype_xdigit($token)) {
            $_SESSION['erro'] = "Link inválido ou expirado.";
            $this->redirect('auth/login');
            return;
        }

        $dadosToken = $this->usuarioService->validarToken($token);

        if (!$dadosToken) {
            $_SESSION['erro'] = "Link inválido ou expirado. Solicite um novo link.";
            $this->redirect('auth/forgotPassword');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->verifyCsrf();

            $novaSenha  = $_POST['novaSenha']      ?? '';
            $confirmar  = $_POST['confirmarSenha'] ?? '';

            if ($novaSenha !== $confirmar) {
                $erro = "As senhas não coincidem.";
                $this->view('auth/reset-password', ['erro' => $erro, 'token' => $token]);
                return;
            }

            if (strlen($novaSenha) < 8) {
                $erro = "A senha deve ter pelo menos 8 caracteres.";
                $this->view('auth/reset-password', ['erro' => $erro, 'token' => $token]);
                return;
            }

            if (!preg_match('/[A-Za-z]/', $novaSenha) || !preg_match('/[0-9]/', $novaSenha)) {
                $erro = "A senha deve conter letras e números.";
                $this->view('auth/reset-password', ['erro' => $erro, 'token' => $token]);
                return;
            }

            $email = $dadosToken['email'];
            $this->usuarioService->atualizarSenha($email, $novaSenha);
            $this->usuarioService->usarToken($dadosToken['id']);

            $_SESSION['success'] = "Senha redefinida com sucesso! Faça login com sua nova senha.";
            $this->redirect('auth/login');
            return;
        }

        $this->view('auth/reset-password', ['token' => $token]);
    }
}
