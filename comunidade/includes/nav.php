<?php

declare(strict_types=1);

/**
 * @param 'home'|'login'|'member'|'admin' $current
 */
function render_page_nav(string $current): void
{
    $siteHome = 'https://perfectpay.agenciajob.com/';
    $isAdmin = function_exists('is_admin') && is_admin();

    $steps = [
        'home' => ['url' => $siteHome, 'label' => 'Site', 'short' => 'Início'],
        'login' => ['url' => '/login.php', 'label' => 'Login', 'short' => 'Login'],
        'member' => ['url' => '/', 'label' => 'Área VIP', 'short' => 'VIP'],
    ];
    if ($isAdmin) {
        $steps['admin'] = ['url' => '/admin/', 'label' => 'Admin', 'short' => 'Admin'];
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
    return <<<'CSS'
  .page-nav{
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    max-width:720px;margin:0 auto 1.5rem;padding:0 1.25rem;
  }
  .page-nav-btn{
    display:inline-flex;align-items:center;gap:6px;
    padding:10px 14px;border-radius:6px;
    background:#141414;border:1px solid #2a2a2a;
    color:#ccc;font-size:13px;font-weight:600;text-decoration:none;
    transition:all .2s;flex:1;max-width:140px;
  }
  .page-nav-btn:hover:not(.page-nav-btn-disabled){
    border-color:#009739;color:#FFDF00;background:#1a1a1a;
  }
  .page-nav-prev{justify-content:flex-start}
  .page-nav-next{justify-content:flex-end;flex-direction:row-reverse}
  .page-nav-next .page-nav-hint{text-align:right}
  .page-nav-btn-disabled{
    opacity:.35;cursor:not-allowed;pointer-events:none;
    justify-content:center;
  }
  .page-nav-hint{
    display:block;font-size:10px;font-weight:500;color:#666;
    letter-spacing:0;text-transform:uppercase;margin-top:2px;
  }
  .page-nav-current{
    flex-shrink:0;padding:8px 16px;border-radius:20px;
    background:rgba(0,151,57,.15);border:1px solid rgba(255,223,0,.4);
    color:#FFDF00;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  }
  @media(max-width:480px){
    .page-nav-hint{display:none}
    .page-nav-btn{padding:10px 12px;font-size:12px;max-width:110px}
  }
  .agency-footer{
    text-align:center;padding:1.25rem 1rem 2rem;
    font-size:12px;color:#555;
    border-top:1px solid #1a1a1a;
    margin-top:1.5rem;
  }
  .agency-footer a{color:#888;text-decoration:none;font-weight:600}
  .agency-footer a:hover{color:#FFDF00}
CSS;
}

function render_favicon(): void
{
    $icon = 'https://perfectpay.agenciajob.com/favicon.jpg';
    echo '<link rel="icon" href="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '" type="image/jpeg">' . "\n";
    echo '<link rel="shortcut icon" href="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '" type="image/jpeg">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

function render_agency_footer(): void
{
    ?>
<footer class="agency-footer">
  Desenvolvido por <a href="https://www.instagram.com/raphaelnogueirafelix/" target="_blank" rel="noopener noreferrer">Agência Job</a>
</footer>
<?php
}
