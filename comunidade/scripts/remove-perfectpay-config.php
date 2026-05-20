<?php

declare(strict_types=1);

$configPath = dirname(__DIR__) . '/data/config.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "config.php não encontrado.\n");
    exit(1);
}

$cfg = require $configPath;
$remove = [
    'perfectpay_webhook_token',
    'perfectpay_product_codes',
    'perfectpay_checkout_url',
    'perfectpay_thankyou_url',
    'perfectpay_billet_url',
];
foreach ($remove as $key) {
    unset($cfg[$key]);
}
$cfg['payment_provider'] = 'asaas';

$export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
file_put_contents($configPath, $export, LOCK_EX);
echo "Chaves Perfect Pay removidas de config.php\n";
