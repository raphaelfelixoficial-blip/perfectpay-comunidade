<?php

declare(strict_types=1);

if (!function_exists('comunidade_url')) {
    require_once __DIR__ . '/bootstrap.php';
}

function albuns_root(): string
{
    return dirname(__DIR__) . '/albuns';
}

function albuns_cache_file(): string
{
    return dirname(__DIR__) . '/data/albuns-catalog.json';
}

function albuns_catalog_ttl(): int
{
    return 86400;
}

function albuns_cache_lock_file(): string
{
    return albuns_cache_file() . '.lock';
}

/** @return array{built_at:int,categories:array,total?:int}|null */
function albuns_read_cache_payload(): ?array
{
    $cacheFile = albuns_cache_file();
    if (!is_file($cacheFile)) {
        return null;
    }

    $raw = json_decode((string) file_get_contents($cacheFile), true);
    if (!is_array($raw) || !is_array($raw['categories'] ?? null)) {
        return null;
    }

    return $raw;
}

function albuns_normalize_path(string $path): string
{
    return str_replace('\\', '/', $path);
}

function albuns_resolve_path(string $relative): ?string
{
    $relative = ltrim(albuns_normalize_path($relative), '/');
    if ($relative === '' || str_contains($relative, '..')) {
        return null;
    }

    $root = realpath(albuns_root());
    if ($root === false) {
        return null;
    }

    $candidate = albuns_normalize_path($root . '/' . $relative);
    $full = realpath($candidate);

    if ($full === false || !is_file($full)) {
        return null;
    }
    if (!str_starts_with(albuns_normalize_path($full), albuns_normalize_path($root . '/'))) {
        return null;
    }
    if (strtolower(pathinfo($full, PATHINFO_EXTENSION)) !== 'pdf') {
        return null;
    }

    return $full;
}

function albuns_relative_path(string $absolute): string
{
    $root = albuns_normalize_path(realpath(albuns_root()) ?: albuns_root());
    $absolute = albuns_normalize_path($absolute);
    if (str_starts_with($absolute, $root)) {
        return ltrim(substr($absolute, strlen($root)), '/');
    }
    return basename($absolute);
}

/** @return list<string> */
function albuns_find_pdf_files(): array
{
    $root = albuns_root();
    if (!is_dir($root)) {
        return [];
    }

    $found = [];

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'pdf') {
                $found[$file->getPathname()] = true;
            }
        }
    } catch (Throwable) {
        foreach ([
            $root . '/*.pdf',
            $root . '/*/*.pdf',
            $root . '/*/*/*.pdf',
            $root . '/*/*/*/*.pdf',
        ] as $pattern) {
            foreach (glob($pattern) ?: [] as $file) {
                if (is_file($file)) {
                    $found[$file] = true;
                }
            }
        }
    }

    return array_keys($found);
}

/** @return array<string, list<array{name:string,path:string,size:int}>> */
function albuns_build_catalog(): array
{
    $categories = [];

    foreach (albuns_find_pdf_files() as $absolute) {
        $relative = albuns_relative_path($absolute);
        $category = dirname($relative);
        if ($category === '.' || $category === '') {
            $category = 'Álbuns';
        }
        $category = str_replace('/', ' · ', $category);

        $categories[$category][] = [
            'name' => basename($absolute),
            'path' => $relative,
            'size' => (int) @filesize($absolute),
        ];
    }

    foreach ($categories as &$files) {
        usort($files, static fn ($a, $b) => strcasecmp($a['name'], $b['name']));
    }
    unset($files);
    ksort($categories, SORT_NATURAL | SORT_FLAG_CASE);

    return $categories;
}

function albuns_invalidate_catalog_cache(): void
{
    $cacheFile = albuns_cache_file();
    $lockFile = albuns_cache_lock_file();
    if (is_file($cacheFile)) {
        @unlink($cacheFile);
    }
    if (is_file($lockFile)) {
        @unlink($lockFile);
    }
}

/** Detecta renomeação/remoção de PDFs (caminho do cache não existe mais no disco). */
function albuns_catalog_paths_outdated(array $categories): bool
{
    $root = realpath(albuns_root());
    if ($root === false) {
        return true;
    }

    $rootPrefix = albuns_normalize_path($root) . '/';

    foreach ($categories as $files) {
        foreach ($files as $file) {
            $relative = ltrim(albuns_normalize_path((string) ($file['path'] ?? '')), '/');
            if ($relative === '' || str_contains($relative, '..')) {
                continue;
            }
            $full = albuns_normalize_path($rootPrefix . $relative);
            if (!is_file($full)) {
                return true;
            }
            $cachedName = (string) ($file['name'] ?? '');
            if ($cachedName !== '' && basename($full) !== $cachedName) {
                return true;
            }
        }
    }

    return false;
}

function albuns_save_catalog_cache(array $categories): void
{
    $cacheFile = albuns_cache_file();
    $dir = dirname($cacheFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    $payload = json_encode([
        'built_at' => time(),
        'categories' => $categories,
        'total' => array_sum(array_map('count', $categories)),
    ], JSON_UNESCAPED_UNICODE);

    if ($payload !== false) {
        file_put_contents($cacheFile, $payload, LOCK_EX);
    }
}

/** @return array<string, list<array{name:string,path:string,size:int}>> */
function albuns_rebuild_catalog_locked(): array
{
    $lockFile = albuns_cache_lock_file();
    $dir = dirname($lockFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    $fp = fopen($lockFile, 'c+');
    if ($fp === false) {
        $categories = albuns_build_catalog();
        albuns_save_catalog_cache($categories);
        return $categories;
    }

    try {
        if (!flock($fp, LOCK_EX)) {
            $categories = albuns_build_catalog();
            albuns_save_catalog_cache($categories);
            return $categories;
        }

        $cached = albuns_read_cache_payload();
        if ($cached !== null) {
            $age = time() - (int) ($cached['built_at'] ?? 0);
            if ($age >= 0 && $age < albuns_catalog_ttl()) {
                return $cached['categories'];
            }
        }

        $categories = albuns_build_catalog();
        albuns_save_catalog_cache($categories);
        return $categories;
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

/** @return array<string, list<array{name:string,path:string,size:int}>> */
function albuns_get_catalog(): array
{
    $cached = albuns_read_cache_payload();
    if ($cached !== null) {
        $categories = $cached['categories'];
        $age = time() - (int) ($cached['built_at'] ?? 0);
        $cacheFresh = $age >= 0 && $age < albuns_catalog_ttl();
        if ($cacheFresh && !albuns_catalog_paths_outdated($categories)) {
            return $categories;
        }

        $lockFile = albuns_cache_lock_file();
        $fp = @fopen($lockFile, 'c+');
        if ($fp !== false && flock($fp, LOCK_EX | LOCK_NB)) {
            try {
                $refreshed = albuns_read_cache_payload();
                if ($refreshed !== null) {
                    $freshAge = time() - (int) ($refreshed['built_at'] ?? 0);
                    if ($freshAge >= 0 && $freshAge < albuns_catalog_ttl()) {
                        return $refreshed['categories'];
                    }
                }
                $categories = albuns_build_catalog();
                albuns_save_catalog_cache($categories);
                return $categories;
            } finally {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }
        if ($fp !== false) {
            fclose($fp);
        }

        return $cached['categories'];
    }

    return albuns_rebuild_catalog_locked();
}

/** @return array<string, list<array{name:string,path:string,size:int}>> */
function albuns_scan_by_category(): array
{
    return albuns_get_catalog();
}

function albuns_total_pdfs(): int
{
    $cached = albuns_read_cache_payload();
    if ($cached !== null) {
        if (isset($cached['total'])) {
            return (int) $cached['total'];
        }
        return array_sum(array_map('count', $cached['categories']));
    }

    $categories = albuns_get_catalog();
    return array_sum(array_map('count', $categories));
}

function albuns_view_url(string $relativePath): string
{
    return comunidade_url('/albuns/ver.php?p=' . rawurlencode($relativePath));
}

function albuns_format_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 0) . ' KB';
    }
    return $bytes . ' B';
}

/** @return list<array{title:string,desc:string,url:string,icon:string,external:bool}> */
function albuns_as_download_items(?array $categories = null): array
{
    $categories ??= albuns_get_catalog();
    $items = [];
    foreach ($categories as $category => $files) {
        foreach ($files as $file) {
            $items[] = [
                'title' => $file['name'],
                'desc' => $category,
                'url' => albuns_view_url($file['path']),
                'icon' => 'ti-file-type-pdf',
                'external' => false,
            ];
        }
    }
    return $items;
}

/** @param list<string> $patterns */
function albuns_featured_downloads(array $patterns, ?array $categories = null): array
{
    $all = albuns_as_download_items($categories);
    if ($patterns === []) {
        return array_slice($all, 0, 5);
    }

    $featured = [];
    $used = [];
    foreach ($patterns as $pattern) {
        foreach ($all as $i => $item) {
            if (isset($used[$i])) {
                continue;
            }
            if (stripos($item['title'], $pattern) !== false) {
                $featured[] = $item;
                $used[$i] = true;
                break;
            }
        }
    }

    return $featured !== [] ? $featured : array_slice($all, 0, 5);
}

function albuns_refresh_rate_limit_ok(): bool
{
    if (!function_exists('start_session')) {
        return true;
    }
    start_session();
    $last = (int) ($_SESSION['albuns_refresh_last'] ?? 0);
    if (time() - $last < 120) {
        return false;
    }
    $_SESSION['albuns_refresh_last'] = time();
    return true;
}

/** @return array{ok:bool,total?:int,error?:string} */
function albuns_process_refresh_request(): array
{
    if (!albuns_refresh_rate_limit_ok()) {
        return ['ok' => false, 'error' => 'Aguarde 2 minutos antes de atualizar a lista novamente.'];
    }

    albuns_invalidate_catalog_cache();
    $categories = albuns_build_catalog();
    albuns_save_catalog_cache($categories);
    $total = array_sum(array_map('count', $categories));

    return ['ok' => true, 'total' => $total];
}

function albuns_refresh_button_styles(): string
{
    return <<<'CSS'
  .albuns-refresh-wrap{margin:0 0 1rem;text-align:right}
  .albuns-refresh-form{display:inline-block;margin:0}
  .albuns-refresh-btn{
    display:inline-flex;align-items:center;gap:6px;
    padding:8px 14px;border-radius:4px;
    border:1px solid #444;    background:#242018;
    color:#b8a878;font-family:'DM Sans',system-ui,sans-serif;
    font-size:13px;font-weight:600;cursor:pointer;
    transition:all .2s;
  }
  .albuns-refresh-btn:hover{border-color:#facc15;color:#facc15;background:#2e2818}
  .albuns-refresh-btn i{font-size:15px}
  .member-flash,.pp-flash{padding:10px 14px;border-radius:10px;margin-bottom:1rem;font-size:13px;background:rgba(250,204,21,.1);border:1px solid rgba(250,204,21,.4);color:#faf6e8}
CSS;
}

function render_albuns_refresh_button(string $returnUrl = '/albuns/'): void
{
    $returnUrl = albuns_normalize_path($returnUrl);
    if ($returnUrl === '' || !str_starts_with($returnUrl, '/')) {
        $returnUrl = '/albuns/';
    }
    ?>
    <div class="albuns-refresh-wrap">
      <form class="albuns-refresh-form" method="post" action="<?= htmlspecialchars(comunidade_url('/albuns/atualizar-lista.php'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="return" value="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="albuns-refresh-btn" title="Atualiza os nomes dos PDFs na lista">
          <i class="ti ti-refresh"></i> Atualizar lista
        </button>
      </form>
    </div>
    <?php
}

function albuns_refresh_card_styles(): string
{
    return albuns_refresh_button_styles();
}

function render_albuns_refresh_card(string $returnUrl = '/albuns/'): void
{
    render_albuns_refresh_button($returnUrl);
}

function albuns_stream_file(string $path): void
{
    if (function_exists('set_time_limit')) {
        @set_time_limit(0);
    }

    $size = filesize($path);
    if ($size === false) {
        http_response_code(500);
        exit('Erro ao ler o arquivo.');
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($path) . '"');
    header('Content-Length: ' . (string) $size);
    header('Accept-Ranges: bytes');
    header('Cache-Control: private, max-age=3600');

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    $fp = fopen($path, 'rb');
    if ($fp === false) {
        http_response_code(500);
        exit('Erro ao abrir o arquivo.');
    }

    while (!feof($fp)) {
        $chunk = fread($fp, 1024 * 256);
        if ($chunk === false) {
            break;
        }
        echo $chunk;
        if (function_exists('flush')) {
            flush();
        }
    }
    fclose($fp);
}
