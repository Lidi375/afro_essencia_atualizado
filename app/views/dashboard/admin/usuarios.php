<?php
$pageTitle  = 'Usuários';
$activePage = 'usuarios';
require __DIR__ . '/../partials/_admin-header.php';

$total       = count($usuarios ?? []);
$admins      = count(array_filter($usuarios ?? [], fn($u) => $u['tipo_usuario'] === 'admin'));
$profis      = count(array_filter($usuarios ?? [], fn($u) => $u['tipo_usuario'] === 'profissional'));
$clisCount   = count(array_filter($usuarios ?? [], fn($u) => $u['tipo_usuario'] === 'cliente'));
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <span>Usuários</span>
        </nav>
        <h1 class="subpage-title">Usuários</h1>
        <p class="subpage-subtitle">Todos os usuários registrados no sistema</p>
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
        <div class="summary-icon gold"><i data-lucide="users"></i></div>
        <div class="summary-body">
            <span class="summary-label">Total</span>
            <span class="summary-value"><?= htmlspecialchars($total ?? '', ENT_QUOTES) ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon warn"><i data-lucide="shield-check"></i></div>
        <div class="summary-body">
            <span class="summary-label">Admins</span>
            <span class="summary-value"><?= htmlspecialchars($admins ?? '', ENT_QUOTES) ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon blue"><i data-lucide="scissors"></i></div>
        <div class="summary-body">
            <span class="summary-label">Profissionais</span>
            <span class="summary-value"><?= htmlspecialchars($profis ?? '', ENT_QUOTES) ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon terra"><i data-lucide="user"></i></div>
        <div class="summary-body">
            <span class="summary-label">Clientes</span>
            <span class="summary-value"><?= htmlspecialchars($clisCount ?? '', ENT_QUOTES) ?></span>
        </div>
    </div>
</div>

<!-- Tabela de usuários -->
<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="shield-check"></i>
            Lista de Usuários
        </h3>
        <span class="data-panel-count"><?= htmlspecialchars($total ?? '', ENT_QUOTES) ?> registro<?= $total !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($usuarios)): ?>
        <div class="empty-panel">
            <i data-lucide="user-x"></i>
            <div class="adm-empty-state">
                <div class="adm-empty-icon">🔐</div>
                <p class="adm-empty-msg">Nenhum usuário cadastrado ainda.</p>
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
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td data-label="ID" class="col-id">#<?= $u['id'] ?></td>
                            <td data-label="Nome">
                                <div class="cell-name">
                                    <div class="cell-avatar"><?= mb_substr($u['nome'], 0, 2) ?></div>
                                    <span class="cell-name-text"><?= htmlspecialchars($u['nome']) ?></span>
                                </div>
                            </td>
                            <td data-label="Email">
                                <span class="cell-email"><?= htmlspecialchars($u['email']) ?></span>
                            </td>
                            <td data-label="Perfil">
                                <span class="badge badge-<?= $u['tipo_usuario'] ?>">
                                    <?= ucfirst($u['tipo_usuario']) ?>
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
