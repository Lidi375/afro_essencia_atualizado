<?php
$pageTitle  = 'Anamneses';
$activePage = 'anamneses';
require __DIR__ . '/../partials/_admin-header.php';

$total = count($anamneses ?? []);
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <span>Anamneses</span>
        </nav>
        <h1 class="subpage-title">Fichas de Anamnese</h1>
        <p class="subpage-subtitle">Fichas de anamnese capilar preenchidas pelos clientes</p>
    </div>
    <div class="subpage-actions">
        <a href="/afro_essencia/dashboard" class="btn-outline">
            <i data-lucide="arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<?php if (!empty($mensagem)): ?>
    <div class="alert-success"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>

<!-- Card resumo -->
<div class="subpage-summary">
    <div class="summary-card">
        <div class="summary-icon gold"><i data-lucide="file-heart"></i></div>
        <div class="summary-body">
            <span class="summary-label">Fichas Preenchidas</span>
            <span class="summary-value"><?= htmlspecialchars($total ?? '', ENT_QUOTES) ?></span>
        </div>
    </div>
</div>

<!-- Tabela de anamneses -->
<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="file-heart"></i>
            Lista de Fichas
        </h3>
        <span class="data-panel-count"><?= htmlspecialchars($total ?? '', ENT_QUOTES) ?> ficha<?= $total !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($anamneses)): ?>
        <div class="empty-panel">
            <i data-lucide="file-x"></i>
            <p>Nenhuma ficha de anamnese preenchida ainda.</p>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Tipo de Cabelo</th>
                        <th>Condição</th>
                        <th>Comprimento</th>
                        <th>Objetivo</th>
                        <th>Atualizado</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $labelsObj  = ['hidratar' => 'Hidratar', 'crescimento' => 'Crescimento', 'definir' => 'Definir cachos', 'recuperar' => 'Recuperação', 'estilo' => 'Estilo', 'manutencao' => 'Manutenção'];
                    $labelsTipo = ['liso' => 'Liso', 'ondulado' => 'Ondulado', 'cacheado' => 'Cacheado', 'crespo' => 'Crespo'];
                    $labelsHid  = ['seco' => 'Seco', 'normal' => 'Normal', 'oleoso' => 'Oleoso', 'misto' => 'Misto'];
                    $labelsComp = ['curto' => 'Curto', 'medio' => 'Médio', 'longo' => 'Longo', 'muito_longo' => 'Muito longo'];

                    foreach ($anamneses as $a):
                    ?>
                        <tr>
                            <td class="col-id">#<?= $a['id'] ?></td>
                            <td>
                                <div class="cell-name">
                                    <div class="cell-avatar"><?= mb_substr($a['cliente_nome'], 0, 2) ?></div>
                                    <div>
                                        <div class="cell-name-text"><?= htmlspecialchars($a['cliente_nome']) ?></div>
                                        <div class="cell-email"><?= htmlspecialchars($a['cliente_email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php $tc = $a['tipo_cabelo'] ?? ''; ?>
                                <?= $tc ? ($labelsTipo[$tc] ?? ucfirst($tc)) . (!empty($a['subtipo_cabelo']) ? ' · ' . $a['subtipo_cabelo'] : '') : "<span style='color:var(--ds-text-subtle)'>—</span>" ?>
                            </td>
                            <td>
                                <?php $hid = $a['nivel_hidratacao'] ?? ''; ?>
                                <?= $hid ? ($labelsHid[$hid] ?? ucfirst($hid)) : "<span style='color:var(--ds-text-subtle)'>—</span>" ?>
                            </td>
                            <td>
                                <?php $comp = $a['comprimento'] ?? ''; ?>
                                <?= $comp ? ($labelsComp[$comp] ?? ucfirst($comp)) : "<span style='color:var(--ds-text-subtle)'>—</span>" ?>
                            </td>
                            <td>
                                <?php $obj = $a['objetivo'] ?? ''; ?>
                                <?= $obj ? ($labelsObj[$obj] ?? ucfirst($obj)) : "<span style='color:var(--ds-text-subtle)'>—</span>" ?>
                            </td>
                            <td class="cell-date">
                                <?= !empty($a['updated_at']) ? date('d/m/Y', strtotime($a['updated_at'])) : '—' ?>
                            </td>
                            <td>
                                <a href="/afro_essencia/dashboard/anamneseDetalhe/<?= $a['id'] ?>" class="btn-ver-ficha">
                                    Ver ficha
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/_admin-footer.php'; ?>
