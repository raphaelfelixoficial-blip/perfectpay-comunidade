<?php

declare(strict_types=1);

$landingStatusBootstrap = __DIR__ . '/comunidade/includes/site-status.php';
if (!is_file($landingStatusBootstrap)) {
    $landingStatusBootstrap = dirname(__DIR__) . '/comunidade/includes/site-status.php';
}
require_once $landingStatusBootstrap;

$S = site_status_view();

function landing_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
