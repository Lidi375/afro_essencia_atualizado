<?php
/**
 * View: avaliacao/create.php
 * Formulário de avaliação pós-atendimento
 */
$servico       = htmlspecialchars($agendamento['servico']      ?? '', ENT_QUOTES);
$profissional  = htmlspecialchars($agendamento['profissional'] ?? '', ENT_QUOTES);
$dataFormatada = !empty($agendamento['data_agendamento'])
    ? date('d/m/Y', strtotime($agendamento['data_agendamento']))
    : '';
$agendId = (int)($agendamento['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Avaliar Atendimento</title>
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

    <h1 class="page-title">Avaliar Atendimento</h1>
    <p class="page-subtitle">Sua opinião ajuda a melhorar nosso serviço</p>

    <?php if ($servico || $profissional || $dataFormatada): ?>
    <div class="av-resumo">
        <p class="av-resumo-head">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            Resumo do atendimento
        </p>
        <?php if ($servico): ?>
        <div class="av-row"><span class="av-chave">Serviço</span><span class="av-val"><?= $servico ?></span></div>
        <?php endif; ?>
        <?php if ($profissional): ?>
        <div class="av-row"><span class="av-chave">Profissional</span><span class="av-val"><?= $profissional ?></span></div>
        <?php endif; ?>
        <?php if ($dataFormatada): ?>
        <div class="av-row"><span class="av-chave">Data</span><span class="av-val"><?= $dataFormatada ?></span></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-err" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES) ?></div>
    <?php endif; ?>

    <form method="POST" action="/afro_essencia/avaliacao/store" novalidate>
        <input type="hidden" name="csrf_token"     value="<?= csrfToken() ?>">
        <input type="hidden" name="agendamento_id" value="<?= $agendId ?>">
        <input type="hidden" name="nota"           id="nota-hidden" value="">

        <!-- Estrelas -->
        <div class="page-card av-estrelas-card">
            <p class="av-estrelas-label">Qual nota você dá ao atendimento?</p>
            <div class="av-estrelas" id="star-rating" role="radiogroup" aria-label="Nota de 1 a 5 estrelas">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <button
                    type="button"
                    class="av-star"
                    data-val="<?= $i ?>"
                    role="radio"
                    aria-checked="false"
                    aria-label="<?= $i ?> estrela<?= $i > 1 ? 's' : '' ?>"
                    tabindex="<?= $i === 1 ? '0' : '-1' ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </button>
                <?php endfor; ?>
            </div>
            <p class="av-estrelas-texto" id="av-texto" aria-live="polite">Toque em uma estrela para avaliar</p>
        </div>

        <!-- Comentário -->
        <div class="page-card" style="margin-top: 12px;">
            <label class="av-label" for="comentario">
                Comentário
                <span class="av-label-opt">(opcional)</span>
            </label>
            <textarea
                id="comentario"
                name="comentario"
                class="av-textarea"
                placeholder="Conte como foi o atendimento, o ambiente, o resultado..."
                maxlength="500"
                rows="4"><?= htmlspecialchars($comentario ?? '', ENT_QUOTES) ?></textarea>
            <p class="av-char-count"><span id="av-chars">0</span>/500</p>
        </div>

        <button type="submit" class="btn-primary av-submit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            Enviar Avaliação
        </button>

    </form>

</div>

<script src="/afro_essencia/public/js/pages/avaliacao-create.js"></script>
<script src="/afro_essencia/public/js/global/alerts.js" defer></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
