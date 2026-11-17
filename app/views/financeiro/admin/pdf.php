<?php
/**
 * View: financeiro/admin/pdf.php
 * Relatório PDF para administrador — renderização A4 para impressão.
 * CSS externo: /public/css/pdf-relatorio.css
 * JS externo:  /public/js/pages/pdf-relatorio.js
 * Nota: sem biblioteca PDF via Composer — usa @media print + window.print().
 */

$totais              = $totais              ?? [];
$totaisProfissionais = $totaisProfissionais ?? [];
$comissoes           = $comissoes           ?? [];
$filtros             = $filtros             ?? [];
$dataEmissao         = $dataEmissao         ?? date('d/m/Y H:i');

function fmtBrlP(float $v): string  { return 'R$ ' . number_format($v, 2, ',', '.'); }
function fmtDateP(?string $d): string { return $d ? date('d/m/Y', strtotime($d)) : '—'; }

$periodoTexto = (!empty($filtros['data_inicio']) && !empty($filtros['data_fim']))
    ? date('d/m/Y', strtotime($filtros['data_inicio'])) . ' a ' . date('d/m/Y', strtotime($filtros['data_fim']))
    : 'Todos os períodos';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA — Relatório Financeiro</title>
    <link rel="stylesheet" href="/afro_essencia/public/css/pdf-relatorio.css">
</head>
<body>

<!-- Barra de ações -->
<div class="pdf-action-bar">
    <a href="/afro_essencia/financeiro" class="pdf-btn-back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Voltar ao Painel
    </a>
    <button class="pdf-btn-print">🖨️ Imprimir / Salvar PDF</button>
</div>

<!-- Cabeçalho -->
<div class="pdf-header">
    <div class="pdf-logo">
        <div>
            <div class="pdf-logo-text">AFRO ESSÊNCIA</div>
            <div class="pdf-logo-sub">Salão de Beleza Afro</div>
        </div>
    </div>
    <div class="pdf-meta">
        <strong>Relatório Financeiro</strong>
        Emitido em: <?= $dataEmissao ?><br>
        Gerado por: <?= htmlspecialchars($_SESSION['user']['nome'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>
    </div>
</div>

<!-- Período -->
<div class="pdf-periodo">
    Período de análise: <strong><?= htmlspecialchars($periodoTexto, ENT_QUOTES, 'UTF-8') ?></strong>
    <?php if (!empty($filtros['profissional_id'])): ?>
        &nbsp;·&nbsp; Profissional: <strong>filtrado</strong>
    <?php endif; ?>
</div>

<!-- KPIs -->
<div class="pdf-kpi-grid pdf-kpi-grid--3">
    <div class="pdf-kpi">
        <div class="pdf-kpi-label">Receita Bruta</div>
        <div class="pdf-kpi-value"><?= fmtBrlP((float)($totais['receita_total']        ?? 0)) ?></div>
    </div>
    <div class="pdf-kpi">
        <div class="pdf-kpi-label">Comissões Pagas</div>
        <div class="pdf-kpi-value"><?= fmtBrlP((float)($totais['comissoes_pagas']       ?? 0)) ?></div>
    </div>
    <div class="pdf-kpi pdf-kpi-lucro">
        <div class="pdf-kpi-label">Lucro Líquido</div>
        <div class="pdf-kpi-value"><?= fmtBrlP((float)($totais['lucro_liquido']         ?? 0)) ?></div>
    </div>
    <div class="pdf-kpi">
        <div class="pdf-kpi-label">Comissões Pendentes</div>
        <div class="pdf-kpi-value"><?= fmtBrlP((float)($totais['comissoes_pendentes']   ?? 0)) ?></div>
    </div>
    <div class="pdf-kpi">
        <div class="pdf-kpi-label">Total Atendimentos</div>
        <div class="pdf-kpi-value"><?= (int)($totais['total_atendimentos'] ?? 0) ?></div>
    </div>
    <div class="pdf-kpi">
        <div class="pdf-kpi-label">Ticket Médio</div>
        <div class="pdf-kpi-value"><?= fmtBrlP((float)($totais['ticket_medio']          ?? 0)) ?></div>
    </div>
</div>

<!-- Por profissional -->
<?php if (!empty($totaisProfissionais)): ?>
<h2>Detalhamento por Profissional</h2>
<table>
    <thead>
        <tr>
            <th>Profissional</th>
            <th>Atendimentos</th>
            <th>Receita</th>
            <th>Com. Pagas</th>
            <th>Com. Pendentes</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($totaisProfissionais as $tp): ?>
        <tr>
            <td><?= htmlspecialchars($tp['profissional_nome'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int)$tp['total_atendimentos'] ?></td>
            <td><?= fmtBrlP((float)$tp['receita_total']) ?></td>
            <td><?= fmtBrlP((float)$tp['comissoes_pagas']) ?></td>
            <td><?= fmtBrlP((float)$tp['comissoes_pendentes']) ?></td>
            <td><strong><?= fmtBrlP((float)$tp['total_comissoes']) ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- Registro de comissões -->
<?php if (!empty($comissoes)): ?>
<h2>Registro de Comissões</h2>
<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Profissional</th>
            <th>Serviço</th>
            <th>Cliente</th>
            <th>Valor</th>
            <th>Comissão</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($comissoes as $comissao): ?>
        <tr>
            <td><?= fmtDateP($comissao['data_geracao']) ?></td>
            <td><?= htmlspecialchars($comissao['profissional_nome'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($comissao['servico_nome'],      ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($comissao['cliente_nome'],      ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= fmtBrlP((float)$comissao['valor_servico']) ?></td>
            <td><?= fmtBrlP((float)$comissao['valor_comissao']) ?></td>
            <td>
                <?php
                $badgeMap = [
                    'pendente'  => 'pdf-badge-pendente',
                    'pago'      => 'pdf-badge-pago',
                    'cancelado' => 'pdf-badge-cancelado',
                ];
                $badgeCls = $badgeMap[$comissao['status_pagamento']] ?? '';
                $badgeLbl = ucfirst($comissao['status_pagamento']);
                echo "<span class=\"pdf-badge {$badgeCls}\">{$badgeLbl}</span>";
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="pdf-footer">
    AFRO ESSÊNCIA · Sistema de Gestão · Relatório Financeiro · Gerado em <?= $dataEmissao ?>
</div>

<script src="/afro_essencia/public/js/pages/pdf-relatorio.js"></script>
</body>
</html>
