<?php

declare(strict_types=1);

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

    if ($pass === '') {
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

        smtp_cmd($socket, 'EHLO ' . ($cfg['smtp_ehlo'] ?? 'perfectpay.agenciajob.com'), [250]);

        if ($encryption === 'tls') {
            smtp_cmd($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Não foi possível iniciar TLS.');
            }
            smtp_cmd($socket, 'EHLO ' . ($cfg['smtp_ehlo'] ?? 'perfectpay.agenciajob.com'), [250]);
        }

        smtp_cmd($socket, 'AUTH LOGIN', [334]);
        smtp_cmd($socket, base64_encode($user), [334]);
        smtp_cmd($socket, base64_encode($pass), [235]);

        smtp_cmd($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
        smtp_cmd($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
        smtp_cmd($socket, 'DATA', [354]);

        $message = 'Date: ' . date('r') . "\r\n";
        $message .= 'To: <' . $toEmail . '>' . "\r\n";
        $message .= 'From: ' . mail_format_address($fromName, $fromEmail) . "\r\n";
        if ($replyTo !== '') {
            $message .= 'Reply-To: <' . $replyTo . '>' . "\r\n";
        }
        $message .= 'Subject: ' . mail_encode_subject($subject) . "\r\n";
        $message .= 'MIME-Version: 1.0' . "\r\n";
        $message .= $mimeBody . "\r\n";

        fwrite($socket, $message . "\r\n.\r\n");
        smtp_expect($socket, [250]);
        smtp_cmd($socket, 'QUIT', [221]);
    } catch (Throwable $e) {
        fclose($socket);
        return ['ok' => false, 'error' => $e->getMessage()];
    }

    fclose($socket);
    return ['ok' => true, 'error' => ''];
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
