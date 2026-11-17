<?php
/**
 * Dashboard Administrativo - AFRO ESSÊNCIA
 * View principal do painel admin
 */

// Defaults para evitar erros se dados não vierem
$totais             = $totais ?? [];
$hoje               = $hoje ?? [];
$agendaHoje         = $agendaHoje ?? [];
$faturamentoDias    = $faturamentoDias ?? [];
$topServicos        = $topServicos ?? [];
$financeiro         = $financeiro ?? [];
$profissionaisDados = $profissionaisDados ?? [];

$pageTitle  = $pageTitle  ?? 'Dashboard Administrativo';
$activePage = $activePage ?? 'dashboard';
$extraCss   = ['dashboard.css', 'admin-dashboard.css'];
$extraJs    = [
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    '/afro_essencia/public/js/pages/admin-dashboard.js',
    '/afro_essencia/public/js/global/alerts.js',
];

require __DIR__ . '/../partials/_admin-header.php';
?>


        <!-- Cabeçalho -->
        <header class="adm-header">
            <div>
                <h1 class="adm-title">Dashboard</h1>
                <p class="adm-subtitle">
                    Bem-vindo, <?= htmlspecialchars($_SESSION['user']['nome'] ?? 'Admin') ?> · 
                    <?= date('d/m/Y') ?>
                </p>
            </div>
            <a href="/afro_essencia" class="btn-ver-site">
                <i data-lucide="external-link"></i>
                Ver site
            </a>
        </header>

        <?php if (!empty($mensagem)): ?>
            <div class="alert-success"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <!-- ===== SEÇÃO 1: CARDS DO DIA ===== -->
        <section class="section">
            <h2 class="section-title">Resumo de Hoje</h2>
            <div class="cards-grid cards-5">

                <div class="metric-card">
                    <div class="metric-icon icon-gold">
                        <i data-lucide="banknote"></i>
                    </div>
                    <div class="metric-body">
                        <span class="metric-label">Faturamento do Dia</span>
                        <span class="metric-value">
                            R$ <?= number_format($hoje['faturamento'] ?? 0, 2, ',', '.') ?>
                        </span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon icon-terra">
                        <i data-lucide="calendar-check"></i>
                    </div>
                    <div class="metric-body">
                        <span class="metric-label">Agendamentos Hoje</span>
                        <span class="metric-value"><?= $hoje['agendamentos'] ?? 0 ?></span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon icon-warn">
                        <i data-lucide="x-circle"></i>
                    </div>
                    <div class="metric-body">
                        <span class="metric-label">Cancelamentos</span>
                        <span class="metric-value"><?= $hoje['cancelamentos'] ?? 0 ?></span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon icon-green">
                        <i data-lucide="user-plus"></i>
                    </div>
                    <div class="metric-body">
                        <span class="metric-label">Novos Clientes</span>
                        <span class="metric-value"><?= $hoje['novos_clientes'] ?? 0 ?></span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon icon-terra">
                        <i data-lucide="check-circle"></i>
                    </div>
                    <div class="metric-body">
                        <span class="metric-label">Serviços Realizados</span>
                        <span class="metric-value"><?= $hoje['concluidos'] ?? 0 ?></span>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===== SEÇÃO 2: TOTAIS GERAIS ===== -->
        <section class="section">
            <h2 class="section-title">Totais do Sistema</h2>
            <div class="cards-grid cards-4">

                <div class="metric-card mini">
                    <div class="metric-icon icon-gold">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="metric-body">
                        <span class="metric-label">Total de Clientes</span>
                        <span class="metric-value"><?= $totais['clientes'] ?? 0 ?></span>
                    </div>
                </div>

                <div class="metric-card mini">
                    <div class="metric-icon icon-terra">
                        <i data-lucide="scissors"></i>
                    </div>
                    <div class="metric-body">
                        <span class="metric-label">Profissionais</span>
                        <span class="metric-value"><?= $totais['profissionais'] ?? 0 ?></span>
                    </div>
                </div>

                <div class="metric-card mini">
                    <div class="metric-icon icon-gold">
                        <i data-lucide="sparkles"></i>
                    </div>
                    <div class="metric-body">
                        <span class="metric-label">Serviços Ativos</span>
                        <span class="metric-value"><?= $totais['servicos'] ?? 0 ?></span>
                    </div>
                </div>

                <div class="metric-card mini">
                    <div class="metric-icon icon-terra">
                        <i data-lucide="calendar"></i>
                    </div>
                    <div class="metric-body">
                        <span class="metric-label">Agendamentos Totais</span>
                        <span class="metric-value"><?= $totais['agendamentos'] ?? 0 ?></span>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===== SEÇÃO 3: GRÁFICOS ===== -->
        <section class="section">
            <div class="charts-row">

                <!-- Gráfico de Faturamento -->
                <div class="chart-card chart-large">
                    <div class="chart-header">
                        <h3>Faturamento nos Últimos 30 Dias</h3>
                        <span class="chart-badge">Apenas concluídos</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="chartFaturamento"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Serviços -->
                <div class="chart-card chart-small">
                    <div class="chart-header">
                        <h3>Top Serviços</h3>
                        <span class="chart-badge">Por agendamentos</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="chartServicos"></canvas>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===== SEÇÃO 4: AGENDA DO DIA + FINANCEIRO ===== -->
        <section class="section">
            <div class="two-col-row">

                <!-- Agenda do Dia -->
                <div class="panel">
                    <div class="panel-header">
                        <h3><i data-lucide="calendar-clock"></i> Agenda de Hoje</h3>
                        <a href="/afro_essencia/dashboard/agendamentos" class="panel-link">Ver tudo</a>
                    </div>
                    <div class="agenda-list">
                        <?php if (empty($agendaHoje)): ?>
                            <p class="empty-msg">Nenhum agendamento para hoje.</p>
                        <?php else: ?>
                            <?php foreach ($agendaHoje as $ag): ?>
                                <div class="agenda-item">
                                    <div class="agenda-hora">
                                        <?= substr($ag['hora_inicio'], 0, 5) ?>
                                    </div>
                                    <div class="agenda-info">
                                        <strong><?= htmlspecialchars($ag['cliente']) ?></strong>
                                        <span><?= htmlspecialchars($ag['servico']) ?> · <?= htmlspecialchars($ag['profissional']) ?></span>
                                    </div>
                                    <span class="badge badge-<?= $ag['status'] ?>">
                                        <?= ucfirst($ag['status']) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Financeiro do Mês -->
                <div class="panel">
                    <div class="panel-header">
                        <h3><i data-lucide="trending-up"></i> Financeiro do Mês</h3>
                    </div>
                    
                    <div class="financeiro-destaque">
                        <span class="fin-label">Receita Total</span>
                        <span class="fin-valor">
                            R$ <?= number_format($financeiro['receita_mes'] ?? 0, 2, ',', '.') ?>
                        </span>
                    </div>

                    <div class="fin-row">
                        <i data-lucide="user-plus"></i>
                        <span>Novos clientes no mês</span>
                        <strong><?= $financeiro['novos_clientes_mes'] ?? 0 ?></strong>
                    </div>

                    <?php if (!empty($financeiro['por_metodo'])): ?>
                        <h4 class="fin-subtitle">Por forma de pagamento</h4>
                        <?php foreach ($financeiro['por_metodo'] as $pm): ?>
                            <div class="fin-row">
                                <i data-lucide="credit-card"></i>
                                <span><?= ucfirst($pm['metodo']) ?></span>
                                <strong>R$ <?= number_format($pm['total'], 2, ',', '.') ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-msg" style="margin-top:12px">Sem pagamentos registrados este mês.</p>
                    <?php endif; ?>
                </div>

            </div>
        </section>

        <!-- ===== SEÇÃO 5: PROFISSIONAIS ===== -->
        <section class="section">
            <div class="panel panel-full">
                <div class="panel-header">
                    <h3><i data-lucide="scissors"></i> Desempenho dos Profissionais (Mês Atual)</h3>
                    <a href="/afro_essencia/dashboard/profissionais" class="panel-link">Ver todos</a>
                </div>

                <?php if (empty($profissionaisDados)): ?>
                    <p class="empty-msg">Nenhum dado de profissional este mês.</p>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Profissional</th>
                                    <th>Atendimentos</th>
                                    <th>Faturamento</th>
                                    <th>Progresso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Calcula o máximo para barra de progresso
                                $maxFat = max(array_column($profissionaisDados, 'faturamento') ?: [1]);
                                foreach ($profissionaisDados as $prof):
                                    $pct = $maxFat > 0 ? round(($prof['faturamento'] / $maxFat) * 100) : 0;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($prof['profissional']) ?></td>
                                    <td><?= (int)$prof['atendimentos'] ?></td>
                                    <td>R$ <?= number_format($prof['faturamento'], 2, ',', '.') ?></td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= htmlspecialchars($pct ?? '', ENT_QUOTES) ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ===== SEÇÃO 6: ATALHOS DE NAVEGAÇÃO ===== -->
        <section class="section">
            <h2 class="section-title">Atalhos</h2>
            <div class="atalhos-grid">
                <a href="/afro_essencia/dashboard/agendamentos" class="atalho-card">
                    <i data-lucide="calendar-days"></i>
                    <span>Agendamentos</span>
                </a>
                <a href="/afro_essencia/dashboard/clientes" class="atalho-card">
                    <i data-lucide="users"></i>
                    <span>Clientes</span>
                </a>
                <a href="/afro_essencia/dashboard/profissionais" class="atalho-card">
                    <i data-lucide="scissors"></i>
                    <span>Profissionais</span>
                </a>
                <a href="/afro_essencia/dashboard/servicos" class="atalho-card">
                    <i data-lucide="sparkles"></i>
                    <span>Serviços</span>
                </a>
                <a href="/afro_essencia/dashboard/usuarios" class="atalho-card">
                    <i data-lucide="shield-check"></i>
                    <span>Usuários</span>
                </a>
                <a href="/afro_essencia/dashboard/avaliacoes" class="atalho-card">
                    <i data-lucide="star"></i>
                    <span>Avaliações</span>
                </a>
            </div>
        </section>

<!-- Dados PHP para o JS de gráficos — padrão de passagem via variáveis globais -->
<script>
window.adminFaturamentoDias = <?= json_encode($faturamentoDias) ?>;
window.adminTopServicos     = <?= json_encode($topServicos) ?>;
</script>
<?php require __DIR__ . '/../partials/_admin-footer.php'; ?>

