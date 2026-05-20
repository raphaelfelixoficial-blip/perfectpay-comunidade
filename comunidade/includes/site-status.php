<?php

declare(strict_types=1);

function site_status_file(): string
{
    return dirname(__DIR__) . '/data/site-status.json';
}

/** @return list<string> */
function site_status_storage_paths(): array
{
    $paths = [site_status_file()];
    $membrosRoot = dirname(__DIR__);

    $serverPublic = dirname($membrosRoot) . '/data/site-status.json';
    if (is_dir(dirname($serverPublic))) {
        $paths[] = $serverPublic;
    }

    $repoPublic = dirname($membrosRoot) . '/public_html/data/site-status.json';
    if (is_dir(dirname($repoPublic))) {
        $paths[] = $repoPublic;
    }

    return array_values(array_unique($paths));
}

/** @return array{home_layout:string,home_title:string,home_message:string,members_enabled:bool,updated_at?:int} */
function site_status_defaults(): array
{
    return [
        'home_layout' => 'simple',
        'home_title' => 'Perfect Pay',
        'home_message' => "Bem-vindo ao Perfect Pay.\n\nAcompanhe novidades sobre figurinhas e a Copa do Mundo 2026.",
        'members_enabled' => true,
        'updated_at' => 0,
    ];
}

function site_status_valid_layout(string $layout): string
{
    return $layout === 'full' ? 'full' : 'simple';
}

/** @param array<string, mixed> $raw */
function site_status_normalize(array $raw): array
{
    $defaults = site_status_defaults();

    if (isset($raw['home_message']) || isset($raw['home_title']) || isset($raw['home_layout'])) {
        return [
            'home_layout' => site_status_valid_layout((string) ($raw['home_layout'] ?? $defaults['home_layout'])),
            'home_title' => trim((string) ($raw['home_title'] ?? $defaults['home_title'])) ?: $defaults['home_title'],
            'home_message' => trim((string) ($raw['home_message'] ?? $defaults['home_message'])),
            'members_enabled' => !isset($raw['members_enabled']) || (bool) $raw['members_enabled'],
            'updated_at' => (int) ($raw['updated_at'] ?? 0),
        ];
    }

    // Formato antigo (modos de venda)
    $custom = trim((string) ($raw['custom_message'] ?? ''));
    $mode = (string) ($raw['mode'] ?? 'open');
    $legacyMessages = [
        'open' => 'Bem-vindo ao Perfect Pay.',
        'goal_reached' => "Encerramos as inscrições.\n\nParabéns a todos que garantiram o acesso!",
        'offer_closed' => "Oferta finalizada.\n\nObrigado pelo interesse.",
        'thanks' => "Parabéns a quem entrou na comunidade!\n\nObrigado a todos que compraram.",
    ];
    $message = $custom !== '' ? $custom : ($legacyMessages[$mode] ?? $defaults['home_message']);

    return [
        'home_layout' => $mode === 'open' ? 'full' : 'simple',
        'home_title' => 'Perfect Pay',
        'home_message' => $message,
        'members_enabled' => $mode === 'open',
        'updated_at' => (int) ($raw['updated_at'] ?? 0),
    ];
}

/** @return array{home_title:string,home_message:string,members_enabled:bool,updated_at?:int} */
function site_status_load_raw(): array
{
    foreach (site_status_storage_paths() as $path) {
        if (!is_file($path)) {
            continue;
        }
        $raw = json_decode((string) file_get_contents($path), true);
        if (is_array($raw)) {
            return site_status_normalize($raw);
        }
    }

    return site_status_defaults();
}

function site_status_view(): array
{
    $raw = site_status_load_raw();
    $paragraphs = array_values(array_filter(
        array_map('trim', preg_split('/\r\n|\r|\n/', $raw['home_message']) ?: []),
        static fn (string $p) => $p !== ''
    ));

    return [
        'home_layout' => site_status_valid_layout((string) $raw['home_layout']),
        'home_title' => $raw['home_title'],
        'home_message' => $raw['home_message'],
        'home_paragraphs' => $paragraphs !== [] ? $paragraphs : [site_status_defaults()['home_message']],
        'members_enabled' => (bool) $raw['members_enabled'],
        'updated_at' => (int) ($raw['updated_at'] ?? 0),
    ];
}

function site_status_uses_full_landing(): bool
{
    return site_status_load_raw()['home_layout'] === 'full';
}

function members_area_enabled(): bool
{
    return (bool) site_status_load_raw()['members_enabled'];
}

/** @return array{ok:bool,error?:string} */
function site_status_save(string $homeLayout, string $homeTitle, string $homeMessage, bool $membersEnabled): array
{
    $homeLayout = site_status_valid_layout($homeLayout);
    $homeTitle = trim($homeTitle);
    $homeMessage = trim($homeMessage);
    if ($homeTitle === '') {
        return ['ok' => false, 'error' => 'Informe um título para a página.'];
    }
    if ($homeLayout === 'simple' && $homeMessage === '') {
        return ['ok' => false, 'error' => 'Informe a mensagem da home simples.'];
    }

    $payload = json_encode([
        'home_layout' => $homeLayout,
        'home_title' => $homeTitle,
        'home_message' => $homeMessage,
        'members_enabled' => $membersEnabled,
        'updated_at' => time(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($payload === false) {
        return ['ok' => false, 'error' => 'Erro ao gerar JSON.'];
    }

    foreach (site_status_storage_paths() as $path) {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        if (file_put_contents($path, $payload, LOCK_EX) === false) {
            return ['ok' => false, 'error' => 'Não foi possível salvar.'];
        }
    }

    return ['ok' => true];
}

function site_status_home_styles(): string
{
    return <<<'CSS'
  :root{--br-green:#009739;--br-yellow:#FFDF00;--br-blue:#002776}
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;background:#0a0a0a;color:#e8e4dc;font-family:'Barlow',system-ui,sans-serif;line-height:1.7}
  .home-wrap{max-width:640px;margin:0 auto;padding:3rem 1.25rem 4rem}
  .home-brand{font-family:'Bebas Neue',Georgia,serif;font-size:clamp(42px,10vw,56px);letter-spacing:3px;color:#fff;margin-bottom:.25rem}
  .home-brand span{color:var(--br-yellow)}
  .home-rule{width:48px;height:3px;background:var(--br-green);margin:1.25rem 0 2rem}
  .home-content{font-size:17px;color:#ccc}
  .home-content p{margin-bottom:1.25rem}
  .home-content p:last-child{margin-bottom:0}
  .home-footer{margin-top:3rem;padding-top:1.5rem;border-top:1px solid #222;font-size:13px;color:#666;text-align:center}
  .home-footer a{color:#888;text-decoration:none}
  .home-footer a:hover{color:var(--br-yellow)}
  .home-members{margin-top:2rem}
  .home-members a{display:inline-flex;align-items:center;gap:8px;color:var(--br-yellow);font-weight:600;font-size:14px;text-decoration:none}
  .home-members a:hover{text-decoration:underline}
CSS;
}

function render_members_area_closed_page(): void
{
    $view = site_status_view();
    $title = htmlspecialchars($view['home_title'], ENT_QUOTES, 'UTF-8');
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Área indisponível — <?= $title ?></title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600&display=swap" rel="stylesheet">
<style><?= site_status_home_styles() ?></style>
</head>
<body>
<div class="home-wrap">
  <h1 class="home-brand"><?= $title ?></h1>
  <div class="home-rule"></div>
  <div class="home-content">
    <?php foreach ($view['home_paragraphs'] as $p): ?>
    <p><?= nl2br(htmlspecialchars($p, ENT_QUOTES, 'UTF-8'), false) ?></p>
    <?php endforeach; ?>
    <p style="margin-top:1.5rem;color:#888">A área de membros está temporariamente indisponível.</p>
  </div>
  <p class="home-footer">
    <a href="https://perfectpay.agenciajob.com/">← Voltar ao site</a>
    · <a href="/login.php?admin=1">Admin</a>
  </p>
</div>
</body>
</html>
    <?php
}
