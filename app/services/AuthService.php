<?php

class AuthService
{
    private $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
    }

    public function login(string $email, string $senha): array
    {
        // ── Proteção contra brute force ────────────────────────────
        // Persistida no banco (tabela login_attempts), e não na sessão
        // PHP: um atacante pode simplesmente descartar o cookie de
        // sessão a cada requisição para "resetar" um contador que
        // vive só na sessão, burlando o bloqueio por completo.
        $maxTentativas = 5;
        $lockSeconds   = 300; // 5 minutos

        $conn = Database::getConnection();

        $stmt = $conn->prepare(
            "SELECT tentativas, bloqueado_ate FROM login_attempts WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        $tentativa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tentativa && $tentativa['bloqueado_ate'] && strtotime($tentativa['bloqueado_ate']) > time()) {
            $restante = ceil((strtotime($tentativa['bloqueado_ate']) - time()) / 60);
            return ['success' => false, 'locked' => true, 'message' => "Muitas tentativas. Aguarde {$restante} minuto(s) para tentar novamente."];
        }

        $user = $this->usuarioDAO->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Credenciais inválidas'];
        }

        // Verifica conta ativa
        if (($user['status'] ?? 'ativo') !== 'ativo') {
            return ['success' => false, 'message' => 'Conta inativa. Entre em contato com a administração.'];
        }

        if (!password_verify($senha, $user['senha'])) {
            $novasTentativas = ($tentativa['tentativas'] ?? 0) + 1;

            if ($novasTentativas >= $maxTentativas) {
                $conn->prepare(
                    "INSERT INTO login_attempts (email, tentativas, bloqueado_ate)
                     VALUES (?, 0, DATE_ADD(NOW(), INTERVAL ? SECOND))
                     ON DUPLICATE KEY UPDATE tentativas = 0, bloqueado_ate = DATE_ADD(NOW(), INTERVAL ? SECOND)"
                )->execute([$email, $lockSeconds, $lockSeconds]);
            } else {
                $conn->prepare(
                    "INSERT INTO login_attempts (email, tentativas, bloqueado_ate)
                     VALUES (?, ?, NULL)
                     ON DUPLICATE KEY UPDATE tentativas = ?, bloqueado_ate = NULL"
                )->execute([$email, $novasTentativas, $novasTentativas]);
            }

            return ['success' => false, 'message' => 'Credenciais inválidas'];
        }

        // Armazena apenas o necessário na sessão (nunca a senha)
        $_SESSION['user'] = [
            'id'           => $user['id'],
            'nome'         => $user['nome'],
            'email'        => $user['email'],
            'tipo_usuario' => $user['tipo_usuario'],
            'foto'         => $user['foto'] ?? null,
        ];

        // Atualiza hash se necessário (melhoria de algoritmo)
        if (password_needs_rehash($user['senha'], PASSWORD_BCRYPT)) {
            $this->usuarioDAO->updatePassword($email, password_hash($senha, PASSWORD_BCRYPT));
        }

        // Login bem-sucedido: limpa o contador de falhas
        $conn->prepare("DELETE FROM login_attempts WHERE email = ?")->execute([$email]);

        return ['success' => true];
    }
}
