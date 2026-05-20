<?php
/** Aplica SMTP do servidor (cPanel) em data/config.php — execute uma vez no servidor. */
$path = dirname(__DIR__) . '/data/config.php';
$cfg = require $path;
$cfg['smtp_host'] = 'localhost';
$cfg['smtp_port'] = 587;
$cfg['smtp_encryption'] = 'tls';
$cfg['mail_from_email'] = 'noreply@agenciajob.com';
$cfg['mail_reply_to'] = 'suporte@agenciajob.com';
$cfg['admin_email'] = 'suporte@agenciajob.com';
$cfg['smtp_username'] = 'noreply@agenciajob.com';
if (!isset($cfg['smtp_password'])) {
    $cfg['smtp_password'] = '';
}
file_put_contents($path, "<?php\nreturn " . var_export($cfg, true) . ";\n");
echo "OK — SMTP servidor (noreply@) e admin suporte@ aplicados.\n";
