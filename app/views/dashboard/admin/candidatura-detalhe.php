<?php
$pageTitle  = 'Detalhe da Candidatura';
$activePage = 'candidaturas';
require __DIR__ . '/../partials/_admin-header.php';

$statusClass = [
    'pendente'   => 'badge-pendente',
    'em_analise' => 'badge-em-analise',
    'aprovado'   => 'badge-aprovado',
    'reprovado'  => 'badge-reprovado',
][$candidatura['status']] ?? '';
?>

<div class="subpage-header">
    <div>
        <nav class="subpage-breadcrumb">
            <a href="/afro_essencia/dashboard">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <a href="/afro_essencia/dashboard/candidaturas">Candidaturas</a>
            <i data-lucide="chevron-right"></i>
            <span>Detalhe #<?= (int)$candidatura['id'] ?></span>
        </nav>
        <h1 class="subpage-title">
            <?= htmlspecialchars($candidatura['nome']) ?>
            <span class="badge <?= $statusClass ?>" style="margin-left:12px;font-size: 14px;">
                <?= Candidatura::statusLabel($candidatura['status']) ?>
            </span>
        </h1>
        <p class="subpage-subtitle">Candidatura recebida em <?= date('d/m/Y \à\s H:i', strtotime($candidatura['created_at'])) ?></p>
    </div>
    <div class="subpage-actions">
        <a href="/afro_essencia/dashboard/candidaturas" class="btn-outline">
            <i data-lucide="arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if (!empty($mensagem)): ?>
    <div class="alert-success"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if (!empty($erro)): ?>
    <div class="alert-danger"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<!-- ─── Painel de ação de status ─────────────────────────────────────────── -->
<?php if ($candidatura['status'] !== 'aprovado'): ?>
<div class="status-action-card">
    <h4 class="status-action-title">
        <i data-lucide="sliders-horizontal"></i>
        Atualizar Status da Candidatura
    </h4>
    <form method="POST"
          action="/afro_essencia/dashboard/candidaturaAtualizarStatus/<?= (int)$candidatura['id'] ?>"
          onsubmit="return confirmarAcao(this)">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="status-select-row">
            <div>
                <label style="font-size: 14px;font-weight:700;color:var(--ds-text-muted);text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:6px;">Novo Status</label>
                <select name="status" required>
                    <option value="pendente"   <?= $candidatura['status'] === 'pendente'   ? 'selected' : '' ?>>Pendente</option>
                    <option value="em_analise" <?= $candidatura['status'] === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
                    <option value="aprovado"   <?= $candidatura['status'] === 'aprovado'   ? 'selected' : '' ?>>Aprovado</option>
                    <option value="reprovado"  <?= $candidatura['status'] === 'reprovado'  ? 'selected' : '' ?>>Reprovado</option>
                </select>
            </div>
            <div style="flex:1;">
                <label style="font-size: 14px;font-weight:700;color:var(--ds-text-muted);text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:6px;">Observação (opcional)</label>
                <textarea name="observacao" placeholder="Descreva o motivo da mudança de status..." maxlength="500"></textarea>
            </div>
            <div style="padding-bottom:2px;">
                <button type="submit" class="btn-primary" style="padding:10px 18px;font-size: 14px;white-space:nowrap;">
                    <i data-lucide="save"></i>
                    Salvar Status
                </button>
            </div>
        </div>
        <p style="font-size: 14px;color:var(--ds-text-subtle);margin-top:10px;">
            ⚠️ Ao selecionar <strong>Aprovado</strong>, um perfil profissional será criado automaticamente e um e-mail de boas-vindas será enviado ao candidato.
        </p>
    </form>
</div>
<?php else: ?>
<div class="status-action-card" style="background:rgba(76,175,80,.06);border-color:rgba(76,175,80,.2);">
    <p style="font-size:14px;color:#a5d6a7;display:flex;align-items:center;gap:8px;margin:0;">
        <i data-lucide="check-circle-2"></i>
        <strong>Candidatura aprovada.</strong> O perfil profissional já foi criado no sistema.
    </p>
</div>
<?php endif; ?>

<!-- ─── Dados da candidatura ──────────────────────────────────────────────── -->
<div class="cand-detalhe-grid">

    <!-- Coluna 1: Dados pessoais -->
    <div class="cand-info-block">
        <h4 class="cand-info-title">
            <i data-lucide="user" style="width:14px;height:14px;"></i>
            Dados Pessoais
        </h4>
        <div class="cand-info-row">
            <span class="cand-info-key">Nome</span>
            <span class="cand-info-val"><?= htmlspecialchars($candidatura['nome']) ?></span>
        </div>
        <div class="cand-info-row">
            <span class="cand-info-key">E-mail</span>
            <span class="cand-info-val">
                <a href="mailto:<?= htmlspecialchars($candidatura['email']) ?>"
                   style="color:#e1a756;text-decoration:none;">
                    <?= htmlspecialchars($candidatura['email']) ?>
                </a>
            </span>
        </div>
        <div class="cand-info-row">
            <span class="cand-info-key">Telefone</span>
            <span class="cand-info-val"><?= htmlspecialchars($candidatura['telefone']) ?></span>
        </div>
        <div class="cand-info-row">
            <span class="cand-info-key">Cidade / Estado</span>
            <span class="cand-info-val"><?= htmlspecialchars($candidatura['cidade']) ?> — <?= htmlspecialchars($candidatura['estado']) ?></span>
        </div>
        <?php if (!empty($candidatura['instagram'])): ?>
        <div class="cand-info-row">
            <span class="cand-info-key">Instagram</span>
            <span class="cand-info-val"><?= htmlspecialchars($candidatura['instagram']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($candidatura['portfolio_url'])): ?>
        <div class="cand-info-row">
            <span class="cand-info-key">Portfólio</span>
            <span class="cand-info-val">
                <a href="<?= htmlspecialchars($candidatura['portfolio_url']) ?>"
                   target="_blank" rel="noopener noreferrer" style="color:#e1a756;text-decoration:none;">
                    <?= htmlspecialchars($candidatura['portfolio_url']) ?>
                    <i data-lucide="external-link" style="width:12px;height:12px;margin-left:4px;"></i>
                </a>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Coluna 2: Dados profissionais -->
    <div class="cand-info-block">
        <h4 class="cand-info-title">
            <i data-lucide="briefcase" style="width:14px;height:14px;"></i>
            Dados Profissionais
        </h4>
        <div class="cand-info-row">
            <span class="cand-info-key">Especialidade</span>
            <span class="cand-info-val"><?= htmlspecialchars($candidatura['especialidade']) ?></span>
        </div>
        <div class="cand-info-row">
            <span class="cand-info-key">Experiência</span>
            <span class="cand-info-val"><?= (int)$candidatura['experiencia_anos'] ?> ano<?= $candidatura['experiencia_anos'] != 1 ? 's' : '' ?></span>
        </div>
        <div class="cand-info-row">
            <span class="cand-info-key">Candidatura</span>
            <span class="cand-info-val"><?= date('d/m/Y \à\s H:i', strtotime($candidatura['created_at'])) ?></span>
        </div>
        <div class="cand-info-row">
            <span class="cand-info-key">Última atualização</span>
            <span class="cand-info-val"><?= date('d/m/Y \à\s H:i', strtotime($candidatura['updated_at'] ?? $candidatura['created_at'])) ?></span>
        </div>
        <!-- Downloads -->
        <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
            <?php if (!empty($candidatura['arquivo_curriculo'])): ?>
                <a href="/afro_essencia/dashboard/candidaturaDownload/<?= (int)$candidatura['id'] ?>?tipo=curriculo"
                   class="btn-download">
                    <i data-lucide="file-down" style="width:14px;height:14px;"></i>
                    Baixar Currículo PDF
                </a>
            <?php endif; ?>
            <?php if (!empty($candidatura['arquivo_certificado'])): ?>
                <a href="/afro_essencia/dashboard/candidaturaDownload/<?= (int)$candidatura['id'] ?>?tipo=certificado"
                   class="btn-download">
                    <i data-lucide="award" style="width:14px;height:14px;"></i>
                    Baixar Certificados PDF
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Descrição profissional -->
<div class="data-panel" style="margin-bottom:24px;">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="file-text"></i>
            Descrição Profissional
        </h3>
    </div>
    <div style="padding:20px 24px;">
        <p style="font-size:14px;line-height:1.75;color:var(--ds-text-soft);white-space:pre-wrap;margin:0;">
            <?= htmlspecialchars($candidatura['descricao']) ?>
        </p>
    </div>
</div>

<!-- Histórico de análise -->
<div class="data-panel">
    <div class="data-panel-header">
        <h3 class="data-panel-title">
            <i data-lucide="history"></i>
            Histórico de Análise
        </h3>
    </div>
    <div style="padding:16px 24px;">
        <?php if (empty($historico)): ?>
            <p style="color:var(--ds-text-subtle);font-size: 14px;text-align:center;padding:20px 0;">
                Nenhuma ação registrada ainda.
            </p>
        <?php else: ?>
            <?php foreach ($historico as $h): ?>
                <div class="historico-item">
                    <div class="historico-dot"></div>
                    <div class="historico-body">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span class="badge <?= [
                                'pendente'   => 'badge-pendente',
                                'em_analise' => 'badge-em-analise',
                                'aprovado'   => 'badge-aprovado',
                                'reprovado'  => 'badge-reprovado',
                            ][$h['status_anterior']] ?? '' ?>" style="font-size: 14px;">
                                <?= Candidatura::statusLabel($h['status_anterior']) ?>
                            </span>
                            <span style="color:var(--ds-text-subtle);font-size: 14px;">→</span>
                            <span class="badge <?= [
                                'pendente'   => 'badge-pendente',
                                'em_analise' => 'badge-em-analise',
                                'aprovado'   => 'badge-aprovado',
                                'reprovado'  => 'badge-reprovado',
                            ][$h['status_novo']] ?? '' ?>" style="font-size: 14px;">
                                <?= Candidatura::statusLabel($h['status_novo']) ?>
                            </span>
                        </div>
                        <div class="historico-meta">
                            <?= date('d/m/Y H:i', strtotime($h['created_at'])) ?>
                            <?php if (!empty($h['admin_nome'])): ?>
                                · por <strong><?= htmlspecialchars($h['admin_nome']) ?></strong>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($h['observacao'])): ?>
                            <div class="historico-obs">"<?= htmlspecialchars($h['observacao']) ?>"</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="/afro_essencia/public/js/pages/admin-candidatura.js"></script>

<?php require __DIR__ . '/../partials/_admin-footer.php'; ?>
