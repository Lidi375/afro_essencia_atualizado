<?php
/**
 * Autoloader manual — gerado para o projeto Afro Essência.
 * Carrega o PHPMailer e registra um PSR-4 simples para as classes do projeto.
 */

// ── PHPMailer ────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $map = [
        'PHPMailer\\PHPMailer\\PHPMailer'  => __DIR__ . '/phpmailer/phpmailer/src/PHPMailer.php',
        'PHPMailer\\PHPMailer\\SMTP'       => __DIR__ . '/phpmailer/phpmailer/src/SMTP.php',
        'PHPMailer\\PHPMailer\\Exception'  => __DIR__ . '/phpmailer/phpmailer/src/Exception.php',
    ];

    if (isset($map[$class])) {
        require_once $map[$class];
    }
});
