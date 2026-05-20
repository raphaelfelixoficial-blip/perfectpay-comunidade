# Perfect Pay — Comunidade

Cópia adaptada do [swatec-site](https://github.com/raphaelfelixoficial-blip/swatec-site) para a área de membros e landing em **perfectpay.agenciajob.com**.

## Estrutura

| Pasta | Deploy no servidor |
|-------|-------------------|
| `public_html/` | `/home/agenciajob/public_html/perfectpay.agenciajob.com/` |
| `comunidade/` | `/home/agenciajob/public_html/perfectpay.agenciajob.com/comunidade/` |

- Site: https://perfectpay.agenciajob.com
- Comunidade (login): https://perfectpay.agenciajob.com/comunidade/login.php

## E-mail

- **Admin (login painel):** `suporte@agenciajob.com`
- **Envio automático:** `noreply@agenciajob.com` (caixa no servidor cPanel — não Hostinger)
- **Respostas:** `suporte@agenciajob.com`

Configure a senha SMTP da caixa `noreply@` no painel admin ou em `comunidade/data/config.php`.

### Entregabilidade (evitar spam)

No cPanel do domínio **agenciajob.com** (não só o subdomínio perfectpay):

1. **E-mail → Entregabilidade de e-mail** (Email Deliverability)
2. Clique em **Gerenciar** no domínio `agenciajob.com`
3. Ative/registre **DKIM** e **SPF** (botões “Ativar” ou registros DNS sugeridos)
4. Corrija **DMARC** — adicione o registro TXT `_dmarc.agenciajob.com` que o cPanel sugerir (comece com `p=none` para monitorar)
5. Envie sempre como `noreply@agenciajob.com` com SMTP autenticado dessa caixa

Teste em [mail-tester.com](https://www.mail-tester.com) após configurar DNS (aguarde até 24h de propagação).

## Pagamento (Asaas)

Único meio de pagamento: **Asaas** (Pix + cartão via checkout).

- **Botões da home:** https://perfectpay.agenciajob.com/checkout.php  
  Cria uma sessão de checkout Asaas (Pix + cartão) e redireciona para pagamento.
- **Webhook:** https://perfectpay.agenciajob.com/comunidade/webhook/asaas.php  
  Eventos: `CHECKOUT_PAID`, `PAYMENT_CONFIRMED`, `PAYMENT_RECEIVED`
- **Config:** `comunidade/data/config.php` (`asaas_api_key`, `asaas_webhook_token`, `asaas_checkout_value`)
- **Admin:** seção *Checkout Asaas* — salvar chave, token e simular pagamento

### Cadastrar webhook no Asaas

1. Painel Asaas → **Integrações → Webhooks → Adicionar**
2. URL: endpoint acima
3. Marque os eventos de pagamento/checkout listados
4. Defina um **authToken** e cole o mesmo valor em `asaas_webhook_token` (admin ou config.php)
5. Teste: admin → **Simular pagamento Asaas** ou compra real em `/checkout.php`
6. Após pagamento confirmado: cadastra o membro e envia e-mail com login e senha (`obrigado.php`)

## Deploy

- **Manual:** `bash scripts/deploy-local.sh`
- **GitHub Actions:** push em `main` (secrets: `SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY`)

## Primeira configuração no servidor

```bash
bash scripts/autorizar-chave-deploy.sh
cp comunidade/includes/config.php.example comunidade/data/config.php
# Edite config.php com senha SMTP e session_secret
```
