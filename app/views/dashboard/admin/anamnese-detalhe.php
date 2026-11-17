<?php
$pageTitle  = 'Detalhe da Anamnese';
$activePage = 'anamneses';
require __DIR__ . '/../partials/_admin-header.php';

$labelsObj   = ['hidratar' => 'Hidratar e nutrir', 'crescimento' => 'Crescimento saudável', 'definir' => 'Definir os cachos', 'recuperar' => 'Recuperação de danos', 'estilo' => 'Mudar o estilo', 'manutencao' => 'Manutenção geral'];
$labelsTipo  = ['liso' => 'Liso', 'ondulado' => 'Ondulado', 'cacheado' => 'Cacheado', 'crespo' => 'Crespo'];
$labelsHid   = ['seco' => 'Seco', 'normal' => 'Normal', 'oleoso' => 'Oleoso', 'misto' => 'Misto'];
$labelsComp  = ['curto' => 'Curto', 'medio' => 'Médio', 'longo' => 'Longo', 'muito_longo' => 'Muito Longo'];
$labelsDens  = ['fina' => 'Fina', 'media' => 'Média', 'grossa' => 'Grossa'];
$labelsLav   = ['diaria' => 'Diária', '2x_semana' => '2x/semana', '3x_semana' => '3x/semana', 'semanal' => '1x/semana', 'quinzenal' => 'Quinzenal'];
$labelsCalor = ['diario' => 'Diário', '2x_semana' => '2-3x/semana', 'semanal' => '1x/semana', 'raramente' => 'Raramente'];

$val   = fn($k) => $anamnese[$k] ?? null;
$fmt   = fn($k) => htmlspecialchars($anamnese[$k] ?? '');
$vazio = fn($k) => empty($anamnese[$k]);
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <a href="/afro_essencia/dashboard/anamnese">Anamneses</a>
            <i data-lucide="chevron-right"></i>
            <span>Detalhe</span>
        </nav>
        <h1 class="subpage-title">Ficha de <?= htmlspecialchars($anamnese['cliente_nome']) ?></h1>
        <p class="subpage-subtitle"><?= htmlspecialchars($anamnese['cliente_email']) ?></p>
    </div>
    <div class="subpage-actions">
        <a href="/afro_essencia/dashboard/anamnese" class="btn-outline">
            <i data-lucide="arrow-left"></i>
            Voltar à lista
        </a>
    </div>
</div>

<!-- Cards rápidos de resumo -->
<div class="subpage-summary">
    <div class="summary-card">
        <div class="summary-icon gold"><i data-lucide="sparkles"></i></div>
        <div class="summary-body">
            <span class="summary-label">Tipo</span>
            <span class="summary-value" style="font-size:1.1rem">
                <?= $vazio('tipo_cabelo') ? '—' : ($labelsTipo[$val('tipo_cabelo')] ?? $fmt('tipo_cabelo')) ?>
            </span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon blue"><i data-lucide="droplets"></i></div>
        <div class="summary-body">
            <span class="summary-label">Condição</span>
            <span class="summary-value" style="font-size:1.1rem">
                <?= $vazio('nivel_hidratacao') ? '—' : ($labelsHid[$val('nivel_hidratacao')] ?? $fmt('nivel_hidratacao')) ?>
            </span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon terra"><i data-lucide="ruler"></i></div>
        <div class="summary-body">
            <span class="summary-label">Comprimento</span>
            <span class="summary-value" style="font-size:1.1rem">
                <?= $vazio('comprimento') ? '—' : ($labelsComp[$val('comprimento')] ?? $fmt('comprimento')) ?>
            </span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon green"><i data-lucide="target"></i></div>
        <div class="summary-body">
            <span class="summary-label">Objetivo</span>
            <span class="summary-value" style="font-size:1rem;line-height:1.3">
                <?= $vazio('objetivo') ? '—' : ($labelsObj[$val('objetivo')] ?? $fmt('objetivo')) ?>
            </span>
        </div>
    </div>
</div>

<!-- Painel principal com todos os dados -->
<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="file-heart"></i>
            Identificação Capilar
        </h3>
    </div>
    <div class="adm-ficha-grid">
        <?php
        $itens = [
            ['Tipo de Cabelo',   $vazio('tipo_cabelo')    ? '—' : ($labelsTipo[$val('tipo_cabelo')] ?? $fmt('tipo_cabelo'))],
            ['Subtipo',          $vazio('subtipo_cabelo') ? '—' : $fmt('subtipo_cabelo')],
            ['Condição do Fio',  $vazio('nivel_hidratacao') ? '—' : ($labelsHid[$val('nivel_hidratacao')] ?? $fmt('nivel_hidratacao'))],
            ['Comprimento',      $vazio('comprimento')    ? '—' : ($labelsComp[$val('comprimento')] ?? $fmt('comprimento'))],
            ['Espessura do Fio', $vazio('densidade')      ? '—' : ($labelsDens[$val('densidade')] ?? $fmt('densidade'))],
        ];
        foreach ($itens as [$chave, $valor]):
        ?>
            <div class="adm-ficha-item">
                <span class="adm-ficha-chave"><?= htmlspecialchars($chave ?? '', ENT_QUOTES) ?></span>
                <span class="adm-ficha-valor"><?= htmlspecialchars($valor ?? '', ENT_QUOTES) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="flask-conical"></i>
            Histórico Químico
        </h3>
    </div>
    <div class="adm-ficha-grid">
        <div class="adm-ficha-item">
            <span class="adm-ficha-chave">Usa Química</span>
            <span class="adm-ficha-valor">
                <?php if ($anamnese['usa_quimica']): ?>
                    <span class="badge badge-concluido">Sim</span>
                <?php else: ?>
                    <span class="badge" style="background:var(--ds-warm-200);color:var(--ds-text-muted)">Não</span>
                <?php endif; ?>
            </span>
        </div>
        <?php if ($anamnese['usa_quimica']): ?>
            <div class="adm-ficha-item">
                <span class="adm-ficha-chave">Tipo de Química</span>
                <span class="adm-ficha-valor"><?= $vazio('tipo_quimica') ? '—' : $fmt('tipo_quimica') ?></span>
            </div>
            <div class="adm-ficha-item">
                <span class="adm-ficha-chave">Última Aplicação</span>
                <span class="adm-ficha-valor"><?= $vazio('ultima_quimica') ? '—' : $fmt('ultima_quimica') ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="droplets"></i>
            Hábitos de Cuidado
        </h3>
    </div>
    <div class="adm-ficha-grid">
        <?php
        $itens2 = [
            ['Frequência de Lavagem', $vazio('frequencia_lavagem') ? '—' : ($labelsLav[$val('frequencia_lavagem')] ?? $fmt('frequencia_lavagem'))],
        ];
        foreach ($itens2 as [$chave, $valor]):
        ?>
            <div class="adm-ficha-item">
                <span class="adm-ficha-chave"><?= htmlspecialchars($chave ?? '', ENT_QUOTES) ?></span>
                <span class="adm-ficha-valor"><?= htmlspecialchars($valor ?? '', ENT_QUOTES) ?></span>
            </div>
        <?php endforeach; ?>

        <div class="adm-ficha-item">
            <span class="adm-ficha-chave">Usa Calor</span>
            <span class="adm-ficha-valor">
                <?php if ($anamnese['usa_calor']): ?>
                    <span class="badge badge-em_andamento">Sim — <?= $labelsCalor[$val('frequencia_calor')] ?? $fmt('frequencia_calor') ?></span>
                <?php else: ?>
                    <span class="badge" style="background:var(--ds-warm-200);color:var(--ds-text-muted)">Não</span>
                <?php endif; ?>
            </span>
        </div>

        <div class="adm-ficha-item">
            <span class="adm-ficha-chave">Problemas no Couro</span>
            <span class="adm-ficha-valor"><?= $vazio('problemas_couro') ? '—' : $fmt('problemas_couro') ?></span>
        </div>

        <div class="adm-ficha-item adm-ficha-item--full">
            <span class="adm-ficha-chave">Alergias</span>
            <span class="adm-ficha-valor"><?= $vazio('alergias') ? 'Nenhuma informada' : $fmt('alergias') ?></span>
        </div>
    </div>
</div>

<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="target"></i>
            Objetivo e Expectativas
        </h3>
    </div>
    <div class="adm-ficha-grid">
        <div class="adm-ficha-item">
            <span class="adm-ficha-chave">Objetivo Principal</span>
            <span class="adm-ficha-valor"><?= $vazio('objetivo') ? '—' : ($labelsObj[$val('objetivo')] ?? $fmt('objetivo')) ?></span>
        </div>
        <div class="adm-ficha-item adm-ficha-item--full">
            <span class="adm-ficha-chave">Serviços de Interesse</span>
            <span class="adm-ficha-valor"><?= $vazio('servicos_interesse') ? '—' : $fmt('servicos_interesse') ?></span>
        </div>
        <div class="adm-ficha-item adm-ficha-item--full">
            <span class="adm-ficha-chave">Observações</span>
            <span class="adm-ficha-valor"><?= $vazio('observacoes') ? 'Nenhuma' : $fmt('observacoes') ?></span>
        </div>
    </div>
</div>

<!-- Data -->
<?php if (!empty($anamnese['updated_at'])): ?>
    <p style="text-align:center;font-size: 14px;color:var(--ds-text-subtle);margin-top:4px;margin-bottom:20px">
        Última atualização: <?= date('d/m/Y \à\s H:i', strtotime($anamnese['updated_at'])) ?>
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../partials/_admin-footer.php'; ?>
