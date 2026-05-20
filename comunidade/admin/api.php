<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/albuns.php';
require_once dirname(__DIR__) . '/includes/site-status.php';
require_once dirname(__DIR__) . '/includes/mail.php';
require_admin();
start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . comunidade_url('/admin/'));
    exit;
}

$action = (string) ($_POST['action'] ?? '');
$members = load_members();
$sendEmail = should_send_member_email();

function redirect_flash(string $message): void
{
    $_SESSION['flash'] = $message;
    header('Location: ' . comunidade_url('/admin/'));
    exit;
}

if ($action === 'save_site_status') {
    $homeLayout = (string) ($_POST['home_layout'] ?? 'simple');
    $homeTitle = (string) ($_POST['home_title'] ?? '');
    $homeMessage = (string) ($_POST['home_message'] ?? '');
    $membersEnabled = isset($_POST['members_enabled']);
    $result = site_status_save($homeLayout, $homeTitle, $homeMessage, $membersEnabled);
    if ($result['ok']) {
        $layoutLabel = $homeLayout === 'full' ? 'Landing completa' : 'Home simples';
        redirect_flash($layoutLabel . ' ativa.' . ($membersEnabled ? '' : ' Área de membros desativada.'));
    }
    redirect_flash((string) ($result['error'] ?? 'Não foi possível salvar.'));
}

if ($action === 'refresh_albuns') {
    $result = albuns_process_refresh_request();
    if ($result['ok']) {
        redirect_flash('Lista de PDFs atualizada (' . (int) $result['total'] . ' arquivo(s)).');
    }
    redirect_flash((string) ($result['error'] ?? 'Não foi possível atualizar a lista.'));
}

if ($action === 'add' || $action === 'import') {
    $raw = $action === 'import'
        ? (string) ($_POST['emails'] ?? '')
        : (string) ($_POST['email'] ?? '');
    $emailList = parse_email_list($raw);

    if ($emailList === []) {
        redirect_flash('Nenhum e-mail válido informado.');
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $sharedPassword = trim((string) ($_POST['password'] ?? ''));
    $added = 0;
    $emailed = 0;
    $mailFailed = 0;
    $skipped = 0;
    $passwords = [];
    $useSharedName = $action === 'add' && count($emailList) === 1;

    foreach ($emailList as $email) {
        if (find_member($email)) {
            $skipped++;
            continue;
        }

        $password = $sharedPassword !== '' ? $sharedPassword : generate_member_password();
        $memberName = $useSharedName && $name !== '' ? $name : $email;

        $members[] = [
            'email' => $email,
            'name' => $memberName,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'added_at' => member_added_at_now(),
        ];
        $passwords[] = "{$email} => {$password}";
        $added++;

        if ($sendEmail) {
            $mail = send_member_credentials_email($email, $memberName, $password);
            if ($mail['ok']) {
                $emailed++;
            } else {
                $mailFailed++;
            }
        }
    }

    if ($added > 0) {
        save_members($members);
    }

    $msg = $action === 'import'
        ? "Importados {$added} e-mail(s)."
        : ($added === 1 ? "Membro adicionado: {$emailList[0]}" : "Adicionados {$added} e-mail(s).");

    if ($skipped > 0) {
        $msg .= " Ignorados (já cadastrados): {$skipped}.";
    }

    if ($sendEmail && $added > 0) {
        $msg .= " Enviados: {$emailed}.";
        if ($mailFailed > 0) {
            $msg .= " Falhas no envio: {$mailFailed} (veja data/mail.log).";
        }
    } elseif (!$sendEmail && $passwords) {
        $detail = implode(' | ', array_slice($passwords, 0, 3));
        if (count($passwords) > 3) {
            $detail .= ' ...';
        }
        $msg .= ' Senhas: ' . $detail;
    }

    redirect_flash($msg);
}

if ($action === 'remove') {
    $email = normalize_email((string) ($_POST['email'] ?? ''));
    $members = array_values(array_filter($members, static function ($m) use ($email) {
        return normalize_email((string) ($m['email'] ?? '')) !== $email;
    }));
    save_members($members);
    redirect_flash('Membro removido.');
}

if ($action === 'save_smtp') {
    $smtpPass = (string) ($_POST['smtp_password'] ?? '');
    if ($smtpPass === '') {
        redirect_flash('Informe a senha SMTP da caixa noreply@agenciajob.com (cPanel do servidor).');
    }
    if (update_smtp_password($smtpPass)) {
        redirect_flash('SMTP do servidor configurado. Use "Enviar e-mail de teste" para confirmar.');
    }
    redirect_flash('Não foi possível salvar a senha SMTP.');
}

if ($action === 'test_email') {
    $testTo = normalize_email((string) ($_POST['test_email'] ?? ''));
    if ($testTo === '' || !filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
        redirect_flash('E-mail de teste inválido.');
    }
    if (!smtp_is_configured()) {
        redirect_flash('Configure a senha SMTP da caixa noreply@ antes de testar.');
    }
    $result = send_member_credentials_email($testTo, 'Teste Perfect Pay', 'senha-teste-123');
    if ($result['ok']) {
        redirect_flash("E-mail de teste enviado para {$testTo}. Verifique a caixa de entrada e o spam.");
    }
    redirect_flash('Falha no teste: ' . $result['error']);
}

if ($action === 'change_admin_password') {
    $newPassword = (string) ($_POST['new_password'] ?? '');
    if (strlen($newPassword) < 8) {
        redirect_flash('A senha deve ter pelo menos 8 caracteres.');
    }
    if (update_admin_password($newPassword)) {
        redirect_flash('Senha do administrador atualizada.');
    }
    redirect_flash('Não foi possível atualizar a senha.');
}

redirect_flash('Ação desconhecida.');
