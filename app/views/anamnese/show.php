<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Minha Ficha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/afro_essencia/public/css/page-layout.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/anamnese.css">
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

    <a href="/afro_essencia/dashboard" class="link-voltar">
        <i data-lucide="arrow-left"></i>
        Voltar ao painel
    </a>

    <h1 class="page-title">MINHA FICHA DE ANAMNESE</h1>
    <p class="page-subtitle">Confira as informações registradas sobre o seu cabelo</p>

    <?php if (!empty($sucesso)): ?>
        <div class="alert-success" role="alert"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <?php
    $val   = fn($k) => $anamnese[$k] ?? null;
    $fmt   = fn($k) => htmlspecialchars($anamnese[$k] ?? '');
    $vazio = fn($k) => empty($anamnese[$k]);

    $labelsTipo  = ['liso' => 'Liso', 'ondulado' => 'Ondulado', 'cacheado' => 'Cacheado', 'crespo' => 'Crespo'];
    $labelsHid   = ['seco' => 'Seco', 'normal' => 'Normal', 'oleoso' => 'Oleoso', 'misto' => 'Misto'];
    $labelsComp  = ['curto' => 'Curto', 'medio' => 'Médio', 'longo' => 'Longo', 'muito_longo' => 'Muito Longo'];
    $labelsDens  = ['fina' => 'Fina', 'media' => 'Média', 'grossa' => 'Grossa'];
    $labelsLav   = ['diaria' => 'Diária', '2x_semana' => '2x por semana', '3x_semana' => '3x por semana', 'semanal' => '1x por semana', 'quinzenal' => 'Quinzenal'];
    $labelsObj   = ['hidratar' => 'Hidratar e nutrir', 'crescimento' => 'Crescimento saudável', 'definir' => 'Definir os cachos', 'recuperar' => 'Recuperação de danos', 'estilo' => 'Mudar o estilo', 'manutencao' => 'Manutenção geral'];
    $labelsCalor = ['diario' => 'Diário', '2x_semana' => '2-3x por semana', 'semanal' => '1x por semana', 'raramente' => 'Raramente'];
    ?>

    <!-- ════ SEÇÃO 1: Identificação Capilar ════ -->
    <div class="anamnese-section">
        <h2 class="section-heading">
            <i data-lucide="sparkles"></i>
            Identificação Capilar
        </h2>
        <div class="ficha-item">
            <span class="ficha-chave">Tipo de Cabelo</span>
            <span class="ficha-valor <?= $vazio('tipo_cabelo') ? 'vazio' : '' ?>">
                <?= $vazio('tipo_cabelo') ? 'Não informado' : ($labelsTipo[$val('tipo_cabelo')] ?? $fmt('tipo_cabelo')) ?>
            </span>
        </div>
        <div class="ficha-item">
            <span class="ficha-chave">Subtipo</span>
            <span class="ficha-valor <?= $vazio('subtipo_cabelo') ? 'vazio' : '' ?>">
                <?= $vazio('subtipo_cabelo') ? 'Não informado' : $fmt('subtipo_cabelo') ?>
            </span>
        </div>
        <div class="ficha-item">
            <span class="ficha-chave">Condição do Fio</span>
            <span class="ficha-valor <?= $vazio('nivel_hidratacao') ? 'vazio' : '' ?>">
                <?= $vazio('nivel_hidratacao') ? 'Não informado' : ($labelsHid[$val('nivel_hidratacao')] ?? $fmt('nivel_hidratacao')) ?>
            </span>
        </div>
        <div class="ficha-item">
            <span class="ficha-chave">Comprimento</span>
            <span class="ficha-valor <?= $vazio('comprimento') ? 'vazio' : '' ?>">
                <?= $vazio('comprimento') ? 'Não informado' : ($labelsComp[$val('comprimento')] ?? $fmt('comprimento')) ?>
            </span>
        </div>
        <div class="ficha-item">
            <span class="ficha-chave">Espessura do Fio</span>
            <span class="ficha-valor <?= $vazio('densidade') ? 'vazio' : '' ?>">
                <?= $vazio('densidade') ? 'Não informado' : ($labelsDens[$val('densidade')] ?? $fmt('densidade')) ?>
            </span>
        </div>
    </div>

    <!-- ════ SEÇÃO 2: Histórico Químico ════ -->
    <div class="anamnese-section">
        <h2 class="section-heading">
            <i data-lucide="flask-conical"></i>
            Histórico Químico
        </h2>
        <div class="ficha-item">
            <span class="ficha-chave">Usa Química</span>
            <span class="ficha-valor">
                <?php if ($anamnese['usa_quimica']): ?>
                    <span class="badge-sim">Sim</span>
                <?php else: ?>
                    <span class="badge-nao">Não</span>
                <?php endif; ?>
            </span>
        </div>
        <?php if ($anamnese['usa_quimica']): ?>
            <div class="ficha-item">
                <span class="ficha-chave">Tipo de Química</span>
                <span class="ficha-valor <?= $vazio('tipo_quimica') ? 'vazio' : '' ?>">
                    <?= $vazio('tipo_quimica') ? 'Não informado' : $fmt('tipo_quimica') ?>
                </span>
            </div>
            <div class="ficha-item">
                <span class="ficha-chave">Última Aplicação</span>
                <span class="ficha-valor <?= $vazio('ultima_quimica') ? 'vazio' : '' ?>">
                    <?= $vazio('ultima_quimica') ? 'Não informado' : $fmt('ultima_quimica') ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- ════ SEÇÃO 3: Hábitos de Cuidado ════ -->
    <div class="anamnese-section">
        <h2 class="section-heading">
            <i data-lucide="droplets"></i>
            Hábitos de Cuidado
        </h2>
        <div class="ficha-item">
            <span class="ficha-chave">Frequência de Lavagem</span>
            <span class="ficha-valor <?= $vazio('frequencia_lavagem') ? 'vazio' : '' ?>">
                <?= $vazio('frequencia_lavagem') ? 'Não informado' : ($labelsLav[$val('frequencia_lavagem')] ?? $fmt('frequencia_lavagem')) ?>
            </span>
        </div>
        <div class="ficha-item">
            <span class="ficha-chave">Usa Calor</span>
            <span class="ficha-valor">
                <?php if ($anamnese['usa_calor']): ?>
                    <span class="badge-sim">Sim</span>
                <?php else: ?>
                    <span class="badge-nao">Não</span>
                <?php endif; ?>
            </span>
        </div>
        <?php if ($anamnese['usa_calor'] && !$vazio('frequencia_calor')): ?>
            <div class="ficha-item">
                <span class="ficha-chave">Frequência do Calor</span>
                <span class="ficha-valor"><?= $labelsCalor[$val('frequencia_calor')] ?? $fmt('frequencia_calor') ?></span>
            </div>
        <?php endif; ?>
        <div class="ficha-item">
            <span class="ficha-chave">Problemas no Couro</span>
            <span class="ficha-valor <?= $vazio('problemas_couro') ? 'vazio' : '' ?>">
                <?= $vazio('problemas_couro') ? 'Nenhum informado' : $fmt('problemas_couro') ?>
            </span>
        </div>
        <div class="ficha-item">
            <span class="ficha-chave">Alergias</span>
            <span class="ficha-valor <?= $vazio('alergias') ? 'vazio' : '' ?>">
                <?= $vazio('alergias') ? 'Nenhuma informada' : $fmt('alergias') ?>
            </span>
        </div>
    </div>

    <!-- ════ SEÇÃO 4: Objetivo ════ -->
    <div class="anamnese-section">
        <h2 class="section-heading">
            <i data-lucide="target"></i>
            Objetivo e Expectativas
        </h2>
        <div class="ficha-item">
            <span class="ficha-chave">Objetivo Principal</span>
            <span class="ficha-valor <?= $vazio('objetivo') ? 'vazio' : '' ?>">
                <?= $vazio('objetivo') ? 'Não informado' : ($labelsObj[$val('objetivo')] ?? $fmt('objetivo')) ?>
            </span>
        </div>
        <div class="ficha-item">
            <span class="ficha-chave">Serviços de Interesse</span>
            <span class="ficha-valor <?= $vazio('servicos_interesse') ? 'vazio' : '' ?>">
                <?= $vazio('servicos_interesse') ? 'Não informado' : $fmt('servicos_interesse') ?>
            </span>
        </div>
        <div class="ficha-item">
            <span class="ficha-chave">Observações</span>
            <span class="ficha-valor <?= $vazio('observacoes') ? 'vazio' : '' ?>">
                <?= $vazio('observacoes') ? 'Nenhuma' : $fmt('observacoes') ?>
            </span>
        </div>
    </div>

    <a href="/afro_essencia/anamnese/form" class="btn-editar">
        <i data-lucide="pencil" style="width:13px;height:13px;vertical-align:middle;margin-right:6px"></i>
        Editar Ficha
    </a>

    <?php if (!empty($anamnese['updated_at'])): ?>
        <p class="ficha-data">
            Última atualização: <?= date('d/m/Y \à\s H:i', strtotime($anamnese['updated_at'])) ?>
        </p>
    <?php endif; ?>

</div>

<script src="/afro_essencia/public/js/pages/anamnese.js" defer></script>
<script src="/afro_essencia/public/js/global/alerts.js" defer></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
