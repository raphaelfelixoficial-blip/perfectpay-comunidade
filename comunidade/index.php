<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/albuns.php';
require_once __DIR__ . '/includes/downloads.php';
require_member();
start_session();
$user = session_user();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$albunsCatalog = albuns_get_catalog();
$albunsCount = array_sum(array_map('count', $albunsCatalog));
$featuredDownloads = load_downloads($albunsCatalog);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Acesso VIP — Comunidade Perfect Pay Copa 2026</title>
<?php render_favicon(); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  :root{--br-green:#009739;--br-yellow:#FFDF00;--br-blue:#002776;--br-yellow-dim:#c9b800}
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;background:#050505;font-family:'Barlow',sans-serif;color:#f0ede8;line-height:1.6}
  .page{max-width:720px;margin:0 auto;padding:2.5rem 1.25rem 4rem}
  .user-bar{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1.5rem;font-size:13px;color:#888}
  .user-bar a{color:var(--br-yellow);text-decoration:none}
  .top-bar{text-align:center;margin-bottom:2rem}
  .badge{display:inline-flex;align-items:center;gap:8px;background:rgba(0,151,57,.2);border:1px solid rgba(255,223,0,.55);color:var(--br-yellow);font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;padding:8px 18px;border-radius:2px;margin-bottom:1.25rem}
  h1{font-family:'Bebas Neue',sans-serif;font-size:clamp(40px,9vw,64px);letter-spacing:3px;line-height:1;color:#fff;margin-bottom:.5rem}
  h1 .gold{color:var(--br-yellow)} h1 .green{color:var(--br-green)}
  .lead{color:#bbb;font-size:16px;max-width:520px;margin:0 auto 2rem;text-align:center}
  .success-box{background:linear-gradient(135deg,rgba(0,151,57,.15),rgba(0,39,118,.12));border:1px solid rgba(0,151,57,.5);border-radius:8px;padding:1.25rem 1.5rem;text-align:center;margin-bottom:2rem}
  .success-box strong{display:block;font-family:'Bebas Neue',sans-serif;font-size:26px;letter-spacing:2px;color:var(--br-yellow);margin-bottom:6px}
  .section-label{font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:2px;color:#fff;margin:2rem 0 1rem;padding-bottom:.5rem;border-bottom:1px solid #222}
  .section-label span{color:var(--br-yellow)}
  .btn{display:flex;align-items:center;justify-content:center;gap:12px;width:100%;padding:18px 24px;border-radius:6px;font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:2px;text-decoration:none;transition:all .25s;border:none;cursor:pointer;font-weight:700}
  .btn-whatsapp{background:linear-gradient(135deg,#25D366,#128C7E);color:#fff;box-shadow:0 8px 32px rgba(37,211,102,.25);margin-bottom:.5rem}
  .btn-whatsapp:hover{transform:translateY(-2px)}
  .btn-whatsapp small{display:block;font-family:'Barlow',sans-serif;font-size:12px;letter-spacing:0;font-weight:500;opacity:.9;margin-top:2px}
  .downloads{display:flex;flex-direction:column;gap:12px}
  .dl-card{display:flex;align-items:center;gap:16px;background:#141414;border:1px solid #2a2a2a;border-radius:8px;padding:16px 18px;text-decoration:none;color:inherit;transition:all .25s}
  .dl-card:hover{border-color:var(--br-green);background:#1a1a1a;transform:translateX(4px)}
  .dl-icon{flex-shrink:0;width:48px;height:48px;display:flex;align-items:center;justify-content:center;background:rgba(0,151,57,.15);border:1px solid rgba(255,223,0,.35);border-radius:8px;color:var(--br-yellow);font-size:24px}
  .dl-info strong{display:block;font-size:16px;color:#fff;font-weight:700;margin-bottom:2px}
  .dl-info span{font-size:13px;color:#888}
  .dl-arrow{color:var(--br-yellow);font-size:22px}
  .note{margin-top:2rem;padding:1rem 1.25rem;background:#0d0d0d;border:1px solid #222;border-radius:6px;font-size:13px;color:#888;text-align:center}
  .footer{margin-top:2.5rem;text-align:center;font-size:12px;color:#666}
  .btn-albuns{
    background:linear-gradient(135deg,var(--br-green),#007a2f);
    color:#fff;
    box-shadow:0 10px 40px rgba(0,151,57,.35);
    margin-bottom:1rem;
    padding:22px 28px;
  }
  .btn-albuns:hover{transform:translateY(-2px);box-shadow:0 14px 48px rgba(0,151,57,.45)}
  .btn-albuns small{display:block;font-family:'Barlow',sans-serif;font-size:12px;letter-spacing:0;font-weight:500;opacity:.9;margin-top:2px}
  .btn-instagram{
    background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045);
    color:#fff;
    box-shadow:0 8px 32px rgba(131,58,180,.3);
    margin-bottom:.75rem;
    padding:18px 20px;
    font-size:clamp(16px,4vw,20px);
    line-height:1.2;
    text-align:center;
  }
  .btn-instagram:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(131,58,180,.45)}
  .btn-instagram small{display:block;font-family:'Barlow',sans-serif;font-size:11px;letter-spacing:0;font-weight:500;opacity:.95;margin-top:4px}
  .btn-instagram-arte{
    background:linear-gradient(135deg,#009739,#002776);
    box-shadow:0 8px 32px rgba(0,151,57,.3);
  }
  .btn-instagram-arte:hover{box-shadow:0 12px 40px rgba(0,151,57,.45)}
  <?= page_nav_styles() ?>
  <?= albuns_refresh_button_styles() ?>
</style>
</head>
<body>
<?php render_page_nav('member'); ?>
<div class="page">
  <div class="user-bar">
    <span>Olá, <?= htmlspecialchars((string)($user['name'] ?? $user['email']), ENT_QUOTES, 'UTF-8') ?></span>
    <a href="/logout.php">Sair</a>
  </div>
  <div class="top-bar">
    <div class="badge"><i class="ti ti-circle-check"></i> Acesso liberado</div>
    <h1>BEM-VINDO À <span class="gold">COMUNIDADE</span> <span class="green">VIP</span></h1>
    <p class="lead">Entre no grupo do WhatsApp e acesse todos os PDFs exclusivos da Copa 2026 abaixo.</p>
  </div>
  <div class="success-box">
    <strong><i class="ti ti-confetti"></i> PARABÉNS!</strong>
    <p>Você faz parte da Comunidade Perfect Pay — mais de 1.000 figurinhas em PDF, Legend, holográficas e atualizações exclusivas.</p>
  </div>
  <p class="section-label">1 · <span>Comunidade</span></p>
  <a href="https://chat.whatsapp.com/DVBiPgbpbiyC8y6mD8iqIS" class="btn btn-whatsapp" target="_blank" rel="noopener noreferrer">
    <i class="ti ti-brand-whatsapp" style="font-size:28px"></i>
    <span>ENTRAR NO GRUPO VIP<small>Chat exclusivo de membros no WhatsApp</small></span>
  </a>
  <p class="section-label">2 · <span>Álbuns e PDFs</span></p>
  <?php if ($flash): ?>
  <div class="member-flash"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php render_albuns_refresh_button('/'); ?>
  <?php if ($featuredDownloads !== []): ?>
  <div class="downloads">
    <?php foreach ($featuredDownloads as $dl): ?>
    <a href="<?= htmlspecialchars($dl['url'], ENT_QUOTES, 'UTF-8') ?>" class="dl-card" target="_blank" rel="noopener">
      <div class="dl-icon"><i class="ti <?= htmlspecialchars($dl['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></div>
      <div class="dl-info">
        <strong><?= htmlspecialchars($dl['title'], ENT_QUOTES, 'UTF-8') ?></strong>
        <span><?= htmlspecialchars($dl['desc'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <i class="ti ti-eye dl-arrow"></i>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <a href="/albuns/" class="btn btn-albuns">
    <i class="ti ti-books" style="font-size:28px"></i>
    <span>VER TODOS OS PDFs ONLINE<?php if ($albunsCount > 0): ?><small><?= (int)$albunsCount ?> arquivos — PDF Definitivo, bônus e álbuns históricos</small><?php endif; ?></span>
  </a>
  <p class="section-label">3 · <span>Instagram</span></p>
  <a href="https://www.instagram.com/raphaelnogueirafelix/" class="btn btn-instagram" target="_blank" rel="noopener noreferrer">
    <i class="ti ti-brand-instagram" style="font-size:28px;flex-shrink:0"></i>
    <span>PEDIR NO WEBSITE INSTAGRAM<small>@raphaelnogueirafelix</small></span>
  </a>
  <a href="https://www.instagram.com/er.artedigital/" class="btn btn-instagram btn-instagram-arte" target="_blank" rel="noopener noreferrer">
    <i class="ti ti-brand-instagram" style="font-size:28px;flex-shrink:0"></i>
    <span>PEDIR ARTE FOTO NO INSTAGRAM<small>@er.artedigital</small></span>
  </a>
  <div class="note"><i class="ti ti-info-circle"></i> Os PDFs abrem no navegador. Acesso exclusivo para membros logados — não compartilhe seu login.</div>
  <p class="footer">Comunidade Perfect Pay · Copa 2026</p>
  <?php render_agency_footer(); ?>
</div>
</body>
</html>
