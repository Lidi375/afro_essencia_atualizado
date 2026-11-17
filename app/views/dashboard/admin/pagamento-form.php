<?php
$pageTitle  = 'Registrar Pagamento';
$activePage = 'agendamentos';
require __DIR__ . '/../partials/_admin-header.php';
?>

<!-- AVISO: distinção entre fluxos de pagamento -->
<div style="background:rgba(209,138,19,.10);border:1px solid rgba(209,138,19,.30);border-radius:10px;padding:13px 18px;margin-bottom:20px;font-size: 14px;color:#e0b96a;display:flex;align-items:flex-start;gap:12px;">
    <i data-lucide="info" style="width:18px;height:18px;flex-shrink:0;margin-top:1px;"></i>
    <div>
        <strong>Registro manual de pagamento</strong> — Use este formulário apenas para pagamentos realizados <em>presencialmente</em> (dinheiro, PIX avulso, etc.).<br>
        Pagamentos feitos online pelo cliente são processados automaticamente pelo gateway e não precisam ser registrados aqui.
    </div>
</div>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <a href="/afro_essencia/dashboard/agendamentos">Agendamentos</a>
            <i data-lucide="chevron-right"></i>
            <span>Pagamento</span>
        </nav>
        <h1 class="subpage-title">Registrar Pagamento</h1>
        <p class="subpage-subtitle">Agendamento #<?= (int)$agendamento['id'] ?> — <?= htmlspecialchars($servico['nome'] ?? '') ?></p>
    </div>
    <div class="subpage-actions">
        <a href="/afro_essencia/dashboard/agendamentos" class="btn-outline">
            <i data-lucide="arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if (!empty($erro)): ?>
    <div class="alert-danger" role="alert"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<?php if (!empty($sucesso)): ?>
    <div class="alert-success"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>

<?php if ($jaPago): ?>
    <div class="data-panel">
        <div class="data-panel-header">
            <h3 class="data-panel-title"><i data-lucide="check-circle"></i> Pagamento já registrado</h3>
        </div>
        <div class="adm-ficha-grid">
            <div class="adm-ficha-item">
                <span class="adm-ficha-chave">Método</span>
                <span class="adm-ficha-valor"><?= ucfirst($jaPago['metodo_pagamento']) ?></span>
            </div>
            <div class="adm-ficha-item">
                <span class="adm-ficha-chave">Valor</span>
                <span class="adm-ficha-valor cell-value">R$ <?= number_format($jaPago['valor'], 2, ',', '.') ?></span>
            </div>
            <div class="adm-ficha-item">
                <span class="adm-ficha-chave">Status</span>
                <span class="adm-ficha-valor"><span class="badge badge-concluido"><?= ucfirst($jaPago['status_pagamento']) ?></span></span>
            </div>
            <div class="adm-ficha-item">
                <span class="adm-ficha-chave">Data</span>
                <span class="adm-ficha-valor"><?= !empty($jaPago['data_pagamento']) ? date('d/m/Y H:i', strtotime($jaPago['data_pagamento'])) : '—' ?></span>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="data-panel">
        <div class="data-panel-header">
            <h3 class="data-panel-title"><i data-lucide="banknote"></i> Registrar Pagamento</h3>
        </div>
        <div style="padding:24px">
            <form method="POST" action="/afro_essencia/dashboard/pagamentoStore">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="agendamento_id" value="<?= (int)$agendamento['id'] ?>">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label class="adm-ficha-chave" style="display:block;margin-bottom:6px">Método de Pagamento</label>
                        <select name="metodo_pagamento"
                            class="input-field no-icon" style="appearance:none;">
                            <option value="pix">PIX</option>
                            <option value="cartao">Cartão</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="transferencia">Transferência</option>
                        </select>
                    </div>
                    <div>
                        <label class="adm-ficha-chave" style="display:block;margin-bottom:6px">
                            Valor (R$) — sugerido: R$ <?= number_format($servico['valor'] ?? 0, 2, ',', '.') ?>
                        </label>
                        <input type="text" name="valor"
                            value="<?= number_format($servico['valor'] ?? 0, 2, ',', '.') ?>"
                            class="input-field no-icon">
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button type="submit" class="btn-primary">
                        <i data-lucide="check-circle"></i>
                        Confirmar Pagamento
                    </button>
                    <a href="/afro_essencia/dashboard/agendamentos" class="btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/_admin-footer.php'; ?>
