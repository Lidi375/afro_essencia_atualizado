<?php
$pageTitle  = 'Clientes';
$activePage = 'clientes';
require __DIR__ . '/../partials/_admin-header.php';

$total = count($clientes ?? []);
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <span>Clientes</span>
        </nav>
        <h1 class="subpage-title">Clientes</h1>
        <p class="subpage-subtitle">Todos os clientes cadastrados na plataforma</p>
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

<?php if (!empty($erro)): ?>
    <div class="alert-danger"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<!-- Card de resumo -->
<div class="subpage-summary">
    <div class="summary-card">
        <div class="summary-icon gold"><i data-lucide="users"></i></div>
        <div class="summary-body">
            <span class="summary-label">Total de Clientes</span>
            <span class="summary-value"><?= htmlspecialchars((string)($total ?? 0), ENT_QUOTES) ?></span>
        </div>
    </div>
</div>

<!-- Tabela de clientes -->
<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="users"></i>
            Lista de Clientes
        </h3>
        <span class="data-panel-count"><?= htmlspecialchars((string)($total ?? 0), ENT_QUOTES) ?> registro<?= $total !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($clientes)): ?>
        <div class="empty-panel">
            <i data-lucide="user-x"></i>
            <div class="adm-empty-state">
                <div class="adm-empty-icon">👤</div>
                <p class="adm-empty-msg">Nenhum cliente cadastrado ainda.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Desde</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td class="col-id">#<?= (int)$c['id'] ?></td>
                            <td>
                                <div class="cell-name">
                                    <div class="cell-avatar"><?= mb_substr(htmlspecialchars($c['nome']), 0, 2) ?></div>
                                    <div>
                                        <div class="cell-name-text"><?= htmlspecialchars($c['nome']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="cell-email"><?= htmlspecialchars($c['email']) ?></span>
                            </td>
                            <td>
                                <span style="color:var(--text-muted);font-size: 14px;">
                                    <?= isset($c['created_at']) ? date('d/m/Y', strtotime($c['created_at'])) : '—' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/_admin-footer.php'; ?>
