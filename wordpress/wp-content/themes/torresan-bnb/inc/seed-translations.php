<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Seed translated page shells for Polylang.
 *
 * Creates one translated WordPress page per language (IT, DE, FR, ES) for every
 * English page that doesn't yet have a translation.  Each page is pre-filled with
 * placeholder content — clearly labelled in the target language — so the client
 * immediately knows what to complete.
 *
 * Skips safely if Polylang is not active or if languages are not yet registered.
 * Gated by the `torresan_translations_seeded` option — runs only once.
 */
function torresan_seed_translations(): void
{
    if (get_option('torresan_translations_seeded')) {
        return;
    }

    if (! function_exists('pll_set_post_language') || ! function_exists('pll_save_post_translations')) {
        return;
    }

    $registered = pll_languages_list(['fields' => 'slug']);
    $needed     = ['it', 'de', 'fr', 'es'];

    foreach ($needed as $lang) {
        if (! in_array($lang, $registered, true)) {
            return; // abort until all languages are set up
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
       Page definitions
       'en_slug'  → WordPress slug of the English original
       Per-language: 'title', 'slug' (used in URL), 'content' (editable placeholder)
       ───────────────────────────────────────────────────────────────────── */
    $pages = [

        /* ── Home ── */
        'home' => [
            'template' => '',
            'it' => [
                'title'   => 'Torre San Bartolo',
                'slug'    => 'torre-san-bartolo-italiano',
                'content' => '<p><strong>[IT — Homepage]</strong> Sostituisci questo testo con la presentazione della struttura in italiano: storia, atmosfera, proposta di valore. Questo testo appare nella sezione "story" della homepage.</p>',
            ],
            'de' => [
                'title'   => 'Torre San Bartolo',
                'slug'    => 'torre-san-bartolo-deutsch',
                'content' => '<p><strong>[DE — Startseite]</strong> Ersetzen Sie diesen Text durch die deutschsprachige Vorstellung des Hauses: Geschichte, Atmosphäre, Alleinstellungsmerkmale. Dieser Text erscheint im "Story"-Bereich der Startseite.</p>',
            ],
            'fr' => [
                'title'   => 'Torre San Bartolo',
                'slug'    => 'torre-san-bartolo-francais',
                'content' => '<p><strong>[FR — Page d\'accueil]</strong> Remplacez ce texte par la présentation de la propriété en français : histoire, atmosphère, valeur ajoutée. Ce texte apparaît dans la section « story » de la page d\'accueil.</p>',
            ],
            'es' => [
                'title'   => 'Torre San Bartolo',
                'slug'    => 'torre-san-bartolo-espanol',
                'content' => '<p><strong>[ES — Página de inicio]</strong> Sustituya este texto por la presentación de la propiedad en español: historia, ambiente, propuesta de valor. Este texto aparece en la sección "story" de la página de inicio.</p>',
            ],
        ],

        /* ── About ── */
        'about' => [
            'template' => 'page-about.php',
            'it' => [
                'title'   => 'Chi Siamo',
                'slug'    => 'chi-siamo',
                'content' => '<p><strong>[IT — Chi Siamo]</strong> Inserisci qui la storia della tenuta in italiano. Descrivi le origini, la famiglia, la visione e ciò che rende Torre San Bartolo un posto unico. Usa il riquadro "Hero" qui sopra per impostare l\'occhiello (eyebrow) e il sottotitolo della pagina.</p>',
            ],
            'de' => [
                'title'   => 'Über Uns',
                'slug'    => 'ueber-uns',
                'content' => '<p><strong>[DE — Über Uns]</strong> Beschreiben Sie hier die Geschichte des Anwesens auf Deutsch: Ursprünge, Familie, Vision und was Torre San Bartolo zu einem einzigartigen Ort macht. Nutzen Sie das "Hero"-Feld oben für Eyebrow-Text und Untertitel.</p>',
            ],
            'fr' => [
                'title'   => 'À Propos',
                'slug'    => 'a-propos',
                'content' => '<p><strong>[FR — À Propos]</strong> Rédigez ici l\'histoire de la propriété en français : origines, famille, vision et ce qui rend Torre San Bartolo unique. Utilisez le champ « Hero » ci-dessus pour le texte d\'introduction et le sous-titre.</p>',
            ],
            'es' => [
                'title'   => 'Acerca de',
                'slug'    => 'acerca-de',
                'content' => '<p><strong>[ES — Acerca de]</strong> Escriba aquí la historia de la propiedad en español: orígenes, familia, visión y lo que hace única a Torre San Bartolo. Use el campo "Hero" de arriba para el texto introductorio y el subtítulo.</p>',
            ],
        ],

        /* ── Suites ── */
        'suites' => [
            'template' => 'page-suites.php',
            'it' => [
                'title'   => 'Suite',
                'slug'    => 'suite',
                'content' => '',
            ],
            'de' => [
                'title'   => 'Suiten',
                'slug'    => 'suiten',
                'content' => '',
            ],
            'fr' => [
                'title'   => 'Suites',
                'slug'    => 'suites',
                'content' => '',
            ],
            'es' => [
                'title'   => 'Suites',
                'slug'    => 'suites',
                'content' => '',
            ],
        ],

        /* ── Pool ── */
        'pool' => [
            'template' => 'page-pool.php',
            'it' => [
                'title'   => 'Piscina',
                'slug'    => 'piscina',
                'content' => '<p><strong>[IT — Piscina]</strong> Descrivi la piscina in italiano: dimensioni, vista, orari, atmosfera. Questo testo appare nella sezione "story" della pagina. Carica le foto della piscina nella galleria tramite il campo Pods "Pool Gallery".</p>',
            ],
            'de' => [
                'title'   => 'Pool',
                'slug'    => 'pool',
                'content' => '<p><strong>[DE — Pool]</strong> Beschreiben Sie den Pool auf Deutsch: Maße, Aussicht, Öffnungszeiten, Atmosphäre. Dieser Text erscheint im "Story"-Bereich der Seite. Laden Sie Pool-Fotos über das Pods-Feld "Pool Gallery" hoch.</p>',
            ],
            'fr' => [
                'title'   => 'Piscine',
                'slug'    => 'piscine',
                'content' => '<p><strong>[FR — Piscine]</strong> Décrivez la piscine en français : dimensions, vue, horaires, atmosphère. Ce texte apparaît dans la section « story » de la page. Téléversez les photos via le champ Pods « Pool Gallery ».</p>',
            ],
            'es' => [
                'title'   => 'Piscina',
                'slug'    => 'piscina',
                'content' => '<p><strong>[ES — Piscina]</strong> Describa la piscina en español: dimensiones, vistas, horarios, ambiente. Este texto aparece en la sección "story" de la página. Suba las fotos a través del campo Pods "Pool Gallery".</p>',
            ],
        ],

        /* ── Experiences ── */
        'experiences' => [
            'template' => 'page-experiences.php',
            'it' => [
                'title'   => 'Esperienze',
                'slug'    => 'esperienze',
                'content' => '',
            ],
            'de' => [
                'title'   => 'Erlebnisse',
                'slug'    => 'erlebnisse',
                'content' => '',
            ],
            'fr' => [
                'title'   => 'Expériences',
                'slug'    => 'experiences',
                'content' => '',
            ],
            'es' => [
                'title'   => 'Experiencias',
                'slug'    => 'experiencias',
                'content' => '',
            ],
        ],

        /* ── Gallery ── */
        'gallery' => [
            'template' => 'page-gallery.php',
            'it' => [
                'title'   => 'Galleria',
                'slug'    => 'galleria',
                'content' => '',
            ],
            'de' => [
                'title'   => 'Galerie',
                'slug'    => 'galerie',
                'content' => '',
            ],
            'fr' => [
                'title'   => 'Galerie',
                'slug'    => 'galerie',
                'content' => '',
            ],
            'es' => [
                'title'   => 'Galería',
                'slug'    => 'galeria',
                'content' => '',
            ],
        ],

        /* ── FAQ ── */
        'faq' => [
            'template' => 'page-faq.php',
            'it' => [
                'title'   => 'FAQ',
                'slug'    => 'faq',
                'content' => '<p><strong>[IT — FAQ]</strong> Collega le domande frequenti in italiano tramite il campo Pods "Domande Frequenti" qui sotto. Crea prima le domande come post "domanda_faq", poi selezionale qui.</p>',
            ],
            'de' => [
                'title'   => 'FAQ',
                'slug'    => 'faq',
                'content' => '<p><strong>[DE — FAQ]</strong> Verknüpfen Sie die FAQ-Einträge auf Deutsch über das Pods-Feld "Domande Frequenti" unten. Erstellen Sie zuerst die Fragen als "domanda_faq"-Beiträge, dann wählen Sie sie hier aus.</p>',
            ],
            'fr' => [
                'title'   => 'FAQ',
                'slug'    => 'faq',
                'content' => '<p><strong>[FR — FAQ]</strong> Liez les questions fréquentes en français via le champ Pods « Domande Frequenti » ci-dessous. Créez d\'abord les questions comme articles « domanda_faq », puis sélectionnez-les ici.</p>',
            ],
            'es' => [
                'title'   => 'FAQ',
                'slug'    => 'faq',
                'content' => '<p><strong>[ES — FAQ]</strong> Vincule las preguntas frecuentes en español a través del campo Pods "Domande Frequenti" abajo. Cree primero las preguntas como publicaciones "domanda_faq" y luego selecciónelas aquí.</p>',
            ],
        ],

        /* ── Contact ── */
        'contact' => [
            'template' => 'page-contact.php',
            'it' => [
                'title'   => 'Contatti',
                'slug'    => 'contatti',
                'content' => '<p><strong>[IT — Contatti]</strong> Compila i campi Pods qui sotto: Telefono, Email, Orari, Indirizzo e il codice embed della mappa Google. Il contenuto di questa pagina viene generato automaticamente dai campi.</p>',
            ],
            'de' => [
                'title'   => 'Kontakt',
                'slug'    => 'kontakt',
                'content' => '<p><strong>[DE — Kontakt]</strong> Füllen Sie die Pods-Felder aus: Telefon, E-Mail, Öffnungszeiten, Adresse und den Google-Maps-Einbettungscode. Der Seiteninhalt wird automatisch aus den Feldern generiert.</p>',
            ],
            'fr' => [
                'title'   => 'Contact',
                'slug'    => 'contact',
                'content' => '<p><strong>[FR — Contact]</strong> Remplissez les champs Pods : téléphone, e-mail, horaires, adresse et le code d\'intégration Google Maps. Le contenu de cette page est généré automatiquement à partir des champs.</p>',
            ],
            'es' => [
                'title'   => 'Contacto',
                'slug'    => 'contacto',
                'content' => '<p><strong>[ES — Contacto]</strong> Rellene los campos Pods: teléfono, correo electrónico, horario, dirección y el código de inserción de Google Maps. El contenido de esta página se genera automáticamente a partir de los campos.</p>',
            ],
        ],

        /* ── Location ── */
        'location' => [
            'template' => 'page-location.php',
            'it' => [
                'title'   => 'Come Arrivare',
                'slug'    => 'come-arrivare',
                'content' => '<p><strong>[IT — Come Arrivare]</strong> Descrivi qui le indicazioni stradali in italiano (dall\'autostrada, dall\'aeroporto, ecc.). Compila anche il campo Pods "Indicazioni Stradali" per le indicazioni formattate, e il campo "Mappa Embed" per il widget Google Maps.</p>',
            ],
            'de' => [
                'title'   => 'Anfahrt',
                'slug'    => 'anfahrt',
                'content' => '<p><strong>[DE — Anfahrt]</strong> Beschreiben Sie hier die Anfahrtsbeschreibung auf Deutsch (von der Autobahn, vom Flughafen usw.). Füllen Sie auch das Pods-Feld "Indicazioni Stradali" aus und fügen Sie den Google-Maps-Einbettungscode ein.</p>',
            ],
            'fr' => [
                'title'   => 'Comment Arriver',
                'slug'    => 'comment-arriver',
                'content' => '<p><strong>[FR — Comment Arriver]</strong> Décrivez ici les indications routières en français (depuis l\'autoroute, l\'aéroport, etc.). Remplissez également le champ Pods « Indicazioni Stradali » et le champ « Mappa Embed » pour le widget Google Maps.</p>',
            ],
            'es' => [
                'title'   => 'Cómo Llegar',
                'slug'    => 'como-llegar',
                'content' => '<p><strong>[ES — Cómo Llegar]</strong> Describa aquí las indicaciones de llegada en español (desde la autopista, el aeropuerto, etc.). Complete también el campo Pods "Indicazioni Stradali" y el campo "Mappa Embed" para el widget de Google Maps.</p>',
            ],
        ],

        /* ── Book ── */
        'book' => [
            'template' => 'page-book.php',
            'it' => [
                'title'   => 'Prenota',
                'slug'    => 'prenota',
                'content' => '',
            ],
            'de' => [
                'title'   => 'Buchen',
                'slug'    => 'buchen',
                'content' => '',
            ],
            'fr' => [
                'title'   => 'Réserver',
                'slug'    => 'reserver',
                'content' => '',
            ],
            'es' => [
                'title'   => 'Reservar',
                'slug'    => 'reservar',
                'content' => '',
            ],
        ],

        /* ── Privacy Policy ── */
        'privacy-policy' => [
            'template' => 'page-legal.php',
            'it' => [
                'title'   => 'Privacy Policy',
                'slug'    => 'privacy-policy-it',
                'content' => '<p><strong>[IT — Privacy Policy]</strong> Inserisci qui il testo completo della Privacy Policy in italiano (conformità GDPR / D.Lgs. 196/2003). Puoi usare il generatore di Iubenda o un testo redatto dal tuo legale.</p>',
            ],
            'de' => [
                'title'   => 'Datenschutzrichtlinie',
                'slug'    => 'datenschutzrichtlinie',
                'content' => '<p><strong>[DE — Datenschutzrichtlinie]</strong> Fügen Sie hier den vollständigen Text der Datenschutzrichtlinie auf Deutsch ein (DSGVO-konform). Sie können den Iubenda-Generator oder einen von Ihrem Rechtsberater erstellten Text verwenden.</p>',
            ],
            'fr' => [
                'title'   => 'Politique de confidentialité',
                'slug'    => 'politique-de-confidentialite',
                'content' => '<p><strong>[FR — Politique de confidentialité]</strong> Insérez ici le texte complet de la politique de confidentialité en français (conforme au RGPD). Vous pouvez utiliser le générateur Iubenda ou un texte rédigé par votre conseiller juridique.</p>',
            ],
            'es' => [
                'title'   => 'Política de privacidad',
                'slug'    => 'politica-de-privacidad',
                'content' => '<p><strong>[ES — Política de privacidad]</strong> Inserte aquí el texto completo de la política de privacidad en español (conforme al RGPD). Puede utilizar el generador de Iubenda o un texto redactado por su asesor legal.</p>',
            ],
        ],

        /* ── Cookie Policy ── */
        'cookie-policy' => [
            'template' => 'page-legal.php',
            'it' => [
                'title'   => 'Cookie Policy',
                'slug'    => 'cookie-policy-it',
                'content' => '<p><strong>[IT — Cookie Policy]</strong> Inserisci qui il testo della Cookie Policy in italiano. Si consiglia di usare il generatore di Iubenda per mantenere il testo aggiornato automaticamente.</p>',
            ],
            'de' => [
                'title'   => 'Cookie-Richtlinie',
                'slug'    => 'cookie-richtlinie',
                'content' => '<p><strong>[DE — Cookie-Richtlinie]</strong> Fügen Sie hier den Text der Cookie-Richtlinie auf Deutsch ein. Es wird empfohlen, den Iubenda-Generator zu verwenden, damit der Text automatisch aktuell bleibt.</p>',
            ],
            'fr' => [
                'title'   => 'Politique des cookies',
                'slug'    => 'politique-des-cookies',
                'content' => '<p><strong>[FR — Politique des cookies]</strong> Insérez ici le texte de la politique des cookies en français. Il est recommandé d\'utiliser le générateur Iubenda pour maintenir le texte automatiquement à jour.</p>',
            ],
            'es' => [
                'title'   => 'Política de cookies',
                'slug'    => 'politica-de-cookies',
                'content' => '<p><strong>[ES — Política de cookies]</strong> Inserte aquí el texto de la política de cookies en español. Se recomienda usar el generador de Iubenda para mantener el texto actualizado automáticamente.</p>',
            ],
        ],

    ];

    /* ─── Seed loop ─────────────────────────────────────────────────────── */
    foreach ($pages as $en_slug => $data) {
        $en_page = get_page_by_path($en_slug);

        if (! $en_page) {
            continue; // English original not found — skip
        }

        // Ensure the English page is tagged as English in Polylang
        pll_set_post_language($en_page->ID, 'en');

        $translation_ids = ['en' => $en_page->ID];

        foreach (['it', 'de', 'fr', 'es'] as $lang) {
            $lang_data = $data[$lang] ?? null;
            if (! $lang_data) {
                continue;
            }

            // Check if a translation already exists
            $existing_id = pll_get_post($en_page->ID, $lang);
            if ($existing_id && get_post_status($existing_id) === 'publish') {
                $translation_ids[$lang] = $existing_id;
                continue;
            }

            $post_args = [
                'post_title'   => $lang_data['title'],
                'post_name'    => $lang_data['slug'],
                'post_content' => $lang_data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'menu_order'   => $en_page->menu_order,
            ];

            if (! empty($data['template'])) {
                $post_args['page_template'] = $data['template'];
            }

            $new_id = wp_insert_post($post_args);

            if (is_wp_error($new_id) || ! $new_id) {
                continue;
            }

            // Set template meta explicitly (some WP versions need this)
            if (! empty($data['template'])) {
                update_post_meta($new_id, '_wp_page_template', $data['template']);
            }

            pll_set_post_language($new_id, $lang);
            $translation_ids[$lang] = $new_id;
        }

        // Link all translations together
        if (count($translation_ids) > 1) {
            pll_save_post_translations($translation_ids);
        }
    }

    update_option('torresan_translations_seeded', '1');
}

/* Run on admin_init — after the language seed (which runs on the same hook). */
add_action('admin_init', function (): void {
    if (
        ! get_option('torresan_translations_seeded') &&
        function_exists('pll_set_post_language') &&
        get_option('torresan_pll_languages_seeded')
    ) {
        torresan_seed_translations();
    }
}, 20); // priority 20 — runs after the language seed (default priority 10)

/* ──────────────────────────────────────────────────────────────────────────────
   SECOND-PASS SEED — fills Pods fields and copies language-neutral meta
   from English pages to all translated copies.
   Gated by 'torresan_translations_seeded_v1' — runs once per site.
   ────────────────────────────────────────────────────────────────────────────── */
function torresan_seed_translation_fields(): void
{
    if (get_option('torresan_translations_seeded_v1')) {
        return;
    }
    if (! get_option('torresan_translations_seeded')) {
        return; // Pages not yet created — wait for first-pass seed
    }
    if (! function_exists('pll_get_post_translations')) {
        return;
    }

    // EN master page IDs
    $en = [
        'home'        => 12,
        'about'       => 180,
        'suites'      => 182,
        'pool'        => 184,
        'experiences' => 186,
        'gallery'     => 188,
        'contact'     => 190,
        'location'    => 192,
        'faq'         => 209,
    ];

    $tr = fn(int $id): array => pll_get_post_translations($id);

    $copy_meta = function (int $src, int $dst, array $keys): void {
        foreach ($keys as $key) {
            $val = get_post_meta($src, $key, true);
            if ($val !== '' && $val !== false) {
                update_post_meta($dst, $key, $val);
            }
        }
    };

    // ── 1. Featured images ────────────────────────────────────────────────────
    foreach ($en as $pid) {
        $thumb = get_post_thumbnail_id($pid);
        if (! $thumb) continue;
        foreach ($tr($pid) as $lang => $tid) {
            if ($lang === 'en') continue;
            set_post_thumbnail($tid, $thumb);
        }
    }

    // ── 2. Contact — neutral fields + translated hours ────────────────────────
    $orari = [
        'it' => "Lun \xe2\x80\x93 Sab: 09:00 \xe2\x80\x93 19:00\nDom: 10:00 \xe2\x80\x93 17:00\n\nCheck-in: dalle 15:00\nCheck-out: entro le 11:00",
        'de' => "Mo \xe2\x80\x93 Sa: 09:00 \xe2\x80\x93 19:00\nSo: 10:00 \xe2\x80\x93 17:00\n\nCheck-in: ab 15:00 Uhr\nCheck-out: bis 11:00 Uhr",
        'fr' => "Lun \xe2\x80\x93 Sam: 09:00 \xe2\x80\x93 19:00\nDim: 10:00 \xe2\x80\x93 17:00\n\nArr\xc3\xa9e: \xc3\xa0 partir de 15h\nD\xc3\xa9part: avant 11h",
        'es' => "Lun \xe2\x80\x93 S\xc3\xa1b: 09:00 \xe2\x80\x93 19:00\nDom: 10:00 \xe2\x80\x93 17:00\n\nEntrada: desde las 15:00\nSalida: antes de las 11:00",
    ];
    foreach ($tr($en['contact']) as $lang => $pid) {
        if ($lang === 'en') continue;
        $copy_meta($en['contact'], $pid, ['contatti_telefono', 'contatti_email', 'maps_embed', 'address']);
        if (isset($orari[$lang])) {
            update_post_meta($pid, 'contatti_orari', $orari[$lang]);
        }
    }

    // ── 3. Location — neutral fields + translated directions ──────────────────
    $directions = [
        'it' => "<h3>In Auto</h3>\n<p>Seguire la SS16 Adriatica in direzione nord verso Pesaro. Uscire a Pesaro Nord e seguire le indicazioni per il Parco Naturale del Monte San Bartolo.</p>\n<h3>Dall'Aeroporto</h3>\n<p>L'aeroporto pi\xc3\xb9 vicino \xc3\xa8 Federico Fellini di Rimini (RMI), a circa 40 km. Si consiglia il noleggio auto.</p>\n<h3>In Treno</h3>\n<p>La stazione pi\xc3\xb9 vicina \xc3\xa8 Pesaro, servita da Trenitalia. Da l\xc3\xac si raggiunge la propriet\xc3\xa0 in taxi (circa 20 min).</p>",
        'de' => "<h3>Mit dem Auto</h3>\n<p>Fahren Sie auf der SS16 Adriatica Richtung Norden nach Pesaro. Verlassen Sie die Autobahn bei Pesaro Nord und folgen Sie den Schildern zum Naturpark Monte San Bartolo.</p>\n<h3>Vom Flughafen</h3>\n<p>Der n\xc3\xa4chste Flughafen ist Federico Fellini in Rimini (RMI), ca. 40 km entfernt. Ein Mietwagen wird empfohlen.</p>\n<h3>Mit dem Zug</h3>\n<p>Der n\xc3\xa4chste Bahnhof ist Pesaro, angebunden an das Trenitalia-Netz. Von dort per Taxi (ca. 20 Min.).</p>",
        'fr' => "<h3>En Voiture</h3>\n<p>Suivez la SS16 Adriatica vers le nord en direction de Pesaro. Sortez \xc3\xa0 Pesaro Nord et suivez les panneaux vers le Parc Naturel du Monte San Bartolo.</p>\n<h3>Depuis l'A\xc3\xa9roport</h3>\n<p>L'a\xc3\xa9roport le plus proche est Federico Fellini de Rimini (RMI), \xc3\xa0 environ 40 km. Une voiture de location est recommand\xc3\xa9e.</p>\n<h3>En Train</h3>\n<p>La gare la plus proche est Pesaro, desservie par Trenitalia. De l\xc3\xa0, rejoignez la propri\xc3\xa9t\xc3\xa9 en taxi (environ 20 min).</p>",
        'es' => "<h3>En Coche</h3>\n<p>Siga la SS16 Adriatica hacia el norte en direcci\xc3\xb3n a Pesaro. Salga en Pesaro Norte y siga las indicaciones hacia el Parque Natural del Monte San Bartolo.</p>\n<h3>Desde el Aeropuerto</h3>\n<p>El aeropuerto m\xc3\xa1s cercano es Federico Fellini de R\xc3\xadmini (RMI), a unos 40 km. Se recomienda alquilar un coche.</p>\n<h3>En Tren</h3>\n<p>La estaci\xc3\xb3n m\xc3\xa1s cercana es Pesaro, conectada a la red Trenitalia. Desde all\xc3\xad en taxi (unos 20 min).</p>",
    ];
    foreach ($tr($en['location']) as $lang => $pid) {
        if ($lang === 'en') continue;
        $copy_meta($en['location'], $pid, ['address', 'maps_embed']);
        if (isset($directions[$lang])) {
            update_post_meta($pid, 'indicazioni_stradali', $directions[$lang]);
        }
    }

    // ── 4. Pool — copy gallery field + fill translated WP editor content ──────
    $pool_content = [
        'it' => "<p>La piscina di Torre San Bartolo \xc3\xa8 un invito a rallentare il tempo. Adagiata su una terrazza naturale con vista sul mare Adriatico, l'acqua cristallina riflette il cielo e gli ulivi centenari che la circondano.</p>\n<p>Riservata esclusivamente agli ospiti della villa, la piscina garantisce la massima privacy. Lettini, ombrelloni e un servizio di bevande freschissime completano un'esperienza di lusso discreto.</p>",
        'de' => "<p>Der Pool von Torre San Bartolo ist eine Einladung, die Zeit zu verlangsamen. Auf einer nat\xc3\xbcrlichen Terrasse mit Blick auf die Adria gelegen, spiegelt das kristallklare Wasser den Himmel und die jahrhundertealten Olivenb\xc3\xa4ume wider.</p>\n<p>Ausschlie\xc3\x9flich f\xc3\xbcr die Villeng\xc3\xa4ste reserviert, garantiert der Pool maximale Privatsph\xc3\xa4re. Liegest\xc3\xbchle, Sonnenschirme und Erfrischungsgetr\xc3\xa4nke sind inklusive.</p>",
        'fr' => "<p>La piscine de Torre San Bartolo est une invitation \xc3\xa0 ralentir le temps. Nich\xc3\xa9e sur une terrasse naturelle avec vue sur la mer Adriatique, l'eau cristalline refl\xc3\xa8te le ciel et les oliviers centenaires qui l'entourent.</p>\n<p>R\xc3\xa9serv\xc3\xa9e exclusivement aux h\xc3\xb4tes de la villa, la piscine garantit une intimit\xc3\xa9 absolue. Chaises longues, parasols et service de boissons fra\xc3\xaeches sont inclus.</p>",
        'es' => "<p>La piscina de Torre San Bartolo es una invitaci\xc3\xb3n a ralentizar el tiempo. Situada en una terraza natural con vistas al mar Adri\xc3\xa1tico, el agua cristalina refleja el cielo y los centenarios olivos que la rodean.</p>\n<p>Reservada exclusivamente para los hu\xc3\xa9spedes de la villa, la piscina garantiza la m\xc3\xa1xima privacidad. Tumbonas, sombrillas y servicio de bebidas frescas incluidos.</p>",
    ];
    foreach ($tr($en['pool']) as $lang => $pid) {
        if ($lang === 'en') continue;
        $copy_meta($en['pool'], $pid, ['pool_gallery']);
        if (isset($pool_content[$lang]) && ! trim(get_post_field('post_content', $pid))) {
            wp_update_post(['ID' => $pid, 'post_content' => $pool_content[$lang]]);
        }
    }

    // ── 5. Gallery — copy photo_gallery + hero_subtitle ───────────────────────
    $gallery_subtitles = [
        'it' => "La bellezza di Torre San Bartolo attraverso il nostro obiettivo.",
        'de' => "Die Sch\xc3\xb6nheit von Torre San Bartolo durch unsere Linse.",
        'fr' => "La beaut\xc3\xa9 de Torre San Bartolo \xc3\xa0 travers notre objectif.",
        'es' => "La belleza de Torre San Bartolo a trav\xc3\xa9s de nuestra lente.",
    ];
    if (! get_post_meta($en['gallery'], 'hero_subtitle', true)) {
        update_post_meta($en['gallery'], 'hero_subtitle', 'The beauty of Torre San Bartolo through our lens.');
    }
    foreach ($tr($en['gallery']) as $lang => $pid) {
        if ($lang === 'en') continue;
        $copy_meta($en['gallery'], $pid, ['photo_gallery']);
        if (isset($gallery_subtitles[$lang])) {
            update_post_meta($pid, 'hero_subtitle', $gallery_subtitles[$lang]);
        }
    }

    // ── 6. FAQ — copy faq_pagina + hero_subtitle ─────────────────────────────
    $faq_subtitles = [
        'it' => "Tutto quello che devi sapere prima di arrivare.",
        'de' => "Alles, was Sie vor Ihrer Ankunft wissen m\xc3\xbcssen.",
        'fr' => "Tout ce que vous devez savoir avant votre arriv\xc3\xa9e.",
        'es' => "Todo lo que necesitas saber antes de llegar.",
    ];
    if (! get_post_meta($en['faq'], 'hero_subtitle', true)) {
        update_post_meta($en['faq'], 'hero_subtitle', 'Everything you need to know before you arrive.');
    }
    foreach ($tr($en['faq']) as $lang => $pid) {
        if ($lang === 'en') continue;
        $copy_meta($en['faq'], $pid, ['faq_pagina']);
        if (isset($faq_subtitles[$lang])) {
            update_post_meta($pid, 'hero_subtitle', $faq_subtitles[$lang]);
        }
    }

    // ── 7. Suites + Experiences — hero_subtitle ────────────────────────────────
    $suites_subtitles = [
        'it' => "Tre suite. Un'unica villa privata.",
        'de' => "Drei Suiten. Ein exklusives Privatanwesen.",
        'fr' => "Trois suites. Un domaine priv\xc3\xa9 unique.",
        'es' => "Tres suites. Una finca privada exclusiva.",
    ];
    if (! get_post_meta($en['suites'], 'hero_subtitle', true)) {
        update_post_meta($en['suites'], 'hero_subtitle', 'Three suites. One exclusive private estate.');
    }
    foreach ($tr($en['suites']) as $lang => $pid) {
        if ($lang === 'en') continue;
        if (isset($suites_subtitles[$lang])) {
            update_post_meta($pid, 'hero_subtitle', $suites_subtitles[$lang]);
        }
    }

    $exp_subtitles = [
        'it' => "Assapora, scopri ed esplora il territorio.",
        'de' => "Genie\xc3\x9fen, entdecken und die Region erkunden.",
        'fr' => "Savourez, d\xc3\xa9couvrez et explorez la r\xc3\xa9gion.",
        'es' => "Saborea, descubre y explora la regi\xc3\xb3n.",
    ];
    if (! get_post_meta($en['experiences'], 'hero_subtitle', true)) {
        update_post_meta($en['experiences'], 'hero_subtitle', 'Taste, discover and explore the region.');
    }
    foreach ($tr($en['experiences']) as $lang => $pid) {
        if ($lang === 'en') continue;
        if (isset($exp_subtitles[$lang])) {
            update_post_meta($pid, 'hero_subtitle', $exp_subtitles[$lang]);
        }
    }

    // ── 8. Fix ugly auto-incremented slugs ────────────────────────────────────
    $slug_fixes = [
        257 => 'pool-de',
        251 => 'suites-fr',
        253 => 'suites-es',
        267 => 'experiences-fr',
        275 => 'galerie-fr',
        291 => 'contact-fr',
    ];
    foreach ($slug_fixes as $pid => $new_slug) {
        $current = get_post_field('post_name', $pid);
        if (str_contains($current, '-2') || str_contains($current, '-3')) {
            wp_update_post(['ID' => $pid, 'post_name' => $new_slug]);
        }
    }

    // ── 9. Flush rewrite rules ────────────────────────────────────────────────
    flush_rewrite_rules(true);

    update_option('torresan_translations_seeded_v1', '1');
}

add_action('admin_init', function (): void {
    if (
        ! get_option('torresan_translations_seeded_v1') &&
        get_option('torresan_translations_seeded') &&
        function_exists('pll_get_post_translations')
    ) {
        torresan_seed_translation_fields();
    }
}, 30); // priority 30 — runs after first-pass seed
