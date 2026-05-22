<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mail.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/theme.php';

start_session();
if (is_member()) {
    header('Location: ' . comunidade_url(is_admin() ? '/admin/' : '/'));
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = (string) ($_POST['email'] ?? '');
    $result = request_member_password_reset($email);
    if (!$result['ok']) {
        $error = $result['error'] ?? 'Não foi possível processar o pedido.';
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Redefinir senha — Figurinhas da Copa</title>
<?php render_favicon(); ?>
<?php render_google_analytics(); ?>
<?= pp_fonts_link() ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
<?= pp_app_shell_styles() ?>
<?= pp_nav_styles() ?>
body{display:flex;flex-direction:column;padding:1rem 0}
.login-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:0 1.5rem}
</style>
</head>
<body>
  <?php render_page_nav('login'); ?>
  <div class="login-wrap">
    <form class="pp-card" method="post" action="" style="width:100%;max-width:420px">
      <div class="pp-badge"><i class="ti ti-key"></i> Recuperar acesso</div>
      <h1 class="pp-head">Redefinir <em>senha</em></h1>
      <p class="pp-hint">Informe o e-mail da compra. Se estiver cadastrado, enviaremos uma nova senha.</p>
      <?php if ($error): ?><div class="pp-err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($success): ?>
      <div class="pp-flash"><strong>Pedido recebido.</strong> Se o e-mail estiver cadastrado, você receberá a nova senha em alguns minutos. Verifique o spam.</div>
      <?php else: ?>
      <label class="pp-label" for="email">E-mail cadastrado</label>
      <input class="pp-input" type="email" id="email" name="email" required value="<?= htmlspecialchars((string)($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="pp-btn">Enviar nova senha</button>
      <?php endif; ?>
      <a href="<?= htmlspecialchars(comunidade_url('/login.php'), ENT_QUOTES, 'UTF-8') ?>" class="pp-btn pp-btn-ghost" style="margin-top:.75rem">Voltar ao login</a>
    </form>
  </div>
  <?php render_pp_footer(); ?>
</body>
</html>
