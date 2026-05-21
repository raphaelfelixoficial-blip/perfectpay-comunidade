#!/bin/bash
# Execute no servidor como root ou agenciajob:
# bash autorizar-chave-deploy.sh

mkdir -p ~/.ssh
chmod 700 ~/.ssh
KEY='ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIILOJvuR7MFNpxK5A3xTZNX0XBOwPX8lQJUUF/6/MQ/I perfectpay-github-deploy'
grep -qF "$KEY" ~/.ssh/authorized_keys 2>/dev/null || echo "$KEY" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
BASE="/home/agenciajob/public_html/copa.agenciajob.com"
mkdir -p "$BASE/comunidade/data/sessions" "$BASE/comunidade/albuns" "$BASE/comunidade/arquivos"
chown -R agenciajob:agenciajob "$BASE/comunidade/data" 2>/dev/null || true
chmod 750 "$BASE/comunidade/data" 2>/dev/null || true
echo "Chave autorizada. Teste: ssh -i perfectpay_deploy root@5.78.75.201"
