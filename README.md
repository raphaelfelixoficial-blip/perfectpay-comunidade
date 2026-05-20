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

## Integração Perfect Pay (compra → acesso automático)

1. **Página de obrigado** (pagamento aprovado):
   `https://perfectpay.agenciajob.com/obrigado.php`

2. **Página de boleto emitido** (quando o cliente gera boleto):
   `https://perfectpay.agenciajob.com/boleto-emitido.php`

3. **Webhook** (cadastro automático + e-mail com login/senha):
   `https://perfectpay.agenciajob.com/comunidade/webhook/perfectpay.php`

   No painel Perfect Pay: **Ferramentas → Webhook - Vendas → Adicionar**
   - URL: endpoint acima
   - Eventos: marque **Aprovado** (e opcionalmente **Completo**)
   - Copie o **token** do webhook para `perfectpay_webhook_token` em `comunidade/data/config.php`

4. **Teste na Perfect Pay:** o envio de teste do painel deles só reenvia uma venda **real** já aprovada na conta. Sem venda, aparece *“nenhuma venda com o evento desejado”*. Alternativas:
   - Simulador no admin: **Integração Perfect Pay → Simular venda aprovada**
   - Fazer uma compra teste (cartão/boleto) e aprovar

5. Quando a venda for aprovada (`sale_status_enum` = 2), o sistema:
   - cadastra o e-mail do comprador na comunidade
   - envia e-mail com link de login, e-mail e senha

## Deploy

- **Manual:** `bash scripts/deploy-local.sh`
- **GitHub Actions:** push em `main` (secrets: `SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY`)

## Primeira configuração no servidor

```bash
bash scripts/autorizar-chave-deploy.sh
cp comunidade/includes/config.php.example comunidade/data/config.php
# Edite config.php com senha SMTP e session_secret
```
