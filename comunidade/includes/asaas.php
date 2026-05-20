<?php

declare(strict_types=1);

require_once __DIR__ . '/members.php';

/** PNG 1×1 transparente (exigido pelo checkout Asaas em cada item). */
const ASAAS_CHECKOUT_PLACEHOLDER_IMAGE_BASE64 =
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

function asaas_processed_file(): string
{
    return dirname(__DIR__) . '/data/asaas-payments.json';
}

/** @return array<string, array{email:string,at:string}> */
function asaas_load_processed(): array
{
    $path = asaas_processed_file();
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function asaas_payment_already_processed(string $paymentId): bool
{
    $paymentId = trim($paymentId);
    if ($paymentId === '') {
        return false;
    }
    return isset(asaas_load_processed()[$paymentId]);
}

function asaas_mark_payment_processed(string $paymentId, string $email): void
{
    $paymentId = trim($paymentId);
    if ($paymentId === '') {
        return;
    }
    $all = asaas_load_processed();
    $all[$paymentId] = [
        'email' => normalize_email($email),
        'at' => date('c'),
    ];
    file_put_contents(
        asaas_processed_file(),
        json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function asaas_log(string $message): void
{
    $line = date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;
    @file_put_contents(dirname(__DIR__) . '/data/asaas-webhook.log', $line, FILE_APPEND | LOCK_EX);
}

function asaas_is_configured(): bool
{
    $cfg = app_config();
    return trim((string) ($cfg['asaas_api_key'] ?? '')) !== '';
}

function asaas_api_base_url(): string
{
    $cfg = app_config();
    $env = strtolower(trim((string) ($cfg['asaas_environment'] ?? 'production')));
    if ($env === 'sandbox') {
        return 'https://api-sandbox.asaas.com/v3';
    }
    return 'https://api.asaas.com/v3';
}

function asaas_site_base_url(): string
{
    $cfg = app_config();
    $base = rtrim((string) ($cfg['site_base_url'] ?? 'https://perfectpay.agenciajob.com'), '/');
    return $base !== '' ? $base : 'https://perfectpay.agenciajob.com';
}

function asaas_checkout_url(): string
{
    return asaas_site_base_url() . '/checkout.php';
}

function asaas_webhook_url(): string
{
    return asaas_site_base_url() . '/comunidade/webhook/asaas.php';
}

/** @return array{ok:bool,status:int,body:array<string,mixed>,error:string} */
function asaas_api_request(string $method, string $path, ?array $body = null): array
{
    $cfg = app_config();
    $apiKey = trim((string) ($cfg['asaas_api_key'] ?? ''));
    if ($apiKey === '') {
        return ['ok' => false, 'status' => 0, 'body' => [], 'error' => 'Chave API Asaas não configurada.'];
    }

    $url = asaas_api_base_url() . '/' . ltrim($path, '/');
    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'status' => 0, 'body' => [], 'error' => 'Falha ao iniciar cURL.'];
    }

    $headers = [
        'Content-Type: application/json',
        'access_token: ' . $apiKey,
        'User-Agent: ComunidadeFigurinhasDaCopa/1.0',
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 45,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'status' => $status, 'body' => [], 'error' => $curlError ?: 'Erro de rede.'];
    }

    $decoded = json_decode((string) $raw, true);
    $responseBody = is_array($decoded) ? $decoded : [];

    if ($status >= 200 && $status < 300) {
        return ['ok' => true, 'status' => $status, 'body' => $responseBody, 'error' => ''];
    }

    $msg = '';
    if (!empty($responseBody['errors']) && is_array($responseBody['errors'])) {
        $first = $responseBody['errors'][0] ?? [];
        if (is_array($first)) {
            $msg = trim((string) ($first['description'] ?? $first['code'] ?? ''));
        }
    }
    if ($msg === '') {
        $msg = 'Erro HTTP ' . $status;
    }

    return ['ok' => false, 'status' => $status, 'body' => $responseBody, 'error' => $msg];
}

/**
 * Cria sessão de checkout e retorna link de pagamento.
 *
 * @return array{ok:bool,link?:string,checkout_id?:string,error?:string}
 */
function asaas_create_checkout_session(string $customerEmail = '', string $customerName = ''): array
{
    if (!asaas_is_configured()) {
        return ['ok' => false, 'error' => 'Integração Asaas não configurada.'];
    }

    $cfg = app_config();
    $value = (float) ($cfg['asaas_checkout_value'] ?? 97);
    if ($value <= 0) {
        return ['ok' => false, 'error' => 'Valor do checkout inválido em config.php.'];
    }

    $itemName = trim((string) ($cfg['asaas_checkout_item_name'] ?? 'Comunidade VIP'));
    $itemName = substr($itemName !== '' ? $itemName : 'Comunidade VIP', 0, 30);
    $itemDesc = substr(trim((string) ($cfg['asaas_checkout_item_description'] ?? 'Acesso à comunidade de figurinhas Copa 2026')), 0, 150);

    $billingRaw = trim((string) ($cfg['asaas_checkout_billing_types'] ?? 'PIX,CREDIT_CARD'));
    $billingTypes = array_values(array_filter(array_map('trim', explode(',', strtoupper($billingRaw)))));
    if ($billingTypes === []) {
        $billingTypes = ['PIX', 'CREDIT_CARD'];
    }

    $minutes = (int) ($cfg['asaas_checkout_minutes_expire'] ?? 60);
    $minutes = max(10, min(1440, $minutes));

    $base = asaas_site_base_url();
    $thankyou = rtrim((string) ($cfg['asaas_thankyou_url'] ?? $base . '/obrigado.php'), '/');
    $cancel = rtrim((string) ($cfg['asaas_cancel_url'] ?? $base . '/'), '/');

    $payload = [
        'billingTypes' => $billingTypes,
        'chargeTypes' => ['DETACHED'],
        'minutesToExpire' => $minutes,
        'externalReference' => 'figcop_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)),
        'callback' => [
            'successUrl' => $thankyou,
            'cancelUrl' => $cancel,
            'expiredUrl' => $cancel,
        ],
        'items' => [[
            'name' => $itemName,
            'description' => $itemDesc,
            'quantity' => 1,
            'value' => round($value, 2),
            'imageBase64' => ASAAS_CHECKOUT_PLACEHOLDER_IMAGE_BASE64,
        ]],
    ];

    // Só pré-preenche cliente com CPF/CNPJ (Asaas exige cpfCnpj quando customerData é enviado).
    $customerEmail = normalize_email($customerEmail);
    $cpfCnpj = preg_replace('/\D/', '', (string) ($GLOBALS['asaas_checkout_cpf'] ?? ''));
    if (
        $customerEmail !== ''
        && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)
        && ($cpfCnpj !== '' && (strlen($cpfCnpj) === 11 || strlen($cpfCnpj) === 14))
    ) {
        $customerData = [
            'email' => $customerEmail,
            'cpfCnpj' => $cpfCnpj,
        ];
        $customerName = trim($customerName);
        if ($customerName !== '') {
            $customerData['name'] = $customerName;
        }
        $payload['customerData'] = $customerData;
    }

    $response = asaas_api_request('POST', '/checkouts', $payload);
    if (!$response['ok']) {
        asaas_log('Falha criar checkout: ' . $response['error']);
        return ['ok' => false, 'error' => $response['error']];
    }

    $link = trim((string) ($response['body']['link'] ?? ''));
    $checkoutId = trim((string) ($response['body']['id'] ?? ''));
    if ($link === '') {
        return ['ok' => false, 'error' => 'Asaas não retornou link do checkout.'];
    }

    asaas_log("Checkout criado id={$checkoutId} ref={$payload['externalReference']}");

    return ['ok' => true, 'link' => $link, 'checkout_id' => $checkoutId];
}

/** @return array{email:string,name:string}|null */
function asaas_fetch_customer_by_id(string $customerId): ?array
{
    $customerId = trim($customerId);
    if ($customerId === '') {
        return null;
    }

    $response = asaas_api_request('GET', '/customers/' . rawurlencode($customerId));
    if (!$response['ok']) {
        asaas_log("Falha buscar cliente {$customerId}: " . $response['error']);
        return null;
    }

    $body = $response['body'];
    $email = normalize_email((string) ($body['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }

    return [
        'email' => $email,
        'name' => trim((string) ($body['name'] ?? '')),
    ];
}

function asaas_webhook_token_valid(): bool
{
    $cfg = app_config();
    $expected = trim((string) ($cfg['asaas_webhook_token'] ?? ''));
    if ($expected === '') {
        return true;
    }

    $received = trim((string) ($_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? ''));
    if ($received === '') {
        $received = trim((string) ($_GET['asaas-access-token'] ?? ''));
    }

    return $received !== '' && hash_equals($expected, $received);
}

/** @return list<string> */
function asaas_approved_events(): array
{
    return [
        'CHECKOUT_PAID',
        'PAYMENT_CONFIRMED',
        'PAYMENT_RECEIVED',
    ];
}

/** @param array<string, mixed> $payload */
function asaas_extract_payment_id(array $payload): string
{
    $event = strtoupper(trim((string) ($payload['event'] ?? '')));
    if ($event === 'CHECKOUT_PAID') {
        $checkout = $payload['checkout'] ?? [];
        if (is_array($checkout)) {
            return trim((string) ($checkout['id'] ?? ''));
        }
        return '';
    }

    $payment = $payload['payment'] ?? [];
    if (!is_array($payment)) {
        return '';
    }

    return trim((string) ($payment['id'] ?? ''));
}

/** @param array<string, mixed> $payload */
function asaas_extract_customer_from_payload(array $payload): array
{
    $event = strtoupper(trim((string) ($payload['event'] ?? '')));

    if ($event === 'CHECKOUT_PAID') {
        $checkout = $payload['checkout'] ?? [];
        if (!is_array($checkout)) {
            return ['email' => '', 'name' => ''];
        }

        $customerData = $checkout['customerData'] ?? null;
        if (is_array($customerData)) {
            $email = normalize_email((string) ($customerData['email'] ?? ''));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'email' => $email,
                    'name' => trim((string) ($customerData['name'] ?? '')),
                ];
            }
        }

        $customerId = trim((string) ($checkout['customer'] ?? ''));
        if ($customerId !== '') {
            $fetched = asaas_fetch_customer_by_id($customerId);
            if ($fetched !== null) {
                return $fetched;
            }
        }

        return ['email' => '', 'name' => ''];
    }

    $payment = $payload['payment'] ?? [];
    if (!is_array($payment)) {
        return ['email' => '', 'name' => ''];
    }

    $customerId = trim((string) ($payment['customer'] ?? ''));
    if ($customerId !== '') {
        $fetched = asaas_fetch_customer_by_id($customerId);
        if ($fetched !== null) {
            return $fetched;
        }
    }

    return ['email' => '', 'name' => ''];
}

/**
 * @param array<string, mixed> $payload
 * @return array{ok:bool,http_status:int,message:string}
 */
function asaas_handle_webhook(array $payload): array
{
    if (!asaas_webhook_token_valid()) {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        asaas_log("Token webhook inválido (IP {$ip}).");
        return ['ok' => false, 'http_status' => 403, 'message' => 'Forbidden'];
    }

    $event = strtoupper(trim((string) ($payload['event'] ?? '')));
    $paymentId = asaas_extract_payment_id($payload);
    $customer = asaas_extract_customer_from_payload($payload);
    $email = $customer['email'];
    $name = $customer['name'];

    asaas_log("Evento {$event} id={$paymentId} email={$email}");

    if (!in_array($event, asaas_approved_events(), true)) {
        return ['ok' => true, 'http_status' => 200, 'message' => 'Evento ignorado: ' . $event];
    }

    if ($email === '') {
        asaas_log("E-mail ausente no evento {$event} id={$paymentId}");
        return ['ok' => false, 'http_status' => 422, 'message' => 'E-mail do comprador ausente'];
    }

    if ($paymentId !== '' && asaas_payment_already_processed($paymentId)) {
        return ['ok' => true, 'http_status' => 200, 'message' => 'Pagamento já processado'];
    }

    $result = provision_member_access($email, $name, true);
    if (!$result['ok']) {
        asaas_log("Falha provisionar {$email}: " . ($result['error'] ?? ''));
        return ['ok' => false, 'http_status' => 500, 'message' => $result['error'] ?? 'Erro ao cadastrar membro'];
    }

    if ($paymentId !== '') {
        asaas_mark_payment_processed($paymentId, $email);
    }

    $action = ($result['created'] ?? false) ? 'criado' : 'atualizado';
    $mail = ($result['email_sent'] ?? false) ? 'e-mail enviado' : 'e-mail NÃO enviado: ' . ($result['error'] ?? '');
    asaas_log("OK {$paymentId}: {$email} {$action}, {$mail}");

    return [
        'ok' => true,
        'http_status' => 200,
        'message' => "Membro {$action}; " . (($result['email_sent'] ?? false) ? 'e-mail enviado' : ($result['error'] ?? 'e-mail não enviado')),
    ];
}

/** @return array<string, mixed> */
function asaas_build_test_payload(string $email, string $name = ''): array
{
    return [
        'event' => 'PAYMENT_CONFIRMED',
        'payment' => [
            'id' => 'pay_TEST_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)),
            'customer' => '',
            'status' => 'CONFIRMED',
            'billingType' => 'PIX',
        ],
        'customerData' => [
            'email' => $email,
            'name' => $name !== '' ? $name : 'Teste Comunidade',
        ],
    ];
}

/**
 * Payload de teste com e-mail embutido (simulação admin sem API de cliente).
 *
 * @return array<string, mixed>
 */
function asaas_build_test_payload_with_email(string $email, string $name = ''): array
{
    $payload = asaas_build_test_payload($email, $name);
    // Handler usa customerData em CHECKOUT; em PAYMENT usa API — injetamos via evento CHECKOUT_PAID
    return [
        'event' => 'CHECKOUT_PAID',
        'checkout' => [
            'id' => 'chk_TEST_' . date('YmdHis'),
            'status' => 'PAID',
            'customerData' => [
                'email' => $email,
                'name' => $name !== '' ? $name : 'Teste Comunidade',
            ],
        ],
    ];
}

function update_asaas_settings(
    string $apiKey,
    string $webhookToken,
    float $checkoutValue,
    string $environment = 'production'
): bool {
    $path = dirname(__DIR__) . '/data/config.php';
    if (!is_file($path)) {
        return false;
    }

    $cfg = require $path;
    if (trim($apiKey) !== '') {
        $cfg['asaas_api_key'] = trim($apiKey);
    }
    $cfg['asaas_webhook_token'] = trim($webhookToken);
    $cfg['asaas_checkout_value'] = max(1, round($checkoutValue, 2));
    $cfg['asaas_environment'] = strtolower($environment) === 'sandbox' ? 'sandbox' : 'production';
    $cfg['payment_provider'] = 'asaas';

    $export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
    return file_put_contents($path, $export, LOCK_EX) !== false;
}
