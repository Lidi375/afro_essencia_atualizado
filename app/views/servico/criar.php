<?php
$pageTitle  = 'Novo Serviço';
$activePage = 'servicos';
require __DIR__ . '/../dashboard/partials/_admin-header.php';
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <a href="/afro_essencia/dashboard/servicos">Serviços</a>
            <i data-lucide="chevron-right"></i>
            <span>Novo Serviço</span>
        </nav>
        <h1 class="subpage-title">Novo Serviço</h1>
        <p class="subpage-subtitle">Preencha os dados para cadastrar um novo serviço no catálogo</p>
    </div>
    <div class="subpage-actions">
        <a href="/afro_essencia/dashboard/servicos" class="btn-outline">
            <i data-lucide="arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if (!empty($erro)): ?>
    <div class="alert-danger" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES) ?></div>
<?php endif; ?>

<?php if (!empty($sucesso)): ?>
    <div class="alert-success"><?= htmlspecialchars($sucesso, ENT_QUOTES) ?></div>
<?php endif; ?>

<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title"><i data-lucide="scissors"></i> Dados do Serviço</h3>
    </div>

    <div style="padding:24px">
        <form method="POST" action="/afro_essencia/servico/store">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label class="adm-ficha-chave" style="display:block;margin-bottom:6px">Nome do serviço</label>
                    <input
                        type="text"
                        name="nome"
                        class="input-field no-icon"
                        placeholder="Ex.: Box Braids"
                        maxlength="120"
                        required
                        value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES) ?>">
                </div>
                <div>
                    <label class="adm-ficha-chave" style="display:block;margin-bottom:6px">Categoria</label>
                    <select name="categoria_id" class="input-field no-icon" style="appearance:none;">
                        <option value="">Sem categoria</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option
                                value="<?= (int)$cat['id'] ?>"
                                <?= (($_POST['categoria_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label class="adm-ficha-chave" style="display:block;margin-bottom:6px">Valor (R$)</label>
                    <input
                        type="number"
                        name="valor"
                        class="input-field no-icon"
                        placeholder="0,00"
                        min="0"
                        step="0.01"
                        required
                        value="<?= htmlspecialchars($_POST['valor'] ?? '', ENT_QUOTES) ?>">
                </div>
                <div>
                    <label class="adm-ficha-chave" style="display:block;margin-bottom:6px">Duração (minutos)</label>
                    <input
                        type="number"
                        name="duracao_minutos"
                        class="input-field no-icon"
                        placeholder="60"
                        min="1"
                        step="1"
                        required
                        value="<?= htmlspecialchars($_POST['duracao_minutos'] ?? '', ENT_QUOTES) ?>">
                </div>
                <div>
                    <label class="adm-ficha-chave" style="display:block;margin-bottom:6px">Status</label>
                    <select name="status" class="input-field no-icon" style="appearance:none;">
                        <option value="ativo"   <?= (($_POST['status'] ?? 'ativo') === 'ativo')   ? 'selected' : '' ?>>Ativo</option>
                        <option value="inativo" <?= (($_POST['status'] ?? '') === 'inativo') ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label class="adm-ficha-chave" style="display:block;margin-bottom:6px">Descrição (opcional)</label>
                <textarea
                    name="descricao"
                    class="input-field no-icon"
                    placeholder="Descreva brevemente o serviço"
                    rows="3"
                    style="resize:vertical; min-height:80px;"><?= htmlspecialchars($_POST['descricao'] ?? '', ENT_QUOTES) ?></textarea>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn-primary">
                    <i data-lucide="check-circle"></i>
                    Cadastrar Serviço
                </button>
                <a href="/afro_essencia/dashboard/servicos" class="btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../dashboard/partials/_admin-footer.php'; ?>
