<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mail.php';

/**
 * Cadastra ou atualiza membro e envia e-mail com acesso.
 *
 * @return array{ok:bool,created:bool,email_sent:bool,password?:string,error?:string}
 */
function provision_member_access(string $email, string $name = '', bool $sendEmail = true): array
{
    $email = normalize_email($email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'created' => false, 'email_sent' => false, 'error' => 'E-mail inválido.'];
    }

    $members = load_members();
    $existing = find_member($email);
    $password = generate_member_password();
    $memberName = trim($name) !== '' ? trim($name) : $email;
    $created = $existing === null;

    if ($existing) {
        foreach ($members as &$member) {
            if (normalize_email((string) ($member['email'] ?? '')) === $email) {
                $member['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                if ($memberName !== $email) {
                    $member['name'] = $memberName;
                }
                break;
            }
        }
        unset($member);
    } else {
        $members[] = [
            'email' => $email,
            'name' => $memberName,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'added_at' => member_added_at_now(),
        ];
    }

    if (!save_members($members)) {
        return ['ok' => false, 'created' => $created, 'email_sent' => false, 'error' => 'Não foi possível salvar o membro.'];
    }

    $emailSent = false;
    if ($sendEmail) {
        $mail = send_member_credentials_email($email, $memberName, $password);
        $emailSent = $mail['ok'];
        if (!$mail['ok']) {
            return [
                'ok' => true,
                'created' => $created,
                'email_sent' => false,
                'password' => $password,
                'error' => $mail['error'] ?? 'Membro salvo, mas o e-mail não foi enviado.',
            ];
        }
    }

    return [
        'ok' => true,
        'created' => $created,
        'email_sent' => $emailSent,
        'password' => $password,
    ];
}
