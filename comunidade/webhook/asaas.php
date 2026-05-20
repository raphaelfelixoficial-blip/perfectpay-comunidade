<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/asaas.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input') ?: '';
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
$len = strlen($raw);
asaas_log("Webhook HTTP POST IP={$ip} bytes={$len}");

$payload = json_decode($raw, true);

if (!is_array($payload)) {
    $payload = $_POST;
}

if (!is_array($payload) || $payload === []) {
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $ct = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
    $preview = substr(preg_replace('/\s+/', ' ', $raw), 0, 200);
    asaas_log("Payload vazio ou inválido IP={$ip} CT={$ct} raw={$preview}");
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
    exit;
}

$result = asaas_handle_webhook($payload);
http_response_code((int) $result['http_status']);
echo json_encode(['ok' => $result['ok'], 'message' => $result['message']]);
