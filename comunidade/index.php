<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/nav.php';
require_once __DIR__ . '/includes/albuns.php';
require_once __DIR__ . '/includes/downloads.php';
require_once __DIR__ . '/includes/theme.php';
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
<title>Área VIP — Perfect Pay</title>
<?php render_favicon(); ?>
<?= pp_fonts_link() ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
<?= pp_app_shell_styles() ?>
<?= pp_nav_styles() ?>
<?= albuns_refresh_button_styles() ?>
.page{max-width:720px;margin:0 auto;padding:2rem 1.25rem 3rem}
.user-bar{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1.5rem;font-size:13px;color:var(--pp-muted)}
.user-bar a{color:var(--pp-primary);text-decoration:none;font-weight:600}
.hero-panel{text-align:center;margin-bottom:2rem;padding:2rem 1.5rem;background:var(--pp-card);border:1px solid var(--pp-border);border-radius:var(--pp-radius)}
.hero-panel h1{font-family:var(--pp-font-head);font-size:clamp(2rem,7vw,3rem);font-weight:800;letter-spacing:-.02em;margin-bottom:.5rem}
.hero-panel h1 em{font-style:normal;color:var(--pp-primary)}
.hero-panel .lead{color:var(--pp-muted);font-size:15px;max-width:480px;margin:0 auto}
.welcome{background:linear-gradient(135deg,rgba(250,204,21,.14),rgba(234,179,8,.08));border:1px solid var(--pp-border);border-radius:var(--pp-radius);padding:1.25rem;text-align:center;margin-bottom:2rem}
.welcome strong{display:block;font-family:var(--pp-font-head);font-size:1.25rem;color:var(--pp-accent);margin-bottom:.35rem}
.section-title{font-family:var(--pp-font-head);font-size:1.1rem;font-weight:700;color:var(--pp-text);margin:2rem 0 1rem;display:flex;align-items:center;gap:10px}
.section-title::before{content:'';width:4px;height:1.1em;background:var(--pp-primary);border-radius:2px}
.action-btn{
  display:flex;align-items:center;gap:14px;width:100%;padding:16px 18px;
  border-radius:var(--pp-radius-sm);text-decoration:none;color:inherit;
  font-family:var(--pp-font-body);transition:transform .2s,border-color .2s,box-shadow .2s;
  margin-bottom:10px;border:1px solid var(--pp-border);background:var(--pp-card);
}
.action-btn:hover{transform:translateY(-2px);border-color:var(--pp-primary);box-shadow:0 12px 32px rgba(0,0,0,.2)}
.action-btn .icon{width:48px;height:48px;border-radius:var(--pp-radius-sm);display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.action-btn .body{flex:1;text-align:left}
.action-btn .body strong{display:block;font-size:15px;font-weight:700;color:var(--pp-text);margin-bottom:2px}
.action-btn .body span{font-size:13px;color:var(--pp-muted)}
.action-btn .arrow{color:var(--pp-primary);font-size:20px}
.btn-wa .icon{background:rgba(37,211,102,.15);color:#4ade80}
.btn-albuns-main{background:linear-gradient(135deg,var(--pp-accent),#14b8a6);color:var(--pp-cta-text);border:none;padding:20px;font-family:var(--pp-font-head);font-size:1.15rem;font-weight:700;justify-content:center}
.btn-albuns-main .icon{background:rgba(255,255,255,.2);color:#fff}
.btn-albuns-main small{display:block;font-family:var(--pp-font-body);font-size:12px;font-weight:500;opacity:.9;margin-top:4px}
.btn-ig .icon{background:rgba(250,204,21,.18);color:var(--pp-primary)}
.dl-list{display:flex;flex-direction:column;gap:10px;margin-bottom:1rem}
.note{margin-top:1.5rem;padding:1rem;background:var(--pp-bg-soft);border:1px solid var(--pp-border);border-radius:var(--pp-radius-sm);font-size:13px;color:var(--pp-muted);text-align:center}
</style>
</head>
<body>
<?php render_page_nav('member'); ?>
<div class="page">
  <div class="user-bar">
    <span>Olá, <?= htmlspecialchars((string)($user['name'] ?? $user['email']), ENT_QUOTES, 'UTF-8') ?></span>
    <a href="<?= htmlspecialchars(comunidade_url('/logout.php'), ENT_QUOTES, 'UTF-8') ?>">Sair</a>
  </div>

  <div class="hero-panel">
    <div class="pp-badge" style="margin:0 auto 1rem"><i class="ti ti-sparkles"></i> Acesso liberado</div>
    <h1>Comunidade <em>VIP</em></h1>
    <p class="lead">PDFs exclusivos, grupo WhatsApp e materiais da Copa 2026 em um só lugar.</p>
  </div>

  <div class="welcome">
    <strong><i class="ti ti-confetti"></i> Parabéns, você está dentro!</strong>
    Mais de 1.000 figurinhas em PDF, Legend, holográficas e atualizações exclusivas.
  </div>

  <h2 class="section-title">Comunidade</h2>
  <a href="https://chat.whatsapp.com/DVBiPgbpbiyC8y6mD8iqIS" class="action-btn btn-wa" target="_blank" rel="noopener noreferrer">
    <span class="icon"><i class="ti ti-brand-whatsapp"></i></span>
    <span class="body"><strong>Grupo VIP no WhatsApp</strong><span>Chat exclusivo de membros</span></span>
    <i class="ti ti-external-link arrow"></i>
  </a>

  <h2 class="section-title">Álbuns e PDFs</h2>
  <?php if ($flash): ?>
  <div class="pp-flash"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php render_albuns_refresh_button('/'); ?>

  <?php if ($featuredDownloads !== []): ?>
  <div class="dl-list">
    <?php foreach ($featuredDownloads as $dl): ?>
    <a href="<?= htmlspecialchars($dl['url'], ENT_QUOTES, 'UTF-8') ?>" class="action-btn" target="_blank" rel="noopener">
      <span class="icon" style="background:rgba(250,204,21,.15);color:var(--pp-accent)"><i class="ti <?= htmlspecialchars($dl['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
      <span class="body"><strong><?= htmlspecialchars($dl['title'], ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars($dl['desc'], ENT_QUOTES, 'UTF-8') ?></span></span>
      <i class="ti ti-eye arrow"></i>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <a href="<?= htmlspecialchars(comunidade_url('/albuns/'), ENT_QUOTES, 'UTF-8') ?>" class="action-btn btn-albuns-main">
    <span class="icon"><i class="ti ti-books"></i></span>
    <span class="body" style="text-align:center"><strong>Ver todos os PDFs online</strong><?php if ($albunsCount > 0): ?><small><?= (int)$albunsCount ?> arquivos disponíveis</small><?php endif; ?></span>
  </a>

  <h2 class="section-title">Contato</h2>
  <a href="https://www.instagram.com/raphaelnogueirafelix/" class="action-btn btn-ig" target="_blank" rel="noopener noreferrer">
    <span class="icon"><i class="ti ti-brand-instagram"></i></span>
    <span class="body"><strong>Instagram</strong><span>@raphaelnogueirafelix</span></span>
    <i class="ti ti-external-link arrow"></i>
  </a>

  <p class="note"><i class="ti ti-info-circle"></i> Os PDFs abrem no navegador. Não compartilhe seu login.</p>
  <?php render_pp_footer(); ?>
</div>
</body>
</html>
