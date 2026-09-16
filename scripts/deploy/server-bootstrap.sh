#!/usr/bin/env bash
# Prepara l'istanza Oracle: swap, Docker, Tailscale. Idempotente.
# Uso:  sudo bash server-bootstrap.sh
set -euo pipefail

[ "$(id -u)" -eq 0 ] || { echo "Esegui con sudo."; exit 1; }

TARGET_USER="${SUDO_USER:-$(logname 2>/dev/null || echo ubuntu)}"

if command -v apt-get >/dev/null; then PKG=apt; else PKG=dnf; fi
echo "==> Package manager: $PKG   utente: $TARGET_USER"

# ── Swap ────────────────────────────────────────────────────────────────────
# Le shape E2/E5 micro hanno 1 GB di RAM: MariaDB + PHP + nginx ci stanno solo
# con swap, altrimenti l'import del dump viene ucciso dall'OOM killer.
MEM_MB=$(awk '/MemTotal/ {print int($2/1024)}' /proc/meminfo)
echo "==> RAM rilevata: ${MEM_MB} MB"
if [ "$MEM_MB" -lt 3000 ] && ! swapon --show | grep -q .; then
	echo "==> Creazione swapfile da 2G"
	fallocate -l 2G /swapfile || dd if=/dev/zero of=/swapfile bs=1M count=2048
	chmod 600 /swapfile
	mkswap /swapfile
	swapon /swapfile
	grep -q '^/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
	sysctl -w vm.swappiness=10
	grep -q '^vm.swappiness' /etc/sysctl.conf || echo 'vm.swappiness=10' >> /etc/sysctl.conf
else
	echo "==> Swap gia' presente o RAM sufficiente, salto"
fi

# ── Docker ──────────────────────────────────────────────────────────────────
if command -v docker >/dev/null && docker compose version >/dev/null 2>&1; then
	echo "==> Docker gia' installato: $(docker --version), $(docker compose version)"
elif command -v docker >/dev/null; then
	# Docker c'e' ma senza plugin compose. Non aggiungo il repo Docker su una
	# installazione esistente (docker.io e docker-ce vanno in conflitto):
	# installo solo il binario del plugin.
	echo "==> Docker presente, installo il plugin compose"
	CV=v2.39.1
	case "$(uname -m)" in
		x86_64) CA=x86_64 ;; aarch64) CA=aarch64 ;;
		*) echo "Architettura non gestita: $(uname -m)"; exit 1 ;;
	esac
	install -m 0755 -d /usr/local/lib/docker/cli-plugins
	curl -fsSL "https://github.com/docker/compose/releases/download/$CV/docker-compose-linux-$CA" \
		-o /usr/local/lib/docker/cli-plugins/docker-compose
	chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
	docker compose version
else
	echo "==> Installazione Docker Engine + Compose plugin"
	if [ "$PKG" = apt ]; then
		export DEBIAN_FRONTEND=noninteractive
		apt-get update -qq
		apt-get install -y -qq ca-certificates curl gnupg
		install -m 0755 -d /etc/apt/keyrings
		curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
			| gpg --dearmor -o /etc/apt/keyrings/docker.gpg --yes
		chmod a+r /etc/apt/keyrings/docker.gpg
		echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
			> /etc/apt/sources.list.d/docker.list
		apt-get update -qq
		apt-get install -y -qq docker-ce docker-ce-cli containerd.io \
			docker-buildx-plugin docker-compose-plugin
	else
		dnf install -y dnf-plugins-core
		dnf config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
		dnf install -y docker-ce docker-ce-cli containerd.io \
			docker-buildx-plugin docker-compose-plugin
	fi
fi
systemctl enable --now docker
usermod -aG docker "$TARGET_USER" || true

# ── Tailscale ───────────────────────────────────────────────────────────────
if command -v tailscale >/dev/null; then
	echo "==> Tailscale gia' installato: $(tailscale version | head -1)"
else
	echo "==> Installazione Tailscale"
	curl -fsSL https://tailscale.com/install.sh | sh
fi
systemctl enable --now tailscaled

echo
echo "Bootstrap completato."
if tailscale status >/dev/null 2>&1; then
	echo "Tailscale connesso: $(tailscale status --json \
		| python3 -c 'import json,sys; print(json.load(sys.stdin)["Self"]["DNSName"].rstrip("."))')"
else
	echo "Tailscale NON connesso: esegui  sudo tailscale up"
fi
echo "Se l'utente $TARGET_USER e' stato aggiunto ora al gruppo docker, riapri la sessione SSH."
