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

## Deploy

- **Manual:** `bash scripts/deploy-local.sh`
- **GitHub Actions:** push em `main` (secrets: `SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY`)

## Primeira configuração no servidor

```bash
bash scripts/autorizar-chave-deploy.sh
cp comunidade/includes/config.php.example comunidade/data/config.php
# Edite config.php com senha SMTP e session_secret
```
