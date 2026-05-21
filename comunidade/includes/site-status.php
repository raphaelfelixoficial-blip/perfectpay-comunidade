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
        'home_title' => 'Figurinhas da Copa',
        'home_message' => "Bem-vindo à Comunidade Figurinhas da Copa.\n\nAcompanhe novidades sobre figurinhas e a Copa do Mundo 2026.",
        'home_video_url' => 'https://www.youtube.com/watch?v=yskrod-EXeQ',
        'home_hero_show_video' => true,
        'home_hero_show_image' => false,
        'home_hero_image' => '',
        'members_enabled' => true,
        'checkout_price' => 97.0,
        'checkout_compare_price' => 197.0,
        'promo_banner_enabled' => true,
        'promo_banner_title' => 'Identifique figurinhas falsas com segurança',
        'promo_banner_text' => 'Bastidores, dicas e PDFs exclusivos da Copa 2026 — tudo na comunidade VIP.',
        'promo_banner_image' => '/ChatGPT-Image-16-de-mai.-de-2026_-13_07_10.webp',
        'updated_at' => 0,
    ];
}

function site_format_price_brl(float $value): string
{
    return number_format(max(0, $value), 2, ',', '.');
}

/** Valor ativo do checkout (site-status → config.php). */
function site_checkout_price(): float
{
    $raw = site_status_load_raw();
    $price = (float) ($raw['checkout_price'] ?? 0);
    if ($price > 0) {
        return round($price, 2);
    }

    $path = dirname(__DIR__) . '/data/config.php';
    if (is_file($path)) {
        $cfg = require $path;
        return round(max(1, (float) ($cfg['asaas_checkout_value'] ?? 97)), 2);
    }

    return 97.0;
}

function site_checkout_compare_price(): float
{
    $raw = site_status_load_raw();
    $compare = (float) ($raw['checkout_compare_price'] ?? 0);
    if ($compare > 0) {
        return round($compare, 2);
    }

    return round(site_checkout_price() * 2, 2);
}

/** @return list<float> */
function site_checkout_price_presets(): array
{
    return [19.9, 47.0, 67.0, 97.0, 127.0, 197.0];
}

function site_status_public_root(): string
{
    return dirname(dirname(__DIR__));
}

/** Normaliza caminho público da imagem (começa com /). */
function site_status_public_image_path(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return '/' . ltrim($path, '/');
}

/** @return list<string> */
function site_status_allowed_image_extensions(): array
{
    return ['png', 'jpg', 'jpeg', 'webp'];
}

function site_status_is_allowed_image_path(string $path): bool
{
    $path = strtolower($path);
    foreach (site_status_allowed_image_extensions() as $ext) {
        if (str_ends_with($path, '.' . $ext)) {
            return true;
        }
    }

    return false;
}

/**
 * Salva upload de imagem (PNG, JPEG, WebP) em /uploads/.
 *
 * @param array<string, mixed> $file
 * @return array{ok:bool,path?:string,error?:string}
 */
function site_status_store_uploaded_image(array $file, string $subdir = 'uploads'): array
{
    $tmp = (string) ($file['tmp_name'] ?? '');
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE || $tmp === '') {
        return ['ok' => false, 'error' => 'Nenhum arquivo enviado.'];
    }
    if ($error !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Falha no upload (código ' . $error . ').'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';
    $map = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];
    if (!isset($map[$mime])) {
        return ['ok' => false, 'error' => 'Use PNG, JPEG ou WebP.'];
    }

    $ext = $map[$mime];
    $dir = site_status_public_root() . '/' . trim($subdir, '/') . '/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return ['ok' => false, 'error' => 'Não foi possível criar pasta de uploads.'];
    }

    $name = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $dir . $name;
    if (!move_uploaded_file($tmp, $dest)) {
        return ['ok' => false, 'error' => 'Não foi possível salvar a imagem.'];
    }

    @chmod($dest, 0644);

    return ['ok' => true, 'path' => '/' . trim($subdir, '/') . '/' . $name];
}

/** @param array<string, mixed> $post */
function site_status_hero_from_post(array $post): array
{
    $image = site_status_public_image_path((string) ($post['home_hero_image'] ?? ''));
    if ($image !== '' && !preg_match('#^https?://#i', $image) && !site_status_is_allowed_image_path($image)) {
        $image = '';
    }

    return [
        'home_hero_show_video' => isset($post['home_hero_show_video']),
        'home_hero_show_image' => isset($post['home_hero_show_image']),
        'home_hero_image' => $image,
    ];
}

/** @param array<string, mixed> $post */
function site_status_offer_from_post(array $post): array
{
    $preset = (string) ($post['checkout_price_preset'] ?? '');
    if ($preset === 'custom') {
        $price = (float) str_replace(',', '.', (string) ($post['checkout_price_custom'] ?? '97'));
    } elseif ($preset !== '' && is_numeric($preset)) {
        $price = (float) $preset;
    } else {
        $price = 97.0;
    }

    $compare = (float) str_replace(',', '.', (string) ($post['checkout_compare_price'] ?? '0'));
    if ($compare <= 0 || $compare <= $price) {
        $compare = round($price * 2, 2);
    }

    $image = trim((string) ($post['promo_banner_image'] ?? ''));
    if ($image === '') {
        $image = site_status_defaults()['promo_banner_image'];
    }
    if ($image !== '' && $image[0] !== '/') {
        $image = '/' . ltrim($image, '/');
    }

    return [
        'checkout_price' => max(1, round($price, 2)),
        'checkout_compare_price' => max(0, round($compare, 2)),
        'promo_banner_enabled' => isset($post['promo_banner_enabled']),
        'promo_banner_title' => trim((string) ($post['promo_banner_title'] ?? '')),
        'promo_banner_text' => trim((string) ($post['promo_banner_text'] ?? '')),
        'promo_banner_image' => $image,
    ];
}

function site_status_sync_checkout_price_to_config(float $price): void
{
    $path = dirname(__DIR__) . '/data/config.php';
    if (!is_file($path)) {
        return;
    }
    $cfg = require $path;
    $cfg['asaas_checkout_value'] = max(1, round($price, 2));
    $export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
    @file_put_contents($path, $export, LOCK_EX);
}

/** Converte link YouTube/Vimeo para URL de embed do iframe. */
function site_status_video_to_embed(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]{11})#', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)([a-zA-Z0-9_-]{11})#', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('#player\.vimeo\.com/video/(\d+)#', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }
    if (preg_match('#vimeo\.com/(\d+)#', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }

    return '';
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
        $videoUrl = trim((string) ($raw['home_video_url'] ?? $defaults['home_video_url']));

        $heroImage = site_status_public_image_path((string) ($raw['home_hero_image'] ?? ''));

        return [
            'home_layout' => site_status_valid_layout((string) ($raw['home_layout'] ?? $defaults['home_layout'])),
            'home_title' => trim((string) ($raw['home_title'] ?? $defaults['home_title'])) ?: $defaults['home_title'],
            'home_message' => trim((string) ($raw['home_message'] ?? $defaults['home_message'])),
            'home_video_url' => $videoUrl !== '' ? $videoUrl : $defaults['home_video_url'],
            'home_hero_show_video' => !isset($raw['home_hero_show_video']) || (bool) $raw['home_hero_show_video'],
            'home_hero_show_image' => !empty($raw['home_hero_show_image']),
            'home_hero_image' => $heroImage,
            'members_enabled' => !isset($raw['members_enabled']) || (bool) $raw['members_enabled'],
            'checkout_price' => max(1, (float) ($raw['checkout_price'] ?? site_status_defaults()['checkout_price'])),
            'checkout_compare_price' => (float) ($raw['checkout_compare_price'] ?? 0),
            'promo_banner_enabled' => !isset($raw['promo_banner_enabled']) || (bool) $raw['promo_banner_enabled'],
            'promo_banner_title' => trim((string) ($raw['promo_banner_title'] ?? site_status_defaults()['promo_banner_title'])),
            'promo_banner_text' => trim((string) ($raw['promo_banner_text'] ?? site_status_defaults()['promo_banner_text'])),
            'promo_banner_image' => trim((string) ($raw['promo_banner_image'] ?? site_status_defaults()['promo_banner_image'])),
            'updated_at' => (int) ($raw['updated_at'] ?? 0),
        ];
    }

    // Formato antigo (modos de venda)
    $custom = trim((string) ($raw['custom_message'] ?? ''));
    $mode = (string) ($raw['mode'] ?? 'open');
    $legacyMessages = [
        'open' => 'Bem-vindo à Comunidade Figurinhas da Copa.',
        'goal_reached' => "Encerramos as inscrições.\n\nParabéns a todos que garantiram o acesso!",
        'offer_closed' => "Oferta finalizada.\n\nObrigado pelo interesse.",
        'thanks' => "Parabéns a quem entrou na comunidade!\n\nObrigado a todos que compraram.",
    ];
    $message = $custom !== '' ? $custom : ($legacyMessages[$mode] ?? $defaults['home_message']);

    return [
        'home_layout' => $mode === 'open' ? 'full' : 'simple',
        'home_title' => 'Figurinhas da Copa',
        'home_message' => $message,
        'home_video_url' => $defaults['home_video_url'],
        'home_hero_show_video' => $defaults['home_hero_show_video'],
        'home_hero_show_image' => $defaults['home_hero_show_image'],
        'home_hero_image' => $defaults['home_hero_image'],
        'members_enabled' => $mode === 'open',
        'checkout_price' => $defaults['checkout_price'],
        'checkout_compare_price' => $defaults['checkout_compare_price'],
        'promo_banner_enabled' => $defaults['promo_banner_enabled'],
        'promo_banner_title' => $defaults['promo_banner_title'],
        'promo_banner_text' => $defaults['promo_banner_text'],
        'promo_banner_image' => $defaults['promo_banner_image'],
        'updated_at' => (int) ($raw['updated_at'] ?? 0),
    ];
}

/** @param array<string, mixed> $view */
function site_status_render_hero_media(array $view): string
{
    $showVideo = !empty($view['home_hero_show_video']);
    $showImage = !empty($view['home_hero_show_image']);
    $embed = trim((string) ($view['home_video_embed'] ?? ''));
    $image = trim((string) ($view['home_hero_image'] ?? ''));

    $html = '';

    if ($showVideo && $embed !== '') {
        $embedSafe = htmlspecialchars($embed, ENT_QUOTES, 'UTF-8');
        $html .= '<div class="hero-video"><iframe src="' . $embedSafe . '" title="Vídeo Comunidade Figurinhas da Copa" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe></div>';
    } elseif ($showImage && $image !== '' && !$showVideo) {
        $imgSafe = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
        $html .= '<div class="hero-media-image hero-media-image--solo"><img src="' . $imgSafe . '" alt="Destaque Comunidade Figurinhas da Copa" loading="lazy" decoding="async"></div>';
    }

    if ($showImage && $image !== '' && ($showVideo && $embed !== '')) {
        $imgSafe = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
        $html .= '<div class="hero-media-image hero-media-image--below"><img src="' . $imgSafe . '" alt="Destaque abaixo do vídeo" loading="lazy" decoding="async"></div>';
    }

    return $html;
}

function site_status_hero_media_css(): string
{
    return <<<'CSS'
.hero-media-image{width:100%;max-width:560px;margin:0 auto 1.5rem;border-radius:8px;overflow:hidden;border:2px solid rgba(0,151,57,0.45);box-shadow:0 8px 32px rgba(0,0,0,0.55)}
.hero-media-image img{width:100%;height:auto;display:block;vertical-align:middle}
.hero-media-image--solo img{max-height:min(70vh,420px);object-fit:cover}
.hero-media-image--below{margin-top:0;margin-bottom:1.25rem}
CSS;
}

function site_status_promo_banner_css(): string
{
    return <<<'CSS'
.promo-bleed-wrap{width:100vw;max-width:100vw;margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw);position:relative;left:0;overflow:hidden}
.promo-bleed{display:grid;grid-template-columns:1fr;min-height:clamp(200px,42vw,380px);background:#0a0906}
@media(min-width:768px){.promo-bleed{grid-template-columns:1.1fr 1fr;min-height:320px}}
.promo-bleed-media{position:relative;min-height:200px}
.promo-bleed-media img{width:100%;height:100%;object-fit:cover;display:block;min-height:200px}
.promo-bleed-media::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,rgba(18,16,10,.15) 0%,rgba(18,16,10,.85) 55%,#12100a 100%)}
@media(max-width:767px){.promo-bleed-media::after{background:linear-gradient(180deg,rgba(18,16,10,.1) 30%,rgba(18,16,10,.92) 100%)}}
.promo-bleed-body{display:flex;flex-direction:column;justify-content:center;gap:12px;padding:clamp(1.25rem,5vw,2.5rem);background:linear-gradient(135deg,#1a1608 0%,#12100a 100%);border-top:3px solid var(--br-yellow)}
@media(min-width:768px){.promo-bleed-body{border-top:none;border-left:3px solid var(--br-yellow)}}
.promo-bleed-kicker{font-size:11px;font-weight:800;letter-spacing:.2em;text-transform:uppercase;color:var(--br-yellow)}
.promo-bleed-title{font-family:'Syne',sans-serif;font-size:clamp(1.35rem,4.5vw,2rem);line-height:1.15;color:#fff;font-weight:800}
.promo-bleed-text{font-size:clamp(14px,2.5vw,16px);line-height:1.65;color:#ccc;max-width:36rem}
.promo-bleed-price{display:inline-flex;align-items:baseline;gap:10px;flex-wrap:wrap;margin-top:4px}
.promo-bleed-price s{color:#777;font-size:1rem}
.promo-bleed-price strong{font-family:'Syne',sans-serif;font-size:clamp(1.75rem,5vw,2.5rem);color:var(--br-yellow)}
.promo-bleed-cta{display:inline-flex;align-items:center;justify-content:center;gap:8px;margin-top:8px;width:fit-content;max-width:100%;padding:14px 24px;border-radius:10px;background:linear-gradient(135deg,#FFDF00,#ca8a04);color:#1a1400;font-family:'Syne',sans-serif;font-weight:800;text-decoration:none;font-size:15px}
.promo-bleed-cta:hover{filter:brightness(1.05);transform:translateY(-1px)}
CSS;
}

/** @param array<string, mixed> $view */
function site_status_render_promo_banner(array $view): string
{
    if (empty($view['promo_banner_enabled'])) {
        return '';
    }

    $title = trim((string) ($view['promo_banner_title'] ?? ''));
    $text = trim((string) ($view['promo_banner_text'] ?? ''));
    $image = trim((string) ($view['promo_banner_image'] ?? ''));
    if ($title === '' && $text === '') {
        return '';
    }

    $titleSafe = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $textSafe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $imageSafe = htmlspecialchars($image !== '' ? $image : '/ChatGPT-Image-16-de-mai.-de-2026_-13_07_10.webp', ENT_QUOTES, 'UTF-8');
    $priceLabel = htmlspecialchars((string) ($view['checkout_price_label'] ?? '97,00'), ENT_QUOTES, 'UTF-8');
    $compareLabel = htmlspecialchars((string) ($view['checkout_compare_label'] ?? ''), ENT_QUOTES, 'UTF-8');

    return <<<HTML
<div class="promo-bleed-wrap" id="identificacao">
  <section class="promo-bleed" aria-label="Destaque da oferta">
    <div class="promo-bleed-media">
      <img src="{$imageSafe}" alt="" loading="lazy" decoding="async">
    </div>
    <div class="promo-bleed-body">
      <span class="promo-bleed-kicker">Comunidade VIP · Copa 2026</span>
      <h2 class="promo-bleed-title">{$titleSafe}</h2>
      <p class="promo-bleed-text">{$textSafe}</p>
      <div class="promo-bleed-price"><s>R$ {$compareLabel}</s><strong>R$ {$priceLabel}</strong></div>
      <a href="/checkout.php" class="promo-bleed-cta"><i class="ti ti-lock-access"></i> Garantir meu acesso</a>
    </div>
  </section>
</div>
HTML;
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

    $videoUrl = (string) ($raw['home_video_url'] ?? site_status_defaults()['home_video_url']);
    $videoEmbed = site_status_video_to_embed($videoUrl);
    if ($videoEmbed === '') {
        $videoEmbed = site_status_video_to_embed(site_status_defaults()['home_video_url']);
    }

    return [
        'home_layout' => site_status_valid_layout((string) $raw['home_layout']),
        'home_title' => $raw['home_title'],
        'home_message' => $raw['home_message'],
        'home_paragraphs' => $paragraphs !== [] ? $paragraphs : [site_status_defaults()['home_message']],
        'home_video_url' => $videoUrl,
        'home_video_embed' => $videoEmbed,
        'home_hero_show_video' => (bool) ($raw['home_hero_show_video'] ?? true),
        'home_hero_show_image' => (bool) ($raw['home_hero_show_image'] ?? false),
        'home_hero_image' => site_status_public_image_path((string) ($raw['home_hero_image'] ?? '')),
        'members_enabled' => (bool) $raw['members_enabled'],
        'checkout_price' => site_checkout_price(),
        'checkout_price_label' => site_format_price_brl(site_checkout_price()),
        'checkout_compare_price' => site_checkout_compare_price(),
        'checkout_compare_label' => site_format_price_brl(site_checkout_compare_price()),
        'promo_banner_enabled' => (bool) ($raw['promo_banner_enabled'] ?? true),
        'promo_banner_title' => trim((string) ($raw['promo_banner_title'] ?? site_status_defaults()['promo_banner_title'])),
        'promo_banner_text' => trim((string) ($raw['promo_banner_text'] ?? site_status_defaults()['promo_banner_text'])),
        'promo_banner_image' => trim((string) ($raw['promo_banner_image'] ?? site_status_defaults()['promo_banner_image'])),
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
function site_status_save(
    string $homeLayout,
    string $homeTitle,
    string $homeMessage,
    bool $membersEnabled,
    string $homeVideoUrl = '',
    ?array $offer = null
): array {
    $homeLayout = site_status_valid_layout($homeLayout);
    $homeTitle = trim($homeTitle);
    $homeMessage = trim($homeMessage);
    $homeVideoUrl = trim($homeVideoUrl);
    if ($homeTitle === '') {
        return ['ok' => false, 'error' => 'Informe um título para a página.'];
    }
    if ($homeLayout === 'simple' && $homeMessage === '') {
        return ['ok' => false, 'error' => 'Informe a mensagem da home simples.'];
    }
    $defaults = site_status_defaults();
    $hero = is_array($offer['_hero'] ?? null) ? $offer['_hero'] : [];
    unset($offer['_hero']);

    $showVideo = !empty($hero['home_hero_show_video']);
    $showImage = !empty($hero['home_hero_show_image']);
    $heroImage = site_status_public_image_path((string) ($hero['home_hero_image'] ?? ''));

    if ($homeLayout === 'full' && $showVideo && $homeVideoUrl !== '' && site_status_video_to_embed($homeVideoUrl) === '') {
        return ['ok' => false, 'error' => 'Link de vídeo inválido. Use YouTube ou Vimeo (ex.: https://www.youtube.com/watch?v=...)'];
    }
    if ($homeLayout === 'full' && !$showVideo && !$showImage) {
        return ['ok' => false, 'error' => 'Ative o vídeo ou a imagem no topo da landing.'];
    }
    if ($homeLayout === 'full' && $showImage && $heroImage === '') {
        return ['ok' => false, 'error' => 'Informe ou envie uma imagem (PNG, JPEG ou WebP) para o topo da landing.'];
    }
    if ($homeVideoUrl === '') {
        $homeVideoUrl = $defaults['home_video_url'];
    }

    $offer = $offer ?? [];
    $checkoutPrice = max(1, round((float) ($offer['checkout_price'] ?? $defaults['checkout_price']), 2));
    $comparePrice = (float) ($offer['checkout_compare_price'] ?? 0);
    if ($comparePrice <= $checkoutPrice) {
        $comparePrice = round($checkoutPrice * 2, 2);
    }

    site_status_sync_checkout_price_to_config($checkoutPrice);

    $payload = json_encode([
        'home_layout' => $homeLayout,
        'home_title' => $homeTitle,
        'home_message' => $homeMessage,
        'home_video_url' => $homeVideoUrl,
        'home_hero_show_video' => $homeLayout === 'full' && $showVideo,
        'home_hero_show_image' => $homeLayout === 'full' && $showImage,
        'home_hero_image' => $heroImage,
        'members_enabled' => $membersEnabled,
        'checkout_price' => $checkoutPrice,
        'checkout_compare_price' => $comparePrice,
        'promo_banner_enabled' => !empty($offer['promo_banner_enabled']),
        'promo_banner_title' => trim((string) ($offer['promo_banner_title'] ?? $defaults['promo_banner_title'])),
        'promo_banner_text' => trim((string) ($offer['promo_banner_text'] ?? $defaults['promo_banner_text'])),
        'promo_banner_image' => trim((string) ($offer['promo_banner_image'] ?? $defaults['promo_banner_image'])),
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
    if (!function_exists('pp_css_variables')) {
        require_once __DIR__ . '/theme.php';
    }
    return pp_css_variables() . pp_footer_styles() . <<<'CSS'
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;background:var(--pp-bg);color:var(--pp-text);font-family:var(--pp-font-body);line-height:1.7}
  .home-wrap{max-width:640px;margin:0 auto;padding:3rem 1.25rem 4rem}
  .home-brand{font-family:var(--pp-font-head);font-size:clamp(2.5rem,10vw,3.5rem);font-weight:800;letter-spacing:-.02em;color:var(--pp-text);margin-bottom:.25rem}
  .home-brand span{color:var(--pp-primary)}
  .home-rule{width:48px;height:4px;background:linear-gradient(90deg,var(--pp-primary),var(--pp-accent));margin:1.25rem 0 2rem;border-radius:2px}
  .home-content{font-size:17px;color:var(--pp-muted)}
  .home-content p{margin-bottom:1.25rem}
  .home-footer{margin-top:3rem;padding-top:1.5rem;border-top:1px solid var(--pp-border);font-size:13px;color:var(--pp-muted);text-align:center}
  .home-footer a{color:var(--pp-primary);text-decoration:none}
  .home-members{margin-top:2rem}
  .home-members a{display:inline-flex;align-items:center;gap:8px;color:var(--pp-accent);font-weight:600;font-size:14px;text-decoration:none}
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
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
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
    <a href="<?= htmlspecialchars(site_url('/'), ENT_QUOTES, 'UTF-8') ?>">← Voltar ao site</a>
    · <a href="<?= htmlspecialchars(comunidade_url('/login.php?admin=1'), ENT_QUOTES, 'UTF-8') ?>">Admin</a>
  </p>
</div>
</body>
</html>
    <?php
}
