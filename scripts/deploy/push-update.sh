#!/usr/bin/env bash
# Sincronizza modifiche locali -> demo pubblica su sommelier-1, senza
# reinstallare nulla (i container restano quelli gia' avviati).
#
# Uso:
#   scripts/deploy/push-update.sh --code                 # solo tema (veloce, la scelta piu' comune)
#   scripts/deploy/push-update.sh --code --media          # tema + nuove foto/upload
#   scripts/deploy/push-update.sh --code --media --db     # tutto, DB locale sovrascrive quello remoto
#
# --db esporta il database locale e SOSTITUISCE quello sulla demo: usalo solo
# quando hai fatto modifiche a prodotti/pagine/contenuti in locale che vuoi
# portare sulla demo. Se qualcuno ha lasciato commenti o modifiche fatte
# DIRETTAMENTE sulla demo (es. da wp-admin), --db le cancella.
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_DIR"

HOST="ubuntu@sommelier-1.tail1583df.ts.net"
REMOTE_DIR="~/alusonic"
PUBLIC_URL="https://sommelier-1.tail1583df.ts.net:8443"

DO_CODE=0
DO_MEDIA=0
DO_DB=0
for arg in "$@"; do
	case "$arg" in
		--code)  DO_CODE=1 ;;
		--media) DO_MEDIA=1 ;;
		--db)    DO_DB=1 ;;
		*) echo "Argomento sconosciuto: $arg"; exit 1 ;;
	esac
done
if [ "$DO_CODE" = 0 ] && [ "$DO_MEDIA" = 0 ] && [ "$DO_DB" = 0 ]; then
	echo "Uso: $0 [--code] [--media] [--db]  (almeno uno)"
	exit 1
fi

if [ "$DO_CODE" = 1 ]; then
	echo "==> Sincronizzo il tema"
	rsync -az --delete -e ssh --rsync-path="sudo rsync" \
		wordpress/wp-content/themes/torresan-bnb/ \
		"$HOST:$REMOTE_DIR/wordpress/wp-content/themes/torresan-bnb/"
	ssh "$HOST" "sudo chown -R 33:33 $REMOTE_DIR/wordpress/wp-content/themes/torresan-bnb"
fi

if [ "$DO_MEDIA" = 1 ]; then
	echo "==> Sincronizzo i media (uploads)"
	rsync -az -e ssh --rsync-path="sudo rsync" \
		wordpress/wp-content/uploads/ \
		"$HOST:$REMOTE_DIR/wordpress/wp-content/uploads/"
	ssh "$HOST" "sudo chown -R 33:33 $REMOTE_DIR/wordpress/wp-content/uploads"
fi

if [ "$DO_DB" = 1 ]; then
	echo "==> Esporto il database locale"
	set -a; source .env; set +a
	DUMP="$(mktemp)"
	trap 'rm -f "$DUMP"' EXIT
	docker exec "${PROJECT_NAME}_db" mariadb-dump \
		--user=root --password="$MYSQL_ROOT_PASSWORD" \
		--single-transaction --quick --default-character-set=utf8mb4 \
		--add-drop-table --routines --events \
		"$WORDPRESS_DB_NAME" > "$DUMP"
	echo "    $(du -h "$DUMP" | cut -f1)"

	echo "==> Trasferisco e importo sul server"
	scp "$DUMP" "$HOST:$REMOTE_DIR/deploy/local-db.sql"
	ssh "$HOST" "cd $REMOTE_DIR && set -a; source .env; set +a; \
		docker exec -i \${PROJECT_NAME}_db mariadb --user=root --password=\"\$MYSQL_ROOT_PASSWORD\" \
		--default-character-set=utf8mb4 \"\$WORDPRESS_DB_NAME\" < deploy/local-db.sql && \
		rm -f deploy/local-db.sql"

	echo "==> Riscrivo gli URL locale -> pubblico"
	LOCAL_URL="$(grep '^WP_HOME=' .env | cut -d= -f2-)"
	ssh "$HOST" "cd $REMOTE_DIR && docker compose --profile tools run --rm cli \
		wp search-replace '$LOCAL_URL' '$PUBLIC_URL' --all-tables --precise --skip-columns=guid"
fi

echo "==> Flush cache"
ssh "$HOST" "cd $REMOTE_DIR && docker compose --profile tools run --rm cli wp cache flush && \
	docker compose --profile tools run --rm cli wp rewrite flush --hard"

echo
echo "Fatto. $PUBLIC_URL"
