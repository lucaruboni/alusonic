#!/usr/bin/env bash
# Ripristina il sito sull'istanza Oracle a partire dal pacchetto scompattato.
# Va lanciato dalla root del progetto (quella che contiene docker-compose.yml).
#
# Uso:  bash scripts/deploy/server-restore.sh [fqdn-tailscale] [porta-https]
# Senza argomenti l'FQDN viene letto da `tailscale status` e la porta e' 443.
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_DIR"

[ -f deploy/db.sql ]   || { echo "Manca deploy/db.sql";   exit 1; }
[ -f deploy/.env.prod ] || { echo "Manca deploy/.env.prod"; exit 1; }

# ── URL pubblico ────────────────────────────────────────────────────────────
FQDN="${1:-}"
if [ -z "$FQDN" ]; then
	# Solo il nodo Self: un grep sul JSON grezzo puo' pescare un peer.
	FQDN="$(tailscale status --json \
		| python3 -c 'import json,sys; print(json.load(sys.stdin)["Self"]["DNSName"].rstrip("."))' \
		2>/dev/null || true)"
fi
[ -n "$FQDN" ] || { echo "FQDN Tailscale non rilevato: passalo come argomento."; exit 1; }

TS_PORT="${2:-443}"
if [ "$TS_PORT" = 443 ]; then NEW_URL="https://$FQDN"; else NEW_URL="https://$FQDN:$TS_PORT"; fi

# Non sovrascrivere un serve gia' attivo su questa porta per un altro servizio:
# `tailscale serve` sostituisce la mappatura senza chiedere conferma.
EXISTING="$(sudo tailscale serve status 2>/dev/null || true)"
if echo "$EXISTING" | grep -q "https://$FQDN:$TS_PORT\b" \
	|| { [ "$TS_PORT" = 443 ] && echo "$EXISTING" | grep -qE "https://$FQDN( |$)"; }; then
	if ! echo "$EXISTING" | grep -q '127.0.0.1:8080'; then
		echo "ERRORE: la porta $TS_PORT di $FQDN e' gia' usata da un altro servizio:"
		echo "$EXISTING"
		echo "Rilancia scegliendo un'altra porta, es.:"
		echo "  bash scripts/deploy/server-restore.sh '' 8443"
		exit 1
	fi
fi
OLD_URL="$(cat deploy/source-url.txt 2>/dev/null || echo 'http://localhost:8082')"
echo "==> Migrazione  $OLD_URL  ->  $NEW_URL"

# ── .env di produzione ──────────────────────────────────────────────────────
if [ -f .env ]; then
	echo "==> .env esistente conservato in .env.bak-$(date +%s)"
	cp .env ".env.bak-$(date +%s)"
fi
sed "s#__WP_URL__#$NEW_URL#g" deploy/.env.prod > .env
chmod 600 .env
# shellcheck disable=SC1091
set -a; source .env; set +a

# ── Datadir MariaDB ─────────────────────────────────────────────────────────
# Se ./mysql contiene gia' un database, MariaDB ignora le password nell'env e
# l'import finirebbe in un DB diverso da quello che WordPress interroga.
if [ -d mysql ] && [ -n "$(ls -A mysql 2>/dev/null)" ]; then
	echo "ATTENZIONE: ./mysql non e' vuota. Rimuovila (sudo rm -rf mysql) e rilancia,"
	echo "oppure salta questo script se il sito e' gia' installato."
	exit 1
fi
mkdir -p mysql

# ── Permessi ────────────────────────────────────────────────────────────────
# Il container gira come www-data (uid 33): senza questo WordPress non puo'
# scrivere in uploads ne' aggiornare i plugin.
echo "==> chown wordpress/ a uid 33"
sudo chown -R 33:33 wordpress

# ── Avvio DB e import ───────────────────────────────────────────────────────
echo "==> Avvio database"
docker compose up -d db
echo -n "    attendo che sia healthy"
for _ in $(seq 1 60); do
	state="$(docker inspect -f '{{.State.Health.Status}}' "${PROJECT_NAME}_db" 2>/dev/null || echo starting)"
	[ "$state" = healthy ] && break
	echo -n "."; sleep 3
done
echo " $state"
[ "$state" = healthy ] || { echo "Database non pronto, controlla: docker compose logs db"; exit 1; }

echo "==> Import del dump ($(du -h deploy/db.sql | cut -f1))"
docker exec -i "${PROJECT_NAME}_db" mariadb \
	--user=root --password="$MYSQL_ROOT_PASSWORD" \
	--default-character-set=utf8mb4 "$WORDPRESS_DB_NAME" < deploy/db.sql

# ── WordPress ───────────────────────────────────────────────────────────────
echo "==> Build e avvio WordPress"
docker compose up -d --build wordpress
echo -n "    attendo wp-config.php"
for _ in $(seq 1 40); do
	[ -f wordpress/wp-config.php ] && break
	echo -n "."; sleep 3
done
echo
[ -f wordpress/wp-config.php ] || { echo "wp-config.php non generato: docker compose logs wordpress"; exit 1; }

# ── Riscrittura degli URL nel database ──────────────────────────────────────
# Necessaria: gli URL sono anche dentro stringhe serializzate (widget, opzioni,
# meta), dove un sed romperebbe i prefissi di lunghezza.
echo "==> search-replace $OLD_URL -> $NEW_URL"
docker compose --profile tools run --rm cli \
	wp search-replace "$OLD_URL" "$NEW_URL" --all-tables --precise --skip-columns=guid
docker compose --profile tools run --rm cli wp cache flush || true
docker compose --profile tools run --rm cli wp rewrite flush --hard || true

# ── Edge nginx ──────────────────────────────────────────────────────────────
echo "==> Avvio edge nginx su $EDGE_BIND"
docker compose --profile prod up -d edge

# ── Esposizione HTTPS via Tailscale ─────────────────────────────────────────
echo "==> tailscale serve su :$TS_PORT (le altre mappature restano intatte)"
sudo tailscale serve --bg --https="$TS_PORT" http://127.0.0.1:8080
sudo tailscale serve status

echo
echo "Fatto. Sito raggiungibile dalla tailnet su: $NEW_URL"
echo "Admin: $NEW_URL/wp-admin"
echo
echo "Per renderlo pubblico su internet (stesso dominio, TLS incluso):"
echo "  sudo tailscale funnel --bg --https=$TS_PORT http://127.0.0.1:8080"
