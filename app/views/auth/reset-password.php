<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Redefinir Senha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/afro_essencia/public/css/auth.css">
</head>
<body>

<main class="auth-layout">

    <!-- Painel visual (esquerda) -->
    <div class="auth-visual">
        <img src="/afro_essencia/public/imagens/auth-login.jpg"
             alt="Afro Essência — Nova senha"
             class="auth-visual-img">
        <div class="auth-visual-overlay"></div>

        <a href="/afro_essencia/" class="auth-visual-brand">
            <img src="/afro_essencia/public/imagens/logo.png" alt="Logo AFRO ESSÊNCIA">
            <span>AFRO ESSÊNCIA</span>
        </a>

        <div class="auth-visual-copy">
            <blockquote>
                "Segurança<br>é parte da<br><em>nossa essência.</em>"
            </blockquote>
            <cite>Afro Essência · Beleza com identidade</cite>
        </div>
    </div>

    <!-- Painel do formulário (direita) -->
    <div class="auth-form-side">
        <div class="login-container">

            <!-- Brand mobile -->
            <header class="brand-header">
                <a href="/afro_essencia/" class="brand-link">
                    <img src="/afro_essencia/public/imagens/logo.png" alt="logo" id="logo-desenho">
                    <div class="logo">AFRO ESSÊNCIA</div>
                </a>
            </header>

            <h1>REDEFINIR SENHA</h1>
            <div class="auth-divider" aria-hidden="true"><span></span><img src="/afro_essencia/public/imagens/logo-flor.png" alt=""><span></span></div>
            <p class="auth-subtitle">Escolha uma senha forte com pelo menos 8 caracteres.</p>

            <?php if (!empty($erro)): ?>
                <div class="alert-danger" role="alert"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

                <!-- NOVA SENHA -->
                <div class="input-group">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <input type="password" name="novaSenha" id="novaSenha"
                           class="input-field" placeholder="Nova senha (mín. 8 caracteres)"
                           required minlength="8" autocomplete="new-password">
                    <span class="toggle-pass" onclick="togglePassword('novaSenha','eyeA','eyeAc')" aria-label="Mostrar/ocultar senha">
                        <svg id="eyeA" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eyeAc" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8 1.5-2.5 3.5-4.5 5.5-6"/>
                            <path d="M1 1l22 22"/><path d="M9.9 9.9A3 3 0 0 0 14.1 14.1"/>
                        </svg>
                    </span>
                </div>

                <!-- CONFIRMAR SENHA -->
                <div class="input-group">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <input type="password" name="confirmarSenha" id="confirmarSenha"
                           class="input-field" placeholder="Confirmar nova senha"
                           required minlength="8" autocomplete="new-password">
                    <span class="toggle-pass" onclick="togglePassword('confirmarSenha','eyeB','eyeBc')" aria-label="Mostrar/ocultar confirmação">
                        <svg id="eyeB" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eyeBc" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8 1.5-2.5 3.5-4.5 5.5-6"/>
                            <path d="M1 1l22 22"/><path d="M9.9 9.9A3 3 0 0 0 14.1 14.1"/>
                        </svg>
                    </span>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-submit">SALVAR NOVA SENHA</button>
                    <a href="/afro_essencia/auth/login" class="forgot-link">← Voltar ao login</a>
                </div>
            </form>

        </div>
    </div>

</main>

<script src="/afro_essencia/public/js/pages/auth.js"></script>
<script src="/afro_essencia/public/js/global/alerts.js" defer></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
