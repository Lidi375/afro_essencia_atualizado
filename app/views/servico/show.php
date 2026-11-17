<?php
/**
 * View: servico/show.php
 * Rota: /servico/show/{slug}
 * Dados: $servico — array completo do ServicoDAO::findBySlug()
 */

$nome       = htmlspecialchars($servico['nome']             ?? '', ENT_QUOTES);
$descricao  = htmlspecialchars($servico['descricao']        ?? '', ENT_QUOTES);
$beneficios = $servico['beneficios']          ?? '';
$tempoDur   = htmlspecialchars($servico['tempo_duracao_texto'] ?? '', ENT_QUOTES);
$manutencao = htmlspecialchars($servico['manutencao']       ?? '', ENT_QUOTES);
$cuidados   = $servico['cuidados_pos_servico'] ?? '';
$valor      = number_format((float)($servico['valor'] ?? 0), 2, ',', '.');
$durMin     = (int)($servico['duracao_minutos'] ?? 0);
$categoria  = htmlspecialchars($servico['categoria']        ?? '', ENT_QUOTES);
$imagem     = $servico['imagem'] ?? null;
// Fallback: se imagem do BD for nula, usar imagem local pelo slug
$_imgFallbacks = [
    'box-braids'          => 'box-braids.jpg',
    'trancas-nago'        => 'nago.jpg',
    'twist'               => 'twist.jpg',
    'dreadlocks'          => 'dreadlocks.jpg',
    'hidratacao-capilar'  => 'hidratacao-capilar.jpg',
    'corte-afro'          => 'corte-afro.jpg',
    'coloracao-natural'   => 'coloracao-natural.jpg',
    'penteado-para-festa' => 'penteado-para-festa.jpg',
];
if (empty($imagem) && isset($_imgFallbacks[$slug])) {
    $imagem = $_imgFallbacks[$slug];
}
$slug       = htmlspecialchars($servico['slug'] ?? '', ENT_QUOTES);
$id         = (int)($servico['id'] ?? 0);

// ── Duração legível ──────────────────────────────────────────
if (!empty($tempoDur)) {
    $duracaoLabel = $tempoDur;
} elseif ($durMin >= 60) {
    $h = intdiv($durMin, 60);
    $m = $durMin % 60;
    $duracaoLabel = $m > 0 ? "{$h}h {$m}min" : "{$h}h";
} else {
    $duracaoLabel = $durMin > 0 ? "{$durMin} minutos" : '—';
}

// ── Botão de agendamento: destino conforme autenticação ──────
$isLogado   = isset($_SESSION['user']['id']);
$tipoUser   = $_SESSION['user']['tipo_usuario'] ?? '';
$podeAgendar = $isLogado && $tipoUser === 'cliente';

if ($podeAgendar) {
    $btnHref  = "/afro_essencia/agendamento/create?servico_id={$id}";
    $btnLabel = 'Agendar este serviço';
} elseif ($isLogado) {
    // Admin ou profissional logado — não agendamos por eles
    $btnHref  = '/afro_essencia/auth/login';
    $btnLabel = 'Agendar este serviço';
} else {
    // Visitante: salva intenção e manda para login
    $btnHref  = "/afro_essencia/auth/login?redirect=agendamento/create%3Fservico_id%3D{$id}";
    $btnLabel = 'Fazer login para agendar';
}

// ── Benefícios: transforma texto separado por ';' em lista ──
function listFromText(string $txt): array {
    if (strpos($txt, ';') !== false) {
        return array_filter(array_map('trim', explode(';', $txt)));
    }
    if (strpos($txt, "\n") !== false) {
        return array_filter(array_map('trim', explode("\n", $txt)));
    }
    return [$txt];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | <?= $nome ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/afro_essencia/public/css/home.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/servico-detalhe.css">
</head>
<body>

<!-- ═══════════════════════ NAVBAR ═══════════════════════ -->
<header class="hero-header">
    <a href="/afro_essencia/home" class="hero-brand">
        <img src="/afro_essencia/public/imagens/logo.png" alt="Logo AFRO ESSÊNCIA">
        <span class="logo-text">AFRO ESSÊNCIA</span>
    </a>
    <nav class="hero-nav">
        <a href="/afro_essencia/home#servicos">Serviços</a>
        <a href="/afro_essencia/home#sobre">Sobre</a>
        <a href="/afro_essencia/home#contato">Contato</a>
        <a href="/afro_essencia/trabalheconosco">Trabalhe Conosco</a>
    </nav>
    <a href="<?= htmlspecialchars($btnHref, ENT_QUOTES) ?>" class="btn-hero">Agendar</a>
</header>

<!-- ═══════════════════════ HERO DO SERVIÇO ═══════════════════════ -->
<section class="sv-hero">
    <?php if ($imagem): ?>
        <div class="sv-hero-img" style="background-image:url('/afro_essencia/public/imagens/<?= htmlspecialchars($imagem, ENT_QUOTES) ?>')"></div>
    <?php else: ?>
        <div class="sv-hero-img sv-hero-img--default"></div>
    <?php endif; ?>

    <div class="sv-hero-overlay"></div>

    <div class="sv-hero-content">
        <!-- Breadcrumb -->
        <nav class="sv-breadcrumb" aria-label="breadcrumb">
            <a href="/afro_essencia/home">Início</a>
            <span>›</span>
            <a href="/afro_essencia/home#servicos">Serviços</a>
            <span>›</span>
            <span><?= $nome ?></span>
        </nav>

        <?php if ($categoria): ?>
            <span class="sv-eyebrow"><?= $categoria ?></span>
        <?php endif; ?>

        <h1 class="sv-title"><?= $nome ?></h1>

        <?php if ($descricao): ?>
            <p class="sv-intro"><?= $descricao ?></p>
        <?php endif; ?>

        <!-- Badges de destaque -->
        <div class="sv-badges">
            <?php if ($valor !== '0,00'): ?>
                <span class="sv-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    A partir de R$ <?= $valor ?>
                </span>
            <?php endif; ?>
            <?php if ($duracaoLabel): ?>
                <span class="sv-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <?= $duracaoLabel ?>
                </span>
            <?php endif; ?>
            <?php if ($manutencao): ?>
                <span class="sv-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                    <?= $manutencao ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════ CONTEÚDO PRINCIPAL ═══════════════════════ -->
<div class="sv-page-body">

    <div class="sv-content-grid">

        <!-- ── COLUNA PRINCIPAL ─────────────────────────────────── -->
        <main class="sv-main">

            <?php if ($beneficios): ?>
            <!-- BENEFÍCIOS -->
            <section class="sv-section">
                <span class="section-eyebrow">Por que escolher</span>
                <h2>Benefícios do serviço</h2>

                <ul class="sv-benefits-list">
                    <?php foreach (listFromText($beneficios) as $item): ?>
                        <?php if (trim($item)): ?>
                        <li>
                            <span class="sv-check">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                            </span>
                            <?= htmlspecialchars(trim($item), ENT_QUOTES) ?>
                        </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </section>
            <?php endif; ?>

            <?php if ($cuidados): ?>
            <!-- CUIDADOS PÓS-SERVIÇO -->
            <section class="sv-section sv-section--care">
                <span class="section-eyebrow">Após o serviço</span>
                <h2>Cuidados pós-serviço</h2>
                <p class="sv-care-intro">Para garantir a durabilidade e a saúde dos seus cabelos, siga as orientações abaixo:</p>

                <ul class="sv-care-list">
                    <?php foreach (listFromText($cuidados) as $item): ?>
                        <?php if (trim($item)): ?>
                        <li>
                            <span class="sv-care-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.5 2 6 4.5 6 8c0 5.25 6 14 6 14s6-8.75 6-14c0-3.5-2.5-6-6-6zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/></svg>
                            </span>
                            <span><?= htmlspecialchars(trim($item), ENT_QUOTES) ?></span>
                        </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </section>
            <?php endif; ?>

        </main>

        <!-- ── SIDEBAR LATERAL ─────────────────────────────────── -->
        <aside class="sv-sidebar">

            <!-- Card resumo -->
            <div class="sv-summary-card">
                <h3>Resumo do serviço</h3>

                <div class="sv-summary-row">
                    <span class="sv-summary-label">Serviço</span>
                    <span class="sv-summary-value"><?= $nome ?></span>
                </div>

                <?php if ($categoria): ?>
                <div class="sv-summary-row">
                    <span class="sv-summary-label">Categoria</span>
                    <span class="sv-summary-value"><?= $categoria ?></span>
                </div>
                <?php endif; ?>

                <?php if ($valor !== '0,00'): ?>
                <div class="sv-summary-row sv-summary-row--price">
                    <span class="sv-summary-label">Valor</span>
                    <span class="sv-summary-value sv-price">R$ <?= $valor ?></span>
                </div>
                <?php endif; ?>

                <?php if ($duracaoLabel): ?>
                <div class="sv-summary-row">
                    <span class="sv-summary-label">Duração</span>
                    <span class="sv-summary-value"><?= $duracaoLabel ?></span>
                </div>
                <?php endif; ?>

                <?php if ($manutencao): ?>
                <div class="sv-summary-row">
                    <span class="sv-summary-label">Manutenção</span>
                    <span class="sv-summary-value"><?= $manutencao ?></span>
                </div>
                <?php endif; ?>

                <div class="sv-summary-divider"></div>

                <a href="<?= htmlspecialchars($btnHref, ENT_QUOTES) ?>" class="sv-btn-agendar">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    <?= htmlspecialchars($btnLabel, ENT_QUOTES) ?>
                </a>

                <?php if (!$isLogado): ?>
                    <p class="sv-login-hint">Faça login ou crie sua conta gratuitamente para agendar.</p>
                <?php endif; ?>
            </div>

            <!-- Link voltar -->
            <a href="/afro_essencia/home#servicos" class="sv-back-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                Ver todos os serviços
            </a>

        </aside>

    </div><!-- /.sv-content-grid -->

</div><!-- /.sv-page-body -->


<!-- ═══════════════════════ CTA FINAL ═══════════════════════ -->
<section class="cta-section sv-cta">
    <div class="cta-inner">
        <span class="section-eyebrow">Próximo passo</span>
        <h2>Pronta para agendar<br>seu <em><?= $nome ?></em>?</h2>
        <p>Escolha o horário ideal e experimente o cuidado especializado do Afro Essência.</p>
        <a href="<?= htmlspecialchars($btnHref, ENT_QUOTES) ?>" class="btn-primary">
            <?= htmlspecialchars($btnLabel, ENT_QUOTES) ?>
        </a>
    </div>
</section>


<!-- ═══════════════════════ FOOTER ═══════════════════════ -->
<footer class="home-footer">
    <div class="footer-brand">
        <img src="/afro_essencia/public/imagens/logo.png" alt="Logo">
        <span>AFRO ESSÊNCIA</span>
    </div>
    <p class="footer-copy">© 2026 AFRO ESSÊNCIA · Beleza com identidade e cuidado.</p>
    <nav class="footer-links">
        <a href="/afro_essencia/home#servicos">Serviços</a>
        <a href="/afro_essencia/home#sobre">Sobre</a>
        <a href="/afro_essencia/home#contato">Contato</a>
        <a href="/afro_essencia/auth/login">Agendar</a>
    </nav>
</footer>

<script src="/afro_essencia/public/js/pages/home.js"></script>


<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
