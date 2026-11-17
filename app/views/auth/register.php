<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Cadastro</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/afro_essencia/public/css/auth.css">
</head>

<body>

<main class="auth-layout">

    <!-- ── Painel visual (esquerda) ───────────────────────── -->
    <div class="auth-visual">
        <img
            src="/afro_essencia/public/imagens/auth-register.jpg"
            alt="Afro Essência — Faça parte"
            class="auth-visual-img">
        <div class="auth-visual-overlay"></div>

        <a href="/afro_essencia/" class="auth-visual-brand">
            <img src="/afro_essencia/public/imagens/logo.png" alt="Logo AFRO ESSÊNCIA">
            <span>AFRO ESSÊNCIA</span>
        </a>

        <div class="auth-visual-copy">
            <blockquote>
                "Faça parte<br>da nossa<br><em>comunidade.</em>"
            </blockquote>
            <cite>Afro Essência · Beleza com identidade</cite>
        </div>
    </div>

    <!-- ── Painel do formulário (direita) ─────────────────── -->
    <div class="auth-form-side">
        <div class="login-container">

            <!-- Brand (visível só no mobile) -->
            <header class="brand-header">
                <a href="/afro_essencia/" class="brand-link">
                    <img src="/afro_essencia/public/imagens/logo.png" alt="logo" id="logo-desenho">
                    <div class="logo">AFRO ESSÊNCIA</div>
                </a>
            </header>

            <!-- TÍTULO -->
            <h1>CADASTRO</h1>
            <div class="auth-divider" aria-hidden="true"><span></span><img src="/afro_essencia/public/imagens/logo-flor.png" alt=""><span></span></div>

            <!-- ERRO / SUCESSO -->
            <?php if (!empty($erro)): ?>
                <div class="alert-danger" role="alert"><?= htmlspecialchars($erro ?? '', ENT_QUOTES) ?></div>
            <?php endif; ?>

            <!-- FORMULÁRIO -->
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <!-- NOME -->
                <div class="input-group">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                            <circle cx="12" cy="7" r="4"/>
                            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"/>
                        </svg>
                    </span>
                    <input type="text" name="nome" class="input-field" placeholder="Nome completo" required>
                </div>

                <!-- EMAIL -->
                <div class="input-group">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                    </span>
                    <input type="email" name="email" class="input-field" placeholder="Email" required>
                </div>

                <!-- SENHA -->
                <div class="input-group">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <input type="password" name="senha" id="senha" class="input-field" placeholder="Senha" required>
                    <span class="toggle-pass" onclick="togglePassword('senha','eye1','eye1c')">
                        <svg id="eye1" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eye1c" width="20" height="20" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-5.94"/>
                            <path d="M1 1l22 22"/>
                            <path d="M9.88 9.88A3 3 0 0 0 14.12 14.12"/>
                        </svg>
                    </span>
                </div>

                <!-- CONFIRMAR SENHA -->
                <div class="input-group">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <input type="password" name="confirmarSenha" id="confirmarSenha" class="input-field" placeholder="Confirmar senha" required>
                    <span class="toggle-pass" onclick="togglePassword('confirmarSenha','eye2','eye2c')">
                        <svg id="eye2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eye2c" width="20" height="20" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-5.94"/>
                            <path d="M1 1l22 22"/>
                            <path d="M9.88 9.88A3 3 0 0 0 14.12 14.12"/>
                        </svg>
                    </span>
                </div>

                <!-- BOTÃO -->
                <div class="actions">
                    <button type="submit" class="btn-submit">CADASTRAR</button>
                    <a href="/afro_essencia/auth/login" class="forgot-link">Já tem conta? Entrar</a>
                    <a href="/afro_essencia/" class="forgot-link back-home-link">&#8592; Voltar ao site</a>
                </div>

            </form>

        </div>
    </div>

</main>

<!-- JS -->
<script src="/afro_essencia/public/js/pages/auth.js"></script>


<script src="/afro_essencia/public/js/global/alerts.js" defer></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
