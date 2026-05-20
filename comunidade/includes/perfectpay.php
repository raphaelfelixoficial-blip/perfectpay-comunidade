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

/** Status que liberam acesso à comunidade. */
function perfectpay_approved_statuses(): array
{
    return [2, 10];
}

function perfectpay_webhook_token_valid(array $payload): bool
{
    $cfg = app_config();
    $expected = trim((string) ($cfg['perfectpay_webhook_token'] ?? ''));
    if ($expected === '') {
        return true;
    }
    $received = trim((string) ($payload['token'] ?? ''));
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
        perfectpay_log('Token inválido ou ausente.');
        return ['ok' => false, 'http_status' => 403, 'message' => 'Forbidden'];
    }

    $saleCode = trim((string) ($payload['code'] ?? ''));
    $status = (int) ($payload['sale_status_enum'] ?? -1);
    $customer = $payload['customer'] ?? [];
    $email = normalize_email((string) ($customer['email'] ?? ''));
    $name = trim((string) ($customer['full_name'] ?? ''));

    perfectpay_log("Recebido code={$saleCode} status={$status} email={$email}");

    if (!in_array($status, perfectpay_approved_statuses(), true)) {
        return ['ok' => true, 'http_status' => 200, 'message' => 'Status ignorado: ' . $status];
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        perfectpay_log("E-mail inválido na venda {$saleCode}");
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
    $mail = ($result['email_sent'] ?? false) ? 'e-mail enviado' : 'e-mail não enviado';
    perfectpay_log("OK {$saleCode}: {$email} {$action}, {$mail}");

    return [
        'ok' => true,
        'http_status' => 200,
        'message' => "Membro {$action}; {$mail}",
    ];
}
