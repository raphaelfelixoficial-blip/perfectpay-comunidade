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

// Alguns envios trazem JSON dentro de um campo (ex.: data, payload).
if (is_array($payload)) {
    foreach (['data', 'payload', 'postback', 'sale'] as $key) {
        if (!empty($payload[$key]) && is_string($payload[$key])) {
            $nested = json_decode($payload[$key], true);
            if (is_array($nested)) {
                $payload = $nested;
                break;
            }
        }
    }
}

if (!is_array($payload) || $payload === []) {
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $ct = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
    $preview = substr(preg_replace('/\s+/', ' ', $raw), 0, 200);
    perfectpay_log("Payload vazio ou inválido IP={$ip} CT={$ct} raw={$preview}");
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
    exit;
}

$result = perfectpay_handle_webhook($payload);
http_response_code((int) $result['http_status']);
echo json_encode(['ok' => $result['ok'], 'message' => $result['message']]);
