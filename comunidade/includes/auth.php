<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/site-status.php';

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $cfg = app_config();
    session_name('copa_vip_sess');
    $cookiePath = comunidade_web_base() . '/';
    if ($cookiePath === '//') {
        $cookiePath = '/';
    }
    session_set_cookie_params([
        'lifetime' => (int) ($cfg['session_lifetime'] ?? 604800),
        'path' => $cookiePath,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function session_user(): ?array
{
    start_session();
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    $user = session_user();
    return ($user['role'] ?? '') === 'admin';
}

function is_member(): bool
{
    $user = session_user();
    return in_array($user['role'] ?? '', ['admin', 'member'], true);
}

function members_area_gate(): void
{
    if (members_area_enabled()) {
        return;
    }
    if (is_admin()) {
        return;
    }

    render_members_area_closed_page();
    exit;
}

function require_member(): void
{
    members_area_gate();
    if (!is_member()) {
        header('Location: ' . comunidade_url('/login.php'));
        exit;
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        if (!members_area_enabled()) {
            render_members_area_closed_page();
            exit;
        }
        header('Location: ' . comunidade_url('/login.php'));
        exit;
    }
}

function find_member(string $email): ?array
{
    $email = normalize_email($email);
    foreach (load_members() as $member) {
        if (normalize_email((string) ($member['email'] ?? '')) === $email) {
            return $member;
        }
    }
    return null;
}

function login_user(string $email, string $password): array
{
    $email = normalize_email($email);
    $cfg = app_config();

    if ($email === normalize_email((string) $cfg['admin_email'])) {
        if (!password_verify($password, (string) $cfg['admin_password_hash'])) {
            return ['ok' => false, 'error' => 'E-mail ou senha incorretos.'];
        }
        return [
            'ok' => true,
            'user' => [
                'email' => $email,
                'role' => 'admin',
                'name' => 'Administrador',
            ],
        ];
    }

    if (!members_area_enabled()) {
        return ['ok' => false, 'error' => 'A área de membros está temporariamente indisponível.'];
    }

    $member = find_member($email);
    if (!$member) {
        return ['ok' => false, 'error' => 'Este e-mail não está autorizado. Entre em contato com o suporte.'];
    }

    $hash = (string) ($member['password_hash'] ?? '');
    if ($hash === '' || !password_verify($password, $hash)) {
        return ['ok' => false, 'error' => 'E-mail ou senha incorretos.'];
    }

    return [
        'ok' => true,
        'user' => [
            'email' => $email,
            'role' => 'member',
            'name' => (string) ($member['name'] ?? $email),
        ],
    ];
}

function persist_session(array $user): void
{
    start_session();
    $_SESSION['user'] = $user;
    session_regenerate_id(true);
}

function logout_user(): void
{
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function generate_member_password(): string
{
    return substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(9))), 0, 10);
}

function update_admin_password(string $newPassword): bool
{
    $path = dirname(__DIR__) . '/data/config.php';
    if (!is_file($path)) {
        return false;
    }
    $cfg = require $path;
    $cfg['admin_password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
    $export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
    return file_put_contents($path, $export, LOCK_EX) !== false;
}

function update_member_password(string $email, string $newPassword): bool
{
    $email = normalize_email($email);
    $members = load_members();
    $updated = false;

    foreach ($members as &$member) {
        if (normalize_email((string) ($member['email'] ?? '')) === $email) {
            $member['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $updated = true;
            break;
        }
    }
    unset($member);

    if (!$updated) {
        return false;
    }

    return save_members($members);
}

function reset_rate_limit_ok(): bool
{
    start_session();
    $last = (int) ($_SESSION['password_reset_last'] ?? 0);
    if (time() - $last < 60) {
        return false;
    }
    $_SESSION['password_reset_last'] = time();
    return true;
}

/** @return array{ok:bool,error?:string,sent?:bool} */
function request_member_password_reset(string $email): array
{
    $email = normalize_email($email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Informe um e-mail válido.'];
    }

    if (!reset_rate_limit_ok()) {
        return ['ok' => false, 'error' => 'Aguarde 1 minuto antes de solicitar novamente.'];
    }

    $cfg = app_config();
    if ($email === normalize_email((string) ($cfg['admin_email'] ?? ''))) {
        return ['ok' => true, 'sent' => false];
    }

    $member = find_member($email);
    if (!$member) {
        return ['ok' => true, 'sent' => false];
    }

    $password = generate_member_password();
    if (!update_member_password($email, $password)) {
        return ['ok' => false, 'error' => 'Não foi possível gerar a nova senha. Tente novamente.'];
    }

    $name = (string) ($member['name'] ?? $email);
    $mail = send_password_reset_email($email, $name, $password);
    if (!$mail['ok']) {
        return ['ok' => false, 'error' => $mail['error'] ?? 'Erro ao enviar o e-mail.'];
    }

    return ['ok' => true, 'sent' => true];
}
