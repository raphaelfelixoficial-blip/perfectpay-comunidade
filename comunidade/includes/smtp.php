<?php

declare(strict_types=1);

function smtp_ehlo_hostname(array $cfg, string $fromEmail): string
{
    $custom = trim((string) ($cfg['smtp_ehlo'] ?? ''));
    if ($custom !== '') {
        return $custom;
    }
    $domain = mail_domain_from_address($fromEmail);
    if ($domain !== '') {
        return 'mail.' . $domain;
    }

    return 'localhost';
}

function mail_domain_from_address(string $email): string
{
    $email = trim($email);
    if (!str_contains($email, '@')) {
        return '';
    }

    return strtolower((string) substr($email, (int) strrpos($email, '@') + 1));
}

function smtp_send(
    array $cfg,
    string $toEmail,
    string $subject,
    string $mimeBody,
    string $fromEmail,
    string $fromName,
    string $replyTo = ''
): array {
    $host = (string) ($cfg['smtp_host'] ?? 'localhost');
    $port = (int) ($cfg['smtp_port'] ?? 587);
    $encryption = strtolower((string) ($cfg['smtp_encryption'] ?? 'tls'));
    $user = (string) ($cfg['smtp_username'] ?? $fromEmail);
    $pass = (string) ($cfg['smtp_password'] ?? '');
    $noTls = in_array($encryption, ['', 'none', 'off', 'false', '0'], true);

    if ($pass === '' && !$noTls && $host !== 'localhost' && $host !== '127.0.0.1') {
        return ['ok' => false, 'error' => 'Senha SMTP não configurada em data/config.php (smtp_password).'];
    }

    $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
    $socket = @stream_socket_client($remote, $errno, $errstr, 30, STREAM_CLIENT_CONNECT);

    if (!$socket) {
        return ['ok' => false, 'error' => "Conexão SMTP falhou: {$errstr} ({$errno})"];
    }

    stream_set_timeout($socket, 30);

    try {
        smtp_expect($socket, [220]);

        $ehlo = smtp_ehlo_hostname($cfg, $fromEmail);
        smtp_cmd($socket, 'EHLO ' . $ehlo, [250]);

        if ($encryption === 'tls') {
            smtp_cmd($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Não foi possível iniciar TLS.');
            }
            smtp_cmd($socket, 'EHLO ' . $ehlo, [250]);
        }

        if ($pass !== '') {
            smtp_cmd($socket, 'AUTH LOGIN', [334]);
            smtp_cmd($socket, base64_encode($user), [334]);
            smtp_cmd($socket, base64_encode($pass), [235]);
        }

        smtp_cmd($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
        smtp_cmd($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
        smtp_cmd($socket, 'DATA', [354]);

        $domain = mail_domain_from_address($fromEmail) ?: 'localhost';
        $message = 'Date: ' . date('r') . "\r\n";
        $message .= 'To: <' . $toEmail . '>' . "\r\n";
        $message .= 'From: ' . mail_format_address($fromName, $fromEmail) . "\r\n";
        if ($replyTo !== '') {
            $message .= 'Reply-To: <' . $replyTo . '>' . "\r\n";
        }
        $message .= 'Message-ID: <' . bin2hex(random_bytes(8)) . '.' . time() . '@' . $domain . '>' . "\r\n";
        $message .= 'Subject: ' . mail_encode_subject($subject) . "\r\n";
        $message .= 'MIME-Version: 1.0' . "\r\n";
        $message .= 'X-Mailer: FigurinhasDaCopa-Comunidade' . "\r\n";
        $message .= $mimeBody . "\r\n";

        smtp_write_message($socket, $message);
        smtp_expect($socket, [250]);
        smtp_cmd($socket, 'QUIT', [221]);
    } catch (Throwable $e) {
        fclose($socket);
        return ['ok' => false, 'error' => $e->getMessage()];
    }

    fclose($socket);
    return ['ok' => true, 'error' => ''];
}

function smtp_write_message($socket, string $data): void
{
    $lines = preg_split("/\r\n|\n|\r/", $data) ?: [];
    foreach ($lines as $line) {
        while (strlen($line) > 990) {
            fwrite($socket, substr($line, 0, 990) . "\r\n");
            $line = substr($line, 990);
        }
        if ($line === '.') {
            $line = '..';
        }
        fwrite($socket, $line . "\r\n");
    }
    fwrite($socket, ".\r\n");
}

function smtp_cmd($socket, string $cmd, array $okCodes): string
{
    fwrite($socket, $cmd . "\r\n");
    return smtp_expect($socket, $okCodes);
}

function smtp_expect($socket, array $okCodes): string
{
    $response = '';
    while ($line = fgets($socket, 515)) {
        $response .= $line;
        if (!isset($line[3]) || $line[3] === ' ') {
            break;
        }
    }
    $code = (int) substr(trim($response), 0, 3);
    if (!in_array($code, $okCodes, true)) {
        throw new RuntimeException('SMTP: ' . trim($response));
    }
    return $response;
}
