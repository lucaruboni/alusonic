---
applyTo: "wordpress/wp-content/themes/torresan-bnb/**/*.php,wordpress/wp-content/themes/torresan-bnb/**/*.js,wordpress/wp-content/themes/torresan-bnb/**/*.scss,wordpress/wp-content/themes/torresan-bnb/**/*.css,docker-compose.yml,docker/wordpress/**"
---

# WordPress BnB Context

## Business Intent

Build a premium brochure and lead-gen website for a BnB where guests rent the entire property.

## UX Direction

- Editorial hero and large imagery
- Clear CTA for availability request
- Elegant, calm palette with high readability
- Mobile-first with smooth section navigation

## Functional Scope

- Home sections: hero, overview, amenities, experiences, FAQ, contact
- Dedicated pages for About, Suites, Pool, Experiences, Gallery, Contact
- Footer with legal links and direct contact

## Technical Scope

- WordPress theme must remain plugin-agnostic
- Enqueue assets via `functions.php`
- Avoid hard dependencies on premium plugins
