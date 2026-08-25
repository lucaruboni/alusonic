# Agent Context - Torresan Bartolo

## Obiettivo progetto

Sito WordPress per BnB che affitta l'intera struttura, con UX premium e storytelling visual.

## Vincoli funzionali principali

- CTA principale: richiesta disponibilita e preventivo.
- Pagine: About, Suites, Pool, Experiences, FAQ, Gallery, Contact.
- Form contatto e mappa integrata stile desaturato.
- Predisposizione multilingua e SEO.

## Vincoli tecnici

- Stack locale: Docker (vedi `docker-compose.yml`).
- Deploy produzione: hosting condiviso Aruba, niente Docker sul server. Solo il tema
  (`wordpress/wp-content/themes/torresan-bnb`) viene pubblicato via FTP/FTPS
  (workflow `.github/workflows/deploy-ftp.yml` o script `scripts/deploy/ftp-deploy.sh`);
  WordPress core e DB restano quelli gia' installati su Aruba.
- Tema custom senza copiare asset proprietari di terzi.

## Priorita agenti

1. Preservare semplicita di manutenzione.
2. Evitare lock-in su servizi non necessari.
3. Favorire plugin free/open-source quando possibile.
