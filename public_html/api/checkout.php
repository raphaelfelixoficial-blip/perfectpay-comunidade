<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$root = dirname(__DIR__);
$comunidade = is_dir($root . '/comunidade') ? $root . '/comunidade' : dirname($root) . '/comunidade';

require_once $comunidade . '/includes/bootstrap.php';
require_once $comunidade . '/includes/asaas.php';
require_once $comunidade . '/includes/site-status.php';

if (!asaas_is_configured()) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Checkout indisponível.']);
    exit;
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

$input = [];
if ($method === 'POST') {
    $decoded = json_decode(file_get_contents('php://input') ?: '', true);
    $input = is_array($decoded) ? $decoded : $_POST;
}

$action = trim((string) ($input['action'] ?? $_GET['action'] ?? $_POST['action'] ?? ''));

if ($method === 'GET' && ($action === '' || $action === 'config')) {
    $product = asaas_checkout_product();
    echo json_encode([
        'ok' => true,
        'product' => $product,
        'price_label' => site_format_price_brl($product['value']),
        'compare_label' => site_checkout_compare_price() > $product['value']
            ? site_format_price_brl(site_checkout_compare_price())
            : '',
    ]);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$name = trim((string) ($input['name'] ?? ''));
$email = normalize_email((string) ($input['email'] ?? ''));
$cpf = (string) ($input['cpf'] ?? '');

if ($action === 'pix') {
    $result = asaas_create_transparent_pix($name, $email, $cpf);
    http_response_code($result['ok'] ? 200 : 422);
    echo json_encode($result);
    exit;
}

if ($action === 'card') {
    $card = is_array($input['card'] ?? null) ? $input['card'] : [];
    $result = asaas_create_transparent_card($name, $email, $cpf, $card);
    http_response_code($result['ok'] ? 200 : 422);
    echo json_encode($result);
    exit;
}

if ($action === 'status') {
    $paymentId = trim((string) ($input['payment_id'] ?? ''));
    $result = asaas_finalize_paid_payment($paymentId, $email, $name);
    http_response_code($result['ok'] ? 200 : 422);
    echo json_encode($result);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Ação inválida.']);
