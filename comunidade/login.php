<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/theme.php';

start_session();
if (is_member()) {
    header('Location: ' . comunidade_url(is_admin() ? '/admin/' : '/'));
    exit;
}

$adminLogin = isset($_GET['admin']);
if (!members_area_enabled() && !$adminLogin) {
    render_members_area_closed_page();
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $result = login_user($email, $password);
    if ($result['ok']) {
        persist_session($result['user']);
        header('Location: ' . comunidade_url(($result['user']['role'] ?? '') === 'admin' ? '/admin/' : '/'));
        exit;
    }
    $error = $result['error'] ?? 'Não foi possível entrar.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Login — Figurinhas da Copa</title>
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
      <div class="pp-badge"><i class="ti ti-lock"></i> Área de membros</div>
      <h1 class="pp-head">Figurinhas da Copa <em>VIP</em></h1>
      <p class="pp-hint">Acesse com o e-mail autorizado e a senha enviada após a compra.</p>
      <?php if ($error): ?><div class="pp-err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <label class="pp-label" for="email">E-mail</label>
      <input class="pp-input" type="email" id="email" name="email" required autocomplete="email" value="<?= htmlspecialchars((string)($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <label class="pp-label" for="password">Senha</label>
      <input class="pp-input" type="password" id="password" name="password" required autocomplete="current-password">
      <button type="submit" class="pp-btn">Entrar na comunidade</button>
      <a href="<?= htmlspecialchars(comunidade_url('/reset-senha.php'), ENT_QUOTES, 'UTF-8') ?>" class="pp-btn pp-btn-ghost" style="margin-top:.75rem">Esqueci minha senha</a>
    </form>
  </div>
  <?php render_pp_footer(); ?>
</body>
</html>
