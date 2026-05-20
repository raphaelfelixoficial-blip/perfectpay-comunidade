<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/albuns.php';
require_member();

$relative = (string) ($_GET['p'] ?? '');
$path = albuns_resolve_path($relative);

if ($path === null) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

albuns_stream_file($path);
exit;
