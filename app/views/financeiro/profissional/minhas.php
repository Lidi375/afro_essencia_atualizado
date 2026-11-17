<?php
/**
 * View: financeiro/profissional/minhas.php
 * Minhas comissões — painel do profissional
 * Seguro: exibe APENAS dados do profissional autenticado
 */

$comissoes  = $comissoes  ?? [];
$totais     = $totais     ?? [];
$evolucao   = $evolucao   ?? [];
$config     = $config     ?? null;
$filtros    = $filtros    ?? [];
$mensagem   = $mensagem   ?? null;
$erro       = $erro       ?? null;
$nomeProf   = htmlspecialchars($_SESSION['user']['nome'] ?? 'Profissional', ENT_QUOTES);

function fmtBrlProf(float $v): string { return 'R$ ' . number_format($v, 2, ',', '.'); }
function fmtDateProf(?string $d): string { return $d ? date('d/m/Y', strtotime($d)) : '—'; }

$labels  = json_encode(array_column($evolucao, 'mes'));
$comisJs = json_encode(array_column($evolucao, 'comissoes'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Minhas Comissões</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/afro_essencia/public/css/dashboard-profissional.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/financeiro.css">
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
</head>
<body>

<!-- Hamburger mobile -->
<button class="prof-hamburger" id="prof-hamburger" aria-label="Abrir menu" aria-expanded="false">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 12h18M3 6h18M3 18h18"/>
    </svg>
</button>

<div class="prof-layout">

    <!-- SIDEBAR -->
    <aside class="prof-sidebar" id="prof-sidebar" aria-label="Menu do profissional">
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

    <!-- Overlay mobile -->
    <div class="prof-overlay" id="prof-overlay"></div>

    <!-- MAIN -->
    <div class="prof-content-wrap" id="prof-main">

        <div class="prof-page-header">
            <div>
                <h1 class="prof-page-title">Minhas Comissões</h1>
                <p class="prof-page-subtitle">Seus ganhos e histórico financeiro</p>
            </div>
            <a href="/afro_essencia/financeiro/meuPdf<?= !empty(array_filter($filtros ?? [])) ? '?' . http_build_query(array_filter($filtros)) : '' ?>"
               class="btn btn-primary btn-sm" target="_blank">
                <i data-lucide="file-down"></i> PDF
            </a>
        </div>

        <?php if ($mensagem): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
        <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <!-- CONFIG ATIVA -->
        <?php if ($config): ?>
        <div class="fin-config-badge">
            <i data-lucide="info"></i>
            Seu modelo de comissão:
            <?php if ($config['tipo'] === 'percentual'): ?>
                <strong><?= number_format((float)$config['percentual'], 1) ?>% sobre o valor do serviço</strong>
            <?php else: ?>
                <strong>R$ <?= number_format((float)$config['valor_fixo'], 2, ',', '.') ?> por atendimento</strong>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- KPI CARDS -->
        <div class="fin-kpi-grid fin-kpi-grid-prof">
            <div class="fin-kpi-card">
                <div class="fin-kpi-icon"><i data-lucide="wallet"></i></div>
                <div class="fin-kpi-body">
                    <span class="fin-kpi-label">Ganhos Totais</span>
                    <span class="fin-kpi-value"><?= fmtBrlProf((float)($totais['ganhos_totais'] ?? 0)) ?></span>
                </div>
            </div>
            <div class="fin-kpi-card fin-kpi-pago">
                <div class="fin-kpi-icon"><i data-lucide="check-circle-2"></i></div>
                <div class="fin-kpi-body">
                    <span class="fin-kpi-label">Recebidos</span>
                    <span class="fin-kpi-value"><?= fmtBrlProf((float)($totais['ganhos_recebidos'] ?? 0)) ?></span>
                </div>
            </div>
            <div class="fin-kpi-card fin-kpi-pendente">
                <div class="fin-kpi-icon"><i data-lucide="clock"></i></div>
                <div class="fin-kpi-body">
                    <span class="fin-kpi-label">Pendentes</span>
                    <span class="fin-kpi-value"><?= fmtBrlProf((float)($totais['ganhos_pendentes'] ?? 0)) ?></span>
                </div>
            </div>
            <div class="fin-kpi-card fin-kpi-receita">
                <div class="fin-kpi-icon"><i data-lucide="calendar-check-2"></i></div>
                <div class="fin-kpi-body">
                    <span class="fin-kpi-label">Ganhos do Mês</span>
                    <span class="fin-kpi-value"><?= fmtBrlProf((float)($totais['ganhos_mes'] ?? 0)) ?></span>
                </div>
            </div>
            <div class="fin-kpi-card">
                <div class="fin-kpi-icon"><i data-lucide="scissors"></i></div>
                <div class="fin-kpi-body">
                    <span class="fin-kpi-label">Atendimentos</span>
                    <span class="fin-kpi-value"><?= (int)($totais['total_atendimentos'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <!-- GRÁFICO -->
        <?php if (!empty($evolucao)): ?>
        <div class="fin-chart-card fin-card-prof">
            <div class="fin-section-header">
                <h2 class="fin-section-title">Evolução dos Ganhos</h2>
                <span class="fin-section-sub">Últimos 6 meses</span>
            </div>
            <canvas id="chartMeus" height="100"></canvas>
        </div>
        <?php endif; ?>

        <!-- FILTROS -->
        <div class="fin-card-prof fin-filter-card">
            <form method="GET" action="/afro_essencia/financeiro/minhas" class="fin-filter-form">
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
                        <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="search"></i> Filtrar</button>
                        <a href="/afro_essencia/financeiro/minhas" class="btn btn-outline btn-sm">Limpar</a>
                    </div>
                </div>
            </form>
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
                            <th>Recebido em</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comissoes as $c): ?>
                        <tr>
                            <td><?= fmtDateProf($c['data_geracao']) ?></td>
                            <td><?= htmlspecialchars($c['servico_nome']) ?></td>
                            <td><?= htmlspecialchars($c['cliente_nome']) ?></td>
                            <td><?= fmtBrlProf((float)$c['valor_servico']) ?></td>
                            <td><strong><?= fmtBrlProf((float)$c['valor_comissao']) ?></strong></td>
                            <td>
                                <?php
                                $map = ['pendente'=>['Pendente','badge-pendente'],'pago'=>['Recebido','badge-concluido'],'cancelado'=>['Cancelado','badge-cancelado']];
                                [$lbl,$cls] = $map[$c['status_pagamento']] ?? ['—','badge-pendente'];
                                echo "<span class=\"badge-status {$cls}\">{$lbl}</span>";
                                ?>
                            </td>
                            <td><?= $c['status_pagamento'] === 'pago' ? fmtDateProf($c['data_pagamento']) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="fin-card-prof fin-empty-state">
            <i data-lucide="coins" class="fin-empty-icon"></i>
            <p>Nenhuma comissão encontrada.</p>
            <p class="fin-empty-hint">Comissões aparecem aqui após a conclusão de atendimentos com pagamento aprovado.</p>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
// Passa dados do PHP para financeiro.js via variáveis globais
window.FIN_PROF_CHART_DATA = {
    labels:    <?= $labels ?>,
    comissoes: <?= $comisJs ?>
};
</script>
<script src="/afro_essencia/public/js/pages/financeiro-profissional.js" defer></script>
</body>
</html>
