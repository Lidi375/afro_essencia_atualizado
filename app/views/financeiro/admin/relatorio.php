<?php
/**
 * View: financeiro/admin/relatorio.php
 * Relatório financeiro administrativo
 */

$pageTitle  = 'Financeiro — Relatório';
$activePage = 'financeiro';
$extraCss   = ['financeiro.css'];
$extraJs    = ['/afro_essencia/public/js/pages/financeiro.js'];

$totais              = $totais              ?? [];
$totaisProfissionais = $totaisProfissionais ?? [];
$comissoes           = $comissoes           ?? [];
$profissionais       = $profissionais       ?? [];
$filtros             = $filtros             ?? [];

function fmtBrlR(float $v): string { return 'R$ ' . number_format($v, 2, ',', '.'); }
function fmtDateR(?string $d): string { return $d ? date('d/m/Y', strtotime($d)) : '—'; }

require __DIR__ . '/../../dashboard/partials/_admin-header.php';
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <a href="/afro_essencia/financeiro">Financeiro</a>
            <i data-lucide="chevron-right"></i>
            <span>Relatório</span>
        </nav>
        <h1 class="subpage-title">Relatório Financeiro</h1>
        <p class="subpage-subtitle">
            <?php
            $di = $filtros['data_inicio'] ?? '';
            $df = $filtros['data_fim']    ?? '';
            if ($di && $df) {
                echo 'Período: ' . date('d/m/Y', strtotime($di)) . ' a ' . date('d/m/Y', strtotime($df));
            } else {
                echo 'Todos os períodos';
            }
            ?>
        </p>
    </div>
    <div class="subpage-actions">
        <a href="/afro_essencia/financeiro/pdf<?= !empty(array_filter($filtros ?? [])) ? '?' . http_build_query(array_filter($filtros)) : '' ?>"
           class="btn btn-primary" target="_blank">
            <i data-lucide="file-down"></i> Exportar PDF
        </a>
    </div>
</div>

<!-- FILTROS -->
<div class="subpage-card fin-filter-card">
    <form method="GET" action="/afro_essencia/financeiro/relatorio" class="fin-filter-form">
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
                <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="search"></i> Filtrar</button>
                <a href="/afro_essencia/financeiro/relatorio" class="btn btn-outline btn-sm">Limpar</a>
            </div>
        </div>
    </form>
</div>

<!-- TOTAIS -->
<div class="subpage-card">
    <h2 class="fin-section-title">Resumo Geral</h2>
    <div class="fin-relatorio-totais">
        <div class="fin-total-item">
            <span class="fin-total-label">Receita Bruta</span>
            <span class="fin-total-value"><?= fmtBrlR((float)($totais['receita_total'] ?? 0)) ?></span>
        </div>
        <div class="fin-total-item">
            <span class="fin-total-label">Total Comissões</span>
            <span class="fin-total-value"><?= fmtBrlR((float)($totais['total_comissoes'] ?? 0)) ?></span>
        </div>
        <div class="fin-total-item fin-total-highlight">
            <span class="fin-total-label">Lucro Líquido</span>
            <span class="fin-total-value"><?= fmtBrlR((float)($totais['lucro_liquido'] ?? 0)) ?></span>
        </div>
        <div class="fin-total-item">
            <span class="fin-total-label">Comissões Pagas</span>
            <span class="fin-total-value fin-valor-pago"><?= fmtBrlR((float)($totais['comissoes_pagas'] ?? 0)) ?></span>
        </div>
        <div class="fin-total-item">
            <span class="fin-total-label">Comissões Pendentes</span>
            <span class="fin-total-value fin-valor-pendente"><?= fmtBrlR((float)($totais['comissoes_pendentes'] ?? 0)) ?></span>
        </div>
        <div class="fin-total-item">
            <span class="fin-total-label">Atendimentos</span>
            <span class="fin-total-value"><?= (int)($totais['total_atendimentos'] ?? 0) ?></span>
        </div>
    </div>
</div>

<!-- POR PROFISSIONAL -->
<?php if (!empty($totaisProfissionais)): ?>
<div class="subpage-card">
    <h2 class="fin-section-title">Por Profissional</h2>
    <div class="table-responsive">
        <table class="adm-table fin-table">
            <thead>
                <tr>
                    <th>Profissional</th>
                    <th>Atendimentos</th>
                    <th>Receita</th>
                    <th>Comissões Pagas</th>
                    <th>Comissões Pendentes</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($totaisProfissionais as $tp): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($tp['profissional_nome']) ?></strong></td>
                    <td><?= (int)$tp['total_atendimentos'] ?></td>
                    <td><?= fmtBrlR((float)$tp['receita_total']) ?></td>
                    <td class="fin-valor-pago"><?= fmtBrlR((float)$tp['comissoes_pagas']) ?></td>
                    <td class="fin-valor-pendente"><?= fmtBrlR((float)$tp['comissoes_pendentes']) ?></td>
                    <td><strong><?= fmtBrlR((float)$tp['total_comissoes']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- DETALHAMENTO -->
<?php if (!empty($comissoes)): ?>
<div class="subpage-card">
    <h2 class="fin-section-title">Detalhamento por Atendimento</h2>
    <div class="table-responsive">
        <table class="adm-table fin-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Profissional</th>
                    <th>Serviço</th>
                    <th>Cliente</th>
                    <th>Valor Serviço</th>
                    <th>Comissão</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comissoes as $c): ?>
                <tr>
                    <td><?= fmtDateR($c['data_geracao']) ?></td>
                    <td><?= htmlspecialchars($c['profissional_nome']) ?></td>
                    <td><?= htmlspecialchars($c['servico_nome']) ?></td>
                    <td><?= htmlspecialchars($c['cliente_nome']) ?></td>
                    <td><?= fmtBrlR((float)$c['valor_servico']) ?></td>
                    <td><?= fmtBrlR((float)$c['valor_comissao']) ?></td>
                    <td>
                        <?php
                        $map = ['pendente'=>'badge-pendente','pago'=>'badge-concluido','cancelado'=>'badge-cancelado'];
                        $cls = $map[$c['status_pagamento']] ?? 'badge-pendente';
                        $lbl = ucfirst($c['status_pagamento']);
                        echo "<span class=\"badge-status {$cls}\">{$lbl}</span>";
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../dashboard/partials/_admin-footer.php'; ?>
