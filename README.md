# Torresan Bartolo - WordPress BnB

Setup WordPress self-hosted con Docker per un sito BnB (affitto intera struttura), visual ispirato al tema The Shore ma con codice e asset originali.

## Requisiti

- Docker Engine
- Docker Compose plugin

## Avvio locale

1. Copia il file ambiente:

```bash
cp .env.example .env
```

2. Avvia i container:

```bash
docker compose up -d --build
```

3. Completa installazione WordPress su:

`http://localhost:8080`

4. Attiva il tema `Torresan BnB` da bacheca WordPress.

5. Vai in `Aspetto > Contenuti Home BnB` e modifica i campi personalizzati della homepage.

6. Crea/imposta una pagina come homepage statica da `Impostazioni > Lettura`.

## Struttura

- `docker-compose.yml`: stack MariaDB + WordPress
- `docker/wordpress/`: configurazione immagine WordPress
- `wordpress/wp-content/themes/torresan-bnb/`: tema custom
- `.github/copilot-instructions.md`: linee guida globali agenti
- `.github/instructions/wordpress-bnb.instructions.md`: regole contestuali progetto
- `AGENTS.md`: contesto operativo rapido per agenti

## Oracle Always Free (linee guida)

1. Crea VM Ubuntu ARM (Ampere A1).
2. Installa Docker e Docker Compose plugin.
3. Apri porte di sicurezza su OCI (80/443).
4. Clona repo e configura `.env`.
5. Usa reverse proxy (Nginx/Caddy) con TLS Let's Encrypt.
6. Monta volumi persistenti e backup giornalieri DB + `wp-content`.

## Plugin consigliati per requisiti Carolina

- Booking base: `Booking Calendar` oppure `MotoPress Hotel Booking Lite`
- GDPR/Cookie: `Complianz`
- SEO: `Rank Math` o `Yoast SEO`
- Multilingua: `Polylang`
- Performance/cache: `LiteSpeed Cache` (se stack compatibile) o `WP Super Cache`

## Security baseline (OWASP-oriented)

Misure implementate nel repository:

- Header HTTP di sicurezza lato Apache (`X-Content-Type-Options`, `X-Frame-Options`, `CSP`, `Referrer-Policy`)
- Hardening PHP (`expose_php=Off`, cookie sessione `HttpOnly/Secure/SameSite`)
- Hardening WordPress (`DISALLOW_FILE_EDIT`, update core minor, XML-RPC disabilitato)
- Riduzione superficie attacco REST (`/wp/v2/users` bloccato per utenti anonimi)
- Pattern anti-enumerazione autori (redirect archive autore)
- Database non esposto esternamente in Docker Compose

Misure operative consigliate in produzione:

1. Reverse proxy con TLS, HSTS e rate-limiting.
2. WAF/Firewall (es. Cloudflare + regole custom bot/login).
3. MFA per account admin e password manager obbligatorio.
4. Backup cifrati offsite giornalieri con test ripristino.
5. Aggiornamento continuo core/plugin/tema e vulnerability scanning periodico.

## Security smoke test

Per validare rapidamente il baseline:

```bash
chmod +x scripts/security/smoke-security-check.sh
scripts/security/smoke-security-check.sh http://localhost:8080
```

## Avvio con edge proxy (profilo produzione)

Il servizio `edge` (Nginx) aggiunge rate limiting su login e blocco `xmlrpc.php`.

```bash
docker compose --profile prod up -d --build
```

Configurazione edge: `docker/nginx/default.conf`.

## Demo contenuti da dashboard

La homepage usa campi personalizzati gestiti dal tema in dashboard:

- Menu admin: `Aspetto > Contenuti Home BnB`
- Sezioni editabili: Hero, About, Suites, Pool & Experiences, FAQ, Contact
- FAQ: una riga per item, formato `domanda|risposta`

I campi sono precompilati con contenuto demo, quindi appena attivi il tema puoi gia mostrare una versione completa al cliente e poi personalizzarla in diretta durante la call.

## FAQ (comportamento caricamento)

- La pagina FAQ legge prima il campo Pods `faq_pagina` (relazione con post `domanda_faq`).
- Se il collegamento non e presente o e salvato in un formato meta non standard, il tema fa fallback automatico e mostra tutte le FAQ pubblicate (`domanda_faq`).
- In questo modo le nuove FAQ aggiunte dalla cliente restano visibili anche se non sono ancora collegate manualmente alla pagina FAQ.
- Con Polylang attivo, la pagina FAQ unisce sempre anche le FAQ pubblicate della lingua corrente (es. EN), cosi una nuova FAQ tradotta appare subito.

## Gallery (sorgente immagini)

- La pagina Gallery ora carica le immagini direttamente dalla Libreria Media di WordPress (`attachment` con mime `image`).
- Non e piu necessario selezionare manualmente le immagini nel campo Pods della pagina Gallery.
- Ordinamento: immagini piu recenti prima.
- Le immagini con tag `no-gallery` vengono escluse automaticamente dalla Gallery (utile per logo, icone e asset tecnici).

## Esperienze (ordine editoriale)

- Pagina Experiences: nuovo campo Pods `experiences_order` per selezionare e ordinare manualmente le esperienze in frontend.
- Homepage: nuovo campo Pods `home_experiences_order` per scegliere e ordinare fino a 3 esperienze in evidenza.
- Se i campi sono vuoti, resta attivo il fallback automatico per `menu_order` del CPT `esperienza`.


