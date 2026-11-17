<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Notificações</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/afro_essencia/public/css/page-layout.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/notificacao.css">
</head>
<body>

<div class="page-container">

    <header class="page-header">
        <a href="/afro_essencia/dashboard" class="page-brand">
            <img src="/afro_essencia/public/imagens/logo.png" alt="AFRO ESSÊNCIA">
            <span class="page-brand-name">AFRO ESSÊNCIA</span>
        </a>
        <a href="/afro_essencia/auth/logout" class="page-logout">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sair
        </a>
    </header>

    

    <h1 class="page-title">Notificações</h1>
    <p class="page-subtitle">Seus avisos e atualizações</p>

    <?php if (empty($notificacoes)): ?>

        <div class="empty-state">
            <span class="empty-state-icon">🔔</span>
            <h3>Tudo em dia por aqui!</h3>
            <p>Você não tem notificações no momento.<br>Elas aparecerão quando houver novidades.</p>
            <a href="/afro_essencia/dashboard" class="btn-primary">← Voltar ao painel</a>
        </div>

    <?php else: ?>

        <?php foreach ($notificacoes as $n):
            $isCancelado = strpos(strtolower($n['titulo'] ?? ''), 'cancelado') !== false;
            $icone = $isCancelado ? '❌' : '✅';
        ?>
        <article class="page-card notif-card" aria-label="<?= htmlspecialchars($n['titulo']) ?>">
            <div class="notif-icon-wrap <?= $isCancelado ? 'notif-cancel' : 'notif-ok' ?>">
                <?= $icone ?>
            </div>
            <div class="notif-body">
                <p class="notif-title <?= $isCancelado ? 'text-error' : '' ?>">
                    <?= htmlspecialchars($n['titulo']) ?>
                </p>
                <p class="notif-msg"><?= htmlspecialchars($n['mensagem']) ?></p>
                <time class="notif-time" datetime="<?= htmlspecialchars($n['created_at']) ?>">
                    <?= date('d/m/Y \à\s H:i', strtotime($n['created_at'])) ?>
                </time>
            </div>
        </article>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
