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
    } catch (Throwable) {
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
