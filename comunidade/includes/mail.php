<?php

declare(strict_types=1);

require_once __DIR__ . '/smtp.php';

function send_password_reset_email(string $email, string $name, string $password): array
{
    $cfg = app_config();

    if (empty($cfg['mail_enabled'])) {
        return ['ok' => false, 'error' => 'Envio de e-mail desativado. Entre em contato com o suporte.'];
    }

    $fromEmail = (string) ($cfg['mail_from_email'] ?? 'suporte@agenciajob.com');
    $replyTo = (string) ($cfg['mail_reply_to'] ?? 'suporte@agenciajob.com');
    $fromName = (string) ($cfg['mail_from_name'] ?? 'Comunidade Figurinhas da Copa');
    $loginUrl = (string) ($cfg['mail_login_url'] ?? site_url('/comunidade/login.php'));
    $siteName = (string) ($cfg['site_name'] ?? 'Comunidade Figurinhas da Copa');
    $displayName = $name !== '' ? $name : $email;

    $subject = 'Nova senha — Comunidade Figurinhas da Copa';
    $content = mail_build_reset_content($displayName, $email, $password, $loginUrl, $siteName);
    $mimeBody = mail_build_mime_body($content['text'], $content['html']);

    if (mail_use_smtp($cfg)) {
        $result = smtp_send($cfg, $email, $subject, $mimeBody, $fromEmail, $fromName, $replyTo);
        if ($result['ok']) {
            mail_log("Reset de senha SMTP enviado para {$email}");
            return $result;
        }
        mail_log("Reset de senha SMTP falha para {$email}: " . $result['error']);
        return [
            'ok' => false,
            'error' => 'SMTP falhou: ' . ($result['error'] ?? 'erro desconhecido')
                . '. Atualize a senha da caixa suporte@ no admin.',
        ];
    }

    $sent = mail_send_via_php_mail(
        $email,
        $subject,
        $content['text'],
        $content['html'],
        $fromEmail,
        $fromName,
        $replyTo
    );

    if (!$sent) {
        mail_log("Reset mail() falhou para {$email}");
        return ['ok' => false, 'error' => 'Não foi possível enviar o e-mail. Tente novamente ou fale com o suporte.'];
    }

    mail_log("Reset mail() aceito para {$email}");
    return ['ok' => true, 'error' => ''];
}

function mail_build_reset_content(string $displayName, string $email, string $password, string $loginUrl, string $siteName): array
{
    $textBody = implode("\n", [
        "Olá, {$displayName}!",
        '',
        'Você solicitou uma nova senha para a área VIP.',
        '',
        'Área VIP: ' . $loginUrl,
        'E-mail: ' . $email,
        'Nova senha: ' . $password,
        '',
        'Se não foi você, ignore este e-mail ou avise o suporte.',
        '',
        '— Equipe Figurinhas da Copa',
    ]);

    $safeName = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safePassword = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
    $safeLogin = htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8');

    $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#050505;font-family:Arial,Helvetica,sans-serif;color:#f0ede8">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#050505;padding:32px 16px">
    <tr><td align="center">
      <table width="100%" style="max-width:560px;background:#141414;border:1px solid #2a2a2a;border-radius:8px">
        <tr><td style="background:linear-gradient(135deg,#ca8a04,#12100a);padding:24px;text-align:center">
          <p style="margin:0;font-size:20px;color:#FFDF00;font-weight:bold">Nova senha — {$siteName}</p>
        </td></tr>
        <tr><td style="padding:24px">
          <p style="margin:0 0 16px;font-size:15px;color:#eee">Olá, <strong style="color:#FFDF00">{$safeName}</strong>!</p>
          <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#ccc">Você solicitou redefinir o acesso à área VIP. Use os dados abaixo para entrar:</p>
          <table width="100%" style="background:#0a0a0a;border:1px solid #333;border-radius:6px;margin-bottom:20px">
            <tr><td style="padding:16px;font-size:14px;line-height:1.9">
              <strong style="color:#888">Login:</strong><br>
              <a href="{$safeLogin}" style="color:#4da3ff">{$safeLogin}</a><br><br>
              <strong style="color:#888">E-mail:</strong><br><span style="color:#fff">{$safeEmail}</span><br><br>
              <strong style="color:#888">Nova senha:</strong><br>
              <span style="color:#FFDF00;font-size:18px;font-weight:bold">{$safePassword}</span>
            </td></tr>
          </table>
          <p style="text-align:center;margin:0">
            <a href="{$safeLogin}" style="display:inline-block;background:#FFDF00;color:#002776;text-decoration:none;font-weight:bold;padding:14px 28px;border-radius:4px">ENTRAR NA ÁREA VIP</a>
          </p>
          <p style="margin:20px 0 0;font-size:12px;color:#666;text-align:center">Se não solicitou esta alteração, ignore este e-mail.</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

    return ['text' => $textBody, 'html' => $htmlBody];
}

function send_member_credentials_email(string $email, string $name, string $password): array
{
    $cfg = app_config();

    if (empty($cfg['mail_enabled'])) {
        return ['ok' => false, 'error' => 'Envio de e-mail desativado em config.php (mail_enabled).'];
    }

    $fromEmail = (string) ($cfg['mail_from_email'] ?? 'suporte@agenciajob.com');
    $replyTo = (string) ($cfg['mail_reply_to'] ?? 'suporte@agenciajob.com');
    $fromName = (string) ($cfg['mail_from_name'] ?? 'Comunidade Figurinhas da Copa');
    $loginUrl = (string) ($cfg['mail_login_url'] ?? site_url('/comunidade/login.php'));
    $siteName = (string) ($cfg['site_name'] ?? 'Comunidade Figurinhas da Copa');
    $displayName = $name !== '' ? $name : $email;

    $subject = 'Seu acesso à Comunidade Figurinhas da Copa';
    $content = mail_build_content($displayName, $email, $password, $loginUrl, $siteName);
    $mimeBody = mail_build_mime_body($content['text'], $content['html']);

    if (mail_use_smtp($cfg)) {
        $result = smtp_send($cfg, $email, $subject, $mimeBody, $fromEmail, $fromName, $replyTo);
        if ($result['ok']) {
            mail_log("SMTP enviado para {$email}");
            return $result;
        }
        mail_log("SMTP falha para {$email}: " . $result['error']);
        return [
            'ok' => false,
            'error' => 'SMTP falhou: ' . ($result['error'] ?? 'erro desconhecido')
                . '. Atualize a senha da caixa suporte@ no admin (Integrações → E-mail).',
        ];
    }

    $sent = mail_send_via_php_mail(
        $email,
        $subject,
        $content['text'],
        $content['html'],
        $fromEmail,
        $fromName,
        $replyTo
    );

    if (!$sent) {
        mail_log("mail() falhou para {$email}");
        return ['ok' => false, 'error' => 'Não foi possível enviar o e-mail. Configure SMTP no admin.'];
    }

    mail_log("mail() aceito para {$email}");
    return ['ok' => true, 'error' => ''];
}

function mail_use_smtp(array $cfg): bool
{
    if (empty($cfg['smtp_host'])) {
        return false;
    }
    $enc = strtolower((string) ($cfg['smtp_encryption'] ?? 'tls'));
    $noTls = in_array($enc, ['', 'none', 'off', 'false', '0'], true);
    $host = (string) $cfg['smtp_host'];
    $local = $host === 'localhost' || $host === '127.0.0.1';

    return $noTls || $local || !empty($cfg['smtp_password']);
}

function mail_chunk_base64(string $content): string
{
    return rtrim(chunk_split(base64_encode($content), 76, "\r\n"), "\r\n");
}

function mail_build_mime_body(string $textBody, string $htmlBody): string
{
    $boundary = 'figcop_' . bin2hex(random_bytes(8));
    $body = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"' . "\r\n\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= mail_chunk_base64($textBody) . "\r\n\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= mail_chunk_base64($htmlBody) . "\r\n\r\n";
    $body .= "--{$boundary}--\r\n";
    return $body;
}

function mail_send_via_php_mail(
    string $toEmail,
    string $subject,
    string $textBody,
    string $htmlBody,
    string $fromEmail,
    string $fromName,
    string $replyTo
): bool {
    $mimeBody = mail_build_mime_body($textBody, $htmlBody);
    $splitAt = strpos($mimeBody, "\r\n\r\n");
    $contentType = $splitAt !== false ? substr($mimeBody, 0, $splitAt) : 'Content-Type: text/plain; charset=UTF-8';
    $body = $splitAt !== false ? substr($mimeBody, $splitAt + 4) : $mimeBody;

    $headers = array_merge([
        'MIME-Version: 1.0',
        $contentType,
        'From: ' . mail_format_address($fromName, $fromEmail),
    ], mail_extra_headers($fromEmail, $replyTo));

    return (bool) @mail($toEmail, mail_encode_subject($subject), $body, implode("\r\n", $headers));
}

function mail_build_content(string $displayName, string $email, string $password, string $loginUrl, string $siteName): array
{
    $albunsUrl = rtrim((string) (app_config()['mail_login_url'] ?? site_url('/comunidade/login.php')), '/');
    $albunsUrl = preg_replace('#/login\.php$#', '', $albunsUrl) . '/albuns/';

    $textBody = implode("\n", [
        "Olá, {$displayName}!",
        '',
        'Bem-vindo à Comunidade Figurinhas da Copa!',
        '',
        'Agora você faz parte da nossa biblioteca exclusiva de PDFs da comunidade.',
        'Aqui você terá acesso a:',
        '- Conteúdos especiais sobre Copa do Mundo',
        '- Bastidores dos vídeos do canal',
        '- Dicas de tecnologia e segurança digital',
        '- Materiais exclusivos, projetos e novidades antecipadas',
        '',
        'Aproveite ao máximo e fique ligado nas atualizações!',
        '',
        '--- SEU ACESSO PESSOAL (não compartilhe) ---',
        'Área VIP: ' . $loginUrl,
        'E-mail: ' . $email,
        'Senha: ' . $password,
        '',
        'Ver PDFs online: ' . $albunsUrl,
        '',
        '— Equipe Figurinhas da Copa',
    ]);

    $safeName = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safePassword = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
    $safeLogin = htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8');
    $safeAlbuns = htmlspecialchars($albunsUrl, ENT_QUOTES, 'UTF-8');

    $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#050505;font-family:Arial,Helvetica,sans-serif;color:#f0ede8">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#050505;padding:32px 16px">
    <tr><td align="center">
      <table width="100%" style="max-width:560px;background:#141414;border:1px solid #2a2a2a;border-radius:8px;overflow:hidden">
        <tr><td style="background:linear-gradient(135deg,#ca8a04,#12100a);padding:28px 24px;text-align:center">
          <p style="margin:0;font-size:22px;line-height:1.3">🔥 <strong style="color:#FFDF00">Bem-vindo à Comunidade Figurinhas da Copa!</strong> 🔥</p>
        </td></tr>
        <tr><td style="padding:28px 24px">
          <p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#eee">Olá, <strong style="color:#FFDF00">{$safeName}</strong>!</p>
          <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#ccc">Agora você faz parte da nossa <strong>biblioteca exclusiva de PDFs</strong> da comunidade 😄</p>
          <p style="margin:0 0 10px;font-size:14px;color:#aaa">Aqui você terá acesso a:</p>
          <ul style="margin:0 0 20px;padding-left:20px;font-size:14px;line-height:1.9;color:#ccc">
            <li>⚽ Conteúdos especiais sobre Copa do Mundo</li>
            <li>🎥 Bastidores dos vídeos do canal</li>
            <li>💻 Dicas de tecnologia e segurança digital</li>
            <li>🚀 Materiais exclusivos, projetos e novidades antecipadas</li>
          </ul>
          <p style="margin:0 0 24px;font-size:14px;line-height:1.6;color:#bbb">Aproveite ao máximo e fique ligado nas atualizações, porque sempre estaremos trazendo conteúdos novos por aqui ❤️</p>

          <p style="margin:0 0 10px;font-size:12px;letter-spacing:2px;color:#FFDF00;font-weight:bold;text-transform:uppercase">Seu acesso pessoal</p>
          <p style="margin:0 0 12px;font-size:13px;color:#888">Guarde estes dados — são só seus. Não compartilhe o link de login.</p>
          <table width="100%" style="background:#0a0a0a;border:1px solid #333;border-radius:6px;margin-bottom:20px">
            <tr><td style="padding:16px;font-size:14px;line-height:1.9">
              <strong style="color:#888">Área VIP:</strong><br>
              <a href="{$safeLogin}" style="color:#4da3ff;word-break:break-all">{$safeLogin}</a><br><br>
              <strong style="color:#888">E-mail:</strong><br><span style="color:#fff">{$safeEmail}</span><br><br>
              <strong style="color:#888">Senha:</strong><br>
              <span style="color:#FFDF00;font-size:18px;font-weight:bold;letter-spacing:1px">{$safePassword}</span>
            </td></tr>
          </table>

          <p style="text-align:center;margin:0 0 12px">
            <a href="{$safeLogin}" style="display:inline-block;background:#FFDF00;color:#002776;text-decoration:none;font-weight:bold;padding:14px 28px;border-radius:4px;font-size:15px;margin:4px">ACESSAR ÁREA VIP</a>
          </p>
          <p style="text-align:center;margin:0">
            <a href="{$safeAlbuns}" style="display:inline-block;background:linear-gradient(135deg,#FFDF00,#ca8a04);color:#fff;text-decoration:none;font-weight:bold;padding:14px 28px;border-radius:4px;font-size:15px;margin:4px">📚 VER PDFs ONLINE</a>
          </p>
        </td></tr>
        <tr><td style="padding:16px 24px;border-top:1px solid #222;text-align:center;font-size:12px;color:#666">
          Comunidade Figurinhas da Copa · Copa do Mundo 2026
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

    return ['text' => $textBody, 'html' => $htmlBody];
}

function mail_format_address(string $name, string $email): string
{
    $name = str_replace(['"', "\r", "\n"], '', $name);
    return sprintf('"%s" <%s>', $name, $email);
}

/** Cabeçalhos extras para entregabilidade (alinhar com domínio do remetente). */
function mail_extra_headers(string $fromEmail, string $replyTo): array
{
    $domain = mail_domain_from_address($fromEmail);
    if ($domain === '') {
        $domain = 'localhost';
    }
    $lines = [
        'Message-ID: <' . bin2hex(random_bytes(8)) . '.' . time() . '@' . $domain . '>',
        'X-Priority: 3',
        'Importance: Normal',
        'Auto-Submitted: auto-generated',
    ];
    if ($replyTo !== '') {
        $lines[] = 'Reply-To: <' . $replyTo . '>';
    }

    return $lines;
}

function mail_encode_subject(string $subject): string
{
    return '=?UTF-8?B?' . base64_encode($subject) . '?=';
}

function mail_log(string $message): void
{
    $line = date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;
    @file_put_contents(dirname(__DIR__) . '/data/mail.log', $line, FILE_APPEND | LOCK_EX);
}

function should_send_member_email(): bool
{
    return !isset($_POST['send_email']) || $_POST['send_email'] === '1';
}

function update_smtp_password(string $password): bool
{
    $path = dirname(__DIR__) . '/data/config.php';
    if (!is_file($path)) {
        return false;
    }
    $cfg = require $path;
    $cfg['smtp_password'] = $password;
    $cfg['smtp_host'] = $cfg['smtp_host'] ?? 'localhost';
    $cfg['smtp_port'] = $cfg['smtp_port'] ?? 465;
    $cfg['smtp_encryption'] = $cfg['smtp_encryption'] ?? 'ssl';
    $cfg['smtp_host'] = $cfg['smtp_host'] ?? 'mail.agenciajob.com';
    $cfg['smtp_ehlo'] = $cfg['smtp_ehlo'] ?? 'mail.agenciajob.com';
    $cfg['mail_from_email'] = $cfg['mail_from_email'] ?? 'suporte@agenciajob.com';
    $cfg['mail_reply_to'] = $cfg['mail_reply_to'] ?? 'suporte@agenciajob.com';
    $cfg['smtp_username'] = $cfg['mail_from_email'] ?? 'suporte@agenciajob.com';
    $export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
    return file_put_contents($path, $export, LOCK_EX) !== false;
}

function smtp_is_configured(): bool
{
    $cfg = app_config();
    return mail_use_smtp($cfg) && trim((string) ($cfg['smtp_password'] ?? '')) !== '';
}

/** Testa autenticação SMTP sem enviar mensagem. */
function smtp_test_auth(): array
{
    $cfg = app_config();
    if (!mail_use_smtp($cfg)) {
        return ['ok' => false, 'error' => 'SMTP não configurado.'];
    }
    if (trim((string) ($cfg['smtp_password'] ?? '')) === '') {
        return ['ok' => false, 'error' => 'Senha SMTP vazia. Salve a senha no admin.'];
    }

    $fromEmail = (string) ($cfg['mail_from_email'] ?? 'suporte@agenciajob.com');
    $host = (string) ($cfg['smtp_host'] ?? 'localhost');
    $port = (int) ($cfg['smtp_port'] ?? 587);
    $encryption = strtolower((string) ($cfg['smtp_encryption'] ?? 'tls'));
    $user = (string) ($cfg['smtp_username'] ?? $fromEmail);
    $pass = (string) ($cfg['smtp_password'] ?? '');

    $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
    $socket = @stream_socket_client($remote, $errno, $errstr, 30, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        return ['ok' => false, 'error' => "Conexão falhou: {$errstr} ({$errno})"];
    }

    try {
        smtp_expect($socket, [220]);
        $ehlo = smtp_ehlo_hostname($cfg, $fromEmail);
        smtp_cmd($socket, 'EHLO ' . $ehlo, [250]);
        if ($encryption === 'tls') {
            smtp_cmd($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('TLS não iniciou.');
            }
            smtp_cmd($socket, 'EHLO ' . $ehlo, [250]);
        }
        smtp_cmd($socket, 'AUTH LOGIN', [334]);
        smtp_cmd($socket, base64_encode($user), [334]);
        smtp_cmd($socket, base64_encode($pass), [235]);
        smtp_cmd($socket, 'QUIT', [221]);
    } catch (Throwable $e) {
        fclose($socket);
        return ['ok' => false, 'error' => $e->getMessage()];
    }

    fclose($socket);
    return ['ok' => true, 'error' => ''];
}
