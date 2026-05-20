<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/perfectpay.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input') ?: '';
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    $payload = $_POST;
}

if (!is_array($payload) || $payload === []) {
    perfectpay_log('Payload vazio ou inválido.');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
    exit;
}

$result = perfectpay_handle_webhook($payload);
http_response_code((int) $result['http_status']);
echo json_encode(['ok' => $result['ok'], 'message' => $result['message']]);
