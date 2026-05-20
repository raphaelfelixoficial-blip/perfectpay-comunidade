<?php

declare(strict_types=1);

$asaasBootstrap = __DIR__ . '/comunidade/includes/bootstrap.php';
if (!is_file($asaasBootstrap)) {
    $asaasBootstrap = dirname(__DIR__) . '/comunidade/includes/bootstrap.php';
}
require_once $asaasBootstrap;
$asaasInclude = __DIR__ . '/comunidade/includes/asaas.php';
if (!is_file($asaasInclude)) {
    $asaasInclude = dirname(__DIR__) . '/comunidade/includes/asaas.php';
}
require_once $asaasInclude;

if (!asaas_is_configured()) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Checkout indisponível</title></head><body style="font-family:sans-serif;padding:2rem;text-align:center">';
    echo '<h1>Checkout em configuração</h1><p>Configure a chave API Asaas no painel admin.</p>';
    echo '<p><a href="/">Voltar ao site</a></p></body></html>';
    exit;
}

$email = normalize_email((string) ($_GET['email'] ?? ''));
$name = trim((string) ($_GET['name'] ?? ''));
$GLOBALS['asaas_checkout_cpf'] = (string) ($_GET['cpf'] ?? '');

$result = asaas_create_checkout_session($email, $name);

if (!$result['ok'] || empty($result['link'])) {
    http_response_code(502);
    header('Content-Type: text/html; charset=utf-8');
    $err = htmlspecialchars((string) ($result['error'] ?? 'Erro ao iniciar pagamento.'), ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro no checkout</title></head><body style="font-family:sans-serif;padding:2rem;text-align:center">';
    echo '<h1>Não foi possível abrir o pagamento</h1><p>' . $err . '</p>';
    echo '<p><a href="/">Voltar ao site</a></p></body></html>';
    exit;
}

header('Location: ' . $result['link'], true, 302);
exit;
