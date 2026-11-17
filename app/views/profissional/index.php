<?php
/**
 * View: profissional/index.php
 * Lista pública de profissionais — acessível sem login
 * Dados: $profissionais (array)
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Nossas Profissionais</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/afro_essencia/public/css/home.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/profissional.css">
</head>
<body>

<!-- Header público -->
<header class="hero-header">
    <a href="/afro_essencia/home" class="hero-brand">
        <img src="/afro_essencia/public/imagens/logo.png" alt="Logo AFRO ESSÊNCIA">
        <span class="logo-text">AFRO ESSÊNCIA</span>
    </a>
    <nav class="hero-nav" aria-label="Navegação principal">
        <a href="/afro_essencia/home#servicos">Serviços</a>
        <a href="/afro_essencia/home#sobre">Sobre</a>
        <a href="/afro_essencia/home#contato">Contato</a>
    </nav>
    <a href="/afro_essencia/auth/login" class="btn-hero">Agendar</a>
</header>

<div class="prof-page">

    <nav class="prof-breadcrumb" aria-label="Caminho">
        <a href="/afro_essencia/home">Início</a>
        <span>›</span>
        <span aria-current="page">Profissionais</span>
    </nav>

    <h1 class="prof-page-title">Nossas Profissionais</h1>
    <p class="prof-page-sub">Conheça as especialistas que cuidam da sua beleza com carinho e técnica.</p>

    <?php if (empty($profissionais)): ?>
        <div class="prof-empty">
            <p>Nenhuma profissional cadastrada ainda.</p>
        </div>
    <?php else: ?>
        <div class="prof-grid">
            <?php foreach ($profissionais as $p): ?>
                <a href="/afro_essencia/profissional/profile/<?= (int)$p['id'] ?>" class="prof-card">
                    <div class="prof-card-avatar">
                        <?php if (!empty($p['foto'])): ?>
                            <img src="<?= htmlspecialchars($p['foto'], ENT_QUOTES) ?>"
                                 alt="Foto de <?= htmlspecialchars($p['nome'], ENT_QUOTES) ?>">
                        <?php else: ?>
                            ✂️
                        <?php endif; ?>
                    </div>
                    <p class="prof-card-name"><?= htmlspecialchars($p['nome']) ?></p>
                    <?php if (!empty($p['especialidade'])): ?>
                        <p class="prof-card-esp">✦ <?= htmlspecialchars($p['especialidade']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($p['bio'])): ?>
                        <p class="prof-card-bio"><?= htmlspecialchars($p['bio']) ?></p>
                    <?php endif; ?>
                    <span class="prof-card-btn">Ver perfil</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<footer class="home-footer">
    <div class="footer-brand">
        <img src="/afro_essencia/public/imagens/logo.png" alt="Logo">
        <span>AFRO ESSÊNCIA</span>
    </div>
    <p class="footer-copy">© 2026 AFRO ESSÊNCIA · Beleza com identidade e cuidado.</p>
</footer>

<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
