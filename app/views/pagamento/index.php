<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Meus Pagamentos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/afro_essencia/public/css/page-layout.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/pagamento.css">
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

    <a href="/afro_essencia/agendamento/index" class="page-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Voltar aos agendamentos
    </a>

    <h1 class="page-title">Meus Pagamentos</h1>
    <p class="page-subtitle">Histórico e situação das suas transações</p>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-ok"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>
    <?php if (!empty($erro)): ?>
        <div class="alert alert-err"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php
    $statusMap = [
        'approved'  => ['label' => 'Aprovado',   'class' => 'badge-approved'],
        'pending'   => ['label' => 'Pendente',    'class' => 'badge-pending'],
        'rejected'  => ['label' => 'Recusado',    'class' => 'badge-rejected'],
        'cancelled' => ['label' => 'Cancelado',   'class' => 'badge-cancelled'],
        'refunded'  => ['label' => 'Reembolsado', 'class' => 'badge-refunded'],
    ];
    ?>

    <?php if (empty($transacoes)): ?>

        <div class="empty-state">
            <span class="empty-state-icon">💳</span>
            <h3>Nenhum pagamento ainda</h3>
            <p>Seus pagamentos aparecerão aqui após<br>concluir um agendamento.</p>
            <a href="/afro_essencia/agendamento/create" class="btn-primary">Agendar serviço</a>
        </div>

    <?php else: ?>

        <?php foreach ($transacoes as $t):
            $sl = $statusMap[$t['status']] ?? ['label' => ucfirst($t['status']), 'class' => 'badge-pending'];
        ?>
        <article class="page-card pag-card">
            <div class="pag-info">
                <p class="pag-servico"><?= htmlspecialchars($t['servico_nome'] ?? '—') ?></p>
                <p class="pag-detalhe">
                    <?php if (!empty($t['data_agendamento'])): ?>
                        <time datetime="<?= htmlspecialchars($t['data_agendamento']) ?>">
                            <?= date('d/m/Y', strtotime($t['data_agendamento'])) ?>
                        </time>
                    <?php endif; ?>
                    <?= !empty($t['hora_inicio'])       ? ' às ' . substr($t['hora_inicio'], 0, 5) : '' ?>
                    <?= !empty($t['profissional_nome']) ? ' · ' . htmlspecialchars($t['profissional_nome']) : '' ?>
                </p>
                <div class="pag-badge-row">
                    <span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span>
                    <?php if (!empty($t['gateway'])): ?>
                        <span class="pag-gateway"><?= htmlspecialchars(ucfirst($t['gateway'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="pag-valor">R$ <?= number_format((float)($t['amount'] ?? 0), 2, ',', '.') ?></div>
        </article>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
