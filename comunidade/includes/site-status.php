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
        'promo_banner_image' => '/uploads/banner/figurinhas-copa.png',
        'site_favicon' => '/favicon.jpg',
        'updated_at' => 0,
    ];
}

function site_status_default_favicon(): string
{
    return '/favicon.jpg';
}

function site_status_path_ends_with(string $path, string $suffix): bool
{
    $len = strlen($suffix);
    if ($len === 0) {
        return true;
    }

    return substr($path, -$len) === $suffix;
}

function site_status_is_allowed_favicon_path(string $path): bool
{
    $path = strtolower($path);
    foreach (['png', 'jpg', 'jpeg', 'webp', 'ico'] as $ext) {
        if (site_status_path_ends_with($path, '.' . $ext)) {
            return true;
        }
    }

    return false;
}

/** @param array<string, mixed> $post @param array<string, mixed> $existing */
function site_status_favicon_from_post(array $post, array $existing): string
{
    $path = site_status_public_image_path(trim((string) ($post['site_favicon'] ?? '')));
    if ($path !== '') {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        if (site_status_is_allowed_favicon_path($path)) {
            return $path;
        }
    }

    $current = site_status_public_image_path((string) ($existing['site_favicon'] ?? ''));

    return $current !== '' ? $current : site_status_default_favicon();
}

function site_status_favicon_path(): string
{
    $raw = site_status_load_raw();
    $path = site_status_public_image_path(trim((string) ($raw['site_favicon'] ?? '')));

    return $path !== '' ? $path : site_status_default_favicon();
}

function site_status_favicon_mime(string $path): string
{
    $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?: $path, PATHINFO_EXTENSION));

    if ($ext === 'png') {
        return 'image/png';
    }
    if ($ext === 'webp') {
        return 'image/webp';
    }
    if ($ext === 'ico') {
        return 'image/x-icon';
    }
    if ($ext === 'svg') {
        return 'image/svg+xml';
    }

    return 'image/jpeg';
}

function site_status_favicon_url(): string
{
    $path = site_status_favicon_path();
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    if (function_exists('site_url')) {
        return site_url($path);
    }

    return $path;
}

function site_status_render_favicon_tags(): void
{
    $url = site_status_favicon_url();
    $mime = site_status_favicon_mime(site_status_favicon_path());
    $urlSafe = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $mimeSafe = htmlspecialchars($mime, ENT_QUOTES, 'UTF-8');
    echo '<link rel="icon" href="' . $urlSafe . '" type="' . $mimeSafe . '">' . "\n";
    echo '<link rel="shortcut icon" href="' . $urlSafe . '" type="' . $mimeSafe . '">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . $urlSafe . '">' . "\n";
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
        if (site_status_path_ends_with($path, '.' . $ext)) {
            return true;
        }
    }

    return false;
}

/** @return array<string, string> mime => ext */
function site_status_image_mime_map(): array
{
    return [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/webp' => 'webp',
    ];
}

/** Detecta MIME de imagem sem depender da extensão fileinfo do PHP. */
function site_status_detect_upload_image_mime(string $tmpPath, string $originalName = ''): string
{
    if ($tmpPath === '' || !is_file($tmpPath)) {
        return '';
    }

    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath) ?: '';
        if ($mime !== '') {
            return strtolower($mime);
        }
    }

    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($tmpPath);
        if (is_string($mime) && $mime !== '') {
            return strtolower($mime);
        }
    }

    $imageInfo = @getimagesize($tmpPath);
    if (is_array($imageInfo) && !empty($imageInfo['mime'])) {
        return strtolower((string) $imageInfo['mime']);
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $byExt = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
    ];

    return $byExt[$ext] ?? '';
}

/**
 * Salva upload de imagem (PNG, JPEG, WebP) em /uploads/.
 *
 * @param array<string, mixed> $file
 * @return array{ok:bool,path?:string,error?:string}
 */
function site_status_store_uploaded_image(array $file, string $subdir = 'uploads', bool $allowIco = false): array
{
    $tmp = (string) ($file['tmp_name'] ?? '');
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE || $tmp === '' || !is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => 'Nenhum arquivo enviado.'];
    }
    if ($error !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo maior que o limite do servidor.',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo maior que o limite do formulário.',
            UPLOAD_ERR_PARTIAL => 'Upload incompleto. Tente novamente.',
        ];
        return ['ok' => false, 'error' => $messages[$error] ?? ('Falha no upload (código ' . $error . ').')];
    }

    $mime = site_status_detect_upload_image_mime($tmp, (string) ($file['name'] ?? ''));
    $map = site_status_image_mime_map();
    if ($allowIco) {
        $map['image/x-icon'] = 'ico';
        $map['image/vnd.microsoft.icon'] = 'ico';
    }
    if ($mime === '' || !isset($map[$mime])) {
        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($allowIco && $ext === 'ico') {
            $mime = 'image/x-icon';
        }
    }
    if ($mime === '' || !isset($map[$mime])) {
        return ['ok' => false, 'error' => $allowIco ? 'Use PNG, JPEG, WebP ou ICO.' : 'Use PNG, JPEG ou WebP.'];
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
            'site_favicon' => site_status_public_image_path((string) ($raw['site_favicon'] ?? site_status_default_favicon())) ?: site_status_default_favicon(),
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
        'site_favicon' => site_status_default_favicon(),
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
.hero-media-image{width:100%;max-width:560px;margin:0 auto 1.5rem;line-height:0;overflow:hidden}
.hero-media-image img{width:100%;height:auto;display:block;vertical-align:middle;border:0}
.hero-media-image--solo img{max-height:min(70vh,420px);object-fit:cover}
.hero-media-image--below{margin-top:0;margin-bottom:1.25rem}
CSS;
}

function site_status_promo_banner_checkout_css(): string
{
    return <<<'CSS'
.promo-banner-checkout{width:100%;margin:0 0 1.25rem;line-height:0;border-radius:12px;overflow:hidden;border:1px solid #4a4028;box-shadow:0 8px 24px rgba(0,0,0,.35)}
.promo-banner-checkout img{width:100%;height:auto;display:block;vertical-align:middle}
CSS;
}

function site_status_promo_banner_css(): string
{
    return <<<'CSS'
.promo-banner-home-wrap{width:100vw;max-width:100vw;margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw);line-height:0;overflow:hidden;background:#0a0906}
.promo-banner-home-wrap img{width:100%;height:auto;display:block;vertical-align:middle}
CSS;
}

/** @param array<string, mixed> $view */
function site_status_render_promo_banner(array $view, bool $forCheckout = false): string
{
    if (empty($view['promo_banner_enabled'])) {
        return '';
    }

    $image = trim((string) ($view['promo_banner_image'] ?? ''));
    if ($image === '') {
        return '';
    }

    $imageSafe = htmlspecialchars(site_status_public_image_path($image), ENT_QUOTES, 'UTF-8');

    if ($forCheckout) {
        return <<<HTML
<div class="promo-banner-checkout">
  <img src="{$imageSafe}" alt="" loading="lazy" decoding="async">
</div>
HTML;
    }

    return <<<HTML
<div class="promo-banner-home-wrap" id="identificacao">
  <img src="{$imageSafe}" alt="" loading="lazy" decoding="async">
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
        'site_favicon' => site_status_favicon_path(),
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
        'site_favicon' => site_status_public_image_path(trim((string) ($offer['site_favicon'] ?? $defaults['site_favicon']))) ?: site_status_default_favicon(),
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
<?php site_status_render_favicon_tags(); ?>
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
