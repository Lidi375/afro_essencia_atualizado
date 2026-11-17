<?php
/**
 * View: profissional/profile.php
 * Perfil público de um profissional
 */
$nome          = htmlspecialchars($profissional['nome']          ?? 'Profissional', ENT_QUOTES);
$bio           = htmlspecialchars($profissional['bio']           ?? '', ENT_QUOTES);
$especialidade = htmlspecialchars($profissional['especialidade'] ?? '', ENT_QUOTES);
$foto          = !empty($profissional['foto']) ? htmlspecialchars($profissional['foto'], ENT_QUOTES) : null;
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
    <link rel="stylesheet" href="/afro_essencia/public/css/profissional.css">
</head>
<body>

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

<div class="profile-wrap">

    <nav class="profile-breadcrumb" aria-label="Navegação de caminho">
        <a href="/afro_essencia/home">Início</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page"><?= $nome ?></span>
    </nav>

    <div class="profile-header">
        <div class="profile-avatar" aria-hidden="true">
            <?php if ($foto): ?>
                <img src="<?= $foto ?>" alt="Foto de <?= $nome ?>">
            <?php else: ?>
                ✂️
            <?php endif; ?>
        </div>
        <div class="profile-meta">
            <h1 class="profile-name"><?= $nome ?></h1>
            <?php if ($especialidade): ?>
                <p class="profile-especialidade">✦ <?= $especialidade ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($bio): ?>
        <p class="profile-bio"><?= nl2br($bio) ?></p>
    <?php endif; ?>

    <?php if (!empty($avaliacoes)): ?>
        <h2 class="reviews-title">
            Avaliações <span class="count">(<?= count($avaliacoes) ?>)</span>
        </h2>
        <div>
            <?php foreach (array_slice($avaliacoes, 0, 5) as $av): ?>
            <article class="review-card">
                <div class="review-stars" aria-label="Nota: <?= (int)($av['nota'] ?? 0) ?> de 5">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= (int)($av['nota'] ?? 0)): ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#d4a96a" class="star-filled"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <?php else: ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(212,169,106,.25)" stroke-width="1.5" class="star-empty"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php if (!empty($av['comentario'])): ?>
                    <p class="review-comment">"<?= htmlspecialchars($av['comentario'], ENT_QUOTES) ?>"</p>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="profile-cta">
        <a href="/afro_essencia/auth/login" class="btn-agendar" aria-label="Agendar com <?= $nome ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            Agendar com <?= $nome ?>
        </a>
    </div>

</div>

<footer class="home-footer">
    <div class="footer-brand">
        <img src="/afro_essencia/public/imagens/logo.png" alt="Logo">
        <span>AFRO ESSÊNCIA</span>
    </div>
    <p class="footer-copy">© 2026 AFRO ESSÊNCIA · Beleza com identidade e cuidado.</p>
</footer>

<script src="/afro_essencia/public/js/pages/home.js"></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
