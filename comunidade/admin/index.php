<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/albuns.php';
require_once dirname(__DIR__) . '/includes/site-status.php';
require_once dirname(__DIR__) . '/includes/mail.php';
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
<title>Admin — Perfect Pay VIP</title>
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
        <span><strong style="color:#fff">Landing completa</strong><br><span style="color:#888;font-size:12px">Página longa com vídeo, oferta, benefícios e botões de compra</span></span>
      </label>
      <label for="home_title">Título (home simples)</label>
      <input type="text" id="home_title" name="home_title" value="<?= htmlspecialchars($siteStatusRaw['home_title'], ENT_QUOTES, 'UTF-8') ?>" required>
      <label for="home_message">Mensagem</label>
      <textarea id="home_message" name="home_message" rows="8" placeholder="Ex.: Encerramos as inscrições.&#10;&#10;Parabéns a quem garantiu o acesso!"><?= htmlspecialchars($siteStatusRaw['home_message'], ENT_QUOTES, 'UTF-8') ?></textarea>
      <label for="home_video_url">Vídeo da home (landing completa)</label>
      <input type="url" id="home_video_url" name="home_video_url" value="<?= htmlspecialchars((string)($siteStatusRaw['home_video_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https://www.youtube.com/watch?v=... ou link do Vimeo">
      <p class="hint">Cole o link do YouTube ou Vimeo. Só aparece quando o tipo de página for <strong>Landing completa</strong>.</p>
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
      <p class="hint">E-mails enviados de noreply@agenciajob.com · respostas para suporte@agenciajob.com</p>
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
      <p class="hint smtp-ok" style="margin-bottom:1rem">SMTP configurado — envio como noreply@agenciajob.com (servidor)</p>
    <?php else: ?>
      <p class="hint" style="color:#dc3545;margin-bottom:1rem">SMTP não configurado — salve a senha da caixa noreply@agenciajob.com (cPanel).</p>
    <?php endif; ?>
    <form method="post" action="api.php">
      <input type="hidden" name="action" value="save_smtp">
      <label for="smtp_password">Senha SMTP (caixa noreply@agenciajob.com no cPanel)</label>
      <input type="password" id="smtp_password" name="smtp_password" required placeholder="Senha do e-mail no servidor">
      <p class="hint">Login: noreply@agenciajob.com · host localhost:587 (TLS) · admin do painel: suporte@agenciajob.com</p>
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
    <h2><i class="ti ti-plug-connected"></i> Integração Perfect Pay</h2>
    <p class="hint" style="margin-top:0">Após compra aprovada, o webhook libera acesso e envia e-mail com login e senha.</p>
    <label>Página de obrigado — pagamento aprovado</label>
    <input type="text" readonly value="https://perfectpay.agenciajob.com/obrigado.php" onclick="this.select()">
    <label style="margin-top:1rem">Página de boleto emitido</label>
    <input type="text" readonly value="https://perfectpay.agenciajob.com/boleto-emitido.php" onclick="this.select()">
    <label>Webhook (URL em Ferramentas → Webhook - Vendas)</label>
    <input type="text" readonly value="https://perfectpay.agenciajob.com/comunidade/webhook/perfectpay.php" onclick="this.select()">
    <p class="hint">No painel Perfect Pay: marque o produto/checkout <strong>PPU38CQC76U</strong>, evento <strong>Aprovado (status 2)</strong>, cole a URL do webhook e o mesmo token de <code>config.php</code>. Sem isso a venda não cadastra sozinha.</p>
    <p class="hint">Log de entregas: <code>comunidade/data/perfectpay-webhook.log</code> · E-mails: <code>mail.log</code></p>
    <p class="hint" style="margin-top:.75rem"><strong>E-mail caindo no spam?</strong> No cPanel → <em>E-mail → Entregabilidade de e-mail</em>: ative <strong>DKIM</strong> e <strong>SPF</strong> para <code>agenciajob.com</code> e corrija o <strong>DMARC</strong>. Remetente deve ser <code>noreply@agenciajob.com</code> (mesmo domínio do DKIM).</p>
    <p class="hint" style="color:#c9a227;margin-top:.75rem"><strong>Teste na Perfect Pay:</strong> o botão de teste deles só funciona se existir uma <em>venda real</em> com status Aprovado na conta — por isso aparece “nenhuma venda com o evento desejado”. Use o simulador abaixo ou faça uma compra teste.</p>
    <form method="post" action="api.php" style="margin-top:1rem;padding-top:1rem;border-top:1px solid #2a2a2a">
      <input type="hidden" name="action" value="simulate_webhook">
      <label for="webhook_test_email">Simular compra aprovada (mesmo fluxo do webhook)</label>
      <div class="row">
        <div>
          <input type="email" id="webhook_test_email" name="test_email" required placeholder="email@comprador.com" value="<?= htmlspecialchars((string)($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <input type="text" name="test_name" placeholder="Nome do comprador (opcional)">
        </div>
      </div>
      <button type="submit" class="btn">SIMULAR VENDA APROVADA</button>
      <p class="hint">Cadastra o e-mail, gera senha e envia o e-mail de acesso (se SMTP estiver OK).</p>
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
