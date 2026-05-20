<?php

declare(strict_types=1);

require_once __DIR__ . '/albuns.php';

function downloads_config(): array
{
    $path = dirname(__DIR__) . '/data/downloads.json';
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function load_downloads(?array $albunsCatalog = null): array
{
    $data = downloads_config();
    $base = rtrim((string) ($data['base'] ?? ''), '/');
    $items = [];

    foreach ($data['items'] ?? [] as $item) {
        if (!is_array($item)) {
            continue;
        }
        $url = (string) ($item['url'] ?? '');
        if ($url === '' && !empty($item['albuns_path'])) {
            $url = albuns_view_url((string) $item['albuns_path']);
        }
        if ($url === '' && !empty($item['file'])) {
            $url = $base . '/' . ltrim((string) $item['file'], '/');
        }
        if ($url === '') {
            continue;
        }
        $items[] = [
            'title' => (string) ($item['title'] ?? 'Download'),
            'desc' => (string) ($item['desc'] ?? ''),
            'url' => $url,
            'icon' => (string) ($item['icon'] ?? 'ti-download'),
            'external' => !empty($item['external']),
        ];
    }

    if ($items !== []) {
        return $items;
    }

    $patterns = $data['featured_patterns'] ?? [];
    if (!is_array($patterns)) {
        $patterns = [];
    }

    return albuns_featured_downloads($patterns, $albunsCatalog);
}

function load_all_albuns_downloads(): array
{
    return albuns_as_download_items();
}

function drive_folder_url(): string
{
    $path = dirname(__DIR__) . '/data/downloads.json';
    if (!is_file($path)) {
        return '';
    }
    $data = json_decode((string) file_get_contents($path), true);
    return (string) ($data['drive_folder'] ?? '');
}
