#!/usr/bin/env bash
# Deploy manuale del tema Torresan BnB su hosting condiviso Aruba via FTPS.
# Richiede: lftp (apt install lftp / brew install lftp)
# Configurazione: variabili in .env (ARUBA_FTP_*) o esportate a mano.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

if [ -f .env ]; then
  # shellcheck disable=SC1091
  set -a; source .env; set +a
fi

: "${ARUBA_FTP_SERVER:?Imposta ARUBA_FTP_SERVER (host FTP Aruba)}"
: "${ARUBA_FTP_USERNAME:?Imposta ARUBA_FTP_USERNAME}"
: "${ARUBA_FTP_PASSWORD:?Imposta ARUBA_FTP_PASSWORD}"
ARUBA_FTP_REMOTE_DIR="${ARUBA_FTP_REMOTE_DIR:-/wp-content/themes/torresan-bnb/}"

LOCAL_DIR="wordpress/wp-content/themes/torresan-bnb"

echo "==> Build asset (CSS/JS minificati)"
npm run build

echo "==> Sincronizzazione $LOCAL_DIR -> ${ARUBA_FTP_SERVER}${ARUBA_FTP_REMOTE_DIR} (FTPS)"
lftp -u "${ARUBA_FTP_USERNAME},${ARUBA_FTP_PASSWORD}" "ftps://${ARUBA_FTP_SERVER}" <<EOF
set ssl:verify-certificate yes
set ftp:ssl-force true
mirror --reverse --verbose --delete \
  --exclude-glob .git* \
  --exclude-glob node_modules/ \
  --exclude-glob "*.map" \
  "${LOCAL_DIR}/" "${ARUBA_FTP_REMOTE_DIR}"
bye
EOF

echo "==> Deploy completato."
