<?php

declare(strict_types=1);

/**
 * Atualiza URLs em data/config.php para copa.agenciajob.com.
 * Uso no servidor: php comunidade/scripts/migrate-domain.php
 */

$path = dirname(__DIR__) . '/data/config.php';
if (!is_file($path)) {
    fwrite(STDERR, "config.php não encontrado em {$path}\n");
    exit(1);
}

$cfg = require $path;
$from = 'https://perfectpay.agenciajob.com';
$to = 'https://copa.agenciajob.com';

$replace = static function (mixed $value) use ($from, $to): mixed {
    if (is_string($value)) {
        return str_replace($from, $to, $value);
    }
    return $value;
};

foreach ($cfg as $key => $value) {
    $cfg[$key] = $replace($value);
}

$cfg['site_base_url'] = $to;
$cfg['mail_login_url'] = $to . '/comunidade/login.php';
$cfg['asaas_thankyou_url'] = $to . '/obrigado.php';
$cfg['asaas_cancel_url'] = $to . '/';

$export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
file_put_contents($path, $export, LOCK_EX);

echo "config.php atualizado para {$to}\n";
