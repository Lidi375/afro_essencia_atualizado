<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | <?= $pageTitle ?? 'Painel Administrativo' ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="/afro_essencia/public/css/dashboard-admin.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/dashboard-admin-subpages.css">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css): ?>
            <link rel="stylesheet" href="/afro_essencia/public/css/<?= htmlspecialchars($css, ENT_QUOTES) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<a href="#adm-content" class="skip-link">Ir para o conteúdo</a>

<div class="adm-layout">

    <aside class="adm-sidebar">
        <div class="sidebar-logo">
            <img src="/afro_essencia/public/imagens/logo.png" alt="AFRO ESSÊNCIA" id="logo-desenho">
            <div class="logo">AFRO ESSÊNCIA</div>
        </div>

        <nav class="sidebar-nav">
            <a href="/afro_essencia/dashboard" class="nav-item <?= ($activePage ?? '') === 'dashboard'     ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
            </a>
            <a href="/afro_essencia/dashboard/agendamentos" class="nav-item <?= ($activePage ?? '') === 'agendamentos'  ? 'active' : '' ?>">
                <i data-lucide="calendar-days"></i><span>Agendamentos</span>
            </a>
            <a href="/afro_essencia/dashboard/clientes" class="nav-item <?= ($activePage ?? '') === 'clientes'      ? 'active' : '' ?>">
                <i data-lucide="users"></i><span>Clientes</span>
            </a>
            <a href="/afro_essencia/dashboard/profissionais" class="nav-item <?= ($activePage ?? '') === 'profissionais' ? 'active' : '' ?>">
                <i data-lucide="scissors"></i><span>Profissionais</span>
            </a>
            <a href="/afro_essencia/dashboard/candidaturas" class="nav-item <?= ($activePage ?? '') === 'candidaturas'  ? 'active' : '' ?>">
                <i data-lucide="briefcase"></i>
                <span>Trabalhe Conosco</span>
                <?php
                // Badge de candidaturas pendentes
                try {
                    $dao = new CandidaturaDAO();
                    $pendentes = $dao->countByStatus('pendente');
                    if ($pendentes > 0): ?>
                        <span class="nav-badge"><?= min($pendentes, 99) ?></span>
                    <?php endif;
                } catch (Throwable $e) { /* silencioso se tabela ainda não existe */ }
                ?>
            </a>
            <a href="/afro_essencia/dashboard/servicos" class="nav-item <?= ($activePage ?? '') === 'servicos'      ? 'active' : '' ?>">
                <i data-lucide="sparkles"></i><span>Serviços</span>
            </a>
            <a href="/afro_essencia/dashboard/usuarios" class="nav-item <?= ($activePage ?? '') === 'usuarios'      ? 'active' : '' ?>">
                <i data-lucide="shield-check"></i><span>Usuários</span>
            </a>
            <a href="/afro_essencia/dashboard/avaliacoes" class="nav-item <?= ($activePage ?? '') === 'avaliacoes'    ? 'active' : '' ?>">
                <i data-lucide="star"></i><span>Avaliações</span>
            </a>
            <a href="/afro_essencia/dashboard/pagamentos" class="nav-item <?= ($activePage ?? '') === 'pagamentos'    ? 'active' : '' ?>">
                <i data-lucide="credit-card"></i><span>Pagamentos</span>
            </a>
            <a href="/afro_essencia/financeiro" class="nav-item <?= in_array(($activePage ?? ''), ['financeiro'], true) ? 'active' : '' ?>">
                <i data-lucide="coins"></i><span>Financeiro</span>
            </a>
            <a href="/afro_essencia/dashboard/galeria" class="nav-item <?= ($activePage ?? '') === 'galeria'       ? 'active' : '' ?>">
                <i data-lucide="image"></i><span>Galeria</span>
            </a>
            <a href="/afro_essencia/dashboard/anamnese" class="nav-item <?= ($activePage ?? '') === 'anamneses'     ? 'active' : '' ?>">
                <i data-lucide="file-heart"></i><span>Anamneses</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <i data-lucide="user-circle"></i>
                <div>
                    <p class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user']['nome'] ?? 'Admin') ?></p>
                    <p class="sidebar-user-role">Administrador</p>
                </div>
            </div>
            <a href="/afro_essencia/auth/logout" class="btn-logout">
                <i data-lucide="log-out"></i>
                Sair
            </a>
        </div>
    </aside>

    <!-- Overlay mobile -->
    <div class="adm-overlay" id="adm-overlay"></div>

    <!-- Hamburger mobile -->
    <button class="adm-hamburger" id="adm-hamburger" aria-label="Menu" aria-expanded="false">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M3 12h18M3 6h18M3 18h18"/>
        </svg>
    </button>

    <main class="adm-main" id="adm-content">
