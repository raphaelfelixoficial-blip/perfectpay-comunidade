<?php

declare(strict_types=1);

/**
 * Reconstrói o índice de PDFs (use no cron do Hostinger, ex.: 1x por dia às 4h).
 * php comunidade/scripts/rebuild-albuns-cache.php
 */

require_once dirname(__DIR__) . '/includes/albuns.php';

$started = microtime(true);
$categories = albuns_rebuild_catalog_locked();
$total = array_sum(array_map('count', $categories));
$elapsed = round(microtime(true) - $started, 1);

echo "Catálogo atualizado: {$total} PDF(s) em {$elapsed}s\n";
