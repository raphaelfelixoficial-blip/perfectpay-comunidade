<?php

declare(strict_types=1);

require __DIR__ . '/landing-bootstrap.php';

if ($S['home_layout'] === 'full') {
    include __DIR__ . '/landing-full.php';
    return;
}

$title = landing_h($S['home_title']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $title ?> — Figurinhas da Copa</title>
<?php site_status_render_favicon_tags(); ?>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<style><?= site_status_home_styles() ?></style>
</head>
<body>
<div class="home-wrap">
  <h1 class="home-brand"><?= landing_h($S['home_title']) ?></h1>
  <div class="home-rule"></div>
  <article class="home-content">
    <?php foreach ($S['home_paragraphs'] as $paragraph): ?>
    <p><?= nl2br(landing_h($paragraph), false) ?></p>
    <?php endforeach; ?>
  </article>
  <?php if ($S['members_enabled']): ?>
  <p class="home-members">
    <a href="/comunidade/login.php">Área de membros →</a>
  </p>
  <?php endif; ?>
  <p class="home-footer">© <?= (int) date('Y') ?> Comunidade Figurinhas da Copa · Copa 2026</p>
</div>
</body>
</html>
