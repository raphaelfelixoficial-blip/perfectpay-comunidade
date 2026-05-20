#!/bin/bash
# Sincroniza index.html e backup de dados da produção para o repo local.
# Uso: bash scripts/sincronizar-do-servidor.sh root@5.78.75.201

set -euo pipefail
REMOTE="${1:-root@5.78.75.201}"
BASE="/home/agenciajob/public_html/perfectpay.agenciajob.com"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

mkdir -p "$ROOT/backup-servidor/data"

rsync -avz \
  "$REMOTE:$BASE/index.html" \
  "$ROOT/public_html/index.html"

rsync -avz \
  "$REMOTE:$BASE/comunidade/data/allowed_emails.json" \
  "$ROOT/backup-servidor/data/" 2>/dev/null || true

rsync -avz \
  "$REMOTE:$BASE/comunidade/data/downloads.json" \
  "$ROOT/backup-servidor/data/" 2>/dev/null || true

echo "Sincronizado. Revise public_html/index.html antes de commitar."
