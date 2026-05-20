<?php

declare(strict_types=1);

$token = trim((string) ($argv[1] ?? ''));
if ($token === '') {
    fwrite(STDERR, "Uso: php set-asaas-webhook-token.php TOKEN\n");
    exit(1);
}

$configPath = dirname(__DIR__) . '/data/config.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "config.php não encontrado.\n");
    exit(1);
}

$cfg = require $configPath;
$cfg['asaas_webhook_token'] = $token;
if (!isset($cfg['asaas_webhook_name']) || $cfg['asaas_webhook_name'] === '') {
    $cfg['asaas_webhook_name'] = 'asaas-figu';
}
$export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
file_put_contents($configPath, $export, LOCK_EX);
echo "asaas_webhook_token atualizado.\n";
