<?php
/**
 * View: financeiro/admin/index.php
 * Dashboard financeiro — visão geral para o administrador
 * Usa os partials _admin-header.php e _admin-footer.php do projeto
 */

$pageTitle  = 'Financeiro — Visão Geral';
$activePage = 'financeiro';
$extraCss   = ['financeiro.css'];
$extraJs    = [
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    '/afro_essencia/public/js/pages/financeiro.js',
];

$totais              = $totais              ?? [];
$totaisProfissionais = $totaisProfissionais ?? [];
$evolucao            = $evolucao            ?? [];
$profissionais       = $profissionais       ?? [];
$filtros             = $filtros             ?? [];

function fmtBrl(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}

$receitaTotal       = (float)($totais['receita_total']        ?? 0);
$comissoesPagas     = (float)($totais['comissoes_pagas']      ?? 0);
$comissoesPendentes = (float)($totais['comissoes_pendentes']  ?? 0);
$lucroLiquido       = (float)($totais['lucro_liquido']        ?? 0);
$totalAtend         = (int)  ($totais['total_atendimentos']   ?? 0);
$ticketMedio        = (float)($totais['ticket_medio']         ?? 0);

$topReceita  = !empty($totaisProfissionais) ? $totaisProfissionais[0] : null;
$topComissao = !empty($totaisProfissionais)
    ? array_reduce($totaisProfissionais, fn($c,$i) => (!$c || $i['total_comissoes'] > $c['total_comissoes']) ? $i : $c)
    : null;

$labels   = json_encode(array_column($evolucao, 'mes'));
$receitas = json_encode(array_column($evolucao, 'receita'));
$comis    = json_encode(array_column($evolucao, 'comissoes'));

require __DIR__ . '/../../dashboard/partials/_admin-header.php';
?>

<!-- FILTROS -->
<div class="fin-filter-card subpage-card">
    <form method="GET" action="/afro_essencia/financeiro" class="fin-filter-form">
        <div class="fin-filter-row">
            <div class="fin-filter-group">
                <label>Data Inicial</label>
                <input type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>">
            </div>
            <div class="fin-filter-group">
                <label>Data Final</label>
                <input type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>">
            </div>
            <div class="fin-filter-group">
                <label>Profissional</label>
                <select name="profissional_id">
                    <option value="">Todos</option>
                    <?php foreach ($profissionais as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"
                            <?= ($filtros['profissional_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nome'] ?? $p['usuario_nome'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fin-filter-actions">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="search"></i> Filtrar
                </button>
                <a href="/afro_essencia/financeiro" class="btn btn-outline btn-sm">
                    <i data-lucide="x"></i> Limpar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- HEADER DA SUBPAGE -->
<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <span>Financeiro</span>
        </nav>
        <h1 class="subpage-title">Visão Geral Financeira</h1>
        <p class="subpage-subtitle">Receitas, comissões e indicadores do salão</p>
    </div>
    <div class="subpage-actions">
        <a href="/afro_essencia/financeiro/relatorio" class="btn btn-outline">
            <i data-lucide="bar-chart-2"></i> Relatório
        </a>
        <a href="/afro_essencia/financeiro/pdf<?= !empty(array_filter($filtros ?? [])) ? '?' . http_build_query(array_filter($filtros)) : '' ?>"
           class="btn btn-primary" target="_blank">
            <i data-lucide="file-down"></i> PDF
        </a>
    </div>
</div>

<!-- KPI CARDS -->
<div class="fin-kpi-grid">
    <div class="fin-kpi-card fin-kpi-receita">
        <div class="fin-kpi-icon"><i data-lucide="trending-up"></i></div>
        <div class="fin-kpi-body">
            <span class="fin-kpi-label">Receita Total</span>
            <span class="fin-kpi-value"><?= fmtBrl($receitaTotal) ?></span>
        </div>
    </div>
    <div class="fin-kpi-card fin-kpi-pago">
        <div class="fin-kpi-icon"><i data-lucide="check-circle-2"></i></div>
        <div class="fin-kpi-body">
            <span class="fin-kpi-label">Comissões Pagas</span>
            <span class="fin-kpi-value"><?= fmtBrl($comissoesPagas) ?></span>
        </div>
    </div>
    <div class="fin-kpi-card fin-kpi-pendente">
        <div class="fin-kpi-icon"><i data-lucide="clock"></i></div>
        <div class="fin-kpi-body">
            <span class="fin-kpi-label">Comissões Pendentes</span>
            <span class="fin-kpi-value"><?= fmtBrl($comissoesPendentes) ?></span>
        </div>
    </div>
    <div class="fin-kpi-card fin-kpi-lucro">
        <div class="fin-kpi-icon"><i data-lucide="banknote"></i></div>
        <div class="fin-kpi-body">
            <span class="fin-kpi-label">Lucro Líquido</span>
            <span class="fin-kpi-value"><?= fmtBrl($lucroLiquido) ?></span>
        </div>
    </div>
    <div class="fin-kpi-card">
        <div class="fin-kpi-icon"><i data-lucide="calendar-check-2"></i></div>
        <div class="fin-kpi-body">
            <span class="fin-kpi-label">Atendimentos</span>
            <span class="fin-kpi-value"><?= $totalAtend ?></span>
        </div>
    </div>
    <div class="fin-kpi-card">
        <div class="fin-kpi-icon"><i data-lucide="receipt"></i></div>
        <div class="fin-kpi-body">
            <span class="fin-kpi-label">Ticket Médio</span>
            <span class="fin-kpi-value"><?= fmtBrl($ticketMedio) ?></span>
        </div>
    </div>
</div>

<!-- DESTAQUES -->
<?php if ($topReceita || $topComissao): ?>
<div class="fin-destaques-row">
    <?php if ($topReceita): ?>
    <div class="fin-destaque-card">
        <div class="fin-destaque-icon fin-icon-star"><i data-lucide="star"></i></div>
        <div>
            <div class="fin-destaque-label">Maior Receita</div>
            <div class="fin-destaque-nome"><?= htmlspecialchars($topReceita['profissional_nome']) ?></div>
            <div class="fin-destaque-valor"><?= fmtBrl((float)$topReceita['receita_total']) ?></div>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($topComissao): ?>
    <div class="fin-destaque-card">
        <div class="fin-destaque-icon fin-icon-award"><i data-lucide="award"></i></div>
        <div>
            <div class="fin-destaque-label">Maior Comissão</div>
            <div class="fin-destaque-nome"><?= htmlspecialchars($topComissao['profissional_nome']) ?></div>
            <div class="fin-destaque-valor"><?= fmtBrl((float)$topComissao['total_comissoes']) ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- GRÁFICO -->
<?php if (!empty($evolucao)): ?>
<div class="subpage-card fin-chart-card">
    <div class="fin-section-header">
        <h2 class="fin-section-title">Evolução Mensal</h2>
        <span class="fin-section-sub">Últimos 6 meses</span>
    </div>
    <canvas id="chartEvolucao" height="90"></canvas>
</div>
<?php endif; ?>

<!-- TABELA POR PROFISSIONAL -->
<?php if (!empty($totaisProfissionais)): ?>
<div class="subpage-card fin-table-card">
    <div class="fin-section-header">
        <h2 class="fin-section-title">Resumo por Profissional</h2>
        <a href="/afro_essencia/financeiro/comissoes" class="fin-link-all">Ver detalhes →</a>
    </div>
    <div class="table-responsive">
        <table class="adm-table fin-table">
            <thead>
                <tr>
                    <th>Profissional</th>
                    <th>Atendimentos</th>
                    <th>Receita</th>
                    <th>Comissões Pagas</th>
                    <th>Comissões Pendentes</th>
                    <th>Total Comissões</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($totaisProfissionais as $tp): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($tp['profissional_nome']) ?></strong></td>
                    <td><?= (int)$tp['total_atendimentos'] ?></td>
                    <td><?= fmtBrl((float)$tp['receita_total']) ?></td>
                    <td><span class="fin-valor-pago"><?= fmtBrl((float)$tp['comissoes_pagas']) ?></span></td>
                    <td><span class="fin-valor-pendente"><?= fmtBrl((float)$tp['comissoes_pendentes']) ?></span></td>
                    <td><strong><?= fmtBrl((float)$tp['total_comissoes']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="subpage-card fin-empty-state">
    <i data-lucide="bar-chart-2" class="fin-empty-icon"></i>
    <p>Nenhuma comissão encontrada para o período selecionado.</p>
    <p class="fin-empty-hint">As comissões são geradas automaticamente quando o agendamento é concluído <strong>e</strong> o pagamento é aprovado.</p>
</div>
<?php endif; ?>

<script>
// Passa dados do PHP para o financeiro.js via variável global
window.FIN_CHART_DATA = {
    labels:    <?= $labels ?>,
    receitas:  <?= $receitas ?>,
    comissoes: <?= $comis ?>
};
</script>

<?php require __DIR__ . '/../../dashboard/partials/_admin-footer.php'; ?>
