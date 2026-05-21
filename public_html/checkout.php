<?php

declare(strict_types=1);

$asaasBootstrap = __DIR__ . '/comunidade/includes/bootstrap.php';
if (!is_file($asaasBootstrap)) {
    $asaasBootstrap = dirname(__DIR__) . '/comunidade/includes/bootstrap.php';
}
require_once $asaasBootstrap;
$asaasInclude = __DIR__ . '/comunidade/includes/asaas.php';
if (!is_file($asaasInclude)) {
    $asaasInclude = dirname(__DIR__) . '/comunidade/includes/asaas.php';
}
require_once $asaasInclude;

if (!asaas_is_configured()) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Checkout indisponível</title></head><body style="font-family:sans-serif;padding:2rem;text-align:center">';
    echo '<h1>Checkout em configuração</h1><p>Configure a chave API Asaas no painel admin.</p>';
    echo '<p><a href="/">Voltar ao site</a></p></body></html>';
    exit;
}

if (asaas_checkout_mode() === 'redirect') {
    $email = normalize_email((string) ($_GET['email'] ?? ''));
    $name = trim((string) ($_GET['name'] ?? ''));
    $GLOBALS['asaas_checkout_cpf'] = (string) ($_GET['cpf'] ?? '');
    $result = asaas_create_checkout_session($email, $name);
    if (!$result['ok'] || empty($result['link'])) {
        http_response_code(502);
        header('Content-Type: text/html; charset=utf-8');
        $err = htmlspecialchars((string) ($result['error'] ?? 'Erro ao iniciar pagamento.'), ENT_QUOTES, 'UTF-8');
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro</title></head><body style="font-family:sans-serif;padding:2rem;text-align:center">';
        echo '<h1>Não foi possível abrir o pagamento</h1><p>' . $err . '</p><p><a href="/">Voltar</a></p></body></html>';
        exit;
    }
    header('Location: ' . $result['link'], true, 302);
    exit;
}

require_once __DIR__ . '/comunidade/includes/site-status.php';
if (!function_exists('site_checkout_price')) {
    require_once dirname(__DIR__) . '/comunidade/includes/site-status.php';
}
$siteView = site_status_view();
$product = asaas_checkout_product();
$priceLabel = site_format_price_brl($product['value']);
$compare = site_checkout_compare_price();
$compareLabel = $compare > $product['value'] ? site_format_price_brl($compare) : '';
$thankyouBase = rtrim((string) (app_config()['asaas_thankyou_url'] ?? site_url('/obrigado.php')), '/');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Finalizar compra — Figurinhas da Copa</title>
<?php site_status_render_favicon_tags(); ?>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
:root{--bg:#12100a;--card:#1c1810;--border:#4a4028;--text:#faf6e8;--muted:#b8a878;--gold:#FFDF00;--green:#ca8a04;--err:#f87171}
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:var(--bg);background-image:radial-gradient(ellipse 80% 50% at 50% -20%,rgba(250,204,21,.12),transparent);font-family:'DM Sans',system-ui,sans-serif;color:var(--text);padding:clamp(1rem,4vw,2rem)}
.wrap{max-width:480px;margin:0 auto}
.back{display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:13px;text-decoration:none;margin-bottom:1.25rem}
.back:hover{color:var(--gold)}
.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.5rem;box-shadow:0 20px 50px rgba(0,0,0,.35)}
.product{display:flex;gap:14px;align-items:flex-start;padding-bottom:1.25rem;border-bottom:1px solid var(--border);margin-bottom:1.25rem}
.product-icon{width:52px;height:52px;border-radius:12px;background:linear-gradient(135deg,var(--green),#3d3208);display:flex;align-items:center;justify-content:center;font-size:26px;color:var(--gold);flex-shrink:0}
.product h1{font-family:'Syne',sans-serif;font-size:1.15rem;line-height:1.3;margin-bottom:4px}
.product p{font-size:13px;color:var(--muted);line-height:1.4}
.price{margin-top:6px;font-family:'Syne',sans-serif;font-size:1.25rem;font-weight:700;color:var(--gold);line-height:1.2}
.price s{font-size:.85rem;color:var(--muted);font-weight:500;margin-right:6px}
label{display:block;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
input{width:100%;padding:12px 14px;border-radius:10px;border:1px solid var(--border);background:#0f0d08;color:var(--text);font-size:15px;margin-bottom:12px}
input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 2px rgba(255,223,0,.2)}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.tabs{display:flex;gap:8px;margin:1rem 0}
.tab{flex:1;padding:12px;border-radius:10px;border:1px solid var(--border);background:#0f0d08;color:var(--muted);font-weight:600;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s}
.tab.active{border-color:var(--gold);color:var(--gold);background:rgba(255,223,0,.08)}
.panel{display:none}
.panel.active{display:block}
.btn{width:100%;padding:12px 16px;border:none;border-radius:10px;background:linear-gradient(135deg,var(--gold),#fde047);color:#1a1400;font-family:'DM Sans',system-ui,sans-serif;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;margin-top:4px}
.btn:disabled{opacity:.55;cursor:not-allowed}
.btn-ghost{background:transparent;border:1px solid var(--border);color:var(--text);margin-top:8px}
.qr-box{text-align:center;padding:1rem 0}
.qr-box img{max-width:220px;border-radius:12px;background:#fff;padding:10px}
.pix-copy{display:flex;gap:8px;margin-top:12px}
.pix-copy input{font-size:12px;margin:0}
.pix-copy button{flex-shrink:0;padding:12px 14px;border-radius:10px;border:1px solid var(--gold);background:rgba(255,223,0,.1);color:var(--gold);font-weight:700;cursor:pointer;white-space:nowrap}
.status{text-align:center;padding:12px;border-radius:10px;background:rgba(0,151,57,.12);border:1px solid rgba(250,204,21,.35);color:var(--gold);font-size:14px;margin-top:12px;display:none}
.status.show{display:block}
.status.error{background:rgba(248,113,113,.1);border-color:var(--err);color:var(--err)}
.msg{font-size:13px;color:var(--err);margin-top:8px;min-height:1.2em}
.secure{text-align:center;margin-top:1rem;font-size:11px;color:var(--muted);display:flex;align-items:center;justify-content:center;gap:6px}
.loader{display:inline-block;width:18px;height:18px;border:2px solid rgba(0,0,0,.2);border-top-color:#1a1400;border-radius:50%;animation:spin .7s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
@media(max-width:400px){.field-row{grid-template-columns:1fr}}
<?= site_status_promo_banner_checkout_css() ?>
</style>
</head>
<body class="checkout-page">
<div class="wrap">
  <a href="/" class="back"><i class="ti ti-arrow-left"></i> Voltar ao site</a>
  <?= site_status_render_promo_banner($siteView, true) ?>
</div>
<div class="wrap">
  <div class="card">
    <div class="product">
      <div class="product-icon"><i class="ti ti-trophy"></i></div>
      <div>
        <h1><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?></p>
        <div class="price">
          <?php if ($compareLabel !== ''): ?><s>R$ <?= htmlspecialchars($compareLabel, ENT_QUOTES, 'UTF-8') ?></s><?php endif ?>
          R$ <?= htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8') ?>
        </div>
      </div>
    </div>

    <form id="checkout-form" autocomplete="on">
      <label for="name">Nome completo</label>
      <input type="text" id="name" name="name" required placeholder="Seu nome" autocomplete="name">

      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" required placeholder="seu@email.com" autocomplete="email">

      <label for="cpf">CPF</label>
      <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00" inputmode="numeric" maxlength="14" autocomplete="off">

      <div class="tabs" role="tablist">
        <button type="button" class="tab active" data-tab="pix"><i class="ti ti-qrcode"></i> Pix</button>
        <button type="button" class="tab" data-tab="card"><i class="ti ti-credit-card"></i> Cartão</button>
      </div>

      <div id="panel-pix" class="panel active">
        <p style="font-size:13px;color:var(--muted);margin-bottom:12px">Pague em segundos. Após confirmar, liberamos seu acesso automaticamente.</p>
        <button type="button" class="btn" id="btn-pix"><i class="ti ti-qrcode"></i> Gerar QR Code Pix</button>
        <div id="pix-area" class="qr-box" hidden>
          <img id="pix-qr" alt="QR Code Pix" width="220" height="220">
          <div class="pix-copy">
            <input type="text" id="pix-payload" readonly>
            <button type="button" id="btn-copy">Copiar</button>
          </div>
          <p style="font-size:12px;color:var(--muted);margin-top:10px">Abra o app do banco, escaneie ou cole o código Pix.</p>
        </div>
      </div>

      <div id="panel-card" class="panel">
        <label for="card-number">Número do cartão</label>
        <input type="text" id="card-number" inputmode="numeric" placeholder="0000 0000 0000 0000" maxlength="19" autocomplete="cc-number">

        <label for="card-name">Nome no cartão</label>
        <input type="text" id="card-name" placeholder="Como está no cartão" autocomplete="cc-name">

        <div class="field-row">
          <div>
            <label for="card-exp">Validade</label>
            <input type="text" id="card-exp" placeholder="MM/AA" maxlength="5" autocomplete="cc-exp">
          </div>
          <div>
            <label for="card-cvv">CVV</label>
            <input type="text" id="card-cvv" inputmode="numeric" placeholder="123" maxlength="4" autocomplete="cc-csc">
          </div>
        </div>
        <button type="button" class="btn" id="btn-card"><i class="ti ti-lock"></i> Pagar com cartão</button>
      </div>

      <p class="msg" id="form-msg" role="alert"></p>
      <div class="status" id="pay-status"></div>
    </form>

    <p class="secure"><i class="ti ti-shield-lock"></i> Pagamento seguro · Acesso liberado após confirmação</p>
  </div>
</div>
<script>
(function () {
  const API = '/api/checkout.php';
  const thankyouBase = <?= json_encode($thankyouBase, JSON_UNESCAPED_UNICODE) ?>;
  let paymentId = '';
  let pollTimer = null;

  const $ = (id) => document.getElementById(id);
  const formMsg = $('form-msg');
  const payStatus = $('pay-status');

  function showMsg(text, isError) {
    formMsg.textContent = text || '';
    formMsg.style.color = isError ? 'var(--err)' : 'var(--muted)';
  }

  function showStatus(text, isError) {
    payStatus.textContent = text;
    payStatus.classList.add('show');
    payStatus.classList.toggle('error', !!isError);
  }

  function customerPayload() {
    return {
      name: $('name').value.trim(),
      email: $('email').value.trim(),
      cpf: $('cpf').value.replace(/\D/g, ''),
    };
  }

  function validateCustomer() {
    const p = customerPayload();
    if (!p.name || !p.email || p.cpf.length !== 11) {
      showMsg('Preencha nome, e-mail e CPF válido.', true);
      return null;
    }
    return p;
  }

  function setLoading(btn, on) {
    btn.disabled = on;
    if (on) {
      btn.dataset.label = btn.innerHTML;
      btn.innerHTML = '<span class="loader"></span> Aguarde...';
    } else if (btn.dataset.label) {
      btn.innerHTML = btn.dataset.label;
    }
  }

  document.querySelectorAll('.tab').forEach((tab) => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.tab').forEach((t) => t.classList.remove('active'));
      document.querySelectorAll('.panel').forEach((p) => p.classList.remove('active'));
      tab.classList.add('active');
      $('panel-' + tab.dataset.tab).classList.add('active');
      showMsg('');
    });
  });

  $('cpf').addEventListener('input', (e) => {
    let v = e.target.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 9) v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
    else if (v.length > 6) v = v.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
    else if (v.length > 3) v = v.replace(/(\d{3})(\d{0,3})/, '$1.$2');
    e.target.value = v;
  });

  $('card-number').addEventListener('input', (e) => {
    let v = e.target.value.replace(/\D/g, '').slice(0, 16);
    e.target.value = v.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
  });

  $('card-exp').addEventListener('input', (e) => {
    let v = e.target.value.replace(/\D/g, '').slice(0, 4);
    if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
    e.target.value = v;
  });

  async function post(action, extra) {
    const body = Object.assign({ action }, customerPayload(), extra || {});
    const res = await fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok && !data.error) {
      data.ok = false;
      data.error = res.status === 400 ? 'Requisição inválida. Recarregue a página.' : ('Erro ' + res.status);
    }
    return data;
  }

  function stopPoll() {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = null;
  }

  function startPoll() {
    stopPoll();
    showStatus('Aguardando confirmação do Pix...', false);
    pollTimer = setInterval(async () => {
      try {
        const data = await post('status', { payment_id: paymentId });
        if (data.paid && data.redirect) {
          stopPoll();
          showStatus('Pagamento confirmado! Redirecionando...', false);
          window.location.href = data.redirect;
        }
      } catch (e) { /* ignore */ }
    }, 3000);
  }

  $('btn-pix').addEventListener('click', async () => {
    const p = validateCustomer();
    if (!p) return;
    const btn = $('btn-pix');
    setLoading(btn, true);
    showMsg('');
    try {
      const data = await post('pix');
      if (!data.ok) {
        showMsg(data.error || 'Erro ao gerar Pix.', true);
        return;
      }
      paymentId = data.payment_id;
      $('pix-area').hidden = false;
      $('pix-qr').src = 'data:image/png;base64,' + data.encoded_image;
      $('pix-payload').value = data.pix_copy || '';
      startPoll();
      showMsg('');
    } catch (e) {
      showMsg('Falha de conexão. Tente novamente.', true);
    } finally {
      setLoading(btn, false);
    }
  });

  $('btn-copy').addEventListener('click', async () => {
    const val = $('pix-payload').value;
    try {
      await navigator.clipboard.writeText(val);
      $('btn-copy').textContent = 'Copiado!';
      setTimeout(() => { $('btn-copy').textContent = 'Copiar'; }, 2000);
    } catch (e) {
      $('pix-payload').select();
      document.execCommand('copy');
    }
  });

  $('btn-card').addEventListener('click', async () => {
    const p = validateCustomer();
    if (!p) return;
    const exp = $('card-exp').value.split('/');
    const btn = $('btn-card');
    setLoading(btn, true);
    showMsg('');
    stopPoll();
    try {
      const data = await post('card', {
        card: {
          holderName: $('card-name').value.trim(),
          number: $('card-number').value.replace(/\D/g, ''),
          expiryMonth: (exp[0] || '').trim(),
          expiryYear: (exp[1] || '').trim(),
          ccv: $('card-cvv').value.replace(/\D/g, ''),
        },
      });
      if (!data.ok) {
        showMsg(data.error || 'Pagamento recusado.', true);
        return;
      }
      paymentId = data.payment_id || '';
      if (data.paid && data.redirect) {
        showStatus('Aprovado! Redirecionando...', false);
        window.location.href = data.redirect;
        return;
      }
      showStatus('Processando cartão...', false);
      if (paymentId) startPoll();
      showMsg(data.message || '', false);
    } catch (e) {
      showMsg('Falha de conexão. Tente novamente.', true);
    } finally {
      setLoading(btn, false);
    }
  });
})();
</script>
</body>
</html>
