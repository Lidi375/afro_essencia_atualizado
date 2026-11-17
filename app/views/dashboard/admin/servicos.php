<?php
$pageTitle  = 'Serviços';
$activePage = 'servicos';
require __DIR__ . '/../partials/_admin-header.php';

$total = count($servicos ?? []);
$totalReceita = array_sum(array_column($servicos ?? [], 'valor'));
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <span>Serviços</span>
        </nav>
        <h1 class="subpage-title">Serviços</h1>
        <p class="subpage-subtitle">Catálogo de serviços disponíveis na plataforma</p>
    </div>
    <div class="subpage-actions">
        <a href="/afro_essencia/servico/novo" class="btn-primary">
            <i data-lucide="plus"></i>
            Novo Serviço
        </a>
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
        <div class="summary-icon green"><i data-lucide="sparkles"></i></div>
        <div class="summary-body">
            <span class="summary-label">Serviços Ativos</span>
            <span class="summary-value"><?= htmlspecialchars($total ?? '', ENT_QUOTES) ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon gold"><i data-lucide="banknote"></i></div>
        <div class="summary-body">
            <span class="summary-label">Valor Médio</span>
            <span class="summary-value" style="font-size:1.4rem">
                R$ <?= $total > 0 ? number_format($totalReceita / $total, 2, ',', '.') : '0,00' ?>
            </span>
        </div>
    </div>
</div>

<!-- Tabela de serviços -->
<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="sparkles"></i>
            Catálogo de Serviços
        </h3>
        <span class="data-panel-count"><?= htmlspecialchars($total ?? '', ENT_QUOTES) ?> serviço<?= $total !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($servicos)): ?>
        <div class="empty-panel">
            <i data-lucide="sparkles"></i>
            <p>Nenhum serviço ativo encontrado.</p>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Serviço</th>
                        <th>Valor</th>
                        <th>Duração</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicos as $s): ?>
                        <tr>
                            <td data-label="ID" class="col-id">#<?= $s['id'] ?></td>
                            <td data-label="Nome">
                                <div class="cell-name">
                                    <div class="cell-avatar" style="background:var(--ds-success-bg);color:var(--ds-success)">
                                        <i data-lucide="sparkles" style="width:14px;height:14px"></i>
                                    </div>
                                    <span class="cell-name-text"><?= htmlspecialchars($s['nome']) ?></span>
                                </div>
                            </td>
                            <td data-label="Valor">
                                <span class="cell-value">R$ <?= number_format($s['valor'], 2, ',', '.') ?></span>
                            </td>
                            <td>
                                <span class="cell-duration">
                                    <i data-lucide="timer"></i>
                                    <?= $s['duracao_minutos'] ?> min
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
