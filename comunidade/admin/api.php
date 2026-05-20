<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/albuns.php';
require_once dirname(__DIR__) . '/includes/site-status.php';
require_once dirname(__DIR__) . '/includes/mail.php';
require_once dirname(__DIR__) . '/includes/members.php';
require_once dirname(__DIR__) . '/includes/asaas.php';
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
    $homeVideoUrl = (string) ($_POST['home_video_url'] ?? '');
    $offer = site_status_offer_from_post($_POST);
    $result = site_status_save($homeLayout, $homeTitle, $homeMessage, $membersEnabled, $homeVideoUrl, $offer);
    if ($result['ok']) {
        $layoutLabel = $homeLayout === 'full' ? 'Landing completa' : 'Home simples';
        $priceLabel = site_format_price_brl((float) $offer['checkout_price']);
        redirect_flash($layoutLabel . ' ativa. Preço do checkout: R$ ' . $priceLabel . ($membersEnabled ? '' : ' Área de membros desativada.'));
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
        if ($action === 'import' && find_member($email)) {
            $skipped++;
            continue;
        }

        $memberName = $useSharedName && $name !== '' ? $name : $email;

        if ($sharedPassword !== '') {
            if (!find_member($email)) {
                $members = load_members();
                $members[] = [
                    'email' => $email,
                    'name' => $memberName,
                    'password_hash' => password_hash($sharedPassword, PASSWORD_DEFAULT),
                    'added_at' => member_added_at_now(),
                ];
                save_members($members);
            } else {
                update_member_password($email, $sharedPassword);
            }
            $added++;
            if ($sendEmail) {
                $mail = send_member_credentials_email($email, $memberName, $sharedPassword);
                if ($mail['ok']) {
                    $emailed++;
                } else {
                    $mailFailed++;
                }
            } else {
                $passwords[] = "{$email} => {$sharedPassword}";
            }
            continue;
        }

        $provision = provision_member_access($email, $memberName, $sendEmail);
        if (!$provision['ok']) {
            $mailFailed++;
            continue;
        }
        $added++;
        if ($provision['email_sent']) {
            $emailed++;
        } elseif ($sendEmail) {
            $mailFailed++;
        } elseif (isset($provision['password'])) {
            $passwords[] = "{$email} => {$provision['password']}";
        }
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
        redirect_flash('Informe a senha SMTP da caixa suporte@agenciajob.com (cPanel do servidor).');
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
        redirect_flash('Configure a senha SMTP da caixa suporte@ no cPanel e salve no admin antes de testar.');
    }
    $auth = smtp_test_auth();
    if (!$auth['ok']) {
        redirect_flash('SMTP não autenticou: ' . ($auth['error'] ?? 'erro') . ' Corrija a senha e tente de novo.');
    }
    $result = send_member_credentials_email($testTo, 'Teste Figurinhas da Copa', 'senha-teste-123');
    if ($result['ok']) {
        redirect_flash("E-mail de teste enviado por SMTP para {$testTo}. Verifique entrada e spam.");
    }
    redirect_flash('Falha no envio: ' . ($result['error'] ?? 'erro desconhecido'));
}

if ($action === 'save_asaas') {
    $apiKey = (string) ($_POST['asaas_api_key'] ?? '');
    $webhookToken = (string) ($_POST['asaas_webhook_token'] ?? '');
    $value = site_checkout_price();
    $environment = (string) ($_POST['asaas_environment'] ?? 'production');
    if ($apiKey === '' && !asaas_is_configured()) {
        redirect_flash('Informe a chave API do Asaas (Integrações → API).');
    }
    if (update_asaas_settings($apiKey, $webhookToken, $value > 0 ? $value : 97, $environment)) {
        redirect_flash('Asaas configurado. Cadastre o webhook no painel Asaas e teste uma compra.');
    }
    redirect_flash('Não foi possível salvar configuração Asaas.');
}

if ($action === 'reconcile_asaas') {
    if (!asaas_is_configured()) {
        redirect_flash('Configure a API Asaas antes de sincronizar pagamentos.');
    }
    $sendEmail = isset($_POST['send_email']) && $_POST['send_email'] === '1';
    $result = asaas_reconcile_recent_payments(30, $sendEmail);
    $msg = "Sincronização: {$result['processed']} membro(s) cadastrado(s), {$result['skipped']} já processado(s).";
    if (!$sendEmail) {
        $msg .= ' Nenhum e-mail foi enviado (padrão).';
    }
    if ($result['details'] !== []) {
        $msg .= ' ' . implode(' | ', array_slice($result['details'], 0, 5));
    }
    if ($result['errors'] !== []) {
        $msg .= ' Avisos: ' . implode(' | ', array_slice($result['errors'], 0, 3));
    }
    redirect_flash($msg);
}

if ($action === 'register_asaas_webhook') {
    if (!asaas_is_configured()) {
        redirect_flash('Configure a API Asaas antes de registrar o webhook.');
    }
    $token = trim((string) (app_config()['asaas_webhook_token'] ?? ''));
    if ($token === '') {
        redirect_flash('Defina o token do webhook no campo acima e salve antes de registrar no Asaas.');
    }
    $result = asaas_register_webhook_in_panel();
    redirect_flash($result['message']);
}

if ($action === 'simulate_asaas_webhook') {
    $testEmail = normalize_email((string) ($_POST['test_email'] ?? ''));
    if ($testEmail === '' || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        redirect_flash('Informe um e-mail válido para simular o pagamento.');
    }
    $name = trim((string) ($_POST['test_name'] ?? ''));
    $sendEmail = isset($_POST['send_email']) && $_POST['send_email'] === '1';
    $payload = asaas_build_test_payload_with_email($testEmail, $name);
    $cfg = app_config();
    $token = trim((string) ($cfg['asaas_webhook_token'] ?? ''));
    if ($token !== '') {
        $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] = $token;
    }
    if (!$sendEmail) {
        $result = asaas_provision_from_payment_entity(
            ['id' => 'chk_SIM_' . date('YmdHis'), 'customerData' => ['email' => $testEmail, 'name' => $name !== '' ? $name : 'Teste']],
            'simulate',
            false
        );
    } else {
        $result = asaas_handle_webhook($payload);
    }
    if ($result['ok']) {
        redirect_flash('Simulação Asaas OK: ' . $result['message'] . " ({$testEmail}).");
    }
    redirect_flash('Simulação Asaas falhou: ' . $result['message']);
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
