# Comunidade Figurinhas da Copa



Área de membros e landing em **copa.agenciajob.com**.



## Estrutura



| Pasta | Deploy no servidor |

|-------|-------------------|

| `public_html/` | `/home/agenciajob/public_html/copa.agenciajob.com/` |

| `comunidade/` | `/home/agenciajob/public_html/copa.agenciajob.com/comunidade/` |



- Site: https://copa.agenciajob.com

- Comunidade (login): https://copa.agenciajob.com/comunidade/login.php



## E-mail



- **Admin (login painel):** `suporte@agenciajob.com`

- **Envio automático:** `suporte@agenciajob.com` (SMTP `mail.agenciajob.com:465`)

- **Respostas:** `suporte@agenciajob.com`



Configure a senha SMTP da caixa `suporte@` no painel admin ou em `comunidade/data/config.php`.



### Entregabilidade (evitar spam)



No cPanel do domínio **agenciajob.com**:



1. **E-mail → Entregabilidade de e-mail** (Email Deliverability)

2. Ative **DKIM** e **SPF** para `agenciajob.com`

3. Configure **DMARC** conforme sugestão do cPanel



## Pagamento (Asaas)



- **Checkout:** https://copa.agenciajob.com/checkout.php

- **Webhook:** https://copa.agenciajob.com/comunidade/webhook/asaas.php

- **Config:** `comunidade/data/config.php` (`asaas_api_key`, `asaas_webhook_token`)

- **Admin:** *Checkout Asaas* — registrar webhook, sincronizar pagamentos, simular



## Deploy



- **Manual:** `bash scripts/deploy-local.sh`

- **GitHub Actions:** push em `main` (secrets: `SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY`)



## Migração de domínio (perfectpay → copa)



No servidor, copiar dados do site antigo e atualizar URLs:



```bash

OLD=/home/agenciajob/public_html/perfectpay.agenciajob.com

NEW=/home/agenciajob/public_html/copa.agenciajob.com

rsync -a "$OLD/" "$NEW/"

cd "$NEW/comunidade" && php scripts/migrate-domain.php

```



Depois rode `bash scripts/deploy-local.sh` do repositório local.



## Primeira configuração no servidor



```bash

bash scripts/autorizar-chave-deploy.sh

cp comunidade/includes/config.php.example comunidade/data/config.php

# Edite config.php com senha SMTP e session_secret

```


