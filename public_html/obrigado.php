<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/site-bootstrap.php';

$comunidadeUrl = site_url('/comunidade/');
$loginUrl = site_url('/comunidade/login.php');
$email = isset($_GET['email']) ? trim((string) $_GET['email']) : '';
$emailSafe = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)
    ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8')
    : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Obrigado — Figurinhas da Copa</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  :root{--pp-bg:#12100a;--pp-card:#242018;--pp-border:#4a4028;--pp-text:#faf6e8;--pp-muted:#b8a878;--pp-primary:#facc15;--pp-accent:#eab308;--pp-cta:#FFDF00;--pp-radius:16px}
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;background:var(--pp-bg);background-image:radial-gradient(ellipse 70% 50% at 50% -10%,rgba(250,204,21,.18),transparent);font-family:'DM Sans',system-ui,sans-serif;color:var(--pp-text);display:flex;align-items:center;justify-content:center;padding:1.5rem}
  .card{max-width:520px;width:100%;background:var(--pp-card);border:1px solid var(--pp-border);border-radius:var(--pp-radius);padding:2.5rem 2rem;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,.35)}
  .icon{width:72px;height:72px;margin:0 auto 1.25rem;border-radius:50%;background:rgba(250,204,21,.15);border:2px solid var(--pp-accent);display:flex;align-items:center;justify-content:center;font-size:36px;color:var(--pp-accent)}
  h1{font-family:'Syne',sans-serif;font-size:2.25rem;font-weight:800;margin-bottom:.75rem}
  h1 span{color:var(--pp-primary)}
  p{color:var(--pp-muted);font-size:15px;line-height:1.7;margin-bottom:1rem}
  .info-box{background:#1c1810;border:1px solid var(--pp-border);border-radius:12px;padding:14px;margin:1rem 0;text-align:left}
  .info-box strong{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.1em;color:var(--pp-muted);margin-bottom:6px}
  .steps{text-align:left;margin:1.5rem 0;list-style:none}
  .steps li{display:flex;gap:12px;margin-bottom:12px;font-size:14px;color:#c5c9e0}
  .steps i{color:var(--pp-accent);margin-top:2px}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:16px 28px;border-radius:12px;background:linear-gradient(135deg,var(--pp-cta),#fde047);color:#1a1400;font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:700;text-decoration:none;margin-top:.5rem;box-shadow:0 10px 32px rgba(250,204,21,.4)}
  .btn-ghost{display:inline-block;margin-top:1rem;color:var(--pp-muted);font-size:13px;text-decoration:none}
  .btn-ghost:hover{color:var(--pp-primary)}
  .footer{margin-top:1.5rem;font-size:12px;color:var(--pp-muted)}
</style>
</head>
<body>
  <div class="card">
    <div class="icon"><i class="ti ti-circle-check"></i></div>
    <h1>Compra <span>confirmada</span></h1>
    <p>Parabéns! Sua compra foi aprovada e o acesso à <strong style="color:var(--pp-text)">Comunidade Figurinhas da Copa</strong> está sendo liberado.</p>
    <?php if ($emailSafe !== ''): ?>
    <div class="info-box"><strong>E-mail de acesso</strong><span><?= $emailSafe ?></span></div>
    <?php endif; ?>
    <ul class="steps">
      <li><i class="ti ti-mail"></i><span>Enviamos um e-mail com seu <strong>login e senha</strong> (verifique o spam).</span></li>
      <li><i class="ti ti-login"></i><span>Entre na comunidade com o e-mail da compra.</span></li>
      <li><i class="ti ti-books"></i><span>Acesse todos os PDFs e materiais exclusivos.</span></li>
    </ul>
    <a href="<?= htmlspecialchars($comunidadeUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn"><i class="ti ti-arrow-right"></i> Acessar comunidade</a>
    <br><a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-ghost">Ir para o login →</a>
    <p class="footer">Dúvidas? <a href="mailto:suporte@agenciajob.com" style="color:var(--pp-primary)">suporte@agenciajob.com</a></p>
  </div>
</body>
</html>
