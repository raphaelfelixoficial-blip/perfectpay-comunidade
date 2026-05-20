<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mail.php';
require_once __DIR__ . '/includes/nav.php';

start_session();
if (is_member()) {
    header('Location: ' . (is_admin() ? '/admin/' : '/'));
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
<title>Redefinir senha — Comunidade Perfect Pay</title>
<?php render_favicon(); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  :root{--br-green:#009739;--br-yellow:#FFDF00;--br-blue:#002776}
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;background:#050505;font-family:'Barlow',sans-serif;color:#f0ede8;display:flex;flex-direction:column;padding:1rem 0 1.5rem}
  .login-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:0 1.5rem;width:100%}
  <?= page_nav_styles() ?>
  .card{width:100%;max-width:420px;background:#141414;border:1px solid #2a2a2a;border-radius:10px;padding:2rem}
  .badge{display:inline-flex;align-items:center;gap:8px;background:rgba(0,151,57,.2);border:1px solid rgba(255,223,0,.5);color:var(--br-yellow);font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;padding:6px 14px;border-radius:2px;margin-bottom:1rem}
  h1{font-family:'Bebas Neue',sans-serif;font-size:36px;letter-spacing:2px;line-height:1;margin-bottom:.5rem}
  h1 .gold{color:var(--br-yellow)}
  p{color:#999;font-size:14px;margin-bottom:1.5rem;line-height:1.6}
  label{display:block;font-size:12px;font-weight:600;color:#aaa;margin-bottom:6px;text-transform:uppercase;letter-spacing:1px}
  input{width:100%;padding:14px 16px;border-radius:6px;border:1px solid #333;background:#0a0a0a;color:#fff;font-size:15px;margin-bottom:1rem}
  input:focus{outline:none;border-color:var(--br-green)}
  .btn{width:100%;padding:16px;border:none;border-radius:6px;background:linear-gradient(135deg,var(--br-yellow),#ffe566);color:var(--br-blue);font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:2px;cursor:pointer;font-weight:700}
  .btn:hover{filter:brightness(1.05)}
  .err{background:rgba(220,53,69,.15);border:1px solid #dc3545;color:#ff8a8a;padding:12px;border-radius:6px;font-size:13px;margin-bottom:1rem;line-height:1.5}
  .ok{background:rgba(0,151,57,.15);border:1px solid var(--br-green);color:#b8e6c8;padding:12px;border-radius:6px;font-size:13px;margin-bottom:1rem;line-height:1.6}
  .back{display:block;text-align:center;margin-top:1.25rem;color:#888;font-size:13px;text-decoration:none}
  .back:hover{color:var(--br-yellow)}
  .back i{vertical-align:-2px;margin-right:4px}
</style>
</head>
<body>
  <?php render_page_nav('login'); ?>
  <div class="login-wrap">
  <form class="card" method="post" action="">
    <div class="badge"><i class="ti ti-key"></i> Recuperar acesso</div>
    <h1>REDEFINIR <span class="gold">SENHA</span></h1>
    <p>Informe o e-mail cadastrado na compra. Se estiver autorizado, enviaremos uma <strong style="color:#ccc">nova senha</strong> para este endereço.</p>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($success): ?>
    <div class="ok">
      <strong style="color:var(--br-yellow)">Pedido recebido.</strong><br>
      Se o e-mail estiver cadastrado, você receberá a nova senha em alguns minutos. Verifique também a pasta de spam.
    </div>
    <?php else: ?>
    <label for="email">E-mail cadastrado</label>
    <input type="email" id="email" name="email" required autocomplete="email" value="<?= htmlspecialchars((string)($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn">ENVIAR NOVA SENHA</button>
    <?php endif; ?>
    <a href="/login.php" class="back"><i class="ti ti-arrow-left"></i> Voltar ao login</a>
  </form>
  </div>
  <?php render_agency_footer(); ?>
</body>
</html>
