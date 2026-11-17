<?php
define('APP_VERSION', '1.0.0'); // bump ao fazer deploy

// ─── Ambiente: detectar produção vs desenvolvimento ──────────────────────────
// Para forçar produção: defina a variável de ambiente APP_ENV=production
// no painel do seu hosting, ou crie um arquivo .env com APP_ENV=production.
$appEnv = getenv('APP_ENV') ?: 'development';
$isProd = ($appEnv === 'production');

if ($isProd) {
    error_reporting(0);
    ini_set('display_errors', '0');

    // ── Security headers (produção) ──────────────────────────
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    // HSTS: só ativar após confirmar HTTPS funcionando
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    // Caminho de log: ajuste conforme o servidor
    ini_set('error_log', __DIR__ . '/logs/app_error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// ─── Configurações de segurança da sessão ────────────────────────────────────
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), // true automaticamente em HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// ─── Inicia sessão ────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Autoload de classes ──────────────────────────────────────────────────────
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . "/app/controllers/",
        __DIR__ . "/app/models/",
        __DIR__ . "/app/dao/",
        __DIR__ . "/app/services/",
        __DIR__ . "/app/gateways/",
    ];

    foreach ($paths as $path) {
        $file = $path . $class . ".php";

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ─── Configurações ────────────────────────────────────────────────────────────
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/mail.php";
require_once __DIR__ . "/config/pagamento.php";

// ─── Composer / PHPMailer ─────────────────────────────────────────────────────
if (file_exists(__DIR__ . "/vendor/autoload.php")) {
    require_once __DIR__ . "/vendor/autoload.php";
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
require_once __DIR__ . "/app/helpers/auth.php";
require_once __DIR__ . "/app/helpers/asset_helper.php";

// ─── Roteamento ───────────────────────────────────────────────────────────────
$url = trim($_GET['url'] ?? 'home', '/');

// Sanitiza URL
$url = preg_replace('/[^a-zA-Z0-9\-\/]/', '', $url);

// Raiz do site (url vazia após o trim) deve cair na home, não em 404
if ($url === '') {
    $url = 'home';
}

$parts = explode('/', $url);

$controller = ucfirst($parts[0] ?? 'home') . 'Controller';
$method     = $parts[1] ?? 'index';
$params     = array_slice($parts, 2);

// ─── Execução ─────────────────────────────────────────────────────────────────
if (class_exists($controller)) {

    $c = new $controller();

    if (method_exists($c, $method)) {

        try {
            call_user_func_array([$c, $method], $params);

        } catch (Throwable $e) {

            http_response_code(500);

            if (ini_get('display_errors')) {
                echo "<h2>Erro interno</h2>";
                echo "<pre>";
                echo htmlspecialchars($e->getMessage());
                echo "\n\n";
                echo htmlspecialchars($e->getFile());
                echo " : ";
                echo $e->getLine();
                echo "</pre>";
            } else {
                echo "<h2>Erro interno do servidor.</h2>";
            }
        }

    } else {
        http_response_code(404);
        echo '<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AFRO ESSÊNCIA | Página não encontrada</title><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet"><style>body{margin:0;font-family:Montserrat,sans-serif;background:#FCFAF7;color:#45352C;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;padding:24px}.box{max-width:480px}.icon{font-size:4rem;margin-bottom:16px;opacity:.5}h1{font-family:"Playfair Display",serif;font-size:2rem;margin:0 0 12px}p{color:#7A6A60;font-size:15px;line-height:1.6;margin:0 0 28px}.btn{display:inline-block;background:#D4A96A;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;transition:background .2s}.btn:hover{background:#C89453}</style></head><body><div class="box"><div class="icon">🔍</div><h1>Página não encontrada</h1><p>O endereço que você acessou não existe ou foi movido.</p><a href="/afro_essencia/" class="btn">← Voltar ao início</a></div></body></html>';
    }

} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AFRO ESSÊNCIA | Página não encontrada</title><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet"><style>body{margin:0;font-family:Montserrat,sans-serif;background:#FCFAF7;color:#45352C;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;padding:24px}.box{max-width:480px}.icon{font-size:4rem;margin-bottom:16px;opacity:.5}h1{font-family:"Playfair Display",serif;font-size:2rem;margin:0 0 12px}p{color:#7A6A60;font-size:15px;line-height:1.6;margin:0 0 28px}.btn{display:inline-block;background:#D4A96A;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;transition:background .2s}.btn:hover{background:#C89453}</style></head><body><div class="box"><div class="icon">🔍</div><h1>Página não encontrada</h1><p>O endereço que você acessou não existe ou foi movido.</p><a href="/afro_essencia/" class="btn">← Voltar ao início</a></div></body></html>';
}