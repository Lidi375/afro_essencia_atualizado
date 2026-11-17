<?php
$pageTitle  = 'Profissionais';
$activePage = 'profissionais';
require __DIR__ . '/../partials/_admin-header.php';

$total = count($profissionais ?? []);
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <span>Profissionais</span>
        </nav>
        <h1 class="subpage-title">Profissionais</h1>
        <p class="subpage-subtitle">Equipe de profissionais cadastrados na plataforma</p>
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

<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="scissors"></i>
            Lista de Profissionais
        </h3>
        <span class="data-panel-count"><?= htmlspecialchars((string)($total ?? 0), ENT_QUOTES) ?> registro<?= $total !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($profissionais)): ?>
    <div class="empty-panel">
        <i data-lucide="scissors"></i>
        <p>Nenhum profissional cadastrado ainda.</p>
    </div>
    <?php else: ?>
    <div class="table-scroll">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Status</th>
                    <th>Biografia</th>
                    <th>Experiência</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($profissionais as $p): ?>
                    <tr>
                        <td class="col-id">#<?= $p['profissional_id'] ?></td>

                        <td data-label="Nome">
                            <div class="cell-name">
                                <div class="cell-avatar"
                                     style="background:rgba(100,180,255,0.12);color:#90d0ff">
                                    <?= mb_substr($p['nome'], 0, 2) ?>
                                </div>
                                <span class="cell-name-text">
                                    <?= htmlspecialchars($p['nome']) ?>
                                </span>
                            </div>
                        </td>

                        <td data-label="Status">
                            <?php if ($p['status'] === 'ativo'): ?>
                                <span class="badge badge-profissional">Ativo</span>
                            <?php else: ?>
                                <span class="badge" style="background:#444;color:#ccc;">Inativo</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="cell-email"
                                  style="display:block;max-width:420px;white-space:normal;line-height:1.4;color:var(--text-muted);">
                                <?= htmlspecialchars(mb_strimwidth($p['biografia'], 0, 120, '...')) ?>
                            </span>
                        </td>

                        <td>
                            <?= (int) $p['experiencia_anos'] ?> ano<?= $p['experiencia_anos'] == 1 ? '' : 's' ?>
                        </td>

                        <td data-label="Status">
                            <?php if ($p['status'] === 'ativo'): ?>
                                <form method="POST"
                                      action="/afro_essencia/dashboard/desligarProfissional/<?= $p['profissional_id'] ?>"
                                      onsubmit="return confirm('Tem certeza que deseja desligar este profissional?');">
                                      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                                    <button type="submit"
                                            class="btn-outline"
                                            style="padding:6px 12px;border-color:rgba(255,90,90,0.25);color:#ff8a8a;background:rgba(255,90,90,0.08);">
                                        Desligar
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="color:#888;">—</span>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/_admin-footer.php'; ?>
