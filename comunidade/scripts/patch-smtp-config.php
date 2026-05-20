<?php
/** Execute no servidor: php scripts/patch-smtp-config.php */
$p = dirname(__DIR__) . '/data/config.php';
$cfg = require $p;
$cfg['smtp_host'] = 'smtp.hostinger.com';
$cfg['smtp_port'] = 465;
$cfg['smtp_encryption'] = 'ssl';
$cfg['mail_from_email'] = 'noreply@agenciajob.com';
$cfg['mail_reply_to'] = 'noreply@agenciajob.com';
$cfg['whatsapp_group_url'] = 'https://chat.whatsapp.com/DVBiPgbpbiyC8y6mD8iqIS';
$cfg['smtp_username'] = 'noreply@agenciajob.com';
if (!isset($cfg['smtp_password'])) {
    $cfg['smtp_password'] = '';
}
file_put_contents($p, "<?php\nreturn " . var_export($cfg, true) . ";\n");
echo "OK\n";
