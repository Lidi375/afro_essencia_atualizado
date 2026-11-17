<?php
$pageTitle  = 'Candidaturas — Trabalhe Conosco';
$activePage = 'candidaturas';
require __DIR__ . '/../partials/_admin-header.php';

$total = count($candidaturas ?? []);

// Monta totais de status
$allTotal = array_sum($totaisPorStatus ?? []);
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <span>Candidaturas</span>
        </nav>
        <h1 class="subpage-title">Candidaturas — Trabalhe Conosco</h1>
        <p class="subpage-subtitle">Gerencie as candidaturas de profissionais recebidas</p>
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

<!-- Resumo por status -->
<div class="subpage-summary">
    <div class="summary-card">
        <div class="summary-icon gold"><i data-lucide="briefcase"></i></div>
        <div class="summary-body">
            <span class="summary-label">Total</span>
            <span class="summary-value"><?= $allTotal ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon terra">
            <i data-lucide="clock"></i>
        </div>
        <div class="summary-body">
            <span class="summary-label">Pendentes</span>
            <span class="summary-value"><?= $totaisPorStatus['pendente'] ?? 0 ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon" style="background:rgba(33,150,243,.1);color:#90caf9;">
            <i data-lucide="search"></i>
        </div>
        <div class="summary-body">
            <span class="summary-label">Em Análise</span>
            <span class="summary-value"><?= $totaisPorStatus['em_analise'] ?? 0 ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon" style="background:rgba(76,175,80,.1);color:#a5d6a7;">
            <i data-lucide="check-circle"></i>
        </div>
        <div class="summary-body">
            <span class="summary-label">Aprovadas</span>
            <span class="summary-value"><?= $totaisPorStatus['aprovado'] ?? 0 ?></span>
        </div>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="/afro_essencia/dashboard/candidaturas" class="filter-bar">
    <input type="text" name="busca" placeholder="Buscar por nome, e-mail, especialidade..."
           value="<?= htmlspecialchars($filtroBusca ?? '') ?>">
    <select name="status">
        <option value="">Todos os status</option>
        <option value="pendente"   <?= ($filtroStatus ?? '') === 'pendente'   ? 'selected' : '' ?>>Pendente</option>
        <option value="em_analise" <?= ($filtroStatus ?? '') === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
        <option value="aprovado"   <?= ($filtroStatus ?? '') === 'aprovado'   ? 'selected' : '' ?>>Aprovado</option>
        <option value="reprovado"  <?= ($filtroStatus ?? '') === 'reprovado'  ? 'selected' : '' ?>>Reprovado</option>
    </select>
    <button type="submit" class="btn-primary" style="padding:9px 16px;font-size: 14px;">
        <i data-lucide="search"></i> Filtrar
    </button>
    <?php if (!empty($filtroStatus) || !empty($filtroBusca)): ?>
        <a href="/afro_essencia/dashboard/candidaturas" class="btn-outline" style="padding:9px 16px;font-size: 14px;">
            Limpar
        </a>
    <?php endif; ?>
</form>

<!-- Tabela -->
<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="list"></i>
            Lista de Candidaturas
        </h3>
        <span class="data-panel-count"><?= $total ?> resultado<?= $total !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($candidaturas)): ?>
        <div class="empty-panel">
            <i data-lucide="inbox"></i>
            <div class="adm-empty-state">
                <div class="adm-empty-icon">💼</div>
                <p class="adm-empty-msg">Nenhuma candidatura encontrada.</p>
                <p class="adm-empty-hint">Tente remover os filtros ou aguarde novas inscrições.</p>
            </div>
            <?php if (!empty($filtroStatus) || !empty($filtroBusca)): ?>
                <p class="adm-empty-hint">Tente remover os filtros aplicados.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Candidato</th>
                        <th>Especialidade</th>
                        <th>Cidade/UF</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidaturas as $c): ?>
                        <tr>
                            <td class="col-id">#<?= (int)$c['id'] ?></td>

                            <td>
                                <div class="cell-name">
                                    <div class="cell-avatar" style="background:rgba(193,127,33,.12);color:#e1a756;">
                                        <?= mb_substr(htmlspecialchars($c['nome']), 0, 2) ?>
                                    </div>
                                    <div>
                                        <div class="cell-name-text"><?= htmlspecialchars($c['nome']) ?></div>
                                        <div style="font-size: 14px;color:var(--ds-text-muted);"><?= htmlspecialchars($c['email']) ?></div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span style="font-size: 14px;color:var(--text-muted);">
                                    <?= htmlspecialchars($c['especialidade']) ?>
                                </span>
                            </td>

                            <td>
                                <span style="font-size: 14px;color:var(--text-muted);">
                                    <?= htmlspecialchars($c['cidade']) ?>/<?= htmlspecialchars($c['estado']) ?>
                                </span>
                            </td>

                            <td>
                                <?php
                                $statusClass = [
                                    'pendente'   => 'badge-pendente',
                                    'em_analise' => 'badge-em-analise',
                                    'aprovado'   => 'badge-aprovado',
                                    'reprovado'  => 'badge-reprovado',
                                ][$c['status']] ?? '';
                                ?>
                                <span class="badge <?= $statusClass ?>">
                                    <?= Candidatura::statusLabel($c['status']) ?>
                                </span>
                            </td>

                            <td style="font-size: 14px;color:var(--ds-text-muted);">
                                <?= date('d/m/Y', strtotime($c['created_at'])) ?>
                            </td>

                            <td>
                                <a href="/afro_essencia/dashboard/candidaturaDetalhe/<?= (int)$c['id'] ?>"
                                   class="btn-ver-ficha">
                                    <i data-lucide="eye" style="width:14px;height:14px;"></i>
                                    Ver detalhes
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
