<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/albuns.php';
require_once dirname(__DIR__) . '/includes/nav.php';
require_member();
start_session();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$categories = albuns_scan_by_category();
$total = array_sum(array_map('count', $categories));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Álbuns VIP — Perfect Pay</title>
<?php render_favicon(); ?>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  :root{--br-green:#009739;--br-yellow:#FFDF00;--br-blue:#002776}
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#050505;font-family:'Barlow',sans-serif;color:#eee;min-height:100vh}
  .wrap{max-width:800px;margin:0 auto;padding:2rem 1.25rem 4rem}
  .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
  h1{font-family:'Bebas Neue',sans-serif;font-size:36px;letter-spacing:2px}
  h1 span{color:var(--br-yellow)}
  .back{color:#888;text-decoration:none;font-size:14px}
  .back:hover{color:var(--br-yellow)}
  .cat{margin:2rem 0 1rem;font-family:'Bebas Neue',sans-serif;font-size:22px;color:var(--br-yellow);letter-spacing:1px;border-bottom:1px solid #222;padding-bottom:.5rem}
  .pdf{display:flex;align-items:center;gap:14px;background:#141414;border:1px solid #2a2a2a;border-radius:8px;padding:14px 16px;margin-bottom:10px;text-decoration:none;color:inherit;transition:.2s}
  .pdf:hover{border-color:var(--br-green);transform:translateX(4px)}
  .pdf i{font-size:28px;color:var(--br-yellow)}
  .pdf strong{display:block;color:#fff;font-size:15px;margin-bottom:2px}
  .pdf span{font-size:12px;color:#888}
  .empty{color:#888;text-align:center;padding:2rem}
  <?= page_nav_styles() ?>
  <?= albuns_refresh_button_styles() ?>
</style>
</head>
<body>
<?php render_page_nav('member'); ?>
<div class="wrap">
  <div class="top">
    <h1>ÁLBUNS <span>VIP</span> <small style="font-size:14px;color:#666;font-family:Barlow,sans-serif">(<?= (int)$total ?> PDFs)</small></h1>
    <a href="/" class="back"><i class="ti ti-arrow-left"></i> Voltar à área VIP</a>
  </div>

  <?php if ($flash): ?>
  <div class="member-flash"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php render_albuns_refresh_button('/albuns/'); ?>

  <?php if (empty($categories)): ?>
    <p class="empty">Nenhum PDF encontrado. As pastas <strong>Bonus</strong> e <strong>PDF Definitivo</strong> devem estar em <code>/albuns/</code>.</p>
  <?php else: ?>
    <?php foreach ($categories as $category => $files): ?>
      <h2 class="cat"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></h2>
      <?php foreach ($files as $file): ?>
      <a class="pdf" href="<?= htmlspecialchars(albuns_view_url($file['path']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
        <i class="ti ti-file-type-pdf"></i>
        <div>
          <strong><?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?></strong>
          <span>Ver online · <?= htmlspecialchars(albuns_format_size($file['size']), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <i class="ti ti-eye" style="margin-left:auto;color:var(--br-green)"></i>
      </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
  <?php render_agency_footer(); ?>
</body>
</html>
