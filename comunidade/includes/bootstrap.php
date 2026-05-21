<?php

declare(strict_types=1);

$configPath = dirname(__DIR__) . '/data/config.php';
if (!is_file($configPath)) {
    $example = dirname(__DIR__) . '/includes/config.php.example';
    if (is_file($example)) {
        copy($example, $configPath);
    }
}

$config = is_file($configPath) ? require $configPath : require __DIR__ . '/config.php.example';

date_default_timezone_set((string) ($config['timezone'] ?? 'America/Sao_Paulo'));

$dataDir = dirname(__DIR__) . '/data';
$emailsFile = $dataDir . '/allowed_emails.json';
$sessionsDir = $dataDir . '/sessions';

foreach ([$dataDir, $sessionsDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
}

if (!is_file($emailsFile)) {
    file_put_contents($emailsFile, json_encode(['members' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function app_config(): array
{
    global $config;
    return $config;
}

/** URL pública do site (sem barra final). */
function site_base_url(): string
{
    $base = rtrim((string) (app_config()['site_base_url'] ?? 'https://copa.agenciajob.com'), '/');

    return $base !== '' ? $base : 'https://copa.agenciajob.com';
}

/** URL absoluta no site (ex.: /comunidade/login.php). */
function site_url(string $path = '/'): string
{
    if ($path === '' || $path === '/') {
        return site_base_url() . '/';
    }
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }

    return site_base_url() . $path;
}

/** Caminho web da pasta comunidade (ex.: /comunidade). */
function comunidade_web_base(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $appRoot = str_replace('\\', '/', dirname(__DIR__));

    if ($docRoot !== '' && str_starts_with($appRoot, $docRoot)) {
        $base = rtrim(substr($appRoot, strlen($docRoot)), '/');
        return $base;
    }

    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (preg_match('#^(/.+?)/comunidade(?:/|$)#', $script, $m)) {
        $base = $m[1] . '/comunidade';
        return $base;
    }

    $dir = dirname($script);
    if (in_array(basename($dir), ['admin', 'albuns'], true)) {
        $dir = dirname($dir);
    }

    $base = ($dir === '/' || $dir === '\\') ? '' : rtrim($dir, '/');
    return $base;
}

function comunidade_url(string $path = '/'): string
{
    if ($path === '' || $path === '/') {
        return comunidade_web_base() . '/';
    }
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }

    return comunidade_web_base() . $path;
}

function emails_file(): string
{
    global $emailsFile;
    return $emailsFile;
}

function load_members(): array
{
    $raw = file_get_contents(emails_file());
    $data = json_decode($raw ?: '{}', true);
    return is_array($data['members'] ?? null) ? $data['members'] : [];
}

function save_members(array $members): bool
{
    $payload = json_encode(['members' => array_values($members)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(emails_file(), $payload, LOCK_EX) !== false;
}

function normalize_email(string $email): string
{
    return strtolower(trim($email));
}

function app_timezone(): DateTimeZone
{
    static $tz = null;
    if ($tz === null) {
        $tz = new DateTimeZone(date_default_timezone_get() ?: 'America/Sao_Paulo');
    }
    return $tz;
}

/** Data/hora atual para cadastro de membros (ISO com fuso de Brasília). */
function member_added_at_now(): string
{
    return (new DateTimeImmutable('now', app_timezone()))->format('Y-m-d\TH:iP');
}

/** Exibe data de cadastro no admin (d/m/Y H:i, horário de Brasília). */
function format_member_added_at(?string $value): string
{
    if ($value === null || trim($value) === '' || $value === '—') {
        return '—';
    }

    $value = trim($value);
    $tzBr = app_timezone();

    try {
        return (new DateTimeImmutable($value))->setTimezone($tzBr)->format('d/m/Y H:i');
    } catch (Throwable $e) {
    }

    // Formato antigo sem fuso (servidor costumava gravar em UTC)
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i', $value, new DateTimeZone('UTC'));
    if ($dt !== false) {
        return $dt->setTimezone($tzBr)->format('d/m/Y H:i');
    }

    return $value;
}

/** @return list<string> */
function parse_email_list(string $input): array
{
    $tokens = preg_split('/[\s,;]+/', trim($input), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $emails = [];

    foreach ($tokens as $token) {
        $email = normalize_email($token);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emails[$email] = true;
        }
    }

    return array_keys($emails);
}
