<?php

class BaseController
{
    /**
     * Renderiza uma view injetando variáveis via extract()
     */
    protected function view(string $path, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . "/../views/" . $path . ".php";

        if (!file_exists($viewFile)) {
            http_response_code(404);
            die("<h2>View não encontrada: {$path}</h2>");
        }

        require $viewFile;
    }

    /**
     * Redireciona para uma rota interna
     */
    protected function redirect(string $path): void
    {
        // Separa caminho da query string antes de sanitizar,
        // para nao destruir '?', '=' e '&' legitimos.
        $parts    = explode('?', $path, 2);
        $safePath = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $parts[0]);

        // Sanitiza query string: permite apenas chars seguros em parametros
        $safeQuery = '';
        if (isset($parts[1])) {
            $safeQuery = '?' . preg_replace('/[^a-zA-Z0-9\-_=&]/', '', $parts[1]);
        }

        // Previne header injection: remove \r e \n residuais
        $base     = defined('APP_BASE') ? rtrim(APP_BASE, '/') : '/afro_essencia';
        $location = $base . '/' . $safePath . $safeQuery;
        $location = str_replace(["\r", "\n"], '', $location);

        header('Location: ' . $location);
        exit;
    }

    /**
     * Exige login — redireciona se não autenticado
     */
    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $_SESSION['erro'] = "Você precisa estar logado para acessar esta página.";
            $base = defined('APP_BASE') ? rtrim(APP_BASE, '/') : '/afro_essencia';
        header("Location: {$base}/auth/login");
            exit;
        }
    }

    /**
     * Exige perfil específico
     */
    protected function requireRole(string $tipoPermitido): void
    {
        $this->requireAuth();

        if (($_SESSION['user']['tipo_usuario'] ?? '') !== $tipoPermitido) {
            $_SESSION['erro'] = "Acesso negado.";
            $base = defined('APP_BASE') ? rtrim(APP_BASE, '/') : '/afro_essencia';
        header("Location: {$base}/dashboard");
            exit;
        }
    }

    /**
     * Middleware de controle de acesso
     *   'auth'         → apenas login
     *   'admin'        → apenas admin
     *   'profissional' → apenas profissional
     *   'cliente'      → apenas cliente
     */
    protected function middleware(string $tipo): void
    {
        if ($tipo === 'auth') {
            $this->requireAuth();
            return;
        }
        $this->requireRole($tipo);
    }

    /**
     * Retorna JSON e encerra execução
     */
    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Valida token CSRF (usa em todos os POSTs sensíveis)
     */
    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die("Requisição inválida. Recarregue a página e tente novamente.");
        }
    }
}
