<?php

declare(strict_types=1);

$bootstrap = __DIR__ . '/../comunidade/includes/bootstrap.php';
if (!is_file($bootstrap)) {
    $bootstrap = dirname(__DIR__) . '/comunidade/includes/bootstrap.php';
}
require_once $bootstrap;
