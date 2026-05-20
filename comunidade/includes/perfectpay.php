<?php

declare(strict_types=1);

require_once __DIR__ . '/members.php';

function perfectpay_processed_file(): string
{
    return dirname(__DIR__) . '/data/perfectpay-sales.json';
}

/** @return array<string, array{email:string,at:string}> */
function perfectpay_load_processed(): array
{
    $path = perfectpay_processed_file();
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function perfectpay_sale_already_processed(string $saleCode): bool
{
    $saleCode = trim($saleCode);
    if ($saleCode === '') {
        return false;
    }
    return isset(perfectpay_load_processed()[$saleCode]);
}

function perfectpay_mark_sale_processed(string $saleCode, string $email): void
{
    $saleCode = trim($saleCode);
    if ($saleCode === '') {
        return;
    }
    $all = perfectpay_load_processed();
    $all[$saleCode] = [
        'email' => normalize_email($email),
        'at' => date('c'),
    ];
    file_put_contents(
        perfectpay_processed_file(),
        json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function perfectpay_log(string $message): void
{
    $line = date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;
    @file_put_contents(dirname(__DIR__) . '/data/perfectpay-webhook.log', $line, FILE_APPEND | LOCK_EX);
}

/** Status que liberam acesso à comunidade (approved + completed). */
function perfectpay_approved_statuses(): array
{
    return [2, 8, 10];
}

/** @return array{email:string,name:string} */
function perfectpay_extract_customer(array $payload): array
{
    $customer = $payload['customer'] ?? $payload['buyer'] ?? $payload['payer'] ?? [];
    if (!is_array($customer)) {
        $customer = [];
    }

    $email = '';
    foreach ([
        (string) ($customer['email'] ?? ''),
        (string) ($payload['customer_email'] ?? ''),
        (string) ($payload['email'] ?? ''),
    ] as $candidate) {
        $candidate = normalize_email($candidate);
        if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
            $email = $candidate;
            break;
        }
    }

    $name = trim((string) ($customer['full_name'] ?? $customer['name'] ?? $payload['customer_name'] ?? ''));

    return ['email' => $email, 'name' => $name];
}

function perfectpay_extract_sale_status(array $payload): int
{
    $raw = $payload['sale_status_enum'] ?? $payload['sale_status'] ?? $payload['status'] ?? -1;

    if (is_string($raw)) {
        $key = strtolower(trim($raw));
        $map = [
            'approved' => 2,
            'completed' => 10,
            'authorized' => 8,
            'pending' => 1,
            'precheckout' => 12,
        ];
        if (isset($map[$key])) {
            return $map[$key];
        }
        if (is_numeric($raw)) {
            return (int) $raw;
        }
        return -1;
    }

    return (int) $raw;
}

function perfectpay_extract_token(array $payload): string
{
    $token = trim((string) ($payload['token'] ?? ''));
    if ($token !== '') {
        return $token;
    }

    $headers = [
        (string) ($_SERVER['HTTP_X_PERFECTPAY_TOKEN'] ?? ''),
        (string) ($_SERVER['HTTP_X_WEBHOOK_TOKEN'] ?? ''),
    ];
    $auth = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    if ($auth !== '' && preg_match('/Bearer\s+(\S+)/i', $auth, $m)) {
        $headers[] = $m[1];
    }

    foreach ($headers as $h) {
        $h = trim($h);
        if ($h !== '') {
            return $h;
        }
    }

    return trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
}

function perfectpay_webhook_token_valid(array $payload): bool
{
    $cfg = app_config();
    $expected = trim((string) ($cfg['perfectpay_webhook_token'] ?? ''));
    if ($expected === '') {
        return true;
    }
    $received = perfectpay_extract_token($payload);
    return $received !== '' && hash_equals($expected, $received);
}

function perfectpay_product_allowed(array $payload): bool
{
    $cfg = app_config();
    $filter = trim((string) ($cfg['perfectpay_product_codes'] ?? ''));
    if ($filter === '') {
        return true;
    }
    $allowed = array_map('trim', explode(',', $filter));
    $productCode = trim((string) ($payload['product']['code'] ?? ''));
    $planCode = trim((string) ($payload['plan']['code'] ?? ''));
    return in_array($productCode, $allowed, true) || in_array($planCode, $allowed, true);
}

/**
 * Processa postback JSON da Perfect Pay.
 *
 * @return array{ok:bool,http_status:int,message:string}
 */
function perfectpay_handle_webhook(array $payload): array
{
    if (!perfectpay_webhook_token_valid($payload)) {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        perfectpay_log("Token inválido ou ausente (IP {$ip}).");
        return ['ok' => false, 'http_status' => 403, 'message' => 'Forbidden'];
    }

    $saleCode = trim((string) ($payload['code'] ?? ''));
    $status = perfectpay_extract_sale_status($payload);
    $customer = perfectpay_extract_customer($payload);
    $email = $customer['email'];
    $name = $customer['name'];
    $statusDetail = (string) ($payload['sale_status_detail'] ?? '');

    perfectpay_log("Recebido code={$saleCode} status={$status} detail={$statusDetail} email={$email}");

    if (!in_array($status, perfectpay_approved_statuses(), true)) {
        return ['ok' => true, 'http_status' => 200, 'message' => 'Status ignorado: ' . $status];
    }

    if ($email === '') {
        perfectpay_log("E-mail ausente na venda {$saleCode}");
        return ['ok' => false, 'http_status' => 422, 'message' => 'E-mail do comprador ausente'];
    }

    if (!perfectpay_product_allowed($payload)) {
        perfectpay_log("Produto não permitido na venda {$saleCode}");
        return ['ok' => true, 'http_status' => 200, 'message' => 'Produto fora do filtro configurado'];
    }

    if ($saleCode !== '' && perfectpay_sale_already_processed($saleCode)) {
        return ['ok' => true, 'http_status' => 200, 'message' => 'Venda já processada'];
    }

    $result = provision_member_access($email, $name, true);
    if (!$result['ok']) {
        perfectpay_log("Falha ao provisionar {$email}: " . ($result['error'] ?? ''));
        return ['ok' => false, 'http_status' => 500, 'message' => $result['error'] ?? 'Erro ao cadastrar membro'];
    }

    if ($saleCode !== '') {
        perfectpay_mark_sale_processed($saleCode, $email);
    }

    $action = ($result['created'] ?? false) ? 'criado' : 'atualizado';
    $mail = ($result['email_sent'] ?? false) ? 'e-mail enviado' : 'e-mail NÃO enviado: ' . ($result['error'] ?? '');
    perfectpay_log("OK {$saleCode}: {$email} {$action}, {$mail}");

    return [
        'ok' => true,
        'http_status' => 200,
        'message' => "Membro {$action}; " . (($result['email_sent'] ?? false) ? 'e-mail enviado' : ($result['error'] ?? 'e-mail não enviado')),
    ];
}

/** Payload de venda aprovada para teste (admin ou curl). */
function perfectpay_build_test_payload(string $email, string $name = ''): array
{
    $cfg = app_config();
    return [
        'token' => (string) ($cfg['perfectpay_webhook_token'] ?? ''),
        'code' => 'PPTEST_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)),
        'sale_status_enum' => 2,
        'sale_status_detail' => 'approved',
        'date_approved' => date('Y-m-d H:i:s'),
        'customer' => [
            'email' => $email,
            'full_name' => $name !== '' ? $name : 'Teste Comunidade',
        ],
        'product' => ['code' => 'TEST', 'name' => 'Teste webhook'],
        'plan' => ['code' => 'TEST', 'name' => 'Plano teste'],
    ];
}
