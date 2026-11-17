<?php $activePage = 'pagamentos'; ?>
<?php require_once __DIR__ . '/../partials/_admin-header.php'; ?>

<?php
// $pagamentos agora vem de payment_transactions (via PagamentoService::listarTodos)
$pagamentos = $pagamentos ?? [];

$totalAprovado   = array_sum(array_column(array_filter($pagamentos, fn($p) => $p['status'] === 'approved'),  'amount'));
$totalPendente   = array_sum(array_column(array_filter($pagamentos, fn($p) => $p['status'] === 'pending'),   'amount'));
$totalRecusado   = array_sum(array_column(array_filter($pagamentos, fn($p) => $p['status'] === 'rejected'),  'amount'));
$totalReembolso  = array_sum(array_column(array_filter($pagamentos, fn($p) => $p['status'] === 'refunded'),  'amount'));

$statusLabel = [
    'approved'  => ['label' => 'Aprovado',   'badge' => 'badge-concluido'],
    'pending'   => ['label' => 'Pendente',   'badge' => 'badge-pendente'],
    'rejected'  => ['label' => 'Recusado',   'badge' => 'badge-cancelado'],
    'cancelled' => ['label' => 'Cancelado',  'badge' => 'badge-cancelado'],
    'refunded'  => ['label' => 'Reembolsado','badge' => 'badge-pendente'],
];
?>

<div class="admin-content-area">
    <div class="admin-page-header">
        <h2 class="admin-page-title">Gestão Financeira</h2>
        <p class="admin-page-subtitle">Transações processadas pelo gateway de pagamento</p>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>
    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <!-- KPIs financeiros -->
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 28px;">
        <div class="stat-card">
            <div class="stat-label">Total Aprovado</div>
            <div class="stat-number" style="color:#81c784;">R$ <?= number_format($totalAprovado, 2, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pendente</div>
            <div class="stat-number" style="color:var(--ds-warning);">R$ <?= number_format($totalPendente, 2, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Recusado</div>
            <div class="stat-number" style="color:#ef9a9a;">R$ <?= number_format($totalRecusado, 2, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Reembolsado</div>
            <div class="stat-number" style="color:#90caf9;">R$ <?= number_format($totalReembolso, 2, ',', '.') ?></div>
        </div>
    </div>

    <div class="data-panel">
        <div class="data-panel-header">
            <h3 class="data-panel-title">Histórico de Transações</h3>
            <span class="data-panel-count"><?= count($pagamentos) ?> transaç<?= count($pagamentos) !== 1 ? 'ões' : 'ão' ?></span>
        </div>

        <?php if (empty($pagamentos)): ?>
            <div class="adm-empty-state">
                <div class="adm-empty-icon">💳</div>
                <p class="adm-empty-msg">Nenhuma transação registrada ainda.</p>
            </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <th>Profissional</th>
                        <th>Data Ag.</th>
                        <th>Gateway</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Pago em</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pagamentos as $p):
                    $sl = $statusLabel[$p['status']] ?? ['label' => ucfirst($p['status']), 'badge' => 'badge-pendente'];
                ?>
                    <tr>
                        <td data-label="Cliente"><?= htmlspecialchars($p['cliente_nome'] ?? '—') ?></td>
                        <td data-label="Serviço"><?= htmlspecialchars($p['servico_nome'] ?? '—') ?></td>
                        <td data-label="Profissional"><?= htmlspecialchars($p['profissional_nome'] ?? '—') ?></td>
                        <td data-label="Data Ag.">
                            <?= !empty($p['data_agendamento']) ? date('d/m/Y', strtotime($p['data_agendamento'])) : '—' ?>
                        </td>
                        <td data-label="Gateway"><?= htmlspecialchars(ucfirst($p['gateway'] ?? '—')) ?></td>
                        <td data-label="Valor"><strong>R$ <?= number_format((float)($p['amount'] ?? 0), 2, ',', '.') ?></strong></td>
                        <td data-label="Status"><span class="badge <?= $sl['badge'] ?>"><?= $sl['label'] ?></span></td>
                        <td data-label="Pago em">
                            <?= !empty($p['paid_at']) ? date('d/m/Y H:i', strtotime($p['paid_at'])) : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/_admin-footer.php'; ?>
