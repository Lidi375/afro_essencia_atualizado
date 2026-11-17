<?php
/**
 * View: dashboard/cliente/index.php
 *
 * CSS carregado:
 *   /public/css/dashboard-profissional.css  — layout base compartilhado (sidebar, topbar)
 *   /public/css/dashboard-cliente-premium.css — identidade visual afro premium + patch v3.1
 *
 * Variáveis injetadas pelo DashboardController (não alteradas):
 *   $mensagem    string|null
 *   $erro        string|null
 *   $temAnamnese bool
 *
 * Nenhuma rota, controller, model ou consulta SQL foi modificada.
 */

// ── Dados do cliente (sessão) ─────────────────────────────────
$nomeCliente  = htmlspecialchars($_SESSION['user']['nome'] ?? '', ENT_QUOTES, 'UTF-8');
$primeiroNome = htmlspecialchars(
    explode(' ', trim($_SESSION['user']['nome'] ?? 'Você'))[0],
    ENT_QUOTES, 'UTF-8'
);
$temAnamnese  = $temAnamnese ?? false;
$anamneseUrl  = $temAnamnese ? '/afro_essencia/anamnese/show' : '/afro_essencia/anamnese/form';

// Iniciais para avatar
$iniciais = mb_strtoupper(mb_substr($nomeCliente, 0, 2, 'UTF-8'), 'UTF-8');

// ── Dica editorial rotativa (conteúdo estático, sem entrada do usuário) ───
$dicas = [
    0 => ['texto' => 'Hidratação profunda é o segredo para fios <strong>mais definidos e elásticos</strong>. Reserve um domingo para caprichar na sua máscara favorita.',               'rotulo' => 'Ritual de domingo'],
    1 => ['texto' => 'Evite pentear o cabelo seco. <strong>Use um leave-in</strong> antes de desembaraçar para proteger os fios e realçar a definição natural.',                        'rotulo' => 'Dica da semana'],
    2 => ['texto' => 'O couro cabeludo também precisa de cuidado. <strong>Massageie suavemente</strong> ao lavar — estimula a circulação e fortalece as raízes.',                      'rotulo' => 'Sabia disso?'],
    3 => ['texto' => 'Proteção térmica não é opcional. Se você usa calor, <strong>aplique sempre um protetor</strong> antes do secador ou prancha para preservar a estrutura do fio.', 'rotulo' => 'Lembrete importante'],
    4 => ['texto' => 'A água fria no final do banho sela as cutículas e deixa os <strong>cachos mais brilhantes e definidos</strong>. Experimente!',                                   'rotulo' => 'Segredo das profissionais'],
    5 => ['texto' => 'Seu cabelo merece um ritual de fim de semana. <strong>Óleo de argan ou coco</strong> na noite anterior transforma o fio até o dia seguinte.',                    'rotulo' => 'Ritual de sábado'],
    6 => ['texto' => 'Cabelo com volume é cabelo com vida. <strong>Difusor + produto definidor</strong> na raiz é o combo perfeito para realçar seus cachos naturais.',                'rotulo' => 'Dica de estilo'],
];
$dica = $dicas[date('w')];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Meu Painel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;700&family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Layout base: sidebar, topbar, badges, alertas -->
    <link rel="stylesheet" href="/afro_essencia/public/css/dashboard-profissional.css">
    <!-- Identidade visual afro premium + patch v3.1 (cards home + hero img + logo) -->
    <link rel="stylesheet" href="/afro_essencia/public/css/dashboard-cliente-premium.css">

    <!-- Font Awesome — ícones dos cards do dashboard cliente -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body>

<!-- Botão hambúrguer (mobile) -->
<button class="prof-hamburger" id="prof-hamburger" aria-label="Abrir menu" aria-expanded="false">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 12h18M3 6h18M3 18h18"/>
    </svg>
</button>

<div class="prof-layout">

    <!-- ════════════════════════════════════════
         SIDEBAR — idêntica à estrutura base
    ════════════════════════════════════════ -->
    <aside class="prof-sidebar" id="prof-sidebar" aria-label="Menu do cliente">

        <!-- Logo igual à home: imagem + Dancing Script -->
        <div class="prof-sidebar-logo">
            <img src="/afro_essencia/public/imagens/logo-flor.png" alt="Logo Flor AFRO ESSÊNCIA" class="prof-sidebar-logo__flor">
            <span class="logo-text-dancing">AFRO ESSÊNCIA</span>
        </div>

        <nav class="prof-sidebar-nav">
            <a href="/afro_essencia/dashboard" class="prof-nav-item active" aria-current="page">
                <i data-lucide="layout-dashboard"></i>
                <span>Início</span>
            </a>
            <a href="/afro_essencia/agendamento/create" class="prof-nav-item">
                <i data-lucide="calendar-plus"></i>
                <span>Agendar</span>
            </a>
            <a href="/afro_essencia/agendamento/index" class="prof-nav-item">
                <i data-lucide="clipboard-list"></i>
                <span>Meus Agendamentos</span>
            </a>
            <a href="<?= $anamneseUrl ?>" class="prof-nav-item">
                <i data-lucide="<?= $temAnamnese ? 'file-heart' : 'file-plus' ?>"></i>
                <span>Meu Cabelo</span>
            </a>
            <a href="/afro_essencia/avaliacao/minhas" class="prof-nav-item">
                <i data-lucide="star"></i>
                <span>Avaliações</span>
            </a>
            <a href="/afro_essencia/pagamento/index" class="prof-nav-item">
                <i data-lucide="credit-card"></i>
                <span>Pagamentos</span>
            </a>
            <a href="/afro_essencia/notificacao/index" class="prof-nav-item">
                <i data-lucide="bell"></i>
                <span>Notificações</span>
            </a>
        </nav>

        <div class="prof-sidebar-footer">
            <div class="prof-sidebar-user">
                <div class="prof-sidebar-avatar"><?= $iniciais ?></div>
                <div>
                    <p class="prof-sidebar-name"><?= $nomeCliente ?></p>
                    <p class="prof-sidebar-role">Cliente</p>
                </div>
            </div>
            <a href="/afro_essencia/auth/logout" class="prof-nav-logout">
                <i data-lucide="log-out"></i> Sair da conta
            </a>
        </div>

    </aside>

    <!-- Overlay (fecha sidebar no mobile) -->
    <div class="prof-overlay" id="prof-overlay" aria-hidden="true"></div>

    <!-- ════════════════════════════════════════
         CONTEÚDO PRINCIPAL
    ════════════════════════════════════════ -->
    <div class="prof-content-wrap">

        <!-- TOPBAR — logo idêntica à home -->
        <header class="prof-topbar">

            <!-- Logo: imagem real + Dancing Script (igual home) -->
            <a href="/afro_essencia/home" class="cl-topbar-logo">
                <img src="/afro_essencia/public/imagens/logo.png" alt="Logo AFRO ESSÊNCIA">
                <span class="logo-text">AFRO ESSÊNCIA</span>
            </a>

            <!-- Usuário -->
            <div class="prof-user">
                <div class="cl-topbar-crown">
                    <div class="cl-topbar-avatar"><?= $iniciais ?></div>
                    <div>
                        <span class="prof-user-name">Olá, <?= $primeiroNome ?></span>
                        <span class="prof-badge">Cliente</span>
                    </div>
                </div>
            </div>

            

        </header>

        <!-- Alertas de sessão -->
        <?php if (!empty($mensagem)): ?>
            <div class="cl-alert-success" role="alert">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="cl-alert-danger" role="alert">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- ════════════════════════════
             HERO — com imagem de fundo
        ════════════════════════════ -->
        <section class="cl-hero cl-hero--img" aria-label="Boas-vindas">
            <div class="cl-hero-crown" aria-hidden="true">♛</div>
            <div class="cl-hero-inner">

                <div class="cl-hero-eyebrow">
                    <span></span>
                    Sua coroa, seu ritual
                </div>

                <h1 class="cl-hero-title">
                    Bem-vinda de volta,<br>
                    <em><?= $primeiroNome ?></em>
                </h1>

                <p class="cl-hero-sub">
                    Cuidar do seu cabelo é um ato de amor próprio.
                    Aqui, cada fio é tratado com a atenção que sua coroa merece.
                </p>

                <div class="cl-hero-actions">
                    <a href="/afro_essencia/agendamento/create" class="cl-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            <line x1="12" y1="14" x2="12" y2="18"/><line x1="10" y1="16" x2="14" y2="16"/>
                        </svg>
                        Novo Agendamento
                    </a>
                    <a href="<?= $anamneseUrl ?>" class="cl-btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        Meu Cabelo
                    </a>
                </div>

            </div>
        </section>

        <!-- ════════════════════════════
             CARDS DE AÇÃO — estilo home
             (6 cards: 4 originais + Avaliações + Notificações)
        ════════════════════════════ -->
        <div class="cl-service-grid" role="list" aria-label="Ações rápidas">

            <!-- 1. Novo Agendamento -->
            <!-- 1. Novo Agendamento -->
            <a href="/afro_essencia/agendamento/create" class="cl-service-card cl-service-card--1" role="listitem">
                <span class="cl-service-card__icon">
                    <i class="fa-regular fa-calendar-plus"></i>
                </span>
                <h3 class="cl-service-card__title">Novo Agendamento</h3>
                <p class="cl-service-card__desc">Reserve seu horário com a profissional ideal para o seu ritual.</p>
            </a>

            <!-- 2. Meus Agendamentos -->
            <a href="/afro_essencia/agendamento/index" class="cl-service-card cl-service-card--2" role="listitem">
                <span class="cl-service-card__icon">
                    <i class="fa-regular fa-square-check"></i>
                </span>
                <h3 class="cl-service-card__title">Meus Agendamentos</h3>
                <p class="cl-service-card__desc">Visualize, gerencie e cancele seus horários marcados.</p>
            </a>

            <!-- 3. Avaliação Capilar -->
            <a href="<?= $anamneseUrl ?>" class="cl-service-card cl-service-card--3" role="listitem">
                <span class="cl-service-card__icon">
                    <i class="fa-regular fa-heart"></i>
                </span>
                <h3 class="cl-service-card__title">Avaliação Capilar</h3>
                <p class="cl-service-card__desc">
                    <?= $temAnamnese
                        ? 'Ficha completa — visualize ou atualize seu perfil capilar.'
                        : 'Preencha sua ficha para um atendimento verdadeiramente personalizado.' ?>
                </p>
            </a>

            <!-- 4. Pagamentos -->
            <a href="/afro_essencia/pagamento/index" class="cl-service-card cl-service-card--4" role="listitem">
                <span class="cl-service-card__icon">
                    <i class="fa-regular fa-credit-card"></i>
                </span>
                <h3 class="cl-service-card__title">Pagamentos</h3>
                <p class="cl-service-card__desc">Histórico completo e faturas dos seus serviços realizados.</p>
            </a>

            <!-- 5. Minhas Avaliações -->
            <a href="/afro_essencia/avaliacao/minhas" class="cl-service-card cl-service-card--5" role="listitem">
                <span class="cl-service-card__icon">
                    <i class="fa-regular fa-star"></i>
                </span>
                <h3 class="cl-service-card__title">Minhas Avaliações</h3>
                <p class="cl-service-card__desc">Confira o histórico dos seus atendimentos e avaliações enviadas.</p>
            </a>

            <!-- 6. Notificações -->
            <a href="/afro_essencia/notificacao/index" class="cl-service-card cl-service-card--6" role="listitem">
                <span class="cl-service-card__icon">
                    <i class="fa-regular fa-bell"></i>
                </span>
                <h3 class="cl-service-card__title">Notificações</h3>
                <p class="cl-service-card__desc">Confirmações, lembretes e avisos importantes do seu salão.</p>
            </a>

        </div>

        <!-- ════════════════════════════
             SAÚDE DO MEU CABELO
             (não mexido — aprovado pelo cliente)
        ════════════════════════════ -->
        <section class="cl-section" aria-label="Saúde do meu cabelo">

            <div class="cl-section-header">
                <h2 class="cl-section-title">
                    <span class="cl-section-title-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </span>
                    Saúde do Meu Cabelo
                </h2>
            </div>

            <div class="cl-hair-panel">

                <div class="cl-hair-panel-top">
                    <div>
                        <div class="cl-hair-panel-title">Seu Ritual de Cuidados</div>
                        <p class="cl-hair-panel-sub">
                            <?php if ($temAnamnese): ?>
                                Sua ficha capilar está completa. Nossas profissionais já conhecem cada detalhe do seu cabelo para um atendimento único.
                            <?php else: ?>
                                Preencha sua avaliação capilar para que possamos oferecer um cuidado verdadeiramente personalizado para a sua coroa.
                            <?php endif; ?>
                        </p>
                    </div>
                    <span class="cl-hair-status-badge <?= $temAnamnese ? 'cl-hair-status-badge--ok' : 'cl-hair-status-badge--pending' ?>">
                        <span class="dot"></span>
                        <?= $temAnamnese ? 'Ficha Completa' : 'Ficha Pendente' ?>
                    </span>
                </div>

                <div class="cl-hair-panel-body">

                    <!-- Stepper de progresso -->
                    <div class="cl-hair-steps" aria-label="Etapas do ritual capilar">

                        <div class="cl-hair-step <?= $temAnamnese ? 'cl-hair-step--done' : 'cl-hair-step--current' ?>">
                            <div class="cl-hair-step-dot <?= $temAnamnese ? 'cl-hair-step-dot--done' : 'cl-hair-step-dot--current' ?>">
                                <?php if ($temAnamnese): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2.5"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                <?php else: ?>1<?php endif; ?>
                            </div>
                            <span class="cl-hair-step-label">Avaliação<br>Capilar</span>
                        </div>

                        <div class="cl-hair-step <?= $temAnamnese ? 'cl-hair-step--current' : 'cl-hair-step--pending' ?>">
                            <div class="cl-hair-step-dot <?= $temAnamnese ? 'cl-hair-step-dot--current' : 'cl-hair-step-dot--pending' ?>">2</div>
                            <span class="cl-hair-step-label">Primeiro<br>Agendamento</span>
                        </div>

                        <div class="cl-hair-step cl-hair-step--pending">
                            <div class="cl-hair-step-dot cl-hair-step-dot--pending">3</div>
                            <span class="cl-hair-step-label">Tratamento<br>Personalizado</span>
                        </div>

                        <div class="cl-hair-step cl-hair-step--pending">
                            <div class="cl-hair-step-dot cl-hair-step-dot--pending">4</div>
                            <span class="cl-hair-step-label">Ritual<br>Contínuo</span>
                        </div>

                    </div>

                    <!-- Ações do painel -->
                    <div class="cl-hair-panel-actions">
                        <?php if ($temAnamnese): ?>
                            <a href="/afro_essencia/anamnese/show" class="cl-hair-btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Ver minha ficha
                            </a>
                            <a href="/afro_essencia/anamnese/form" class="cl-hair-btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Atualizar
                            </a>
                        <?php else: ?>
                            <a href="/afro_essencia/anamnese/form" class="cl-hair-btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="12" y1="18" x2="12" y2="12"/>
                                    <line x1="9"  y1="15" x2="15" y2="15"/>
                                </svg>
                                Fazer avaliação agora
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </section>

        <!-- ════════════════════════════
             DICA DA SEMANA
             (não mexido — aprovado pelo cliente)
        ════════════════════════════ -->
        <section class="cl-section" aria-label="Dica da semana">

            <div class="cl-section-header">
                <h2 class="cl-section-title">
                    <span class="cl-section-title-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8"  x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </span>
                    <?= htmlspecialchars($dica['rotulo'], ENT_QUOTES, 'UTF-8') ?>
                </h2>
            </div>

            <div class="cl-tip-card">
                <div class="cl-tip-icon-wrap" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div class="cl-tip-body">
                    <div class="cl-tip-rotulo">
                        <?= htmlspecialchars($dica['rotulo'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <p class="cl-tip-text">
                        <?= $dica['texto'] /* conteúdo editorial estático */ ?>
                    </p>
                    <p class="cl-tip-meta">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        Dica das profissionais Afro Essência &middot; <?= date('d/m/Y') ?>
                    </p>
                </div>
            </div>

        </section>

    </div><!-- /prof-content-wrap -->

</div><!-- /prof-layout -->

<!-- Script: sidebar mobile (lógica idêntica à do profissional) -->
<script src="/afro_essencia/public/js/global/sidebar.js" defer></script>

<script src="/afro_essencia/public/js/global/alerts.js" defer></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
