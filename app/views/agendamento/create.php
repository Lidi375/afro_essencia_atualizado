<?php
/** @var array $profissionais */
/** @var array $servicos */
/** @var string|null $erro */

$preServico = isset($_GET['servico_id']) ? (int)$_GET['servico_id'] : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Agendamento</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/afro_essencia/public/css/auth.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/agendamento.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>

<main class="auth-layout">

    <!-- ── Painel visual (esquerda) ───────────────────────── -->
    <div class="auth-visual">
        <img
            src="/afro_essencia/public/imagens/auth-register.jpg"
            alt="Afro Essência — Agendamento"
            class="auth-visual-img">
        <div class="auth-visual-overlay"></div>

        <a href="/afro_essencia/" class="auth-visual-brand">
            <img src="/afro_essencia/public/imagens/logo.png" alt="Logo AFRO ESSÊNCIA">
            <span>AFRO ESSÊNCIA</span>
        </a>

        <div class="auth-visual-copy">
            <blockquote>
                "Agende com<br>quem entende<br><em>sua beleza.</em>"
            </blockquote>
            <cite>Afro Essência · Belo Horizonte</cite>
        </div>
    </div>

    <!-- ── Painel do formulário (direita) ─────────────────── -->
    <div class="auth-form-side">
        <div class="login-container">

            <!-- Brand mobile -->
            <header class="brand-header">
                <a href="/afro_essencia/dashboard" class="brand-link">
                    <img src="/afro_essencia/public/imagens/logo.png" alt="AFRO ESSÊNCIA" id="logo-desenho">
                    <div class="logo">AFRO ESSÊNCIA</div>
                </a>
            </header>

            <!-- Header autenticado desktop -->
            <div class="agend-auth-header">
                <a href="/afro_essencia/dashboard" class="agend-brand">
                    <img src="/afro_essencia/public/imagens/logo.png" alt="AFRO ESSÊNCIA">
                    <span>AFRO ESSÊNCIA</span>
                </a>
                <a href="/afro_essencia/auth/logout" class="agend-logout">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Sair
                </a>
            </div>

            <!-- Voltar -->
            <a href="/afro_essencia/dashboard" class="agend-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Voltar ao painel
            </a>

            <h1 class="agend-title">Agendar Serviço</h1>
            <p class="agend-subtitle">Escolha a profissional, o serviço, a data e o horário.</p>

            <?php if (!empty($erro)): ?>
                <div class="alert-danger" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <form action="/afro_essencia/agendamento/store" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <!-- Profissional -->
                <div class="form-field">
                    <label class="field-label" for="profissional_id">Profissional</label>
                    <select name="profissional_id" id="profissional_id" class="agend-select" required>
                        <option value="" disabled selected>Selecione uma profissional</option>
                        <?php foreach ($profissionais as $p): ?>
                            <option value="<?= $p['id'] ?? $p['profissional_id'] ?>">
                                <?= htmlspecialchars($p['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Serviço -->
                <div class="form-field">
                    <label class="field-label" for="servico_id">Serviço</label>

                    <!-- Select nativo oculto -->
                    <select name="servico_id" id="servico_id" data-pre-id="<?= (int)$preServico ?>" required style="display:none;" aria-hidden="true">
                        <option value="" disabled selected>Selecione um serviço</option>
                        <?php foreach ($servicos as $s): ?>
                            <option
                                value="<?= $s['id'] ?>"
                                data-imagem="<?= htmlspecialchars($s['imagem'] ?? '', ENT_QUOTES) ?>"
                                data-nome="<?= htmlspecialchars($s['nome'] ?? '', ENT_QUOTES) ?>">
                                <?= htmlspecialchars($s['nome']) ?> — R$ <?= number_format($s['valor'], 2, ',', '.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Custom dropdown com scroll -->
                    <div class="cs-wrap" id="cs-servico" role="combobox" aria-haspopup="listbox" aria-expanded="false">
                        <button type="button" class="cs-trigger agend-select" id="cs-servico-btn" aria-controls="cs-servico-list">
                            <span class="cs-label" id="cs-servico-label">Selecione um serviço</span>
                            <svg class="cs-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9C8778" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <ul class="cs-list" id="cs-servico-list" role="listbox" aria-label="Serviços disponíveis">
                            <?php foreach ($servicos as $s): ?>
                            <li class="cs-option"
                                role="option"
                                aria-selected="false"
                                data-value="<?= $s['id'] ?>"
                                data-imagem="<?= htmlspecialchars($s['imagem'] ?? '', ENT_QUOTES) ?>"
                                data-nome="<?= htmlspecialchars($s['nome'] ?? '', ENT_QUOTES) ?>">
                                <span class="cs-opt-nome"><?= htmlspecialchars($s['nome']) ?></span>
                                <span class="cs-opt-preco">R$ <?= number_format($s['valor'], 2, ',', '.') ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Duração / preview -->
                <div id="duracao-servico"></div>
                <div id="servico-preview">
                    <img id="servico-preview-img" src="" alt="">
                    <div class="preview-overlay"></div>
                    <span id="servico-preview-nome"></span>
                </div>
                <div id="horario-termino"></div>

                <!-- Data -->
                <div class="form-field">
                    <label class="field-label" for="data">Data</label>
                    <div class="agend-date-wrap">
                        <input
                            type="text"
                            name="data"
                            id="data"
                            class="input-field no-icon input-date"
                            placeholder="Selecione a data"
                            autocomplete="off"
                            required>
                        <span class="date-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <path d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <!-- Horário -->
                <div class="form-field">
                    <label class="field-label" for="hora">Horário</label>
                    <select name="hora" id="hora" class="agend-select" required>
                        <option value="" disabled selected>Selecione um horário</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">CONFIRMAR AGENDAMENTO</button>

            </form>

        </div>
    </div>

</main>

<script src="/afro_essencia/public/js/pages/agendamento.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

<!-- pré-seleção via data-pre-id — lida por agendamento.js -->

<script src="/afro_essencia/public/js/global/alerts.js" defer></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
