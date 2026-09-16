#!/usr/bin/env bash
# Crea il pacchetto di migrazione da portare sull'istanza Oracle.
#
# Uso (da qualsiasi directory):   scripts/deploy/package.sh [dir-output]
#
# Produce un unico tarball con:
#   - il repo (compose, docker/, tema, script)  senza node_modules/.git/mysql
#   - wp-content: uploads, plugins, languages, themes
#   - deploy/db.sql       dump del database locale
#   - deploy/.env.prod    ambiente di produzione con segreti NUOVI
#
# Include il core WordPress: l'installazione locale si e' auto-aggiornata a
# 6.9.4 mentre l'immagine ne fornisce 6.8.3, e lasciar ripopolare i file
# all'entrypoint farebbe girare codice 6.8.3 su uno schema DB 6.9.4.
# Escluso invece wp-config.php, rigenerato sul server con i segreti di
# produzione, e i backup All-in-One (473 MB inutili sul server).
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
OUT_DIR="${1:-$REPO_DIR/backups}"
STAMP="$(date +%Y%m%d-%H%M)"
STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

cd "$REPO_DIR"
# shellcheck disable=SC1091
set -a; source .env; set +a

echo "==> Dump del database da ${PROJECT_NAME}_db"
mkdir -p "$STAGE/deploy"
docker exec "${PROJECT_NAME}_db" mariadb-dump \
	--user=root --password="$MYSQL_ROOT_PASSWORD" \
	--single-transaction --quick --default-character-set=utf8mb4 \
	--add-drop-table --routines --events \
	"$WORDPRESS_DB_NAME" > "$STAGE/deploy/db.sql"
echo "    $(du -h "$STAGE/deploy/db.sql" | cut -f1)"

echo "==> Generazione .env di produzione (segreti rigenerati)"
rnd() { openssl rand -base64 48 | tr -d '\n=+/' | cut -c1-48; }
cat > "$STAGE/deploy/.env.prod" <<EOF
PROJECT_NAME=$PROJECT_NAME
# WordPress non e' esposto direttamente: ci arriva solo l'edge nginx.
WORDPRESS_PORT=127.0.0.1:8081
# L'edge ascolta su loopback, davanti c'e' tailscale serve.
EDGE_BIND=127.0.0.1:8080
WORDPRESS_DB_NAME=$WORDPRESS_DB_NAME
WORDPRESS_DB_USER=$WORDPRESS_DB_USER
# Istanza da 1 GB condivisa con altri stack: MariaDB tenuta al minimo.
DB_BUFFER_POOL=64M
DB_PERF_SCHEMA=OFF
WORDPRESS_DB_PASSWORD=$(rnd)
MYSQL_ROOT_PASSWORD=$(rnd)
# Sostituito da server-restore.sh con l'FQDN Tailscale reale.
WP_HOME=__WP_URL__
WP_SITEURL=__WP_URL__
WORDPRESS_TABLE_PREFIX=$WORDPRESS_TABLE_PREFIX
WORDPRESS_AUTH_KEY=$(rnd)
WORDPRESS_SECURE_AUTH_KEY=$(rnd)
WORDPRESS_LOGGED_IN_KEY=$(rnd)
WORDPRESS_NONCE_KEY=$(rnd)
WORDPRESS_AUTH_SALT=$(rnd)
WORDPRESS_SECURE_AUTH_SALT=$(rnd)
WORDPRESS_LOGGED_IN_SALT=$(rnd)
WORDPRESS_NONCE_SALT=$(rnd)
EOF
# URL di origine, serve al search-replace lato server.
echo "$WP_HOME" > "$STAGE/deploy/source-url.txt"

echo "==> Creazione tarball"
mkdir -p "$OUT_DIR"
TARBALL="$OUT_DIR/alusonic-deploy-$STAMP.tgz"
tar czf "$TARBALL" \
	--exclude='./.git' \
	--exclude='./.env' \
	--exclude='./node_modules' \
	--exclude='./mysql' \
	--exclude='./backups' \
	--exclude='./tmp-book.html' \
	--exclude='./wordpress/wp-config.php' \
	--exclude='./wordpress/_import_artists' \
	--exclude='./wordpress/wp-content/ai1wm-backups' \
	--exclude='./wordpress/wp-content/upgrade' \
	--exclude='./wordpress/wp-content/cache' \
	--exclude='./wordpress/wp-content/*.log' \
	-C "$REPO_DIR" . \
	-C "$STAGE" ./deploy

echo
echo "Pacchetto pronto: $TARBALL  ($(du -h "$TARBALL" | cut -f1))"
echo "Contiene segreti: trasferiscilo solo sulla tailnet."
