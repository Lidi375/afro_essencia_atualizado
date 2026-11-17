<?php
/**
 * View: financeiro/profissional/pdf.php
 * Relatório de comissões do profissional — NUNCA exibe dados de outros profissionais.
 * CSS externo: /public/css/pdf-relatorio.css
 * JS externo:  /public/js/pages/pdf-relatorio.js
 */

$comissoes        = $comissoes        ?? [];
$totais           = $totais           ?? [];
$filtros          = $filtros          ?? [];
$dataEmissao      = $dataEmissao      ?? date('d/m/Y H:i');
$nomeProfissional = htmlspecialchars(
    $nomeProfissional ?? ($_SESSION['user']['nome'] ?? 'Profissional'),
    ENT_QUOTES, 'UTF-8'
);

function fmtBrlPP(float $v): string   { return 'R$ ' . number_format($v, 2, ',', '.'); }
function fmtDatePP(?string $d): string { return $d ? date('d/m/Y', strtotime($d)) : '—'; }

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
    <title>AFRO ESSÊNCIA — Minhas Comissões</title>
    <link rel="stylesheet" href="/afro_essencia/public/css/pdf-relatorio.css">
</head>
<body>

<!-- Barra de ações -->
<div class="pdf-action-bar">
    <a href="/afro_essencia/financeiro/minhas" class="pdf-btn-back">
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
    <div>
        <div class="pdf-title">AFRO ESSÊNCIA</div>
        <div class="pdf-sub">Relatório de Comissões — Profissional</div>
    </div>
    <div class="pdf-meta">
        <strong><?= $nomeProfissional ?></strong>
        Emitido em: <?= $dataEmissao ?>
    </div>
</div>

<!-- Caixa profissional + período -->
<div class="pdf-prof-box">
    Profissional: <strong><?= $nomeProfissional ?></strong> &nbsp;·&nbsp;
    Período: <strong><?= htmlspecialchars($periodoTexto, ENT_QUOTES, 'UTF-8') ?></strong>
</div>

<!-- KPIs -->
<div class="pdf-kpi-grid pdf-kpi-grid--4">
    <div class="pdf-kpi">
        <div class="pdf-kpi-label">Ganhos Totais</div>
        <div class="pdf-kpi-value"><?= fmtBrlPP((float)($totais['ganhos_totais']      ?? 0)) ?></div>
    </div>
    <div class="pdf-kpi">
        <div class="pdf-kpi-label">Já Recebido</div>
        <div class="pdf-kpi-value"><?= fmtBrlPP((float)($totais['ganhos_recebidos']   ?? 0)) ?></div>
    </div>
    <div class="pdf-kpi">
        <div class="pdf-kpi-label">A Receber</div>
        <div class="pdf-kpi-value"><?= fmtBrlPP((float)($totais['ganhos_pendentes']   ?? 0)) ?></div>
    </div>
    <div class="pdf-kpi">
        <div class="pdf-kpi-label">Atendimentos</div>
        <div class="pdf-kpi-value"><?= (int)($totais['total_atendimentos'] ?? 0) ?></div>
    </div>
</div>

<!-- Registro de comissões -->
<h2>Registro de Comissões</h2>

<?php if (!empty($comissoes)): ?>
<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Serviço</th>
            <th>Cliente</th>
            <th>Valor Serviço</th>
            <th>Comissão</th>
            <th>Status</th>
            <th>Recebido em</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($comissoes as $comissao): ?>
        <tr>
            <td><?= fmtDatePP($comissao['data_geracao']) ?></td>
            <td><?= htmlspecialchars($comissao['servico_nome'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($comissao['cliente_nome'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= fmtBrlPP((float)$comissao['valor_servico']) ?></td>
            <td><strong><?= fmtBrlPP((float)$comissao['valor_comissao']) ?></strong></td>
            <td>
                <?php
                $isPago  = $comissao['status_pagamento'] === 'pago';
                $badgeCls = $isPago ? 'pdf-badge-pago' : 'pdf-badge-pendente';
                $badgeLbl = $isPago ? 'Recebido'       : 'Pendente';
                echo "<span class=\"pdf-badge {$badgeCls}\">{$badgeLbl}</span>";
                ?>
            </td>
            <td><?= $comissao['status_pagamento'] === 'pago' ? fmtDatePP($comissao['data_pagamento']) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <p class="pdf-empty">Nenhuma comissão no período selecionado.</p>
<?php endif; ?>

<div class="pdf-footer">
    AFRO ESSÊNCIA · <?= $nomeProfissional ?> · Gerado em <?= $dataEmissao ?>
</div>

<script src="/afro_essencia/public/js/pages/pdf-relatorio.js"></script>
</body>
</html>
