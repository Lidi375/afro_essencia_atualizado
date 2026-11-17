<?php

/**
 * ✉️ CONFIGURAÇÃO DE EMAIL — SMTP
 *
 * ⚠️  Preencha com suas credenciais reais antes de usar em produção.
 *
 * GMAIL: você precisa de uma "Senha de App" (não a senha normal).
 *   Acesse: https://myaccount.google.com/apppasswords
 *   Ative a verificação em duas etapas primeiro, depois gere a senha de app.
 */

define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_USERNAME',   'seuemail@gmail.com');  // ← seu Gmail aqui
define('MAIL_PASSWORD',   'SENHA-DE-APP-DO-GMAIL-AQUI');            // ← senha de app aqui
define('MAIL_FROM_EMAIL', 'seuemail@gmail.com');  // ← mesmo Gmail
define('MAIL_FROM_NAME',  'Afro Essência');

/**
 * URL base da aplicação — usada nos links enviados por email.
 * Este valor é calculado automaticamente a partir de APP_PUBLIC_URL (definido em database.php).
 * Para forçar um valor fixo (ex: em produção sem $_SERVER disponível), defina abaixo:
 *
 * define('APP_URL', 'https://www.seudominio.com.br');
 */
if (!defined('APP_URL')) {
    // Usa APP_PUBLIC_URL se já foi calculado; caso contrário, fallback local
    define('APP_URL', defined('APP_PUBLIC_URL') ? APP_PUBLIC_URL : 'http://localhost/afro_essencia');
}
