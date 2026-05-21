#!/bin/bash
# Deploy local → produção (sem --delete: preserva uploads e PDFs)
set -euo pipefail

HOST="${DEPLOY_HOST:-root@5.78.75.201}"
BASE="/home/agenciajob/public_html/copa.agenciajob.com"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SSH_KEY="${DEPLOY_KEY:-$HOME/.ssh/perfectpay_deploy}"
SSH_OPTS=(-i "$SSH_KEY" -o StrictHostKeyChecking=accept-new)

if ! command -v rsync >/dev/null 2>&1; then
  echo "Instale rsync (Git Bash / WSL) para usar este script."
  exit 1
fi

echo "→ Site principal"
rsync -avz -e "ssh ${SSH_OPTS[*]}" \
  --exclude '.git' \
  --exclude 'data/site-status.json' \
  "$ROOT/public_html/" \
  "$HOST:$BASE/"

echo "→ Comunidade"
rsync -avz -e "ssh ${SSH_OPTS[*]}" \
  --exclude 'data/allowed_emails.json' \
  --exclude 'data/downloads.json' \
  --exclude 'data/albuns-catalog.json' \
  --exclude 'data/site-status.json' \
  --exclude 'data/config.php' \
  --exclude 'data/sessions/' \
  --exclude 'data/mail.log' \
  --exclude 'albuns/' \
  --exclude 'arquivos/' \
  "$ROOT/comunidade/" \
  "$HOST:$BASE/comunidade/"

echo "→ PHP dos álbuns"
rsync -avz -e "ssh ${SSH_OPTS[*]}" \
  "$ROOT/comunidade/albuns/index.php" \
  "$ROOT/comunidade/albuns/ver.php" \
  "$ROOT/comunidade/albuns/atualizar-lista.php" \
  "$ROOT/comunidade/albuns/.htaccess" \
  "$HOST:$BASE/comunidade/albuns/"

echo "→ Admin"
rsync -avz -e "ssh ${SSH_OPTS[*]}" \
  "$ROOT/comunidade/admin/" \
  "$HOST:$BASE/comunidade/admin/"

ssh "${SSH_OPTS[@]}" "$HOST" "mkdir -p $BASE/comunidade/data/sessions $BASE/comunidade/albuns $BASE/comunidade/arquivos && chown -R agenciajob:agenciajob $BASE/comunidade/data $BASE/comunidade/albuns 2>/dev/null || true"

echo "Deploy concluído: https://copa.agenciajob.com"
