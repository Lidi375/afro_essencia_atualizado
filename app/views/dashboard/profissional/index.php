<?php
/**
 * View: dashboard/profissional/index.php
 * CSS:  /public/css/dashboard-profissional.css
 */

$nomeProf  = htmlspecialchars($_SESSION['user']['nome'] ?? 'Profissional', ENT_QUOTES);
$hojeLabel = date('d/m/Y');

function statusBadge(string $status): string {
    $map = [
        'pendente'     => ['label' => 'Pendente',     'class' => 'badge-pendente'],
        'confirmado'   => ['label' => 'Confirmado',   'class' => 'badge-confirmado'],
        'em_andamento' => ['label' => 'Em andamento', 'class' => 'badge-em_andamento'],
        'concluido'    => ['label' => 'Concluído',    'class' => 'badge-concluido'],
        'cancelado'    => ['label' => 'Cancelado',    'class' => 'badge-cancelado'],
    ];
    $s     = $map[$status] ?? ['label' => ucfirst($status), 'class' => 'badge-pendente'];
    return "<span class=\"badge-status {$s['class']}\">{$s['label']}</span>";
}

function estrelas(float $nota): string {
    $cheias    = (int) round($nota);
    $filled    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="#d4a96a"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    $empty     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#CBB8A6" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    $html      = '<span class="estrelas-wrap">';
    for ($i = 1; $i <= 5; $i++) $html .= ($i <= $cheias) ? $filled : $empty;
    return $html . '</span>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Painel Profissional</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/afro_essencia/public/css/dashboard-profissional.css">
    <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body>

<!-- Hamburger — fora do prof-layout para ficar sempre no topo no mobile -->
<button class="prof-hamburger" id="prof-hamburger" aria-label="Abrir menu" aria-expanded="false">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 12h18M3 6h18M3 18h18"/>
    </svg>
</button>

<div class="prof-layout">

    <!-- ── SIDEBAR ─────────────────────────────────────────── -->
    <aside class="prof-sidebar" id="prof-sidebar" aria-label="Menu do profissional">

        <div class="prof-sidebar-logo">
            <img src="/afro_essencia/public/imagens/logo.png" alt="Logo AFRO ESSÊNCIA">
            <span>AFRO ESSÊNCIA</span>
        </div>

        <nav class="prof-sidebar-nav">
            <a href="/afro_essencia/dashboard/profissional" class="prof-nav-item active" aria-current="page">
                <i data-lucide="layout-dashboard"></i><span>Início</span>
            </a>
            <a href="/afro_essencia/notificacao/index" class="prof-nav-item">
                <i data-lucide="bell"></i><span>Notificações</span>
            </a>
        </nav>

        <div class="prof-sidebar-footer">
            <div class="prof-sidebar-user">
                <div class="prof-sidebar-avatar">
                    <?= mb_strtoupper(mb_substr($nomeProf, 0, 2)) ?>
                </div>
                <div>
                    <p class="prof-sidebar-name"><?= $nomeProf ?></p>
                    <p class="prof-sidebar-role">Profissional</p>
                </div>
            </div>
            <a href="/afro_essencia/auth/logout" class="prof-nav-logout">
                <i data-lucide="log-out"></i> Sair da conta
            </a>
        </div>

    </aside>

    <!-- Overlay mobile -->
    <div class="prof-overlay" id="prof-overlay" aria-hidden="true"></div>

    <!-- ── CONTEÚDO PRINCIPAL ───────────────────────────────── -->
    <div class="prof-content-wrap" id="prof-main">

        <!-- Topbar -->
        <header class="prof-topbar">
            <div class="prof-brand">
                <img src="/afro_essencia/public/imagens/logo.png" alt="Logo">
                <span class="prof-brand-name">AFRO ESSÊNCIA</span>
            </div>
            <div class="prof-user">
                <span class="prof-user-name"><?= $nomeProf ?></span>
                <span class="prof-badge">Profissional</span>
            </div>
            <div class="prof-topbar-actions">
                <a href="/afro_essencia/notificacao/index" class="btn-topbar">
                    <i data-lucide="bell" style="width:15px;height:15px;"></i>
                    Notificações
                </a>
                <a href="/afro_essencia/auth/logout" class="btn-topbar logout">
                    <i data-lucide="log-out" style="width:15px;height:15px;"></i>
                    Sair
                </a>
            </div>
        </header>

        <!-- Breadcrumb -->
        <nav class="prof-breadcrumb" aria-label="Caminho">
            <a href="/afro_essencia/">Início</a>
            <span aria-hidden="true">›</span>
            <span aria-current="page">Painel Profissional</span>
        </nav>

        <!-- Alertas -->
        <?php if (!empty($mensagem)): ?>
            <div class="prof-alert success" role="alert"><?= htmlspecialchars($mensagem, ENT_QUOTES) ?></div>
        <?php endif; ?>
        <?php if (!empty($erro)): ?>
            <div class="prof-alert danger" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <!-- Saudação -->
        <div class="prof-greeting">
            <h1>Olá, <?= $nomeProf ?> ✨</h1>
            <p>Hoje é <?= date('d/m/Y') ?> — <?= count($agendaHoje ?? []) ?> atendimento(s) programado(s) para hoje</p>
        </div>

        <!-- ── MÉTRICAS ──────────────────────────────────────── -->
        <div class="prof-metrics">

            <div class="metric-card">
                <div class="metric-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                </div>
                <div class="metric-value"><?= (int)($metricas['hoje'] ?? 0) ?></div>
                <div class="metric-label">Hoje</div>
            </div>

            <div class="metric-card">
                <div class="metric-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                    </svg>
                </div>
                <div class="metric-value"><?= (int)($metricas['proximos'] ?? 0) ?></div>
                <div class="metric-label">Próximos</div>
            </div>

            <div class="metric-card">
                <div class="metric-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/>
                    </svg>
                </div>
                <div class="metric-value"><?= (int)($metricas['atendimentos_mes'] ?? 0) ?></div>
                <div class="metric-label">Concluídos/Mês</div>
            </div>

            <div class="metric-card">
                <div class="metric-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div class="metric-value">R$&nbsp;<?= number_format((float)($metricas['receita_mes'] ?? 0), 0, ',', '.') ?></div>
                <div class="metric-label">Receita/Mês</div>
            </div>

            <div class="metric-card">
                <div class="metric-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="#d4a96a">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <div class="metric-value"><?= number_format((float)($metricas['nota_media'] ?? 0), 1, ',', '.') ?></div>
                <div class="metric-label">Nota Média</div>
            </div>

        </div>

        <!-- ── AGENDA DE HOJE ──────────────────────────────── -->
        <section class="prof-section">
            <h2 class="prof-section-title">
                <i data-lucide="calendar-check"></i>
                Agenda de Hoje — <?= $hojeLabel ?>
            </h2>

            <?php if (empty($agendaHoje)): ?>
                <div class="prof-empty">
                    <i data-lucide="calendar-x"></i>
                    <p>Nenhum atendimento agendado para hoje.</p>
                </div>
            <?php else: ?>
                <div class="hoje-grid">
                    <?php foreach ($agendaHoje as $ag): ?>
                        <div class="hoje-card">
                            <div class="hoje-hora">
                                <?= htmlspecialchars(substr($ag['hora_inicio'], 0, 5)) ?>
                                &nbsp;→&nbsp;
                                <?= htmlspecialchars(substr($ag['hora_fim'], 0, 5)) ?>
                            </div>
                            <div class="hoje-servico"><?= htmlspecialchars($ag['servico'] ?? '') ?></div>
                            <div class="hoje-cliente">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                                <?= htmlspecialchars($ag['cliente_nome'] ?? '') ?>
                            </div>
                            <div class="hoje-valor">R$ <?= number_format((float)($ag['valor'] ?? 0), 2, ',', '.') ?></div>
                            <div style="margin-top:8px;"><?= statusBadge($ag['status'] ?? 'pendente') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ── PRÓXIMOS AGENDAMENTOS ───────────────────────── -->
        <section class="prof-section">
            <h2 class="prof-section-title">
                <i data-lucide="clock"></i>
                Próximos Agendamentos
            </h2>

            <?php if (empty($proximos)): ?>
                <div class="prof-empty">
                    <i data-lucide="calendar"></i>
                    <p>Nenhum agendamento futuro no momento.</p>
                </div>
            <?php else: ?>
                <div class="ag-table-wrap">
                    <table class="ag-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Valor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proximos as $ag): ?>
                                <tr>
                                    <td data-label="Data"><?= date('d/m/Y', strtotime($ag['data_agendamento'])) ?></td>
                                    <td data-label="Horário"><?= substr($ag['hora_inicio'],0,5) ?> – <?= substr($ag['hora_fim'],0,5) ?></td>
                                    <td data-label="Cliente">
                                        <div class="ag-cliente-nome"><?= htmlspecialchars($ag['cliente_nome'] ?? '') ?></div>
                                        <?php if (!empty($ag['cliente_email'])): ?>
                                            <div class="ag-servico-nome"><?= htmlspecialchars($ag['cliente_email']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Serviço"><?= htmlspecialchars($ag['servico'] ?? '') ?></td>
                                    <td data-label="Valor">R$ <?= number_format((float)($ag['valor'] ?? 0), 2, ',', '.') ?></td>
                                    <td data-label="Status"><?= statusBadge($ag['status'] ?? 'pendente') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- ── HISTÓRICO ───────────────────────────────────── -->
        <section class="prof-section">
            <h2 class="prof-section-title">
                <i data-lucide="history"></i>
                Histórico de Atendimentos
            </h2>

            <?php if (empty($historico)): ?>
                <div class="prof-empty">
                    <i data-lucide="inbox"></i>
                    <p>Nenhum atendimento no histórico ainda.</p>
                </div>
            <?php else: ?>
                <div class="ag-table-wrap">
                    <table class="ag-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Valor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historico as $ag): ?>
                                <tr>
                                    <td data-label="Data"><?= date('d/m/Y', strtotime($ag['data_agendamento'])) ?></td>
                                    <td data-label="Horário"><?= substr($ag['hora_inicio'],0,5) ?> – <?= substr($ag['hora_fim'],0,5) ?></td>
                                    <td data-label="Cliente">
                                        <div class="ag-cliente-nome"><?= htmlspecialchars($ag['cliente_nome'] ?? '') ?></div>
                                    </td>
                                    <td data-label="Serviço"><?= htmlspecialchars($ag['servico'] ?? '') ?></td>
                                    <td data-label="Valor">R$ <?= number_format((float)($ag['valor'] ?? 0), 2, ',', '.') ?></td>
                                    <td data-label="Status"><?= statusBadge($ag['status'] ?? 'pendente') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- ── AVALIAÇÕES ─────────────────────────────────── -->
        <?php if (!empty($avaliacoes)): ?>
        <section class="prof-section">
            <h2 class="prof-section-title">
                <i data-lucide="star"></i>
                Avaliações dos Clientes
                <span class="prof-section-count">(<?= count($avaliacoes) ?>)</span>
            </h2>
            <div class="aval-grid">
                <?php foreach (array_slice($avaliacoes, 0, 6) as $av): ?>
                    <div class="aval-card">
                        <div class="aval-stars">
                            <?= estrelas((float)($av['nota'] ?? 0)) ?>
                            <strong><?= htmlspecialchars($av['nota']) ?>/5</strong>
                        </div>
                        <?php if (!empty($av['comentario'])): ?>
                            <p class="aval-comentario">"<?= htmlspecialchars($av['comentario']) ?>"</p>
                        <?php endif; ?>
                        <p class="aval-meta">
                            Por <?= htmlspecialchars($av['cliente_nome'] ?? 'Cliente') ?>
                            · <time><?= date('d/m/Y', strtotime($av['created_at'])) ?></time>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </div><!-- /.prof-content-wrap -->

</div><!-- /.prof-layout -->

<script src="/afro_essencia/public/js/global/sidebar.js" defer></script>

<script src="/afro_essencia/public/js/global/alerts.js" defer></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
