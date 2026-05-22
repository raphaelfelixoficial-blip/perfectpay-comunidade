<?php

declare(strict_types=1);

$bootstrap = __DIR__ . '/../comunidade/includes/bootstrap.php';
if (!is_file($bootstrap)) {
    $bootstrap = dirname(__DIR__) . '/comunidade/includes/bootstrap.php';
}
require_once $bootstrap;

$siteStatus = __DIR__ . '/../comunidade/includes/site-status.php';
if (!is_file($siteStatus)) {
    $siteStatus = dirname(__DIR__) . '/comunidade/includes/site-status.php';
}
require_once $siteStatus;
