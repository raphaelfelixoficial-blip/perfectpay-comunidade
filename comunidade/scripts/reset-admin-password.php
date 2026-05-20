<?php
/** Gera hash e atualiza senha admin em data/config.php — uso: php scripts/reset-admin-password.php [senha] */
declare(strict_types=1);

$password = $argv[1] ?? 'PerfectPayAdmin2026!';
$path = dirname(__DIR__) . '/data/config.php';

if (!is_file($path)) {
    fwrite(STDERR, "config.php não encontrado.\n");
    exit(1);
}

$cfg = require $path;
$cfg['admin_password_hash'] = password_hash($password, PASSWORD_DEFAULT);
file_put_contents($path, "<?php\nreturn " . var_export($cfg, true) . ";\n");
echo "Senha admin atualizada para: {$password}\n";
