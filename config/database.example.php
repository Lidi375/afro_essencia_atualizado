<?php

// ─── Caminho base da aplicação ────────────────────────────────────────────────
// Altere aqui ao subir para produção.
// Subpasta (XAMPP/shared hosting): '/afro_essencia'
// Raiz de domínio:                 ''
if (!defined('APP_BASE')) {
    define('APP_BASE', '/afro_essencia');
}

// URL pública completa (sem barra final) — usada em e-mails e gateways
if (!defined('APP_PUBLIC_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('APP_PUBLIC_URL', $protocol . '://' . $host . APP_BASE);
}


/**
 * Classe Database
 * Conexão PDO Singleton com suporte a múltiplos ambientes (XAMPP / produção)
 */
class Database {

    private static $conn = null;

    public static function getConnection() {

        if (self::$conn === null) {

            $host   = defined('DB_HOST')   ? DB_HOST   : 'localhost';
            $name   = defined('DB_NAME')   ? DB_NAME   : 'afro_essencia';
            $user   = defined('DB_USER')   ? DB_USER   : 'root';
            $pass   = defined('DB_PASS')   ? DB_PASS   : '';
            $charset = 'utf8mb4';

            try {
                self::$conn = new PDO(
                    "mysql:host={$host};dbname={$name};charset={$charset}",
                    $user,
                    $pass
                );
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            } catch (PDOException $e) {
                // Em produção: nunca expor detalhes do erro
                error_log('DB Connection error: ' . $e->getMessage());
                http_response_code(500);
                die("<h2>Erro interno. Tente novamente em instantes.</h2>");
            }
        }

        return self::$conn;
    }
}
