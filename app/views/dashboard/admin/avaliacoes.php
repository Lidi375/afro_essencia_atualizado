<?php
$pageTitle  = 'Avaliações dos Profissionais';
$activePage = 'avaliacoes';
require __DIR__ . '/../partials/_admin-header.php';

$total = count($avaliacoes ?? []);
$mediageral = 0;
if (!empty($avaliacoes)) {
    $mediageral = round(array_sum(array_column($avaliacoes, 'nota')) / count($avaliacoes), 1);
}
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <span>Avaliações</span>
        </nav>
        <h1 class="subpage-title">Avaliações dos Profissionais</h1>
        <p class="subpage-subtitle">Todas as avaliações recebidas pelos clientes</p>
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
        <div class="summary-icon gold"><i data-lucide="bar-chart-2"></i></div>
        <div class="summary-body">
            <span class="summary-label">Total de Avaliações</span>
            <span class="summary-value"><?= $total ?></span>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon gold"><i data-lucide="star"></i></div>
        <div class="summary-body">
            <span class="summary-label">Nota Média Geral</span>
            <!-- FIX: sintaxe correta sem ternário quebrado -->
            <span class="summary-value"><?= $mediageral > 0 ? $mediageral . ' / 5' : '—' ?></span>
        </div>
    </div>
</div>

<!-- Painel principal -->
<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="star"></i>
            Lista de Avaliações
        </h3>
        <span class="data-panel-count">
            <?= $total ?> avaliação<?= $total !== 1 ? 'ões' : '' ?>
        </span>
    </div>

    <?php if (empty($avaliacoes)): ?>

        <!-- Estado vazio elegante -->
        <div class="adm-empty-state">
            <div class="adm-empty-icon">⭐</div>
            <p class="adm-empty-msg">Nenhuma avaliação registrada ainda</p>
            <p class="adm-empty-hint">As avaliações aparecem aqui quando clientes avaliarem as profissionais após um atendimento.</p>
        </div>

    <?php else: ?>

        <div class="table-scroll">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Profissional</th>
                        <th>Cliente</th>
                        <th>Nota</th>
                        <th>Comentário</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($avaliacoes as $a): ?>
                    <tr>
                        <td class="col-id"><?= (int)$a['id'] ?></td>

                        <!-- Profissional -->
                        <td>
                            <div class="cell-name">
                                <div class="cell-avatar cell-avatar--blue">
                                    <?= mb_strtoupper(mb_substr($a['profissional_nome'], 0, 2)) ?>
                                </div>
                                <span class="cell-name-text"><?= htmlspecialchars($a['profissional_nome']) ?></span>
                            </div>
                        </td>

                        <!-- Cliente -->
                        <td>
                            <div class="cell-name">
                                <div class="cell-avatar">
                                    <?= mb_strtoupper(mb_substr($a['cliente_nome'], 0, 2)) ?>
                                </div>
                                <span class="cell-name-text"><?= htmlspecialchars($a['cliente_nome']) ?></span>
                            </div>
                        </td>

                        <!-- Nota com estrelas — FIX: stroke correto para fundo claro -->
                        <td data-label="Nota">
                            <span class="cell-nota">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= (int)$a['nota']): ?>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="#d4a96a">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    <?php else: ?>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#CBB8A6" stroke-width="1.5">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <strong class="nota-num"><?= (int)$a['nota'] ?>/5</strong>
                            </span>
                        </td>

                        <!-- Comentário -->
                        <td data-label="Comentário">
                            <?php if (!empty($a['comentario'])): ?>
                                <span class="cell-comment" title="<?= htmlspecialchars($a['comentario']) ?>">
                                    "<?= htmlspecialchars(mb_substr($a['comentario'], 0, 55)) ?><?= mb_strlen($a['comentario']) > 55 ? '…' : '' ?>"
                                </span>
                            <?php else: ?>
                                <span style="color:var(--ds-text-subtle);">—</span>
                            <?php endif; ?>
                        </td>

                        <!-- Data -->
                        <td data-label="Data" class="cell-date">
                            <time datetime="<?= htmlspecialchars($a['created_at']) ?>">
                                <?= date('d/m/Y', strtotime($a['created_at'])) ?>
                            </time>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/_admin-footer.php'; ?>
