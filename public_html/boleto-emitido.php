<?php

declare(strict_types=1);

$comunidadeUrl = 'https://perfectpay.agenciajob.com/comunidade/';
$email = isset($_GET['email']) ? trim((string) $_GET['email']) : '';
$emailSafe = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)
    ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8')
    : '';

/** URL do boleto (Perfect Pay ou parâmetros comuns na query string). */
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

$billetNumber = '';
foreach (['billet_number', 'linha_digitavel', 'codigo'] as $key) {
    if (!empty($_GET[$key])) {
        $billetNumber = preg_replace('/\s+/', '', trim((string) $_GET[$key]));
        break;
    }
}

$expiration = trim((string) ($_GET['billet_expiration'] ?? $_GET['vencimento'] ?? ''));
$expirationSafe = $expiration !== ''
    ? htmlspecialchars($expiration, ENT_QUOTES, 'UTF-8')
    : '';

$billetUrlSafe = $billetUrl !== ''
    ? htmlspecialchars($billetUrl, ENT_QUOTES, 'UTF-8')
    : '';

$billetNumberSafe = $billetNumber !== ''
    ? htmlspecialchars($billetNumber, ENT_QUOTES, 'UTF-8')
    : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Boleto emitido — Comunidade Perfect Pay</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  :root{--br-green:#009739;--br-yellow:#FFDF00;--br-blue:#002776}
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;background:#050505;font-family:'Barlow',sans-serif;color:#f0ede8;display:flex;align-items:center;justify-content:center;padding:1.5rem}
  .card{max-width:560px;width:100%;background:#141414;border:1px solid #2a2a2a;border-radius:12px;padding:2.5rem 2rem;text-align:center}
  .icon{width:72px;height:72px;margin:0 auto 1.25rem;border-radius:50%;background:rgba(0,39,118,.35);border:2px solid var(--br-green);display:flex;align-items:center;justify-content:center;font-size:36px;color:var(--br-green)}
  h1{font-family:'Bebas Neue',sans-serif;font-size:40px;letter-spacing:2px;line-height:1.1;margin-bottom:.75rem}
  h1 span{color:var(--br-yellow)}
  p{color:#aaa;font-size:15px;line-height:1.7;margin-bottom:1rem}
  .info-box{background:#0a0a0a;border:1px solid #333;border-radius:8px;padding:14px 16px;margin:1rem 0;text-align:left}
  .info-box strong{display:block;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#888;margin-bottom:6px}
  .info-box span,.info-box code{color:#fff;font-size:14px;word-break:break-all}
  .info-box code{display:block;font-family:monospace;font-size:13px;line-height:1.5;margin-top:4px}
  .copy-btn{margin-top:8px;padding:8px 14px;font-size:12px;border:1px solid #444;background:#1a1a1a;color:#ccc;border-radius:4px;cursor:pointer}
  .copy-btn:hover{border-color:var(--br-green);color:var(--br-yellow)}
  .steps{text-align:left;margin:1.5rem 0;padding:0;list-style:none}
  .steps li{display:flex;gap:12px;margin-bottom:14px;font-size:14px;color:#ccc;line-height:1.5}
  .steps i{color:var(--br-yellow);font-size:20px;flex-shrink:0;margin-top:2px}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;margin-top:.5rem;padding:16px 32px;border-radius:6px;background:linear-gradient(135deg,var(--br-green),#007a2f);color:#fff;font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:2px;text-decoration:none;font-weight:700;border:2px solid var(--br-yellow);transition:transform .2s,box-shadow .2s}
  .btn:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,151,57,.4)}
  .btn-outline{background:transparent;color:#888;border-color:#444;font-size:16px;padding:12px 24px;margin-top:1rem}
  .btn-outline:hover{color:var(--br-yellow);border-color:var(--br-yellow)}
  .alert{background:rgba(255,223,0,.08);border:1px solid rgba(255,223,0,.35);color:#ddd;font-size:13px;padding:12px;border-radius:6px;margin:1rem 0;text-align:left;line-height:1.5}
  .footer{margin-top:1.5rem;font-size:12px;color:#555}
  .footer a{color:#888;text-decoration:none}
</style>
</head>
<body>
  <div class="card">
    <div class="icon"><i class="ti ti-file-invoice"></i></div>
    <h1>Boleto <span>emitido!</span></h1>
    <p>Seu boleto foi gerado com sucesso. <strong style="color:#fff">Pague até o vencimento</strong> para liberar o acesso à Comunidade Perfect Pay.</p>

    <?php if ($emailSafe !== ''): ?>
    <div class="info-box">
      <strong>E-mail da compra</strong>
      <span><?= $emailSafe ?></span>
    </div>
    <?php endif; ?>

    <?php if ($expirationSafe !== ''): ?>
    <div class="info-box">
      <strong>Vencimento</strong>
      <span><?= $expirationSafe ?></span>
    </div>
    <?php endif; ?>

    <?php if ($billetNumberSafe !== ''): ?>
    <div class="info-box">
      <strong>Linha digitável</strong>
      <code id="linha"><?= $billetNumberSafe ?></code>
      <button type="button" class="copy-btn" onclick="copiarLinha()">Copiar código</button>
    </div>
    <?php endif; ?>

    <div class="alert">
      <i class="ti ti-info-circle"></i>
      O acesso à comunidade e o e-mail com <strong>login e senha</strong> são enviados automaticamente após a <strong>confirmação do pagamento</strong> (em geral 1 a 3 dias úteis).
    </div>

    <ul class="steps">
      <li><i class="ti ti-printer"></i><span>Abra ou imprima o boleto e pague no banco, lotérica ou app do seu banco.</span></li>
      <li><i class="ti ti-clock"></i><span>Aguarde a compensação do pagamento.</span></li>
      <li><i class="ti ti-mail"></i><span>Você receberá um e-mail com o link de acesso e sua senha quando o pagamento for aprovado.</span></li>
      <li><i class="ti ti-login"></i><span>Depois entre em <strong>perfectpay.agenciajob.com/comunidade</strong> com seus dados.</span></li>
    </ul>

    <?php if ($billetUrlSafe !== ''): ?>
    <a href="<?= $billetUrlSafe ?>" class="btn" target="_blank" rel="noopener noreferrer">
      <i class="ti ti-external-link"></i> ABRIR BOLETO PARA PAGAR
    </a>
    <?php else: ?>
    <p style="font-size:13px;color:#888">O link do boleto também foi exibido na tela anterior da Perfect Pay. Guarde ou imprima para pagar.</p>
    <?php endif; ?>

    <br>
    <a href="<?= htmlspecialchars($comunidadeUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline">Sobre a área de membros →</a>

    <p class="footer">
      Dúvidas? <a href="mailto:suporte@agenciajob.com">suporte@agenciajob.com</a>
    </p>
  </div>
  <?php if ($billetNumberSafe !== ''): ?>
  <script>
  function copiarLinha() {
    var t = document.getElementById('linha').innerText;
    navigator.clipboard.writeText(t).then(function() {
      alert('Linha digitável copiada!');
    }).catch(function() {
      prompt('Copie a linha digitável:', t);
    });
  }
  </script>
  <?php endif; ?>
</body>
</html>
