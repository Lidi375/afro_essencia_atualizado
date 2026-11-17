<?php

/**
 * Verifica se o usuário está logado
 */
function isLogged(): bool {
    return isset($_SESSION['user']['id']);
}

/**
 * Retorna o tipo do usuário logado, ou null
 */
function userTipo(): ?string {
    return $_SESSION['user']['tipo_usuario'] ?? null;
}

/**
 * Verifica se é admin
 */
function isAdmin(): bool {
    return userTipo() === 'admin';
}

/**
 * Verifica se é profissional
 */
function isProfissional(): bool {
    return userTipo() === 'profissional';
}

/**
 * Verifica se é cliente
 */
function isCliente(): bool {
    return userTipo() === 'cliente';
}

/**
 * Redireciona forçando login
 */
function requireLogin(): void {
    if (!isLogged()) {
        $base = defined('APP_BASE') ? rtrim(APP_BASE, '/') : '/afro_essencia';
        header("Location: {$base}/auth/login");
        exit;
    }
}

/**
 * Gera token CSRF e armazena na sessão
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF do POST
 */
function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die("Requisição inválida. Por favor, recarregue a página e tente novamente.");
    }
}

/**
 * Escapa saída para HTML (evita XSS)
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
