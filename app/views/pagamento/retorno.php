<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Resultado do Pagamento</title>
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
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Sair
        </a>
    </header>

    <a href="/afro_essencia/agendamento/index" class="page-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Voltar aos agendamentos
    </a>

    <h1 class="page-title">Resultado do Pagamento</h1>

    <?php
        $statusMap = [
            'approved' => [
                'card_mod'    => 'ret-card--approved',
                'badge_mod'   => 'ret-badge--approved',
                'badge_label' => 'Aprovado',
                'icon'        => '<svg width="52" height="52" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="rgba(45,106,79,.12)" stroke="rgba(45,106,79,.30)" stroke-width="1.5"/><path d="M8 12l3 3 5-5" stroke="#2D6A4F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'titulo'      => 'Pagamento Aprovado!',
                'subtitulo'   => 'Seu pagamento foi confirmado com sucesso. O agendamento está garantido.',
                'btn_mod'     => 'ret-btn--success',
                'btn_label'   => 'Ver meus agendamentos',
                'btn_href'    => '/afro_essencia/agendamento/index',
            ],
            'pending' => [
                'card_mod'    => 'ret-card--pending',
                'badge_mod'   => 'ret-badge--pending',
                'badge_label' => 'Aguardando confirmação',
                'icon'        => '<svg width="52" height="52" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="rgba(139,94,0,.10)" stroke="rgba(139,94,0,.28)" stroke-width="1.5"/><path d="M12 7v5l3 3" stroke="#8B5E00" stroke-width="2" stroke-linecap="round"/></svg>',
                'titulo'      => 'Pagamento em Processamento',
                'subtitulo'   => 'Seu pagamento foi recebido e está sendo processado. Assim que confirmado você receberá uma notificação e o agendamento será garantido.',
                'btn_mod'     => 'ret-btn--warning',
                'btn_label'   => 'Ver meus pagamentos',
                'btn_href'    => '/afro_essencia/pagamento/index',
            ],
            'rejected' => [
                'card_mod'    => 'ret-card--rejected',
                'badge_mod'   => 'ret-badge--rejected',
                'badge_label' => 'Recusado',
                'icon'        => '<svg width="52" height="52" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="rgba(155,35,53,.08)" stroke="rgba(155,35,53,.28)" stroke-width="1.5"/><path d="M15 9l-6 6M9 9l6 6" stroke="#9B2335" stroke-width="2" stroke-linecap="round"/></svg>',
                'titulo'      => 'Pagamento Não Aprovado',
                'subtitulo'   => 'Seu pagamento não foi aprovado. Verifique os dados do cartão ou tente outro método de pagamento.',
                'btn_mod'     => 'ret-btn--danger',
                'btn_label'   => 'Tentar novamente',
                'btn_href'    => '/afro_essencia/pagamento/iniciar/' . ((int)($transaction['agendamento_id'] ?? 0)),
            ],
        ];

        $info = $statusMap[$status] ?? $statusMap['pending'];
    ?>

    <div class="ret-card <?= $info['card_mod'] ?>">

        <div class="ret-icon" aria-hidden="true"><?= $info['icon'] ?></div>

        <span class="ret-badge <?= $info['badge_mod'] ?>"><?= $info['badge_label'] ?></span>

        <h2 class="ret-titulo"><?= $info['titulo'] ?></h2>
        <p class="ret-subtitulo"><?= $info['subtitulo'] ?></p>

        <div class="ret-resumo">
            <p class="ret-resumo-head">Resumo da transação</p>
            <div class="ret-row">
                <span class="ret-chave">Serviço</span>
                <span class="ret-valor"><?= htmlspecialchars($transaction['servico_nome'] ?? '—') ?></span>
            </div>
            <div class="ret-row">
                <span class="ret-chave">Data</span>
                <span class="ret-valor">
                    <?= !empty($transaction['data_agendamento']) ? date('d/m/Y', strtotime($transaction['data_agendamento'])) : '—' ?>
                </span>
            </div>
            <div class="ret-row">
                <span class="ret-chave">Horário</span>
                <span class="ret-valor"><?= substr($transaction['hora_inicio'] ?? '—', 0, 5) ?></span>
            </div>
            <div class="ret-row">
                <span class="ret-chave">Valor</span>
                <span class="ret-valor ret-valor--destaque">
                    R$ <?= number_format((float)($transaction['amount'] ?? 0), 2, ',', '.') ?>
                </span>
            </div>
            <div class="ret-row">
                <span class="ret-chave">Gateway</span>
                <span class="ret-valor"><?= htmlspecialchars(ucfirst($transaction['gateway'] ?? '—')) ?></span>
            </div>
            <?php if (!empty($transaction['gateway_reference'])): ?>
            <div class="ret-row">
                <span class="ret-chave">Referência</span>
                <span class="ret-valor ret-valor--ref">
                    <?= htmlspecialchars(substr($transaction['gateway_reference'], 0, 40)) ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($status === 'pending'): ?>
        <p class="ret-aviso">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Atualizações de status chegam automaticamente via notificação do gateway.
        </p>
        <?php endif; ?>

        <a href="<?= $info['btn_href'] ?>" class="ret-btn <?= $info['btn_mod'] ?>">
            <?= $info['btn_label'] ?>
        </a>

        <?php if ($status !== 'approved'): ?>
        <a href="/afro_essencia/agendamento/index" class="ret-btn-outline">
            ← Voltar aos agendamentos
        </a>
        <?php endif; ?>

    </div>

</div>

<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
