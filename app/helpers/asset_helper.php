<?php
/**
 * asset() — retorna URL do asset com cache-busting via query string.
 * Uso:  <link rel="stylesheet" href="<?= asset('css/home.css') ?>">
 * Em produção gera: /afro_essencia/public/css/home.css?v=1.0.0
 */
function asset(string $path): string
{
    $base    = defined('APP_BASE') ? rtrim(APP_BASE, '/') : '';
    $version = defined('APP_VERSION') ? APP_VERSION : '1';
    $path    = ltrim($path, '/');
    return "{$base}/public/{$path}?v={$version}";
}

/**
 * url() — gera URL interna da aplicação.
 * Uso:  <a href="<?= url('agendamento/create') ?>">
 */
function url(string $path = ''): string
{
    $base = defined('APP_BASE') ? rtrim(APP_BASE, '/') : '';
    return $base . '/' . ltrim($path, '/');
}
