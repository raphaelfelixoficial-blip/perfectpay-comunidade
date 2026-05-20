<?php

declare(strict_types=1);

$comunidadeUrl = 'https://perfectpay.agenciajob.com/comunidade/';
$loginUrl = 'https://perfectpay.agenciajob.com/comunidade/login.php';
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
<title>Obrigado — Comunidade Perfect Pay</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  :root{--br-green:#009739;--br-yellow:#FFDF00;--br-blue:#002776}
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;background:#050505;font-family:'Barlow',sans-serif;color:#f0ede8;display:flex;align-items:center;justify-content:center;padding:1.5rem}
  .card{max-width:560px;width:100%;background:#141414;border:1px solid #2a2a2a;border-radius:12px;padding:2.5rem 2rem;text-align:center}
  .icon{width:72px;height:72px;margin:0 auto 1.25rem;border-radius:50%;background:rgba(0,151,57,.2);border:2px solid var(--br-yellow);display:flex;align-items:center;justify-content:center;font-size:36px;color:var(--br-yellow)}
  h1{font-family:'Bebas Neue',sans-serif;font-size:42px;letter-spacing:2px;line-height:1.1;margin-bottom:.75rem}
  h1 span{color:var(--br-yellow)}
  p{color:#aaa;font-size:15px;line-height:1.7;margin-bottom:1rem}
  .email-box{background:#0a0a0a;border:1px solid #333;border-radius:8px;padding:14px 16px;margin:1.25rem 0;text-align:left}
  .email-box strong{display:block;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#888;margin-bottom:6px}
  .email-box span{color:#fff;font-size:15px;word-break:break-all}
  .steps{text-align:left;margin:1.5rem 0;padding:0;list-style:none}
  .steps li{display:flex;gap:12px;margin-bottom:14px;font-size:14px;color:#ccc;line-height:1.5}
  .steps i{color:var(--br-green);font-size:20px;flex-shrink:0;margin-top:2px}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;margin-top:.5rem;padding:16px 32px;border-radius:6px;background:linear-gradient(135deg,var(--br-yellow),#ffe566);color:var(--br-blue);font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:2px;text-decoration:none;font-weight:700;border:2px solid var(--br-green);transition:transform .2s,box-shadow .2s}
  .btn:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(255,223,0,.35)}
  .btn-secondary{display:inline-block;margin-top:1rem;color:#888;font-size:13px;text-decoration:none}
  .btn-secondary:hover{color:var(--br-yellow)}
  .footer{margin-top:2rem;font-size:12px;color:#555}
  .footer a{color:#888;text-decoration:none}
</style>
</head>
<body>
  <div class="card">
    <div class="icon"><i class="ti ti-circle-check"></i></div>
    <h1>Compra <span>confirmada!</span></h1>
    <p>Parabéns! Sua compra foi aprovada e o acesso à <strong style="color:#fff">Comunidade Perfect Pay</strong> está sendo liberado.</p>

    <?php if ($emailSafe !== ''): ?>
    <div class="email-box">
      <strong>E-mail de acesso</strong>
      <span><?= $emailSafe ?></span>
    </div>
    <?php endif; ?>

    <ul class="steps">
      <li><i class="ti ti-mail"></i><span>Enviamos um e-mail com seu <strong>login e senha</strong> de acesso (verifique também o spam).</span></li>
      <li><i class="ti ti-login"></i><span>Clique no botão abaixo e entre com o e-mail da compra e a senha recebida.</span></li>
      <li><i class="ti ti-books"></i><span>Na comunidade você acessa todos os materiais exclusivos da área VIP.</span></li>
    </ul>

    <a href="<?= htmlspecialchars($comunidadeUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn">
      <i class="ti ti-arrow-right"></i> ACESSAR A COMUNIDADE
    </a>
    <br>
    <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary">Ir direto para o login →</a>

    <p class="footer" style="margin-top:1.5rem">
      Dúvidas? Fale com <a href="mailto:suporte@agenciajob.com">suporte@agenciajob.com</a>
    </p>
  </div>
</body>
</html>
