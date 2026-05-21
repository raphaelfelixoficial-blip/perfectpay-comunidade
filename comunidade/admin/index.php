<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/albuns.php';
require_once dirname(__DIR__) . '/includes/site-status.php';
require_once dirname(__DIR__) . '/includes/mail.php';
require_once dirname(__DIR__) . '/includes/asaas.php';
require_once dirname(__DIR__) . '/includes/nav.php';
require_once dirname(__DIR__) . '/includes/theme.php';
require_admin();
$user = session_user();
$members = load_members();
$siteStatusRaw = site_status_load_raw();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Admin — Figurinhas da Copa VIP</title>
<?php render_favicon(); ?>
<?= pp_fonts_link() ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
<?= pp_admin_shell_styles() ?>
<?= albuns_refresh_button_styles() ?>
</style>
</head>
<body>
<?php render_page_nav('admin'); ?>
<div class="wrap">
  <header>
    <h1 class="admin-title">Admin <span>VIP</span></h1>
    <div class="links">
      <span style="color:#666;font-size:13px"><?= htmlspecialchars((string)$user['email'], ENT_QUOTES, 'UTF-8') ?></span>
      <a href="<?= htmlspecialchars(comunidade_url('/'), ENT_QUOTES, 'UTF-8') ?>">Área do membro</a>
      <a href="<?= htmlspecialchars(comunidade_url('/logout.php'), ENT_QUOTES, 'UTF-8') ?>">Sair</a>
    </div>
  </header>

  <?php if ($flash): ?>
  <div class="flash"><?= htmlspecialchars((string)$flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php render_albuns_refresh_button('/admin/'); ?>

  <div class="card">
    <h2>Página inicial (<?= htmlspecialchars(parse_url(site_base_url(), PHP_URL_HOST) ?: 'copa.agenciajob.com', ENT_QUOTES, 'UTF-8') ?>)</h2>
    <p class="hint" style="margin-top:0">Escolha o tipo de página em <strong><?= htmlspecialchars(site_base_url(), ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    <form method="post" action="api.php" enctype="multipart/form-data">
      <input type="hidden" name="action" value="save_site_status">
      <label style="display:block;margin-bottom:.75rem;text-transform:none;font-size:14px;color:#ccc;font-weight:400">Tipo de página</label>
      <label style="display:flex;align-items:flex-start;gap:10px;margin-bottom:.75rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc;font-weight:400">
        <input type="radio" name="home_layout" value="simple" <?= ($siteStatusRaw['home_layout'] ?? 'simple') === 'simple' ? 'checked' : '' ?> style="width:auto;margin:4px 0 0">
        <span><strong style="color:#fff">Home simples (blog)</strong><br><span style="color:#888;font-size:12px">Só título e mensagem — ideal para avisos e encerramento</span></span>
      </label>
      <label style="display:flex;align-items:flex-start;gap:10px;margin-bottom:1rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc;font-weight:400">
        <input type="radio" name="home_layout" value="full" <?= ($siteStatusRaw['home_layout'] ?? '') === 'full' ? 'checked' : '' ?> style="width:auto;margin:4px 0 0">
        <span><strong style="color:#fff">Landing completa</strong><br><span style="color:#888;font-size:12px">Página longa com vídeo, oferta, benefícios e botões de compra</span></span>
      </label>
      <h3 style="margin:0 0 .75rem;font-size:15px;color:#FFDF00;text-transform:uppercase;letter-spacing:.08em">Favicon do site</h3>
      <p class="hint" style="margin-top:0">Ícone nas abas do navegador (home, checkout, área de membros). PNG, JPEG, WebP ou ICO.</p>
      <?php
      $faviconPath = site_status_public_image_path((string) ($siteStatusRaw['site_favicon'] ?? site_status_default_favicon()));
      if ($faviconPath === '') {
          $faviconPath = site_status_default_favicon();
      }
      $faviconPreview = preg_match('#^https?://#i', $faviconPath) ? $faviconPath : site_url($faviconPath);
      ?>
      <p style="margin:0 0 .75rem"><img src="<?= htmlspecialchars($faviconPreview, ENT_QUOTES, 'UTF-8') ?>" alt="" width="32" height="32" style="vertical-align:middle;border-radius:4px;background:#222"></p>
      <label for="site_favicon">URL do favicon</label>
      <input type="text" id="site_favicon" name="site_favicon" value="<?= htmlspecialchars($faviconPath, ENT_QUOTES, 'UTF-8') ?>" placeholder="/favicon.jpg ou /uploads/favicon/meu-icone.png">
      <label for="site_favicon_file">Ou enviar arquivo</label>
      <input type="file" id="site_favicon_file" name="site_favicon_file" accept="image/png,image/jpeg,image/webp,image/x-icon,.png,.jpg,.jpeg,.webp,.ico" style="margin-bottom:1.25rem">

      <label for="home_title">Título (home simples)</label>
      <input type="text" id="home_title" name="home_title" value="<?= htmlspecialchars($siteStatusRaw['home_title'], ENT_QUOTES, 'UTF-8') ?>" required>
      <label for="home_message">Mensagem</label>
      <textarea id="home_message" name="home_message" rows="8" placeholder="Ex.: Encerramos as inscrições.&#10;&#10;Parabéns a quem garantiu o acesso!"><?= htmlspecialchars($siteStatusRaw['home_message'], ENT_QUOTES, 'UTF-8') ?></textarea>
      <h3 style="margin:1.25rem 0 .75rem;font-size:15px;color:#FFDF00;text-transform:uppercase;letter-spacing:.08em">Vídeo e imagem no topo (hero)</h3>
      <p class="hint" style="margin-top:0">Só na <strong>Landing completa</strong>. Vídeo, imagem ou os dois (imagem abaixo do vídeo).</p>
      <label style="display:flex;align-items:center;gap:10px;margin-bottom:.75rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc;font-weight:400">
        <input type="checkbox" name="home_hero_show_video" value="1" <?= !isset($siteStatusRaw['home_hero_show_video']) || !empty($siteStatusRaw['home_hero_show_video']) ? 'checked' : '' ?> style="width:auto;margin:0">
        Exibir vídeo (YouTube ou Vimeo)
      </label>
      <label for="home_video_url">Link do vídeo</label>
      <input type="url" id="home_video_url" name="home_video_url" value="<?= htmlspecialchars((string)($siteStatusRaw['home_video_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https://www.youtube.com/watch?v=...">
      <label style="display:flex;align-items:center;gap:10px;margin:1rem 0 .75rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc;font-weight:400">
        <input type="checkbox" name="home_hero_show_image" value="1" <?= !empty($siteStatusRaw['home_hero_show_image']) ? 'checked' : '' ?> style="width:auto;margin:0">
        Exibir imagem abaixo do vídeo (ou só imagem, se o vídeo estiver desmarcado)
      </label>
      <label for="home_hero_image">URL da imagem no site</label>
      <input type="text" id="home_hero_image" name="home_hero_image" value="<?= htmlspecialchars((string)($siteStatusRaw['home_hero_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="/uploads/hero/minha-imagem.webp">
      <p class="hint">Formatos: PNG, JPEG ou WebP. Caminho começando com <code>/</code> (ex.: <code>/uploads/hero/foto.png</code>).</p>
      <label for="home_hero_image_file">Ou enviar arquivo</label>
      <input type="file" id="home_hero_image_file" name="home_hero_image_file" accept="image/png,image/jpeg,image/webp,.png,.jpg,.jpeg,.webp" style="margin-bottom:1rem">

      <h3 style="margin:1.5rem 0 .75rem;font-size:15px;color:#FFDF00;text-transform:uppercase;letter-spacing:.08em">Preço do checkout (Asaas)</h3>
      <p class="hint" style="margin-top:0">Escolha o valor cobrado nos botões da landing e em <code>/checkout.php</code>.</p>
      <?php
      $currentPrice = (float) ($siteStatusRaw['checkout_price'] ?? 97);
      $pricePresets = site_checkout_price_presets();
      $priceIsCustom = !in_array($currentPrice, $pricePresets, true);
      ?>
      <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:1rem">
        <?php foreach ($pricePresets as $preset): ?>
        <label style="display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border:1px solid #333;border-radius:6px;cursor:pointer;text-transform:none;font-size:13px;color:#ccc;font-weight:600;background:#0a0a0a">
          <input type="radio" name="checkout_price_preset" value="<?= htmlspecialchars((string) $preset, ENT_QUOTES, 'UTF-8') ?>" <?= !$priceIsCustom && abs($currentPrice - $preset) < 0.01 ? 'checked' : '' ?> style="width:auto;margin:0">
          R$ <?= htmlspecialchars(site_format_price_brl($preset), ENT_QUOTES, 'UTF-8') ?>
        </label>
        <?php endforeach; ?>
        <label style="display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border:1px solid #333;border-radius:6px;cursor:pointer;text-transform:none;font-size:13px;color:#ccc;font-weight:600;background:#0a0a0a">
          <input type="radio" name="checkout_price_preset" value="custom" <?= $priceIsCustom ? 'checked' : '' ?> style="width:auto;margin:0">
          Outro
        </label>
      </div>
      <label for="checkout_price_custom">Valor personalizado (R$)</label>
      <input type="text" id="checkout_price_custom" name="checkout_price_custom" value="<?= htmlspecialchars(site_format_price_brl($currentPrice), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex.: 29,90">
      <label for="checkout_compare_price">Preço “de” riscado (opcional)</label>
      <input type="text" id="checkout_compare_price" name="checkout_compare_price" value="<?= htmlspecialchars(site_format_price_brl((float)($siteStatusRaw['checkout_compare_price'] ?? 0)), ENT_QUOTES, 'UTF-8') ?>" placeholder="Vazio = o dobro do preço atual">

      <h3 style="margin:1.5rem 0 .75rem;font-size:15px;color:#FFDF00;text-transform:uppercase;letter-spacing:.08em">Banner (só imagem — landing e checkout)</h3>
      <p class="hint" style="margin-top:0">Mesma imagem na home e no checkout, <strong>sem texto</strong> por cima. Use arte já pronta (PNG/JPEG/WebP).</p>
      <label style="display:flex;align-items:center;gap:10px;margin-bottom:1rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc;font-weight:400">
        <input type="checkbox" name="promo_banner_enabled" value="1" <?= !empty($siteStatusRaw['promo_banner_enabled']) ? 'checked' : '' ?> style="width:auto;margin:0">
        Exibir banner na landing e no checkout
      </label>
      <label for="promo_banner_image">Imagem do banner (URL no site)</label>
      <input type="text" id="promo_banner_image" name="promo_banner_image" value="<?= htmlspecialchars((string)($siteStatusRaw['promo_banner_image'] ?? '/uploads/banner/figurinhas-copa.png'), ENT_QUOTES, 'UTF-8') ?>" placeholder="/uploads/banner/minha-arte.png">
      <p class="hint">Ex.: <code>/uploads/banner/figurinhas-copa.png</code></p>

      <label style="display:flex;align-items:center;gap:10px;margin-bottom:1rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc;font-weight:400">
        <input type="checkbox" name="members_enabled" value="1" <?= $siteStatusRaw['members_enabled'] ? 'checked' : '' ?> style="width:auto;margin:0">
        Manter área de membros ativa (comunidade)
      </label>
      <p class="hint">Desmarcado: área VIP fechada; admin entra por <a href="<?= htmlspecialchars(comunidade_url('/login.php?admin=1'), ENT_QUOTES, 'UTF-8') ?>" style="color:#FFDF00">login com ?admin=1</a></p>
      <button type="submit" class="btn">SALVAR PÁGINA</button>
    </form>
  </div>

  <div class="card">
    <h2>Adicionar membro</h2>
    <form method="post" action="api.php">
      <input type="hidden" name="action" value="add">
      <div class="row">
        <div>
          <label for="email">E-mail autorizado (vários separados por vírgula)</label>
          <input type="text" id="email" name="email" required placeholder="cliente@email.com, outro@email.com">
        </div>
        <div>
          <label for="name">Nome (opcional)</label>
          <input type="text" id="name" name="name" placeholder="Nome do membro">
        </div>
      </div>
      <label for="password">Senha de acesso (deixe vazio para gerar automaticamente)</label>
      <input type="text" id="password" name="password" placeholder="Gerar automaticamente">
      <label style="display:flex;align-items:center;gap:10px;margin-bottom:1rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc">
        <input type="checkbox" name="send_email" value="1" checked style="width:auto;margin:0">
        Enviar e-mail automático com link de login, e-mail e senha
      </label>
      <p class="hint">E-mails enviados de suporte@agenciajob.com</p>
      <button type="submit" class="btn">ADICIONAR E-MAIL</button>
    </form>
  </div>

  <div class="card">
    <h2>Importar lista de e-mails</h2>
    <form method="post" action="api.php">
      <input type="hidden" name="action" value="import">
      <label for="emails">E-mails separados por vírgula, ponto e vírgula ou um por linha</label>
      <textarea id="emails" name="emails" rows="6" placeholder="email1@exemplo.com, email2@exemplo.com&#10;email3@exemplo.com"></textarea>
      <label style="display:flex;align-items:center;gap:10px;margin-bottom:1rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc">
        <input type="checkbox" name="send_email" value="1" checked style="width:auto;margin:0">
        Enviar e-mail para cada novo membro importado
      </label>
      <button type="submit" class="btn">IMPORTAR LISTA</button>
    </form>
  </div>

  <div class="card">
    <h2>Membros autorizados (<?= count($members) ?>)</h2>
    <?php if (empty($members)): ?>
      <p style="color:#666">Nenhum e-mail cadastrado ainda.</p>
    <?php else: ?>
    <table>
      <thead><tr><th>E-mail</th><th>Nome</th><th>Desde (Brasília)</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($members as $m): ?>
        <tr>
          <td><?= htmlspecialchars((string)($m['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string)($m['name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars(format_member_added_at($m['added_at'] ?? null), ENT_QUOTES, 'UTF-8') ?></td>
          <td>
            <form method="post" action="api.php" style="display:inline" onsubmit="return confirm('Remover este membro?')">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="email" value="<?= htmlspecialchars((string)($m['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              <button type="submit" class="btn btn-danger" style="padding:6px 12px;font-size:14px">Remover</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>E-mail do servidor (SMTP)</h2>
    <?php if (smtp_is_configured()): ?>
      <p class="hint smtp-ok" style="margin-bottom:1rem">SMTP configurado — envio como suporte@agenciajob.com (servidor)</p>
    <?php else: ?>
      <p class="hint" style="color:#dc3545;margin-bottom:1rem">SMTP não configurado — salve a senha da caixa suporte@agenciajob.com (cPanel).</p>
    <?php endif; ?>
    <form method="post" action="api.php">
      <input type="hidden" name="action" value="save_smtp">
      <label for="smtp_password">Senha SMTP (caixa suporte@agenciajob.com no cPanel)</label>
      <input type="password" id="smtp_password" name="smtp_password" required placeholder="Senha do e-mail no servidor">
      <p class="hint">Login: suporte@agenciajob.com · host mail.agenciajob.com:465 (SSL). Use a senha do e-mail no cPanel (não a senha do painel do site).</p>
      <p class="hint" style="color:#f0ad4e">Se o teste falhar com &quot;535 Incorrect authentication data&quot;, a senha SMTP está errada — salve a senha correta acima.</p>
      <button type="submit" class="btn">SALVAR SENHA SMTP</button>
    </form>
    <form method="post" action="api.php" style="margin-top:1rem">
      <input type="hidden" name="action" value="test_email">
      <label for="test_email">Enviar e-mail de teste para</label>
      <div class="row">
        <div><input type="email" id="test_email" name="test_email" required placeholder="seu@email.com"></div>
        <div style="display:flex;align-items:flex-end"><button type="submit" class="btn" style="width:100%">TESTAR ENVIO</button></div>
      </div>
    </form>
  </div>

  <div class="card">
    <h2><i class="ti ti-credit-card"></i> Checkout Asaas</h2>
    <?php if (asaas_is_configured()): ?>
      <p class="hint smtp-ok" style="margin-bottom:1rem">API Asaas configurada — checkout ativo em <code>/checkout.php</code></p>
    <?php else: ?>
      <p class="hint" style="color:#dc3545;margin-bottom:1rem">Chave API Asaas não configurada.</p>
    <?php endif; ?>
    <form method="post" action="api.php">
      <input type="hidden" name="action" value="save_asaas">
      <label for="asaas_api_key">Chave API Asaas (access_token)</label>
      <input type="password" id="asaas_api_key" name="asaas_api_key" placeholder="Cole a chave de Integrações → API" autocomplete="off">
      <p class="hint">Deixe em branco para manter a chave já salva no servidor.</p>
      <label for="asaas_webhook_token">Token do webhook (authToken no painel Asaas)</label>
      <input type="text" id="asaas_webhook_token" name="asaas_webhook_token" placeholder="Token que você define ao criar o webhook" value="<?= htmlspecialchars((string)(app_config()['asaas_webhook_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <p class="hint" style="margin-bottom:1rem">Preço da oferta: configure em <strong>Página inicial → Preço do checkout</strong> (acima).</p>
      <div class="row">
        <div>
          <label>Preço ativo agora</label>
          <input type="text" readonly value="R$ <?= htmlspecialchars(site_format_price_brl(site_checkout_price()), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label for="asaas_environment">Ambiente</label>
          <select id="asaas_environment" name="asaas_environment" style="width:100%;padding:10px;background:#0a0a0a;border:1px solid #333;color:#fff;border-radius:6px">
            <option value="production" <?= (app_config()['asaas_environment'] ?? 'production') === 'production' ? 'selected' : '' ?>>Produção</option>
            <option value="sandbox" <?= (app_config()['asaas_environment'] ?? '') === 'sandbox' ? 'selected' : '' ?>>Sandbox (testes)</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn">SALVAR ASAAS</button>
    </form>
    <label style="margin-top:1rem">URL do checkout (botões da home)</label>
    <input type="text" readonly value="<?= htmlspecialchars(asaas_checkout_url(), ENT_QUOTES, 'UTF-8') ?>" onclick="this.select()">
    <label style="margin-top:1rem">Webhook no Asaas (nome: <code><?= htmlspecialchars((string)(app_config()['asaas_webhook_name'] ?? 'asaas-figu'), ENT_QUOTES, 'UTF-8') ?></code>)</label>
    <input type="text" readonly value="<?= htmlspecialchars(asaas_webhook_url(), ENT_QUOTES, 'UTF-8') ?>" onclick="this.select()">
    <p class="hint">Eventos recomendados: <code>CHECKOUT_PAID</code>, <code>PAYMENT_CONFIRMED</code>, <code>PAYMENT_RECEIVED</code>. O Asaas envia o header <code>asaas-access-token</code> com o mesmo token acima.</p>
    <label style="margin-top:1rem">Página de obrigado (após pagamento)</label>
    <input type="text" readonly value="<?= htmlspecialchars(rtrim((string)(app_config()['asaas_thankyou_url'] ?? asaas_site_base_url() . '/obrigado.php'), '/'), ENT_QUOTES, 'UTF-8') ?>" onclick="this.select()">
    <p class="hint">Checkout: <strong>transparente</strong> em <code>/checkout.php</code> (PIX com QR na página + cartão, sem redirecionar ao Asaas). Em <code>config.php</code>, <code>checkout_mode</code> = <code>redirect</code> volta ao checkout hospedado Asaas.</p>
    <p class="hint">Após pagamento aprovado: cadastra membro e envia e-mail automaticamente. Log: <code>comunidade/data/asaas-webhook.log</code> · E-mails: <code>mail.log</code></p>
    <p class="hint" style="color:#f0ad4e">Se o pagamento foi confirmado no Asaas mas o membro não foi criado, o webhook provavelmente não chegou ao servidor. Use os botões abaixo.</p>
    <div class="row" style="margin-top:0.75rem">
      <div>
        <form method="post" action="api.php">
          <input type="hidden" name="action" value="register_asaas_webhook">
          <button type="submit" class="btn">REGISTRAR/REATIVAR WEBHOOK NO ASAAS</button>
        </form>
      </div>
      <div>
        <form method="post" action="api.php">
          <input type="hidden" name="action" value="reconcile_asaas">
          <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:13px;color:#aaa">
            <input type="checkbox" name="send_email" value="1" style="width:auto;margin:0">
            Enviar e-mail ao sincronizar (desmarque — só cadastra)
          </label>
          <button type="submit" class="btn">SINCRONIZAR PAGAMENTOS (30 DIAS)</button>
        </form>
      </div>
    </div>
    <form method="post" action="api.php" style="margin-top:1rem;padding-top:1rem;border-top:1px solid #2a2a2a">
      <input type="hidden" name="action" value="simulate_asaas_webhook">
      <label for="asaas_test_email">Simular pagamento confirmado</label>
      <div class="row">
        <div>
          <input type="email" id="asaas_test_email" name="test_email" required placeholder="email@comprador.com" value="<?= htmlspecialchars((string)($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <input type="text" name="test_name" placeholder="Nome (opcional)">
        </div>
      </div>
      <label style="display:flex;align-items:center;gap:8px;margin:8px 0;font-size:13px;color:#aaa">
        <input type="checkbox" name="send_email" value="1" style="width:auto;margin:0">
        Enviar e-mail na simulação (marque só para testar SMTP)
      </label>
      <button type="submit" class="btn">SIMULAR PAGAMENTO ASAAS</button>
    </form>
  </div>

  <div class="card">
    <h2>Alterar senha do admin</h2>
    <form method="post" action="api.php">
      <input type="hidden" name="action" value="change_admin_password">
      <label for="new_password">Nova senha</label>
      <input type="password" id="new_password" name="new_password" required minlength="8">
      <button type="submit" class="btn">SALVAR NOVA SENHA</button>
    </form>
  </div>
</div>
<?php render_pp_footer(); ?>
</body>
</html>
