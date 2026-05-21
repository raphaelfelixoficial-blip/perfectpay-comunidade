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
    $path = dirname(__DIR__) . '/data/asaas-webhook.log';
    $written = @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    if ($written === false) {
        error_log('[asaas-webhook] ' . trim($message));
    }
}

/** Token enviado pelo Asaas no header asaas-access-token (várias formas no Apache/PHP). */
function asaas_received_webhook_token(): string
{
    $candidates = [
        (string) ($_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? ''),
    ];

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (is_array($headers)) {
            foreach ($headers as $name => $value) {
                if (strtolower((string) $name) === 'asaas-access-token') {
                    $candidates[] = (string) $value;
                    break;
                }
            }
        }
    }

    $auth = trim((string) ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
    if ($auth !== '' && stripos($auth, 'bearer ') === 0) {
        $candidates[] = trim(substr($auth, 7));
    }

    $candidates[] = (string) ($_GET['asaas-access-token'] ?? '');

    foreach ($candidates as $token) {
        $token = trim($token);
        if ($token !== '') {
            return $token;
        }
    }

    return '';
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
    return site_base_url();
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

    if (!function_exists('site_checkout_price')) {
        require_once __DIR__ . '/site-status.php';
    }
    $cfg = app_config();
    $value = site_checkout_price();
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

/** @return array<string, mixed>|null */
function asaas_fetch_payment_by_id(string $paymentId): ?array
{
    $paymentId = trim($paymentId);
    if ($paymentId === '') {
        return null;
    }

    $response = asaas_api_request('GET', '/payments/' . rawurlencode($paymentId));
    if (!$response['ok']) {
        asaas_log("Falha buscar pagamento {$paymentId}: " . $response['error']);
        return null;
    }

    return $response['body'];
}

/** @return array<string, mixed>|null */
function asaas_fetch_checkout_by_id(string $checkoutId): ?array
{
    $checkoutId = trim($checkoutId);
    if ($checkoutId === '') {
        return null;
    }

    $response = asaas_api_request('GET', '/checkouts/' . rawurlencode($checkoutId));
    if (!$response['ok']) {
        asaas_log("Falha buscar checkout {$checkoutId}: " . $response['error']);
        return null;
    }

    return $response['body'];
}

/** @param array<string, mixed> $entity */
function asaas_customer_from_entity(array $entity): array
{
    $customerData = $entity['customerData'] ?? null;
    if (is_array($customerData)) {
        $email = normalize_email((string) ($customerData['email'] ?? ''));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => $email,
                'name' => trim((string) ($customerData['name'] ?? '')),
            ];
        }
    }

    foreach (['customerEmail', 'payerEmail', 'email'] as $field) {
        $email = normalize_email((string) ($entity[$field] ?? ''));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => $email,
                'name' => trim((string) ($entity['payerName'] ?? $entity['name'] ?? '')),
            ];
        }
    }

    $customerId = trim((string) ($entity['customer'] ?? ''));
    if ($customerId !== '') {
        $fetched = asaas_fetch_customer_by_id($customerId);
        if ($fetched !== null) {
            return $fetched;
        }
    }

    return ['email' => '', 'name' => ''];
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

    $received = asaas_received_webhook_token();

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

        $customer = asaas_customer_from_entity($checkout);
        if ($customer['email'] !== '') {
            return $customer;
        }

        $checkoutId = trim((string) ($checkout['id'] ?? ''));
        if ($checkoutId !== '') {
            $fetchedCheckout = asaas_fetch_checkout_by_id($checkoutId);
            if (is_array($fetchedCheckout)) {
                return asaas_customer_from_entity($fetchedCheckout);
            }
        }

        return ['email' => '', 'name' => ''];
    }

    $payment = $payload['payment'] ?? [];
    if (!is_array($payment)) {
        return ['email' => '', 'name' => ''];
    }

    $customer = asaas_customer_from_entity($payment);
    if ($customer['email'] !== '') {
        return $customer;
    }

    $paymentId = trim((string) ($payment['id'] ?? ''));
    if ($paymentId !== '') {
        $fetchedPayment = asaas_fetch_payment_by_id($paymentId);
        if (is_array($fetchedPayment)) {
            $customer = asaas_customer_from_entity($fetchedPayment);
            if ($customer['email'] !== '') {
                return $customer;
            }

            $checkoutId = trim((string) ($fetchedPayment['checkoutSession'] ?? $fetchedPayment['checkout'] ?? ''));
            if ($checkoutId !== '') {
                $fetchedCheckout = asaas_fetch_checkout_by_id($checkoutId);
                if (is_array($fetchedCheckout)) {
                    return asaas_customer_from_entity($fetchedCheckout);
                }
            }
        }
    }

    return ['email' => '', 'name' => ''];
}

/**
 * @param array<string, mixed> $payment
 * @return array{ok:bool,http_status:int,message:string,email?:string}
 */
function asaas_provision_from_payment_entity(array $payment, string $source = 'api', bool $sendEmail = true): array
{
    $paymentId = trim((string) ($payment['id'] ?? ''));
    $customer = asaas_customer_from_entity($payment);
    $email = $customer['email'];
    $name = $customer['name'];

    if ($email === '' && $paymentId !== '') {
        $fetched = asaas_fetch_payment_by_id($paymentId);
        if (is_array($fetched)) {
            $payment = $fetched;
            $customer = asaas_customer_from_entity($payment);
            $email = $customer['email'];
            $name = $customer['name'];
            if ($email === '') {
                $checkoutId = trim((string) ($payment['checkoutSession'] ?? $payment['checkout'] ?? ''));
                if ($checkoutId !== '') {
                    $checkout = asaas_fetch_checkout_by_id($checkoutId);
                    if (is_array($checkout)) {
                        $customer = asaas_customer_from_entity($checkout);
                        $email = $customer['email'];
                        $name = $customer['name'];
                    }
                }
            }
        }
    }

    if ($email === '') {
        return ['ok' => false, 'http_status' => 422, 'message' => 'E-mail do comprador ausente', 'email' => ''];
    }

    if ($paymentId !== '' && asaas_payment_already_processed($paymentId)) {
        return ['ok' => true, 'http_status' => 200, 'message' => 'Pagamento já processado', 'email' => $email];
    }

    $result = provision_member_access($email, $name, $sendEmail);
    if (!$result['ok']) {
        asaas_log("[{$source}] Falha provisionar {$email}: " . ($result['error'] ?? ''));
        return ['ok' => false, 'http_status' => 500, 'message' => $result['error'] ?? 'Erro ao cadastrar membro', 'email' => $email];
    }

    if ($paymentId !== '') {
        asaas_mark_payment_processed($paymentId, $email);
    }

    $action = ($result['created'] ?? false) ? 'criado' : 'atualizado';
    if ($sendEmail) {
        $mail = ($result['email_sent'] ?? false) ? 'e-mail enviado' : 'e-mail NÃO enviado: ' . ($result['error'] ?? '');
    } else {
        $mail = 'sem e-mail (sincronização manual)';
    }
    asaas_log("[{$source}] OK {$paymentId}: {$email} {$action}, {$mail}");

    $message = "Membro {$action}";
    if ($sendEmail) {
        $message .= '; ' . (($result['email_sent'] ?? false) ? 'e-mail enviado' : ($result['error'] ?? 'e-mail não enviado'));
    } else {
        $message .= '; cadastrado sem enviar e-mail';
    }

    return [
        'ok' => true,
        'http_status' => 200,
        'message' => $message,
        'email' => $email,
    ];
}

/**
 * Busca cobranças confirmadas/recebidas no Asaas e provisiona as que ainda não foram processadas.
 *
 * @return array{ok:bool,processed:int,skipped:int,errors:list<string>,details:list<string>}
 */
function asaas_reconcile_recent_payments(int $days = 30, bool $sendEmail = false): array
{
    if (!asaas_is_configured()) {
        return ['ok' => false, 'processed' => 0, 'skipped' => 0, 'errors' => ['Asaas não configurado.'], 'details' => []];
    }

    $days = max(1, min(90, $days));
    $since = date('Y-m-d', strtotime('-' . $days . ' days'));
    $statuses = ['CONFIRMED', 'RECEIVED'];
    $processed = 0;
    $skipped = 0;
    $errors = [];
    $details = [];

    foreach ($statuses as $status) {
        $offset = 0;
        do {
            $query = http_build_query([
                'status' => $status,
                'limit' => 50,
                'offset' => $offset,
                'dateCreated[ge]' => $since,
            ]);
            $response = asaas_api_request('GET', '/payments?' . $query);
            if (!$response['ok']) {
                $errors[] = "Listar {$status}: " . $response['error'];
                break;
            }

            $items = $response['body']['data'] ?? [];
            if (!is_array($items) || $items === []) {
                break;
            }

            foreach ($items as $payment) {
                if (!is_array($payment)) {
                    continue;
                }
                $paymentId = trim((string) ($payment['id'] ?? ''));
                if ($paymentId !== '' && asaas_payment_already_processed($paymentId)) {
                    $skipped++;
                    continue;
                }

                $result = asaas_provision_from_payment_entity($payment, 'reconcile', $sendEmail);
                if ($result['ok'] && ($result['message'] ?? '') !== 'Pagamento já processado') {
                    $processed++;
                    $details[] = ($result['email'] ?? '') . ': ' . $result['message'];
                } elseif (!$result['ok']) {
                    $errors[] = ($paymentId !== '' ? $paymentId : 'pagamento') . ': ' . $result['message'];
                } else {
                    $skipped++;
                }
            }

            $offset += count($items);
            $hasMore = (bool) ($response['body']['hasMore'] ?? false);
        } while ($hasMore && $offset < 200);
    }

    asaas_log("Reconciliação {$days}d: processados={$processed} ignorados={$skipped} erros=" . count($errors));

    return [
        'ok' => $errors === [] || $processed > 0,
        'processed' => $processed,
        'skipped' => $skipped,
        'errors' => $errors,
        'details' => $details,
    ];
}

/** @return array{ok:bool,message:string} */
function asaas_register_webhook_in_panel(): array
{
    if (!asaas_is_configured()) {
        return ['ok' => false, 'message' => 'Asaas não configurado.'];
    }

    $cfg = app_config();
    $name = trim((string) ($cfg['asaas_webhook_name'] ?? 'asaas-figu'));
    $token = trim((string) ($cfg['asaas_webhook_token'] ?? ''));
    $url = asaas_webhook_url();
    $events = [
        'CHECKOUT_PAID',
        'PAYMENT_CONFIRMED',
        'PAYMENT_RECEIVED',
    ];
    $notifyEmail = trim((string) ($cfg['admin_email'] ?? $cfg['mail_from_email'] ?? 'suporte@agenciajob.com'));
    if ($notifyEmail === '' || !filter_var($notifyEmail, FILTER_VALIDATE_EMAIL)) {
        $notifyEmail = 'suporte@agenciajob.com';
    }

    $webhookPayload = [
        'name' => $name,
        'url' => $url,
        'email' => $notifyEmail,
        'enabled' => true,
        'interrupted' => false,
        'events' => $events,
        'sendType' => 'SEQUENTIALLY',
    ];
    if ($token !== '') {
        $webhookPayload['authToken'] = $token;
    }

    $list = asaas_api_request('GET', '/webhooks?limit=20');
    if ($list['ok'] && is_array($list['body']['data'] ?? null)) {
        foreach ($list['body']['data'] as $hook) {
            if (!is_array($hook)) {
                continue;
            }
            $hookUrl = rtrim((string) ($hook['url'] ?? ''), '/');
            $hookName = trim((string) ($hook['name'] ?? ''));
            $matches = $hookUrl === rtrim($url, '/')
                || $hookName === $name
                || str_contains($hookUrl, 'perfectpay.agenciajob.com')
                || str_contains($hookUrl, 'copa.agenciajob.com');
            if ($matches) {
                $hookId = trim((string) ($hook['id'] ?? ''));
                if ($hookId !== '') {
                    $update = asaas_api_request('PUT', '/webhooks/' . rawurlencode($hookId), $webhookPayload);
                    if ($update['ok']) {
                        return ['ok' => true, 'message' => 'Webhook atualizado no Asaas para ' . $url];
                    }
                }
            }
        }
    }

    $body = $webhookPayload;
    $create = asaas_api_request('POST', '/webhooks', $body);
    if ($create['ok']) {
        return ['ok' => true, 'message' => 'Webhook criado no Asaas com URL e eventos corretos.'];
    }

    return ['ok' => false, 'message' => $create['error']];
}

/**
 * @param array<string, mixed> $payload
 * @return array{ok:bool,http_status:int,message:string}
 */
function asaas_handle_webhook(array $payload): array
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $eventPreview = strtoupper(trim((string) ($payload['event'] ?? '')));
    asaas_log("POST recebido IP={$ip} evento={$eventPreview}");

    if (!asaas_webhook_token_valid()) {
        $receivedLen = strlen(asaas_received_webhook_token());
        asaas_log("Token webhook inválido (IP {$ip}, token recebido len={$receivedLen}).");
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

    if ($event === 'CHECKOUT_PAID') {
        $checkout = $payload['checkout'] ?? [];
        if (is_array($checkout)) {
            $checkoutId = trim((string) ($checkout['id'] ?? ''));
            if ($checkoutId !== '' && asaas_payment_already_processed($checkoutId)) {
                return ['ok' => true, 'http_status' => 200, 'message' => 'Checkout já processado'];
            }
        }
    }

    if ($paymentId !== '' && asaas_payment_already_processed($paymentId)) {
        return ['ok' => true, 'http_status' => 200, 'message' => 'Pagamento já processado'];
    }

    $paymentEntity = $payload['payment'] ?? null;
    if (is_array($paymentEntity) && in_array($event, ['PAYMENT_CONFIRMED', 'PAYMENT_RECEIVED'], true)) {
        $provision = asaas_provision_from_payment_entity($paymentEntity, 'webhook');
        return [
            'ok' => $provision['ok'],
            'http_status' => (int) $provision['http_status'],
            'message' => $provision['message'],
        ];
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
