<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/albuns.php';
require_once dirname(__DIR__) . '/includes/nav.php';
require_once dirname(__DIR__) . '/includes/theme.php';
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
<title>Álbuns — Perfect Pay</title>
<?php render_favicon(); ?>
<?= pp_fonts_link() ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
<?= pp_app_shell_styles() ?>
<?= pp_nav_styles() ?>
<?= albuns_refresh_button_styles() ?>
.page{max-width:800px;margin:0 auto;padding:2rem 1.25rem 3rem}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.top h1{font-family:var(--pp-font-head);font-size:2rem;font-weight:800}
.top h1 em{font-style:normal;color:var(--pp-primary)}
.back{color:var(--pp-muted);text-decoration:none;font-size:14px;font-weight:600}
.back:hover{color:var(--pp-primary)}
.cat{margin:2rem 0 .75rem;font-family:var(--pp-font-head);font-size:1.15rem;font-weight:700;color:var(--pp-accent);padding-bottom:.5rem;border-bottom:1px solid var(--pp-border)}
.pdf{display:flex;align-items:center;gap:14px;background:var(--pp-card);border:1px solid var(--pp-border);border-radius:var(--pp-radius-sm);padding:14px 16px;margin-bottom:8px;text-decoration:none;color:inherit;transition:.2s}
.pdf:hover{border-color:var(--pp-primary);transform:translateX(4px);box-shadow:0 8px 24px rgba(0,0,0,.15)}
.pdf i.pdf-icon{font-size:26px;color:var(--pp-primary)}
.pdf strong{display:block;color:var(--pp-text);font-size:15px;margin-bottom:2px}
.pdf span{font-size:12px;color:var(--pp-muted)}
.empty{color:var(--pp-muted);text-align:center;padding:2rem;background:var(--pp-card);border-radius:var(--pp-radius);border:1px dashed var(--pp-border)}
</style>
</head>
<body>
<?php render_page_nav('member'); ?>
<div class="page">
  <div class="top">
    <h1>Biblioteca <em>VIP</em> <small style="font-size:14px;color:var(--pp-muted);font-family:var(--pp-font-body)">(<?= (int)$total ?> PDFs)</small></h1>
    <a href="<?= htmlspecialchars(comunidade_url('/'), ENT_QUOTES, 'UTF-8') ?>" class="back"><i class="ti ti-arrow-left"></i> Voltar</a>
  </div>
  <?php if ($flash): ?>
  <div class="pp-flash"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php render_albuns_refresh_button('/albuns/'); ?>
  <?php if (empty($categories)): ?>
    <p class="empty">Nenhum PDF encontrado nas pastas de álbuns.</p>
  <?php else: ?>
    <?php foreach ($categories as $category => $files): ?>
      <h2 class="cat"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></h2>
      <?php foreach ($files as $file): ?>
      <a class="pdf" href="<?= htmlspecialchars(albuns_view_url($file['path']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
        <i class="ti ti-file-type-pdf pdf-icon"></i>
        <div>
          <strong><?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?></strong>
          <span>Abrir online · <?= htmlspecialchars(albuns_format_size($file['size']), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <i class="ti ti-external-link" style="margin-left:auto;color:var(--pp-primary)"></i>
      </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  <?php endif; ?>
  <?php render_pp_footer(); ?>
</div>
</body>
</html>
