<?php
/**
 * manutencao.php — AFRO ESSÊNCIA
 * Página de manutenção temporária.
 * Para ativar: renomeie para index.php (guarde o original).
 * O .htaccess já retorna 503 quando este arquivo é chamado via ErrorDocument.
 */
http_response_code(503);
header('Retry-After: 3600');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA — Em Manutenção</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Georgia', serif;
            background: #F5EDE4;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 56px 48px;
            text-align: center;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(75,46,43,.12);
            border-top: 4px solid #A56758;
        }
        .icon { font-size: 56px; margin-bottom: 24px; line-height: 1; }
        h1 {
            font-size: 1.6rem;
            color: #4B2E2B;
            margin-bottom: 12px;
            font-family: 'Georgia', serif;
        }
        p { font-size: 15px; color: #7A6A60; line-height: 1.7; margin-bottom: 8px; }
        .brand {
            margin-top: 32px;
            font-size: 11px;
            color: #A89080;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        @media (max-width: 480px) {
            .card { padding: 36px 24px; }
            h1 { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">♛</div>
        <h1>Voltamos em breve</h1>
        <p>Estamos realizando melhorias no sistema para servir você ainda melhor.</p>
        <p>Por favor, tente novamente em alguns minutos.</p>
        <div class="brand">AFRO ESSÊNCIA — Sistema de Agendamento</div>
    </div>
</body>
</html>
