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

Envio configurado para `noreply@agenciajob.com` (ajuste SMTP em `comunidade/data/config.php` no servidor).

## Deploy

- **Manual:** `bash scripts/deploy-local.sh`
- **GitHub Actions:** push em `main` (secrets: `SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY`)

## Primeira configuração no servidor

```bash
bash scripts/autorizar-chave-deploy.sh
cp comunidade/includes/config.php.example comunidade/data/config.php
# Edite config.php com senha SMTP e session_secret
```
