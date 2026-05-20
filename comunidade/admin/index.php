<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/albuns.php';
require_once dirname(__DIR__) . '/includes/site-status.php';
require_once dirname(__DIR__) . '/includes/mail.php';
require_once dirname(__DIR__) . '/includes/nav.php';
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
<title>Admin — Perfect Pay VIP</title>
<?php render_favicon(); ?>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  :root{--br-green:#009739;--br-yellow:#FFDF00;--br-blue:#002776}
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#050505;font-family:'Barlow',sans-serif;color:#eee;min-height:100vh}
  .wrap{max-width:900px;margin:0 auto;padding:2rem 1.25rem}
  header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem}
  h1{font-family:'Bebas Neue',sans-serif;font-size:36px;letter-spacing:2px}
  h1 span{color:var(--br-yellow)}
  .links a{color:#888;margin-left:1rem;font-size:14px;text-decoration:none}
  .links a:hover{color:var(--br-yellow)}
  .card{background:#141414;border:1px solid #2a2a2a;border-radius:8px;padding:1.5rem;margin-bottom:1.5rem}
  .card h2{font-family:'Bebas Neue',sans-serif;font-size:22px;margin-bottom:1rem;color:var(--br-yellow)}
  label{display:block;font-size:12px;color:#999;margin-bottom:6px;text-transform:uppercase}
  input,textarea{width:100%;padding:12px;border-radius:6px;border:1px solid #333;background:#0a0a0a;color:#fff;margin-bottom:1rem}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  @media(max-width:600px){.row{grid-template-columns:1fr}}
  .btn{padding:12px 20px;border:none;border-radius:6px;background:var(--br-yellow);color:var(--br-blue);font-family:'Bebas Neue',sans-serif;font-size:18px;letter-spacing:1px;cursor:pointer}
  .btn-danger{background:#dc3545;color:#fff}
  table{width:100%;border-collapse:collapse;font-size:14px}
  th,td{padding:10px 8px;text-align:left;border-bottom:1px solid #222}
  th{color:#888;font-size:11px;text-transform:uppercase}
  .flash{padding:12px;border-radius:6px;margin-bottom:1rem;background:rgba(0,151,57,.2);border:1px solid var(--br-green)}
  .pwd-box{background:#0d0d0d;padding:12px;border-radius:6px;font-family:monospace;color:var(--br-yellow);margin-top:8px;word-break:break-all}
  .hint{font-size:12px;color:#666;margin-top:-8px;margin-bottom:1rem}
  .page-nav{max-width:900px}
  <?= page_nav_styles() ?>
  <?= albuns_refresh_button_styles() ?>
</style>
</head>
<body>
<?php render_page_nav('admin'); ?>
<div class="wrap">
  <header>
    <h1>ADMIN <span>VIP</span></h1>
    <div class="links">
      <span style="color:#666;font-size:13px"><?= htmlspecialchars((string)$user['email'], ENT_QUOTES, 'UTF-8') ?></span>
      <a href="/">Área do membro</a>
      <a href="/logout.php">Sair</a>
    </div>
  </header>

  <?php if ($flash): ?>
  <div class="flash"><?= htmlspecialchars((string)$flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php render_albuns_refresh_button('/admin/'); ?>

  <div class="card">
    <h2>Página inicial (perfectpay.agenciajob.com)</h2>
    <p class="hint" style="margin-top:0">Escolha o tipo de página em <strong>perfectpay.agenciajob.com</strong>.</p>
    <form method="post" action="api.php">
      <input type="hidden" name="action" value="save_site_status">
      <label style="display:block;margin-bottom:.75rem;text-transform:none;font-size:14px;color:#ccc;font-weight:400">Tipo de página</label>
      <label style="display:flex;align-items:flex-start;gap:10px;margin-bottom:.75rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc;font-weight:400">
        <input type="radio" name="home_layout" value="simple" <?= ($siteStatusRaw['home_layout'] ?? 'simple') === 'simple' ? 'checked' : '' ?> style="width:auto;margin:4px 0 0">
        <span><strong style="color:#fff">Home simples (blog)</strong><br><span style="color:#888;font-size:12px">Só título e mensagem — ideal para avisos e encerramento</span></span>
      </label>
      <label style="display:flex;align-items:flex-start;gap:10px;margin-bottom:1rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc;font-weight:400">
        <input type="radio" name="home_layout" value="full" <?= ($siteStatusRaw['home_layout'] ?? '') === 'full' ? 'checked' : '' ?> style="width:auto;margin:4px 0 0">
        <span><strong style="color:#fff">Landing completa</strong><br><span style="color:#888;font-size:12px">Página longa original com vídeo, oferta, figurinhas e botões de compra</span></span>
      </label>
      <label for="home_title">Título (home simples)</label>
      <input type="text" id="home_title" name="home_title" value="<?= htmlspecialchars($siteStatusRaw['home_title'], ENT_QUOTES, 'UTF-8') ?>" required>
      <label for="home_message">Mensagem</label>
      <textarea id="home_message" name="home_message" rows="8" placeholder="Ex.: Encerramos as inscrições.&#10;&#10;Parabéns a quem garantiu o acesso!"><?= htmlspecialchars($siteStatusRaw['home_message'], ENT_QUOTES, 'UTF-8') ?></textarea>
      <label style="display:flex;align-items:center;gap:10px;margin-bottom:1rem;cursor:pointer;text-transform:none;font-size:14px;color:#ccc;font-weight:400">
        <input type="checkbox" name="members_enabled" value="1" <?= $siteStatusRaw['members_enabled'] ? 'checked' : '' ?> style="width:auto;margin:0">
        Manter área de membros ativa (comunidade)
      </label>
      <p class="hint">Desmarcado: área VIP fechada; admin entra por <a href="/login.php?admin=1" style="color:#FFDF00">login.php?admin=1</a></p>
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
      <p class="hint">E-mails enviados de noreply@agenciajob.com (alias da conta parceria)</p>
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
    <h2>E-mail Hostinger (SMTP)</h2>
    <?php if (smtp_is_configured()): ?>
      <p class="hint" style="color:#009739;margin-bottom:1rem">SMTP configurado — envio como noreply@agenciajob.com</p>
    <?php else: ?>
      <p class="hint" style="color:#dc3545;margin-bottom:1rem">SMTP não configurado — salve a senha da Hostinger para os e-mails chegarem.</p>
    <?php endif; ?>
    <form method="post" action="api.php">
      <input type="hidden" name="action" value="save_smtp">
      <label for="smtp_password">Senha SMTP (conta noreply@agenciajob.com na Hostinger)</label>
      <input type="password" id="smtp_password" name="smtp_password" required placeholder="Senha da conta principal">
      <p class="hint">Alias noreply@agenciajob.com — login SMTP com parceria@ + senha da caixa principal</p>
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
    <h2>Alterar senha do admin</h2>
    <form method="post" action="api.php">
      <input type="hidden" name="action" value="change_admin_password">
      <label for="new_password">Nova senha</label>
      <input type="password" id="new_password" name="new_password" required minlength="8">
      <button type="submit" class="btn">SALVAR NOVA SENHA</button>
    </form>
  </div>
</div>
<?php render_agency_footer(); ?>
</body>
</html>
