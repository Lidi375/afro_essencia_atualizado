<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Minhas Avaliações</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/afro_essencia/public/css/page-layout.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/avaliacao.css">
</head>
<body>

<div class="page-container">

    <header class="page-header">
        <a href="/afro_essencia/dashboard" class="page-brand">
            <img src="/afro_essencia/public/imagens/logo.png" alt="AFRO ESSÊNCIA">
            <span class="page-brand-name">AFRO ESSÊNCIA</span>
        </a>
        <a href="/afro_essencia/auth/logout" class="page-logout">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Sair
        </a>
    </header>

    <a href="/afro_essencia/dashboard" class="page-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Voltar ao painel
    </a>

    <h1 class="page-title">Minhas Avaliações</h1>
    <p class="page-subtitle">Avaliações que você enviou sobre os atendimentos</p>

    <?php if (empty($avaliacoes)): ?>

        <div class="empty-state">
            <div class="empty-state-icon" aria-hidden="true">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                          fill="none" stroke="#CBB8A6" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3>Nenhuma avaliação ainda</h3>
            <p>Após concluir um atendimento você poderá<br>avaliar as profissionais aqui.</p>
            <a href="/afro_essencia/agendamento/index" class="btn-primary">Ver agendamentos</a>
        </div>

    <?php else: ?>

        <?php foreach ($avaliacoes as $av):
            $nota = (int)($av['nota'] ?? 0);
            $labels = ['', 'Péssimo', 'Ruim', 'Regular', 'Bom', 'Excelente'];
            $notaLabel = $labels[$nota] ?? '';
        ?>
        <article class="page-card mv-card">

            <div class="mv-top">
                <div class="mv-prof-wrap">
                    <div class="mv-avatar" aria-hidden="true">
                        <?= mb_strtoupper(mb_substr($av['profissional_nome'] ?? 'P', 0, 1)) ?>
                    </div>
                    <div>
                        <p class="mv-prof"><?= htmlspecialchars($av['profissional_nome'] ?? 'Profissional') ?></p>
                        <?php if (!empty($av['servico_nome'])): ?>
                            <p class="mv-servico"><?= htmlspecialchars($av['servico_nome']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <time class="mv-data" datetime="<?= $av['created_at'] ?? '' ?>">
                    <?= !empty($av['created_at']) ? date('d/m/Y', strtotime($av['created_at'])) : '' ?>
                </time>
            </div>

            <div class="mv-nota-wrap">
                <div class="mv-stars" aria-label="Nota: <?= $nota ?> de 5">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                        <?php if ($i <= $nota): ?>
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="#D4A96A"/>
                        <?php else: ?>
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="none" stroke="#CBB8A6" stroke-width="1.5"/>
                        <?php endif; ?>
                    </svg>
                    <?php endfor; ?>
                </div>
                <?php if ($notaLabel): ?>
                <span class="mv-nota-badge mv-nota-badge--<?= $nota >= 4 ? 'bom' : ($nota >= 3 ? 'medio' : 'ruim') ?>">
                    <?= $notaLabel ?>
                </span>
                <?php endif; ?>
            </div>

            <?php if (!empty($av['comentario'])): ?>
            <blockquote class="mv-comentario">
                "<?= htmlspecialchars($av['comentario']) ?>"
            </blockquote>
            <?php endif; ?>

        </article>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
