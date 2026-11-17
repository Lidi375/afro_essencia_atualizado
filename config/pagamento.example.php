<?php

// ═══════════════════════════════════════════════════════════════════════
// CONFIGURAÇÃO DE PAGAMENTO — Afro Essência
//
// ATENÇÃO: este arquivo contém credenciais sensíveis.
// - Nunca versione com credenciais reais
// - Em produção, prefira variáveis de ambiente (.env)
// - Adicione config/pagamento.php ao .gitignore
// ═══════════════════════════════════════════════════════════════════════

// ── Gateway ativo ────────────────────────────────────────────────────
// Opções: 'mercadopago' | 'pagseguro' | 'stripe'
define('PAYMENT_GATEWAY', 'mercadopago');

// ── Mercado Pago ─────────────────────────────────────────────────────
// Sandbox:  https://www.mercadopago.com.br/developers/pt/docs/credentials
// Produção: substitua pelo token de produção quando go-live
define('MP_ACCESS_TOKEN',   getenv('MP_ACCESS_TOKEN')   ?: 'TEST-COLOQUE-SEU-ACCESS-TOKEN-AQUI');
define('MP_WEBHOOK_SECRET', getenv('MP_WEBHOOK_SECRET') ?: '');

// ── PagSeguro (preparado, não ativo) ──────────────────────────────────
define('PAGSEGURO_TOKEN', getenv('PAGSEGURO_TOKEN') ?: '');
define('PAGSEGURO_EMAIL', getenv('PAGSEGURO_EMAIL') ?: '');

// ── Stripe (preparado, não ativo) ─────────────────────────────────────
define('STRIPE_SECRET_KEY',     getenv('STRIPE_SECRET_KEY')     ?: '');
define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET') ?: '');

// ── URL base da aplicação (usada para montar back_urls e webhook) ─────
// Em produção, defina a variável de ambiente APP_BASE_URL no painel do hosting.
// Se não definida, é calculada automaticamente a partir do host atual.
if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', getenv('APP_BASE_URL') ?: (defined('APP_PUBLIC_URL') ? APP_PUBLIC_URL : 'https://seudominio.com.br/afro_essencia'));
}
