<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/albuns.php';

require_member();
start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . comunidade_url('/albuns/'));
    exit;
}

$return = (string) ($_POST['return'] ?? '/albuns/');
$return = albuns_normalize_path($return);
if ($return === '' || !str_starts_with($return, '/')) {
    $return = '/albuns/';
}

$result = albuns_process_refresh_request();
if ($result['ok']) {
    $_SESSION['flash'] = 'Lista de PDFs atualizada (' . (int) $result['total'] . ' arquivo(s)).';
} else {
    $_SESSION['flash'] = (string) ($result['error'] ?? 'Não foi possível atualizar a lista.');
}

header('Location: ' . comunidade_url($return));
exit;
