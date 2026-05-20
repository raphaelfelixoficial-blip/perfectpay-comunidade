<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/downloads.php';
require_member();

$items = load_downloads();
$arquivosDir = __DIR__ . '/arquivos';

if (isset($_GET['file'])) {
    $name = basename((string) $_GET['file']);
    $path = $arquivosDir . '/' . $name;
    if ($name === '' || !is_file($path)) {
        http_response_code(404);
        exit('Arquivo não encontrado.');
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $name . '"');
    header('Content-Length: ' . (string) filesize($path));
    readfile($path);
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : -1;
if ($id < 0 || $id >= count($items)) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

$item = $items[$id];
$file = basename(parse_url($item['url'], PHP_URL_PATH) ?: '');
$local = $arquivosDir . '/' . $file;

if ($file !== '' && is_file($local)) {
    header('Location: download.php?file=' . rawurlencode($file), true, 302);
    exit;
}

header('Location: ' . $item['url'], true, 302);
exit;
