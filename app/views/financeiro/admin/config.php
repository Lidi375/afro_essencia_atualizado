<?php
/**
 * View: financeiro/admin/config.php
 * Configuração de comissões dos profissionais
 */

$pageTitle  = 'Financeiro — Configurar Comissões';
$activePage = 'financeiro';
$extraCss   = ['financeiro.css'];
$extraJs    = ['/afro_essencia/public/js/pages/financeiro.js'];

$configs       = $configs       ?? [];
$profissionais = $profissionais ?? [];
$mensagem      = $mensagem      ?? null;
$erro          = $erro          ?? null;

$csrfToken = $_SESSION['csrf_token'] ??
    ($_SESSION['csrf_token'] = bin2hex(random_bytes(32)));

// Mapa de configs existentes por profissional_id
$configMap = [];
foreach ($configs as $cfg) {
    if ((int)$cfg['ativo']) {
        $configMap[(int)$cfg['profissional_id']] = $cfg;
    }
}

require __DIR__ . '/../../dashboard/partials/_admin-header.php';
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <a href="/afro_essencia/financeiro">Financeiro</a>
            <i data-lucide="chevron-right"></i>
            <span>Configurar Comissões</span>
        </nav>
        <h1 class="subpage-title">Configurar Comissões</h1>
        <p class="subpage-subtitle">Defina o modelo de comissão de cada profissional</p>
    </div>
</div>

<?php if ($mensagem): ?>
<div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if ($erro): ?>
<div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<!-- FORMULÁRIO -->
<div class="subpage-card fin-config-form-card">
    <h2 class="fin-section-title" style="margin-bottom:1.25rem">Nova / Atualizar Configuração</h2>
    <form method="POST" action="/afro_essencia/financeiro/salvarConfig" class="fin-config-form" id="formConfig">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="fin-form-grid">
            <div class="fin-form-group fin-form-full">
                <label for="profissional_id">Profissional <span class="fin-required">*</span></label>
                <select name="profissional_id" id="profissional_id" required>
                    <option value="">Selecione o profissional...</option>
                    <?php foreach ($profissionais as $p): ?>
                        <option value="<?= (int)$p['id'] ?>">
                            <?= htmlspecialchars($p['nome'] ?? $p['usuario_nome'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="fin-form-group">
                <label>Tipo de Comissão <span class="fin-required">*</span></label>
                <div class="fin-tipo-toggle">
                    <label class="fin-tipo-option">
                        <input type="radio" name="tipo" value="percentual" checked>
                        <span class="fin-tipo-label">
                            <i data-lucide="percent"></i>
                            <strong>Percentual</strong>
                            <small>Ex: 30% do serviço</small>
                        </span>
                    </label>
                    <label class="fin-tipo-option">
                        <input type="radio" name="tipo" value="fixo">
                        <span class="fin-tipo-label">
                            <i data-lucide="tag"></i>
                            <strong>Valor Fixo</strong>
                            <small>Ex: R$ 50 por atendimento</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="fin-form-group" id="grupoPercentual">
                <label for="percentual">Percentual (%) <span class="fin-required">*</span></label>
                <div class="fin-input-prefix">
                    <span>%</span>
                    <input type="number" name="percentual" id="percentual"
                           min="0.01" max="100" step="0.01" placeholder="Ex: 30.00">
                </div>
            </div>

            <div class="fin-form-group" id="grupoFixo" style="display:none">
                <label for="valor_fixo">Valor Fixo (R$) <span class="fin-required">*</span></label>
                <div class="fin-input-prefix">
                    <span>R$</span>
                    <input type="number" name="valor_fixo" id="valor_fixo"
                           min="0.01" step="0.01" placeholder="Ex: 50.00">
                </div>
            </div>

            <div class="fin-form-group fin-form-full">
                <label for="observacoes">Observações</label>
                <textarea name="observacoes" id="observacoes" rows="2"
                          placeholder="Opcional: descreva os termos acordados..."></textarea>
            </div>
        </div>

        <div class="fin-form-actions">
            <a href="/afro_essencia/financeiro" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i> Salvar Configuração
            </button>
        </div>
    </form>
</div>

<!-- LISTA DE CONFIGURAÇÕES ATUAIS -->
<?php if (!empty($configs)): ?>
<div class="subpage-card">
    <h2 class="fin-section-title" style="margin-bottom:1rem">Configurações Ativas</h2>
    <div class="table-responsive">
        <table class="adm-table fin-table">
            <thead>
                <tr>
                    <th>Profissional</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Última Atualização</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configs as $cfg): ?>
                <?php if ((int)$cfg['ativo']): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($cfg['profissional_nome']) ?></strong></td>
                    <td>
                        <?php if ($cfg['tipo'] === 'percentual'): ?>
                            <span class="fin-badge-tipo">Percentual</span>
                        <?php else: ?>
                            <span class="fin-badge-tipo fin-badge-fixo">Valor Fixo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($cfg['tipo'] === 'percentual'): ?>
                            <?= number_format((float)$cfg['percentual'], 1) ?>%
                        <?php else: ?>
                            R$ <?= number_format((float)$cfg['valor_fixo'], 2, ',', '.') ?>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge-status badge-concluido">Ativa</span></td>
                    <td><?= date('d/m/Y H:i', strtotime($cfg['updated_at'])) ?></td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
// Passa mapa de configurações do PHP para financeiro.js
window.FIN_CONFIG_MAP = <?= json_encode($configMap, JSON_UNESCAPED_UNICODE) ?>;
</script>

<?php require __DIR__ . '/../../dashboard/partials/_admin-footer.php'; ?>
