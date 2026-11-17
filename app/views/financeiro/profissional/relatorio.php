<?php
/**
 * View: financeiro/profissional/relatorio.php
 * Relatório do profissional — apenas seus próprios dados
 */

$comissoes  = $comissoes ?? [];
$totais     = $totais    ?? [];
$filtros    = $filtros   ?? [];
$nomeProf   = htmlspecialchars($_SESSION['user']['nome'] ?? 'Profissional', ENT_QUOTES);

function fmtBrlPR(float $v): string { return 'R$ ' . number_format($v, 2, ',', '.'); }
function fmtDatePR(?string $d): string { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Relatório de Comissões</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/afro_essencia/public/css/dashboard-profissional.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/financeiro.css">
    <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body>

<button class="prof-hamburger" id="prof-hamburger" aria-label="Abrir menu" aria-expanded="false">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 12h18M3 6h18M3 18h18"/>
    </svg>
</button>

<div class="prof-layout">
    <aside class="prof-sidebar" id="prof-sidebar">
        <div class="prof-sidebar-logo">
            <img src="/afro_essencia/public/imagens/logo.png" alt="Logo AFRO ESSÊNCIA">
            <span>AFRO ESSÊNCIA</span>
        </div>
        <nav class="prof-sidebar-nav">
            <a href="/afro_essencia/dashboard/profissional" class="prof-nav-item">
                <i data-lucide="layout-dashboard"></i><span>Início</span>
            </a>
            <a href="/afro_essencia/agendamento/index" class="prof-nav-item">
                <i data-lucide="calendar-days"></i><span>Agenda</span>
            </a>
            <a href="/afro_essencia/financeiro/minhas" class="prof-nav-item active" aria-current="page">
                <i data-lucide="coins"></i><span>Financeiro</span>
            </a>
            <a href="/afro_essencia/notificacao/index" class="prof-nav-item">
                <i data-lucide="bell"></i><span>Notificações</span>
            </a>
        </nav>
        <div class="prof-sidebar-footer">
            <div class="prof-sidebar-user">
                <div class="prof-sidebar-avatar"><?= mb_strtoupper(mb_substr($nomeProf, 0, 2)) ?></div>
                <div>
                    <p class="prof-sidebar-name"><?= $nomeProf ?></p>
                    <p class="prof-sidebar-role">Profissional</p>
                </div>
            </div>
            <a href="/afro_essencia/auth/logout" class="prof-logout-btn">
                <i data-lucide="log-out"></i><span>Sair</span>
            </a>
        </div>
    </aside>
    <div class="prof-overlay" id="prof-overlay"></div>

    <div class="prof-content-wrap" id="prof-main">

        <div class="prof-page-header">
            <div>
                <h1 class="prof-page-title">Relatório de Comissões</h1>
                <p class="prof-page-subtitle">Histórico financeiro de <?= $nomeProf ?></p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="/afro_essencia/financeiro/minhas" class="btn btn-outline btn-sm">
                    <i data-lucide="arrow-left"></i> Voltar
                </a>
                <a href="/afro_essencia/financeiro/meuPdf<?= !empty(array_filter($filtros)) ? '?' . http_build_query(array_filter($filtros)) : '' ?>"
                   class="btn btn-primary btn-sm" target="_blank">
                    <i data-lucide="file-down"></i> PDF
                </a>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="fin-card-prof fin-filter-card">
            <form method="GET" action="/afro_essencia/financeiro/meusRelatorios" class="fin-filter-form">
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
                        <label>Status</label>
                        <select name="status">
                            <option value="">Todos</option>
                            <option value="pendente" <?= ($filtros['status'] ?? '') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="pago"     <?= ($filtros['status'] ?? '') === 'pago'     ? 'selected' : '' ?>>Recebido</option>
                        </select>
                    </div>
                    <div class="fin-filter-actions">
                        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                        <a href="/afro_essencia/financeiro/meusRelatorios" class="btn btn-outline btn-sm">Limpar</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- TOTAIS -->
        <div class="fin-card-prof">
            <h2 class="fin-section-title">Resumo do Período</h2>
            <div class="fin-relatorio-totais">
                <div class="fin-total-item">
                    <span class="fin-total-label">Ganhos Totais</span>
                    <span class="fin-total-value"><?= fmtBrlPR((float)($totais['ganhos_totais'] ?? 0)) ?></span>
                </div>
                <div class="fin-total-item">
                    <span class="fin-total-label">Já Recebido</span>
                    <span class="fin-total-value fin-valor-pago"><?= fmtBrlPR((float)($totais['ganhos_recebidos'] ?? 0)) ?></span>
                </div>
                <div class="fin-total-item">
                    <span class="fin-total-label">A Receber</span>
                    <span class="fin-total-value fin-valor-pendente"><?= fmtBrlPR((float)($totais['ganhos_pendentes'] ?? 0)) ?></span>
                </div>
                <div class="fin-total-item">
                    <span class="fin-total-label">Atendimentos</span>
                    <span class="fin-total-value"><?= (int)($totais['total_atendimentos'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <!-- TABELA -->
        <?php if (!empty($comissoes)): ?>
        <div class="fin-card-prof">
            <div class="table-responsive">
                <table class="fin-table-prof">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Serviço</th>
                            <th>Cliente</th>
                            <th>Valor Serviço</th>
                            <th>Comissão</th>
                            <th>Status</th>
                            <th>Data Recebimento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comissoes as $c): ?>
                        <tr>
                            <td><?= fmtDatePR($c['data_geracao']) ?></td>
                            <td><?= htmlspecialchars($c['servico_nome']) ?></td>
                            <td><?= htmlspecialchars($c['cliente_nome']) ?></td>
                            <td><?= fmtBrlPR((float)$c['valor_servico']) ?></td>
                            <td><strong><?= fmtBrlPR((float)$c['valor_comissao']) ?></strong></td>
                            <td>
                                <?php
                                $map = ['pendente'=>['Pendente','badge-pendente'],'pago'=>['Recebido','badge-concluido'],'cancelado'=>['Cancelado','badge-cancelado']];
                                [$lbl,$cls] = $map[$c['status_pagamento']] ?? ['—','badge-pendente'];
                                echo "<span class=\"badge-status {$cls}\">{$lbl}</span>";
                                ?>
                            </td>
                            <td><?= $c['status_pagamento'] === 'pago' ? fmtDatePR($c['data_pagamento']) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="fin-card-prof fin-empty-state">
            <i data-lucide="file-text" class="fin-empty-icon"></i>
            <p>Nenhuma comissão no período selecionado.</p>
        </div>
        <?php endif; ?>

    </div>
</div>

<script src="/afro_essencia/public/js/pages/financeiro-profissional.js" defer></script>
</body>
</html>
