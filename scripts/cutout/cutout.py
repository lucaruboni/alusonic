#!/usr/bin/env python3
"""Rimuove lo sfondo dalle foto hero dei modelli elencati in models.json.

Uso:
    scripts/.venv-cutout/bin/python scripts/cutout/cutout.py [--force] [--id MODEL_ID]

Legge scripts/cutout/models.json, per ogni modello elabora
alusonic-assets/<source> con rembg e salva un PNG con sfondo trasparente in
alusonic-assets/cutouts/<category>/<id>.png. Salta i modelli il cui output
esiste già ed è più recente del sorgente, a meno di --force.
"""
import argparse
import json
import sys
from pathlib import Path

from PIL import Image
from rembg import remove, new_session

ROOT = Path(__file__).resolve().parents[2]
ASSETS_DIR = ROOT / "alusonic-assets"
MANIFEST_PATH = Path(__file__).resolve().parent / "models.json"
OUTPUT_DIR = ASSETS_DIR / "cutouts"


def load_models():
    with open(MANIFEST_PATH, encoding="utf-8") as f:
        data = json.load(f)
    return data["models"]


def harden_mask(im: Image.Image, threshold: int = 128, rim: int = 2) -> Image.Image:
    """Rende opaco l'interno del soggetto, lasciando solo un bordo antialiasato.

    U^2-Net (e ancor più l'alpha matting) sulle parti chiare/argento del corpo
    in alluminio restituisce spesso alpha parziale (100-180): sul sito lo
    sfondo scuro della card traspare e lo strumento sembra "bucato". Qui la
    maschera viene binarizzata, i buchi interni riempiti e l'interno forzato a
    opaco; i pixel entro `rim` px dal bordo mantengono l'alpha originale, così
    il contorno resta morbido invece che frastagliato.
    """
    import numpy as np
    from scipy import ndimage

    arr = np.array(im.convert("RGBA"))
    alpha = arr[:, :, 3]

    solid = ndimage.binary_fill_holes(alpha > threshold)
    interior = ndimage.binary_erosion(solid, iterations=rim)

    new_alpha = alpha.copy()
    new_alpha[interior] = 255      # interno completamente opaco
    new_alpha[~solid] = 0          # fuori dal soggetto completamente trasparente

    arr[:, :, 3] = new_alpha
    return Image.fromarray(arr, "RGBA")


def needs_processing(source_path: Path, output_path: Path, force: bool) -> bool:
    if force:
        return True
    if not output_path.exists():
        return True
    return source_path.stat().st_mtime > output_path.stat().st_mtime


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--force", action="store_true", help="Rielabora anche i modelli già ritagliati")
    parser.add_argument("--id", help="Elabora solo il modello con questo id")
    args = parser.parse_args()

    models = load_models()
    if args.id:
        models = [m for m in models if m["id"] == args.id]
        if not models:
            print(f"Nessun modello con id '{args.id}' in {MANIFEST_PATH}", file=sys.stderr)
            sys.exit(1)

    session = new_session("u2net")

    done, skipped, failed = 0, 0, 0
    for model in models:
        source_path = ASSETS_DIR / model["source"]
        out_dir = OUTPUT_DIR / model["category"]
        out_dir.mkdir(parents=True, exist_ok=True)
        output_path = out_dir / f"{model['id']}.png"

        # Ritaglio fatto a mano: su questi modelli U^2-Net sbagliava (mangiava
        # i fianchi chiari del corpo in alluminio), quindi il PNG in cutouts/ è
        # stato prodotto esternamente. Va protetto PRIMA di --force, altrimenti
        # una rigenerazione lo sovrascriverebbe con la versione difettosa.
        if model.get("manual"):
            print(f"[MANUALE] {model['id']} (ritaglio fatto a mano, non rigenerato)")
            skipped += 1
            continue

        if not source_path.exists():
            print(f"[MANCA SORGENTE] {model['id']}: {source_path} non trovato")
            failed += 1
            continue

        if not needs_processing(source_path, output_path, args.force):
            print(f"[SKIP] {model['id']} (già aggiornato)")
            skipped += 1
            continue

        try:
            with Image.open(source_path) as img:
                img = img.convert("RGBA")
                # Alpha matting rifinisce i bordi della maschera (evita il
                # classico contorno frastagliato/aliasing del solo U^2-Net),
                # ma su strumenti chiari/bianchi su sfondo bianco a volte
                # "mangia" pezzi di corpo scambiati per sfondo: per quei
                # modelli si può disattivare con "alpha_matting": false
                # nel manifest e usare solo la maschera grezza.
                use_matting = model.get("alpha_matting", True)
                if use_matting:
                    result = remove(
                        img,
                        session=session,
                        alpha_matting=True,
                        alpha_matting_foreground_threshold=240,
                        alpha_matting_background_threshold=15,
                        alpha_matting_erode_size=8,
                    )
                else:
                    result = remove(img, session=session, alpha_matting=False)
                if model.get("harden", True):
                    result = harden_mask(result)
                result.save(output_path)
            print(f"[OK] {model['id']} -> {output_path.relative_to(ROOT)}")
            done += 1
        except Exception as e:
            print(f"[ERRORE] {model['id']}: {e}")
            failed += 1

    print(f"\nCompletati: {done}, saltati: {skipped}, falliti: {failed}")
    if failed:
        sys.exit(1)


if __name__ == "__main__":
    main()
