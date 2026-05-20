<?php

declare(strict_types=1);

$comunidadeUrl = 'https://perfectpay.agenciajob.com/comunidade/';
$email = isset($_GET['email']) ? trim((string) $_GET['email']) : '';
$emailSafe = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)
    ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8')
    : '';

$billetUrl = '';
foreach (['billet_url', 'url', 'link', 'boleto', 'billet'] as $key) {
    if (!empty($_GET[$key])) {
        $candidate = trim((string) $_GET[$key]);
        if (preg_match('#^https?://#i', $candidate)) {
            $billetUrl = $candidate;
            break;
        }
    }
}
$billetUrlSafe = $billetUrl !== '' ? htmlspecialchars($billetUrl, ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Boleto emitido — Perfect Pay</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  :root{--pp-bg:#12100a;--pp-card:#242018;--pp-border:#4a4028;--pp-text:#faf6e8;--pp-muted:#b8a878;--pp-primary:#facc15;--pp-accent:#eab308;--pp-cta:#FFDF00;--pp-radius:16px}
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;background:var(--pp-bg);font-family:'DM Sans',system-ui,sans-serif;color:var(--pp-text);display:flex;align-items:center;justify-content:center;padding:1.5rem}
  .card{max-width:520px;width:100%;background:var(--pp-card);border:1px solid var(--pp-border);border-radius:var(--pp-radius);padding:2.5rem 2rem;text-align:center}
  .icon{width:72px;height:72px;margin:0 auto 1.25rem;border-radius:50%;background:rgba(250,204,21,.15);border:2px solid var(--pp-primary);display:flex;align-items:center;justify-content:center;font-size:36px;color:var(--pp-primary)}
  h1{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;margin-bottom:.75rem}
  h1 span{color:var(--pp-accent)}
  p{color:var(--pp-muted);font-size:15px;line-height:1.7;margin-bottom:1rem}
  .alert{background:rgba(250,204,21,.1);border:1px solid rgba(250,204,21,.35);padding:12px;border-radius:12px;font-size:13px;margin:1rem 0;text-align:left}
  .btn{display:inline-flex;align-items:center;gap:10px;padding:16px 28px;border-radius:12px;background:linear-gradient(135deg,var(--pp-cta),#fde047);color:#1a1400;font-family:'Syne',sans-serif;font-weight:700;text-decoration:none;margin-top:.5rem}
  .btn-ghost{display:inline-block;margin-top:1rem;color:var(--pp-muted);font-size:13px;text-decoration:none}
  .footer{margin-top:1.5rem;font-size:12px;color:var(--pp-muted)}
</style>
</head>
<body>
  <div class="card">
    <div class="icon"><i class="ti ti-file-invoice"></i></div>
    <h1>Boleto <span>emitido</span></h1>
    <p>Seu boleto foi gerado. Pague até o vencimento para liberar o acesso à comunidade.</p>
    <?php if ($emailSafe !== ''): ?>
    <div class="info-box" style="background:#10162e;border:1px solid var(--pp-border);border-radius:12px;padding:14px;margin:1rem 0;text-align:left">
      <strong style="font-size:11px;text-transform:uppercase;color:var(--pp-muted)">E-mail da compra</strong><br><?= $emailSafe ?>
    </div>
    <?php endif; ?>
    <div class="alert">Após a confirmação do pagamento você receberá o e-mail com <strong>login e senha</strong> de acesso.</div>
    <?php if ($billetUrlSafe !== ''): ?>
    <a href="<?= $billetUrlSafe ?>" class="btn" target="_blank" rel="noopener"><i class="ti ti-external-link"></i> Abrir boleto</a>
    <?php endif; ?>
    <br><a href="<?= htmlspecialchars($comunidadeUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-ghost">Sobre a comunidade →</a>
    <p class="footer">Dúvidas? <a href="mailto:suporte@agenciajob.com" style="color:var(--pp-primary)">suporte@agenciajob.com</a></p>
  </div>
</body>
</html>
