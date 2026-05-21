#!/bin/bash
# Copia o site de perfectpay.agenciajob.com para copa.agenciajob.com e atualiza config.
set -euo pipefail

HOST="${DEPLOY_HOST:-root@5.78.75.201}"
OLD="/home/agenciajob/public_html/perfectpay.agenciajob.com"
NEW="/home/agenciajob/public_html/copa.agenciajob.com"
SSH_KEY="${DEPLOY_KEY:-$HOME/.ssh/perfectpay_deploy}"
SSH_OPTS=(-i "$SSH_KEY" -o StrictHostKeyChecking=accept-new)

echo "→ Copiar arquivos de ${OLD} para ${NEW}"
ssh "${SSH_OPTS[@]}" "$HOST" "mkdir -p '$NEW' && rsync -a '$OLD/' '$NEW/' && chown -R agenciajob:agenciajob '$NEW'"

echo "→ Atualizar URLs em config.php"
ssh "${SSH_OPTS[@]}" "$HOST" "cd '$NEW/comunidade' && php scripts/migrate-domain.php"

echo "→ Redirecionamento 301 no domínio antigo (perfectpay)"
ssh "${SSH_OPTS[@]}" "$HOST" "cat > '$OLD/.htaccess' <<'HT'
RewriteEngine On
RewriteCond %{HTTP_HOST} ^perfectpay\\.agenciajob\\.com\$ [NC]
RewriteRule ^(.*)\$ https://copa.agenciajob.com/\$1 [R=301,L]
HT
chown agenciajob:agenciajob '$OLD/.htaccess'"

echo "Migração base concluída. Rode scripts/deploy-local.sh para publicar o código atualizado."
