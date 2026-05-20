<?php

declare(strict_types=1);

/**
 * Uso no servidor (não versionar chaves):
 * php patch-asaas-config.php "SUA_CHAVE_API" "TOKEN_WEBHOOK_OPCIONAL" 97
 */

$configPath = dirname(__DIR__) . '/data/config.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "config.php não encontrado.\n");
    exit(1);
}

$apiKey = trim((string) ($argv[1] ?? ''));
$webhookToken = trim((string) ($argv[2] ?? ''));
$value = (float) str_replace(',', '.', (string) ($argv[3] ?? '97'));

if ($apiKey === '') {
    fwrite(STDERR, "Informe a chave API Asaas.\n");
    exit(1);
}

$cfg = require $configPath;
$cfg['payment_provider'] = 'asaas';
$cfg['site_base_url'] = $cfg['site_base_url'] ?? 'https://perfectpay.agenciajob.com';
$cfg['asaas_api_key'] = $apiKey;
$cfg['asaas_environment'] = 'production';
$cfg['asaas_webhook_token'] = $webhookToken;
$cfg['asaas_checkout_value'] = $value > 0 ? round($value, 2) : 97.0;
$cfg['asaas_checkout_item_name'] = $cfg['asaas_checkout_item_name'] ?? 'Comunidade VIP';
$cfg['asaas_checkout_item_description'] = $cfg['asaas_checkout_item_description'] ?? 'Acesso figurinhas Copa 2026';
$cfg['asaas_checkout_billing_types'] = $cfg['asaas_checkout_billing_types'] ?? 'PIX,CREDIT_CARD';
$cfg['asaas_checkout_minutes_expire'] = (int) ($cfg['asaas_checkout_minutes_expire'] ?? 60);
$cfg['asaas_thankyou_url'] = $cfg['asaas_thankyou_url'] ?? 'https://perfectpay.agenciajob.com/obrigado.php';
$cfg['asaas_cancel_url'] = $cfg['asaas_cancel_url'] ?? 'https://perfectpay.agenciajob.com/';

$export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
file_put_contents($configPath, $export, LOCK_EX);
echo "Asaas configurado em {$configPath}\n";
