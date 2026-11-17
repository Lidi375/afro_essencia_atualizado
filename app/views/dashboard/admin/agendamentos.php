<?php
$pageTitle  = 'Agendamentos';
$activePage = 'agendamentos';
require __DIR__ . '/../partials/_admin-header.php';

// Contagens por status
$total      = count($agendamentos ?? []);
$pendentes  = count(array_filter($agendamentos ?? [], fn($a) => $a['status'] === 'pendente'));
$concluidos = count(array_filter($agendamentos ?? [], fn($a) => $a['status'] === 'concluido'));
$cancelados = count(array_filter($agendamentos ?? [], fn($a) => $a['status'] === 'cancelado'));
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <span>Agendamentos</span>
        </nav>
        <h1 class="subpage-title">Agendamentos</h1>
        <p class="subpage-subtitle">Gerencie todos os agendamentos da plataforma</p>
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

<!-- Cards de resumo -->
<div class="subpage-summary">
    <div class="summary-card">
        <div class="summary-icon gold"><i data-lucide="calendar-days"></i></div>
        <div class="summary-body">
            <span class="summary-label">Total</span>
            <span class="summary-value"><?= htmlspecialchars($total ?? '', ENT_QUOTES) ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon terra"><i data-lucide="clock"></i></div>
        <div class="summary-body">
            <span class="summary-label">Pendentes</span>
            <span class="summary-value"><?= htmlspecialchars($pendentes ?? '', ENT_QUOTES) ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon green"><i data-lucide="check-circle"></i></div>
        <div class="summary-body">
            <span class="summary-label">Concluídos</span>
            <span class="summary-value"><?= htmlspecialchars($concluidos ?? '', ENT_QUOTES) ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon warn"><i data-lucide="x-circle"></i></div>
        <div class="summary-body">
            <span class="summary-label">Cancelados</span>
            <span class="summary-value"><?= htmlspecialchars($cancelados ?? '', ENT_QUOTES) ?></span>
        </div>
    </div>
</div>

<!-- Tabela de agendamentos -->
<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="calendar-days"></i>
            Lista de Agendamentos
        </h3>
        <span class="data-panel-count"><?= htmlspecialchars($total ?? '', ENT_QUOTES) ?> registro<?= $total !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($agendamentos)): ?>
        <div class="empty-panel">
            <i data-lucide="calendar-off"></i>
            <div class="adm-empty-state">
                <div class="adm-empty-icon">📅</div>
                <p class="adm-empty-msg">Nenhum agendamento encontrado.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Profissional</th>
                        <th>Serviço</th>
                        <th>Data</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Status</th>
                        <th>Pagamento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos as $a): ?>
                        <tr>
                            <td class="col-id">#<?= $a['id'] ?></td>
                            <td>
                                <div class="cell-name">
                                    <div class="cell-avatar"><?= mb_substr($a['cliente'], 0, 2) ?></div>
                                    <span class="cell-name-text"><?= htmlspecialchars($a['cliente']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-name">
                                    <div class="cell-avatar" style="background:rgba(100,180,255,0.12);color:#90d0ff"><?= mb_substr($a['profissional'], 0, 2) ?></div>
                                    <span class="cell-name-text"><?= htmlspecialchars($a['profissional']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($a['servico']) ?></td>
                            <td class="cell-date"><?= date('d/m/Y', strtotime($a['data_agendamento'])) ?></td>
                            <td>
                                <span class="cell-time">
                                    <i data-lucide="clock"></i>
                                    <?= $a['hora_inicio'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="cell-time">
                                    <i data-lucide="clock"></i>
                                    <?= $a['hora_fim'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= $a['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $a['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                                <?php if ($a['status'] !== 'cancelado' && $a['status'] !== 'concluido'): ?>
                                    <a href="/afro_essencia/dashboard/pagamentoForm/<?= $a['id'] ?>" class="btn-ver-ficha">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M7 15h2m4 0h4"/></svg> Pagar
                                    </a>
                                    <form method="POST" action="/afro_essencia/dashboard/cancelarAgendamento/<?= $a['id'] ?>"
                                          onsubmit="return confirm('Cancelar este agendamento?');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <button type="submit" class="btn-ver-ficha" class="btn-action btn-danger">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg> Cancelar
                                        </button>
                                    </form>
                                <?php elseif ($a['status'] === 'concluido'): ?>
                                    <?php
                                    $pagamentoDAO  = new PagamentoDAO();
                                    $pagConcluido  = $pagamentoDAO->findByAgendamento($a['id']);
                                    ?>
                                    <span style="color:#4ade80;font-size: 14px;display:inline-flex;align-items:center;gap:4px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg> Concluído</span>
                                    <?php if ($pagConcluido && $pagConcluido['status_pagamento'] === 'pago'): ?>
                                    <form method="POST"
                                          action="/afro_essencia/dashboard/estornarPagamento/<?= (int)$pagConcluido['id'] ?>"
                                          onsubmit="return confirm('Estornar este pagamento e reabrir o agendamento?');"
                                          style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <button type="submit" class="btn-ver-ficha"
                                                style="background:rgba(255,152,0,.15);border-color:rgba(255,152,0,.35);color:#ffb74d;cursor:pointer;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg> Estornar
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge-status badge-cancelado">Cancelado</span>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/_admin-footer.php'; ?>
