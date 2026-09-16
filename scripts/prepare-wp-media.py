#!/usr/bin/env python3
"""Prepara le foto per l'import in WordPress a partire da alusonic-assets/.

Copia in wordpress/_alu_media/ (letto da scripts/seed-alusonic.php):
  - hero-<model-id>.png     — il ritaglio (sfondo trasparente) da alusonic-assets/cutouts/
  - gal-<model-id>-NN.<ext> — le foto originali di galleria (fino a --max-gallery per modello)

Ripetibile: da rilanciare ogni volta che si aggiunge un nuovo strumento
(dopo aver aggiunto le foto in alusonic-assets/, una voce in
scripts/cutout/models.json + rilanciato scripts/cutout/cutout.sh, e una
voce in scripts/data/models.json con le specifiche reali).

Uso:
    python3 scripts/prepare-wp-media.py [--max-gallery 8]
"""
import argparse
import json
import shutil
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ASSETS = ROOT / "alusonic-assets"
MEDIA = ROOT / "wordpress" / "_alu_media"

# Cartelle "dedicate a un solo modello": i file fratelli dell'hero SONO la
# galleria (non ci sono altre anteprime di altri modelli mischiate dentro).
SOLO_ROOT_FOLDERS = {"thedoom", "cabinets"}
# Modelli la cui foto vive in una cartella condivisa con altre anteprime
# (niente galleria dedicata scaricata per loro).
NO_GALLERY_IDS = {"stratosonic"}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--max-gallery", type=int, default=8, help="Foto di galleria max per modello")
    args = parser.parse_args()

    MEDIA.mkdir(parents=True, exist_ok=True)

    manifest = json.loads((ROOT / "scripts/cutout/models.json").read_text())["models"]

    hero_count = 0
    gallery_count = 0
    for m in manifest:
        mid = m["id"]

        # Hero = il ritaglio già generato da scripts/cutout/cutout.sh
        cutout = ASSETS / "cutouts" / m["category"] / f"{mid}.png"
        if cutout.exists():
            shutil.copy(cutout, MEDIA / f"hero-{mid}.png")
            hero_count += 1
        else:
            print(f"[MANCA CUTOUT] {mid}: {cutout} non trovato — rilancia scripts/cutout/cutout.sh")

        # Galleria = foto originali (non ritagliate) della stessa cartella del modello
        parts = Path(m["source"]).parts
        gallery_files = []
        if mid in NO_GALLERY_IDS:
            gallery_files = []
        elif len(parts) == 3:
            folder = ASSETS / parts[0] / parts[1]
            gallery_files = sorted(p for p in folder.iterdir() if p.is_file() and p.name != parts[2])
        elif len(parts) == 2 and parts[0] in SOLO_ROOT_FOLDERS:
            folder = ASSETS / parts[0]
            gallery_files = sorted(p for p in folder.iterdir() if p.is_file() and p.name != parts[1])
        gallery_files = gallery_files[: args.max_gallery]

        # Ripulisci eventuali foto di galleria precedenti per questo modello
        # prima di ricopiare (evita di lasciare file orfani se il set cambia).
        for old in MEDIA.glob(f"gal-{mid}-*.*"):
            old.unlink()
        for i, gf in enumerate(gallery_files, start=1):
            dest = MEDIA / f"gal-{mid}-{i:02d}{gf.suffix.lower()}"
            shutil.copy(gf, dest)
            gallery_count += 1

    # Foto artisti
    artists = json.loads((ROOT / "scripts/data/artists.json").read_text())["artists"]
    artist_count = 0
    for a in artists:
        src = ASSETS / "artists" / a["photo"]
        if src.exists():
            shutil.copy(src, MEDIA / f"artist-{a['photo']}")
            artist_count += 1
        else:
            print(f"[MANCA FOTO ARTISTA] {a['name']}: {src} non trovato")

    print(f"\nHero: {hero_count} | Galleria: {gallery_count} foto | Artisti: {artist_count}")
    print(f"Copiati in: {MEDIA}")
    print("\nRicorda: il logo e la foto hero del sito (logo-ritagliato.png, IMG_4283.jpeg)")
    print("vanno copiati manualmente in _alu_media/ la prima volta (non fanno parte")
    print("del catalogo modelli/artisti).")


if __name__ == "__main__":
    main()
