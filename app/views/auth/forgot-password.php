<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Recuperar Senha</title>

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
            src="/afro_essencia/public/imagens/auth-login.jpg"
            alt="Afro Essência — Recuperar acesso"
            class="auth-visual-img">
        <div class="auth-visual-overlay"></div>

        <a href="/afro_essencia/" class="auth-visual-brand">
            <img src="/afro_essencia/public/imagens/logo.png" alt="Logo AFRO ESSÊNCIA">
            <span>AFRO ESSÊNCIA</span>
        </a>

        <div class="auth-visual-copy">
            <blockquote>
                "Cuidar de<br>você é o<br><em>nosso propósito.</em>"
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

            <h1>RECUPERAR SENHA</h1>
            <div class="auth-divider" aria-hidden="true"><span></span><img src="/afro_essencia/public/imagens/logo-flor.png" alt=""><span></span></div>
            <p class="auth-subtitle">Informe seu e-mail e enviaremos um link para redefinir sua senha.</p>

            <!-- MENSAGEM DE SUCESSO -->
            <?php if (!empty($_SESSION['success'])): ?>
                <p class="sucesso"><?= htmlspecialchars($_SESSION['success']) ?></p>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- ERRO -->
            <?php if (!empty($erro)): ?>
                <div class="alert-danger" role="alert"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- FORM -->
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <!-- EMAIL -->
                <div class="input-group">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                    </span>
                    <input type="email" name="email" class="input-field" placeholder="Digite seu email" required>
                </div>

                <!-- AÇÕES -->
                <div class="actions">
                    <button type="submit" class="btn-submit">ENVIAR LINK</button>
                    <a href="/afro_essencia/auth/login" class="forgot-link">Voltar ao login</a>
                </div>

            </form>

        </div>
    </div>

</main>


<script src="/afro_essencia/public/js/global/alerts.js" defer></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>

</html>
