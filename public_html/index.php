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
<title><?= $title ?> — Copa 2026</title>
<link rel="icon" href="/favicon.jpg" type="image/jpeg">
<link rel="shortcut icon" href="/favicon.jpg" type="image/jpeg">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600&display=swap" rel="stylesheet">
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
    <a href="https://perfectpay.agenciajob.com/comunidade/login.php">Área de membros →</a>
  </p>
  <?php endif; ?>
  <p class="home-footer">
    Desenvolvido por <a href="https://www.instagram.com/raphaelnogueirafelix/" target="_blank" rel="noopener noreferrer">Agência Job</a>
  </p>
</div>
</body>
</html>
