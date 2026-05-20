<?php

declare(strict_types=1);

require_once __DIR__ . '/theme.php';

/**
 * @param 'home'|'login'|'member'|'admin' $current
 */
function render_page_nav(string $current): void
{
    $siteHome = 'https://perfectpay.agenciajob.com/';
    $isAdmin = function_exists('is_admin') && is_admin();

    $steps = [
        'home' => ['url' => $siteHome, 'label' => 'Site', 'short' => 'Início'],
        'login' => ['url' => comunidade_url('/login.php'), 'label' => 'Login', 'short' => 'Login'],
        'member' => ['url' => comunidade_url('/'), 'label' => 'Área VIP', 'short' => 'VIP'],
    ];
    if ($isAdmin) {
        $steps['admin'] = ['url' => comunidade_url('/admin/'), 'label' => 'Admin', 'short' => 'Admin'];
    }

    $order = array_keys($steps);
    $index = array_search($current, $order, true);
    if ($index === false) {
        return;
    }

    $prev = $index > 0 ? $steps[$order[$index - 1]] : null;
    $next = $index < count($order) - 1 ? $steps[$order[$index + 1]] : null;
    $here = $steps[$current];
    ?>
<nav class="page-nav" aria-label="Navegação entre páginas">
  <?php if ($prev): ?>
  <a href="<?= htmlspecialchars($prev['url'], ENT_QUOTES, 'UTF-8') ?>" class="page-nav-btn page-nav-prev">
    <i class="ti ti-chevron-left"></i> Voltar
    <span class="page-nav-hint"><?= htmlspecialchars($prev['short'], ENT_QUOTES, 'UTF-8') ?></span>
  </a>
  <?php else: ?>
  <span class="page-nav-btn page-nav-btn-disabled" aria-disabled="true">
    <i class="ti ti-chevron-left"></i> Voltar
  </span>
  <?php endif; ?>

  <span class="page-nav-current" title="<?= htmlspecialchars($here['label'], ENT_QUOTES, 'UTF-8') ?>">
    <?= htmlspecialchars($here['short'], ENT_QUOTES, 'UTF-8') ?>
  </span>

  <?php if ($next): ?>
  <a href="<?= htmlspecialchars($next['url'], ENT_QUOTES, 'UTF-8') ?>" class="page-nav-btn page-nav-next">
    Avançar <i class="ti ti-chevron-right"></i>
    <span class="page-nav-hint"><?= htmlspecialchars($next['short'], ENT_QUOTES, 'UTF-8') ?></span>
  </a>
  <?php else: ?>
  <span class="page-nav-btn page-nav-btn-disabled" aria-disabled="true">
    Avançar <i class="ti ti-chevron-right"></i>
  </span>
  <?php endif; ?>
</nav>
    <?php
}

function page_nav_styles(): string
{
    return pp_nav_styles();
}

function render_favicon(): void
{
    $icon = 'https://perfectpay.agenciajob.com/favicon.jpg';
    echo '<link rel="icon" href="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '" type="image/jpeg">' . "\n";
    echo '<link rel="shortcut icon" href="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '" type="image/jpeg">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

