<?php
/**
 * View: financeiro/admin/comissoes.php
 * Lista de comissões com filtros e ações
 */

$pageTitle  = 'Financeiro — Comissões';
$activePage = 'financeiro';
$extraCss   = ['financeiro.css'];
$extraJs    = ['/afro_essencia/public/js/pages/financeiro.js'];

$comissoes     = $comissoes     ?? [];
$profissionais = $profissionais ?? [];
$filtros       = $filtros       ?? [];
$mensagem      = $mensagem      ?? null;
$erro          = $erro          ?? null;

$csrfToken = $_SESSION['csrf_token'] ??
    ($_SESSION['csrf_token'] = bin2hex(random_bytes(32)));

function statusBadgeCom(string $s): string {
    $map = [
        'pendente'  => ['Pendente',  'badge-pendente'],
        'pago'      => ['Pago',      'badge-concluido'],
        'cancelado' => ['Cancelado', 'badge-cancelado'],
    ];
    $b = $map[$s] ?? ['Desconhecido','badge-pendente'];
    return "<span class=\"badge-status {$b[1]}\">{$b[0]}</span>";
}

function fmtBrlC(float $v): string { return 'R$ ' . number_format($v, 2, ',', '.'); }
function fmtDateC(?string $d): string { return $d ? date('d/m/Y', strtotime($d)) : '—'; }

require __DIR__ . '/../../dashboard/partials/_admin-header.php';
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <a href="/afro_essencia/financeiro">Financeiro</a>
            <i data-lucide="chevron-right"></i>
            <span>Comissões</span>
        </nav>
        <h1 class="subpage-title">Comissões</h1>
        <p class="subpage-subtitle">Gerencie o pagamento das comissões dos profissionais</p>
    </div>
    <div class="subpage-actions">
        <a href="/afro_essencia/financeiro/config" class="btn btn-outline">
            <i data-lucide="settings"></i> Configurar
        </a>
        <a href="/afro_essencia/financeiro/pdf<?= !empty(array_filter($filtros ?? [])) ? '?' . http_build_query(array_filter($filtros)) : '' ?>"
           class="btn btn-primary" target="_blank">
            <i data-lucide="file-down"></i> PDF
        </a>
    </div>
</div>

<?php if ($mensagem): ?>
<div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if ($erro): ?>
<div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<!-- FILTROS -->
<div class="subpage-card fin-filter-card">
    <form method="GET" action="/afro_essencia/financeiro/comissoes" class="fin-filter-form">
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
            <div class="fin-filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="pendente"  <?= ($filtros['status'] ?? '') === 'pendente'  ? 'selected' : '' ?>>Pendente</option>
                    <option value="pago"      <?= ($filtros['status'] ?? '') === 'pago'      ? 'selected' : '' ?>>Pago</option>
                    <option value="cancelado" <?= ($filtros['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="fin-filter-actions">
                <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="search"></i> Filtrar</button>
                <a href="/afro_essencia/financeiro/comissoes" class="btn btn-outline btn-sm"><i data-lucide="x"></i> Limpar</a>
            </div>
        </div>
    </form>
</div>

<!-- TABELA -->
<?php if (!empty($comissoes)): ?>
<div class="subpage-card">
    <div class="table-responsive">
        <table class="adm-table fin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Profissional</th>
                    <th>Serviço</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Valor Serviço</th>
                    <th>Tipo</th>
                    <th>Comissão</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comissoes as $c): ?>
                <tr>
                    <td class="fin-id"><?= (int)$c['id'] ?></td>
                    <td><strong><?= htmlspecialchars($c['profissional_nome']) ?></strong></td>
                    <td><?= htmlspecialchars($c['servico_nome']) ?></td>
                    <td><?= htmlspecialchars($c['cliente_nome']) ?></td>
                    <td><?= fmtDateC($c['data_geracao']) ?></td>
                    <td><?= fmtBrlC((float)$c['valor_servico']) ?></td>
                    <td>
                        <?php if ($c['tipo_comissao'] === 'percentual'): ?>
                            <span class="fin-badge-tipo">% <?= number_format((float)$c['percentual'], 1) ?>%</span>
                        <?php else: ?>
                            <span class="fin-badge-tipo fin-badge-fixo">Fixo</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= fmtBrlC((float)$c['valor_comissao']) ?></strong></td>
                    <td><?= statusBadgeCom($c['status_pagamento']) ?></td>
                    <td>
                        <?php if ($c['status_pagamento'] === 'pendente'): ?>
                        <div class="fin-actions">
                            <button class="btn btn-primary btn-xs fin-btn-pagar"
                                    data-id="<?= (int)$c['id'] ?>"
                                    data-nome="<?= htmlspecialchars($c['profissional_nome']) ?>"
                                    data-valor="<?= fmtBrlC((float)$c['valor_comissao']) ?>">
                                <i data-lucide="check"></i> Pagar
                            </button>
                            <form method="POST" action="/afro_essencia/financeiro/cancelarComissao" style="display:inline"
                                  onsubmit="return confirm('Cancelar esta comissão?')">
                                <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="comissao_id" value="<?= (int)$c['id'] ?>">
                                <button type="submit" class="btn btn-outline btn-xs">
                                    <i data-lucide="x"></i>
                                </button>
                            </form>
                        </div>
                        <?php elseif ($c['status_pagamento'] === 'pago'): ?>
                            <span class="fin-pago-em">Pago em <?= fmtDateC($c['data_pagamento']) ?></span>
                        <?php else: ?>
                            <span class="fin-txt-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="subpage-card fin-empty-state">
    <i data-lucide="coins" class="fin-empty-icon"></i>
    <p>Nenhuma comissão encontrada.</p>
    <p class="fin-empty-hint">Comissões são geradas automaticamente quando o agendamento é concluído e o pagamento é aprovado.</p>
</div>
<?php endif; ?>

<!-- MODAL: Pagar comissão -->
<div class="fin-modal-overlay" id="modalPagar" style="display:none">
    <div class="fin-modal">
        <div class="fin-modal-header">
            <h3 class="fin-modal-title">Marcar Comissão como Paga</h3>
            <button class="fin-modal-close" id="fecharModal"><i data-lucide="x"></i></button>
        </div>
        <div class="fin-modal-body">
            <p>Profissional: <strong id="modalNome"></strong></p>
            <p>Valor: <strong id="modalValor"></strong></p>
            <form method="POST" action="/afro_essencia/financeiro/pagar" id="formPagar">
                <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="comissao_id" id="modalComissaoId">
                <div class="fin-form-group">
                    <label for="forma_pagamento">Forma de Pagamento</label>
                    <select name="forma_pagamento" id="forma_pagamento" required>
                        <option value="">Selecione...</option>
                        <option value="pix">PIX</option>
                        <option value="transferencia">Transferência Bancária</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="fin-modal-actions">
                    <button type="button" class="btn btn-outline" id="cancelarModal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Pagamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../dashboard/partials/_admin-footer.php'; ?>
