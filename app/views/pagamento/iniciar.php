<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Realizar Pagamento</title>
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

    <h1 class="page-title">Realizar Pagamento</h1>
    <p class="page-subtitle">Confirme os dados e prossiga para o pagamento seguro</p>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-err" role="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <div class="pag-iniciar-card">

        <?php
            $imgServico = $servico['imagem'] ?? '';
            $imgPath    = '/afro_essencia/public/imagens/' . $imgServico;
            if (!empty($imgServico)):
        ?>
        <div class="pag-banner" aria-hidden="true">
            <img src="<?= htmlspecialchars($imgPath, ENT_QUOTES) ?>" alt="" class="pag-banner-img">
            <div class="pag-banner-overlay"></div>
            <span class="pag-banner-nome"><?= htmlspecialchars($servico['nome'] ?? '', ENT_QUOTES) ?></span>
        </div>
        <?php endif; ?>

        <div class="pag-head">
            <h2 class="pag-titulo">Resumo do agendamento</h2>
            <p class="pag-subtitulo">Verifique os detalhes antes de confirmar</p>
        </div>

        <div class="pag-rows-wrap">
            <div class="pag-row">
                <span class="pag-chave">Serviço</span>
                <span class="pag-row-valor"><?= htmlspecialchars($servico['nome'] ?? '—') ?></span>
            </div>
            <div class="pag-row">
                <span class="pag-chave">Profissional</span>
                <span class="pag-row-valor"><?= htmlspecialchars($nomeProfissional ?: ($agendamento['profissional'] ?? '—')) ?></span>
            </div>
            <div class="pag-row">
                <span class="pag-chave">Data</span>
                <span class="pag-row-valor"><?= date('d/m/Y', strtotime($agendamento['data_agendamento'])) ?></span>
            </div>
            <div class="pag-row">
                <span class="pag-chave">Horário</span>
                <span class="pag-row-valor"><?= substr($agendamento['hora_inicio'], 0, 5) ?></span>
            </div>
        </div>

        <div class="pag-valor-destaque">
            <span class="pag-valor-label">Total a pagar</span>
            <span class="pag-valor-num">R$ <?= number_format($servico['valor'] ?? 0, 2, ',', '.') ?></span>
        </div>

        <div class="pag-gateway-wrap">
            <div class="pag-gateway-logo" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 38 38" fill="none">
                    <rect width="38" height="38" rx="8" fill="#009EE3" opacity="0.15"/>
                    <text x="19" y="25" text-anchor="middle" font-size="13" font-weight="bold" fill="#009EE3">MP</text>
                </svg>
            </div>
            <p class="pag-gateway-texto">
                Você será redirecionado ao <strong>Mercado Pago</strong> para concluir com segurança.<br>
                Aceita <strong>PIX, cartão e boleto</strong>. O agendamento é confirmado após aprovação.
            </p>
        </div>

        <a href="/afro_essencia/pagamento/checkout/<?= (int)$agendamento['id'] ?>" class="pag-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>
            </svg>
            Pagar agora — R$ <?= number_format($servico['valor'] ?? 0, 2, ',', '.') ?>
        </a>

        <p class="pag-seguranca">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            Pagamento 100% seguro — criptografia SSL
        </p>

    </div>

</div>

<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
