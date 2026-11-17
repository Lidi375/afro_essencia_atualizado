<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * EmailService — envio de e-mails via SMTP (PHPMailer / Gmail)
 */
class EmailService
{
    /**
     * Envia link de redefinição de senha
     */
    public function enviarLinkRedefinicao(string $emailDestino, string $nomeUsuario, string $token): bool
    {
        $link = rtrim(APP_URL, '/') . '/auth/resetPassword/' . rawurlencode($token);

        return $this->enviar(
            $emailDestino,
            $nomeUsuario,
            'Redefinição de senha — Afro Essência',
            $this->templateRedefinicao($nomeUsuario, $link),
            "Olá, {$nomeUsuario}!\n\nClique no link para redefinir sua senha (válido por 1 hora):\n{$link}\n\nSe não foi você, ignore este e-mail.\n\nAfro Essência"
        );
    }

    /**
     * Envia senha temporária gerada (conversão cliente → profissional)
     */
    public function enviarSenhaGerada(string $emailDestino, string $nomeUsuario, string $novaSenha): bool
    {
        $link = rtrim(APP_URL, '/') . '/auth/login';

        return $this->enviar(
            $emailDestino,
            $nomeUsuario,
            'Seu acesso ao painel profissional — Afro Essência',
            $this->templateSenhaGerada($nomeUsuario, $novaSenha, $link),
            "Olá, {$nomeUsuario}!\n\nSua conta foi convertida para profissional.\nSua senha temporária: {$novaSenha}\n\nAcesse: {$link}\n\nAfro Essência"
        );
    }

    /**
     * Envia link de redefinição para novo profissional (sem expor senha)
     */
    public function enviarBoasVindasProfissional(string $emailDestino, string $nomeUsuario, string $token): bool
    {
        $link = rtrim(APP_URL, '/') . '/auth/resetPassword/' . rawurlencode($token);

        return $this->enviar(
            $emailDestino,
            $nomeUsuario,
            'Bem-vindo ao painel profissional — Afro Essência',
            $this->templateBoasVindas($nomeUsuario, $link),
            "Olá, {$nomeUsuario}!\n\nSua conta foi convertida para profissional.\nCrie sua senha clicando no link:\n{$link}\n\nO link expira em 1 hora.\n\nAfro Essência"
        );
    }

    // ─── Núcleo de envio ──────────────────────────────────────────────────────
    private function enviar(
        string $para,
        string $nome,
        string $assunto,
        string $htmlBody,
        string $textBody
    ): bool {

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            // Evita que a requisição fique travada por minutos caso o
            // servidor SMTP esteja lento, fora do ar ou inacessível
            // (o padrão do PHPMailer é 300s sem isso).
            $mail->Timeout       = 10;
            $mail->SMTPKeepAlive = false;

            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($para, $nome);

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('EmailService erro: ' . $mail->ErrorInfo);
            return false;
        }
    }

    // ─── Templates HTML ───────────────────────────────────────────────────────

    private function templateRedefinicao(string $nome, string $link): string
    {
        $nomeEsc = htmlspecialchars($nome, ENT_QUOTES);
        $linkEsc = htmlspecialchars($link, ENT_QUOTES);
        $ano     = date('Y');

        return "<!DOCTYPE html>
<html lang='pt-br'>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4;padding:40px 0;'>
<tr><td align='center'>
<table width='520' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);'>
<tr><td style='background:#2c1a0e;padding:32px;text-align:center;'>
<h1 style='color:#d4a96a;margin:0;font-size:22px;letter-spacing:2px;'>AFRO ESSÊNCIA</h1>
<p style='color:#c8a97e;margin:8px 0 0;font-size:13px;'>Beleza que valoriza sua identidade</p>
</td></tr>
<tr><td style='padding:40px 36px;'>
<p style='color:#333;font-size:16px;margin:0 0 12px;'>Olá, <strong>{$nomeEsc}</strong>!</p>
<p style='color:#555;font-size:14px;line-height:1.6;margin:0 0 28px;'>
Recebemos uma solicitação para redefinir a senha da sua conta.<br>
Clique no botão abaixo para criar uma nova senha. O link é válido por <strong>1 hora</strong>.
</p>
<table cellpadding='0' cellspacing='0' width='100%'>
<tr><td align='center'>
<a href='{$linkEsc}' style='display:inline-block;background:#2c1a0e;color:#d4a96a;text-decoration:none;padding:14px 36px;border-radius:6px;font-size:15px;font-weight:bold;letter-spacing:1px;'>
REDEFINIR MINHA SENHA
</a>
</td></tr>
</table>
<p style='color:#999;font-size:12px;margin:28px 0 0;line-height:1.6;'>
Se o botão não funcionar, copie e cole este link no navegador:<br>
<a href='{$linkEsc}' style='color:#2c1a0e;word-break:break-all;'>{$linkEsc}</a>
</p>
<hr style='border:none;border-top:1px solid #eee;margin:28px 0;'>
<p style='color:#bbb;font-size:12px;margin:0;'>Se você não solicitou a redefinição, ignore este e-mail.</p>
</td></tr>
<tr><td style='background:#f9f5f0;padding:20px;text-align:center;'>
<p style='color:#bbb;font-size:11px;margin:0;'>© {$ano} Afro Essência — Todos os direitos reservados</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>";
    }

    private function templateSenhaGerada(string $nome, string $senha, string $link): string
    {
        $nomeEsc = htmlspecialchars($nome,  ENT_QUOTES);
        $senhaEsc = htmlspecialchars($senha, ENT_QUOTES);
        $linkEsc = htmlspecialchars($link,  ENT_QUOTES);
        $ano     = date('Y');

        return "<!DOCTYPE html>
<html lang='pt-br'>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4;padding:40px 0;'>
<tr><td align='center'>
<table width='520' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);'>
<tr><td style='background:#2c1a0e;padding:32px;text-align:center;'>
<h1 style='color:#d4a96a;margin:0;font-size:22px;letter-spacing:2px;'>AFRO ESSÊNCIA</h1>
<p style='color:#c8a97e;margin:8px 0 0;font-size:13px;'>Seu acesso ao painel profissional</p>
</td></tr>
<tr><td style='padding:40px 36px;'>
<p style='color:#333;font-size:16px;margin:0 0 12px;'>Olá, <strong>{$nomeEsc}</strong>!</p>
<p style='color:#555;font-size:14px;line-height:1.6;margin:0 0 28px;'>
Sua conta foi convertida para profissional. Use as credenciais abaixo para acessar o sistema:
</p>
<div style='background:#f7f2e8;border-radius:12px;padding:22px;margin-bottom:20px;'>
<p style='margin:0 0 8px;color:#333;font-size:14px;'>Senha temporária:</p>
<p style='margin:0;font-size:18px;letter-spacing:1px;font-weight:700;color:#2c1a0e;'>{$senhaEsc}</p>
</div>
<p style='color:#555;font-size:14px;margin:0 0 20px;'>Recomendamos alterar a senha após o primeiro login.</p>
<table cellpadding='0' cellspacing='0' width='100%'>
<tr><td align='center'>
<a href='{$linkEsc}' style='display:inline-block;background:#2c1a0e;color:#d4a96a;text-decoration:none;padding:14px 36px;border-radius:6px;font-size:15px;font-weight:bold;'>
ACESSAR PAINEL
</a>
</td></tr>
</table>
<p style='color:#999;font-size:12px;margin:28px 0 0;'>Se não foi você, entre em contato com a administração.</p>
</td></tr>
<tr><td style='background:#f9f5f0;padding:20px;text-align:center;'>
<p style='color:#bbb;font-size:11px;margin:0;'>© {$ano} Afro Essência — Todos os direitos reservados</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>";
    }

    private function templateBoasVindas(string $nome, string $link): string
    {
        $nomeEsc = htmlspecialchars($nome, ENT_QUOTES);
        $linkEsc = htmlspecialchars($link, ENT_QUOTES);
        $ano     = date('Y');

        return "<!DOCTYPE html>
<html lang='pt-br'>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4;padding:40px 0;'>
<tr><td align='center'>
<table width='520' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);'>
<tr><td style='background:#2c1a0e;padding:32px;text-align:center;'>
<h1 style='color:#d4a96a;margin:0;font-size:22px;letter-spacing:2px;'>AFRO ESSÊNCIA</h1>
<p style='color:#c8a97e;margin:8px 0 0;font-size:13px;'>Bem-vindo ao painel profissional!</p>
</td></tr>
<tr><td style='padding:40px 36px;'>
<p style='color:#333;font-size:16px;margin:0 0 12px;'>Olá, <strong>{$nomeEsc}</strong>!</p>
<p style='color:#555;font-size:14px;line-height:1.6;margin:0 0 28px;'>
Sua conta foi promovida a <strong>profissional</strong> no sistema Afro Essência.<br>
Crie sua senha de acesso clicando no botão abaixo. O link é válido por <strong>1 hora</strong>.
</p>
<table cellpadding='0' cellspacing='0' width='100%'>
<tr><td align='center'>
<a href='{$linkEsc}' style='display:inline-block;background:#2c1a0e;color:#d4a96a;text-decoration:none;padding:14px 36px;border-radius:6px;font-size:15px;font-weight:bold;'>
CRIAR MINHA SENHA
</a>
</td></tr>
</table>
<p style='color:#999;font-size:12px;margin:28px 0 0;line-height:1.6;'>
Link direto:<br>
<a href='{$linkEsc}' style='color:#2c1a0e;word-break:break-all;'>{$linkEsc}</a>
</p>
<hr style='border:none;border-top:1px solid #eee;margin:28px 0;'>
<p style='color:#bbb;font-size:12px;margin:0;'>Se não esperava este e-mail, entre em contato com a administração.</p>
</td></tr>
<tr><td style='background:#f9f5f0;padding:20px;text-align:center;'>
<p style='color:#bbb;font-size:11px;margin:0;'>© {$ano} Afro Essência — Todos os direitos reservados</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>";
    }

    /**
     * Notifica administradores sobre nova candidatura Trabalhe Conosco.
     */
    public function notificarNovaCandidatura(string $nome, string $email, string $especialidade): bool
    {
        $painel = rtrim(APP_URL, '/') . '/dashboard/candidaturas';

        $html = "<!DOCTYPE html>
<html lang='pt-br'>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4;padding:40px 0;'>
<tr><td align='center'>
<table width='520' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);'>
<tr><td style='background:#2c1a0e;padding:28px 32px;text-align:center;'>
<h1 style='color:#d4a96a;margin:0;font-size:20px;letter-spacing:2px;'>AFRO ESSÊNCIA</h1>
<p style='color:#c8a97e;margin:8px 0 0;font-size:13px;'>Nova Candidatura — Trabalhe Conosco</p>
</td></tr>
<tr><td style='padding:36px;'>
<p style='color:#333;font-size:15px;margin:0 0 20px;'>Uma nova candidatura foi recebida:</p>
<table width='100%' cellpadding='8' cellspacing='0' style='border-collapse:collapse;font-size:14px;'>
<tr style='background:#f9f5f0;'><td style='color:#666;font-weight:bold;width:40%;border-radius:4px;padding:10px 14px;'>Nome</td><td style='color:#333;padding:10px 14px;'>" . htmlspecialchars($nome, ENT_QUOTES) . "</td></tr>
<tr><td style='color:#666;font-weight:bold;padding:10px 14px;'>E-mail</td><td style='color:#333;padding:10px 14px;'>" . htmlspecialchars($email, ENT_QUOTES) . "</td></tr>
<tr style='background:#f9f5f0;'><td style='color:#666;font-weight:bold;padding:10px 14px;'>Especialidade</td><td style='color:#333;padding:10px 14px;'>" . htmlspecialchars($especialidade, ENT_QUOTES) . "</td></tr>
</table>
<br>
<table cellpadding='0' cellspacing='0' width='100%'>
<tr><td align='center'>
<a href='" . htmlspecialchars($painel, ENT_QUOTES) . "' style='display:inline-block;background:#2c1a0e;color:#d4a96a;text-decoration:none;padding:13px 32px;border-radius:6px;font-size:14px;font-weight:bold;'>
VER CANDIDATURAS
</a>
</td></tr>
</table>
</td></tr>
<tr><td style='background:#f9f5f0;padding:18px;text-align:center;'>
<p style='color:#bbb;font-size:11px;margin:0;'>© " . date('Y') . " Afro Essência</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>";

        $texto = "Nova candidatura recebida!\nNome: {$nome}\nE-mail: {$email}\nEspecialidade: {$especialidade}\n\nAcesse o painel para analisar: {$painel}";

        return $this->enviar(
            MAIL_FROM_EMAIL,
            MAIL_FROM_NAME,
            "Nova candidatura: {$nome} — {$especialidade}",
            $html,
            $texto
        );
    }
}
