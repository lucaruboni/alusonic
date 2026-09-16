#!/usr/bin/env bash
# Wrapper per lanciare cutout.py con il virtualenv dedicato.
# Uso: scripts/cutout/cutout.sh [--force] [--id MODEL_ID]
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
VENV="$ROOT_DIR/scripts/.venv-cutout"

if [ ! -x "$VENV/bin/python" ]; then
  echo "Virtualenv non trovato in $VENV. Creazione..."
  python3 -m venv "$VENV"
  "$VENV/bin/pip" install --quiet --upgrade pip
  "$VENV/bin/pip" install --quiet rembg onnxruntime pillow
fi

exec "$VENV/bin/python" "$ROOT_DIR/scripts/cutout/cutout.py" "$@"
