# Alusonic — pipeline contenuti (foto → ritaglio → WordPress)

Tre passi, ripetibili ogni volta che si aggiunge un nuovo strumento o si aggiornano le foto.

## 1. Foto sorgente

Le foto vanno in `alusonic-assets/<categoria>/<modello>/` (vedi `alusonic-content-inventory.md` per la struttura attuale e i modelli già presenti).

## 2. Ritaglio (rimozione sfondo)

```
scripts/cutout/cutout.sh              # elabora solo i modelli nuovi/aggiornati
scripts/cutout/cutout.sh --force      # rielabora tutto
scripts/cutout/cutout.sh --id <id>    # un solo modello
```

Legge `scripts/cutout/models.json` (id, categoria, foto sorgente), usa **rembg** (IA locale, offline) con alpha matting per bordi puliti, salva in `alusonic-assets/cutouts/<categoria>/<id>.png`.

Per un nuovo modello: aggiungere una voce in `scripts/cutout/models.json`.

## 3. Dati reali del modello

Aggiungere una voce in `scripts/data/models.json` (specifiche tecniche, testi) — o in `scripts/data/artists.json` per un nuovo artista.

## 4. Preparazione media per WordPress

```
python3 scripts/prepare-wp-media.py
```

Copia in `wordpress/_alu_media/`:
- `hero-<id>.png` — il ritaglio (dal passo 2)
- `gal-<id>-NN.<ext>` — fino a 8 foto di galleria originali (non ritagliate) per modello
- `artist-<file>` — foto artisti

## 5. Seed su WordPress

```
docker compose exec -T wordpress wp eval-file _alu_seed/seed-alusonic.php --allow-root
```

`scripts/seed-alusonic.php` legge `scripts/data/models.json` e `scripts/data/artists.json`, importa le foto da `_alu_media/` (idempotente: non ricarica un file già importato), e **ricrea da zero** tutte le pagine/modelli/artisti/FAQ (cancella prima quelli esistenti — è il comportamento voluto per un reseed completo, non usarlo se in WP admin sono state fatte modifiche manuali che si vogliono conservare).

Nota: essendo `scripts/` fuori dalla document root di WordPress, va copiato dentro `wordpress/_alu_seed/` prima di lanciare `wp eval-file` (il container vede solo `wordpress/`):

```
mkdir -p wordpress/_alu_seed/data
cp scripts/seed-alusonic.php wordpress/_alu_seed/seed-alusonic.php
cp scripts/data/*.json wordpress/_alu_seed/data/
```

Dopo il primo seed, se i permalink non sono ancora impostati:

```
docker compose exec -T wordpress wp rewrite structure '/%postname%/' --allow-root
docker compose exec -T wordpress wp rewrite flush --allow-root
```

## Media di sito (una tantum)

Logo e foto hero (`logo-ritagliato.png`, `IMG_4283.jpeg`) non fanno parte del catalogo modelli/artisti e vanno copiati manualmente in `wordpress/_alu_media/` la prima volta (sono già presenti nell'installazione WordPress principale del progetto, in `wordpress/_alu_media/`).
