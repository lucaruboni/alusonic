<?php
/**
 * Seed all Pods text fields with coherent demo content for Torre San Bartolo.
 *
 * Run once via: wp eval-file or require from a CLI script.
 * Idempotent — overwrites existing field values.
 */

if (! defined('ABSPATH')) {
    exit;
}

function torresan_seed_content(): void
{
    if (! function_exists('pods')) {
        echo "Pods non attivo.\n";
        return;
    }

    // Save via Pods AND update_post_meta for maximum compatibility.
    $save = function (object $pod, int $post_id, string $field, $value): void {
        $pod->save($field, $value);
        // Also write to post meta directly so values survive editor saves
        if (is_array($value)) {
            delete_post_meta($post_id, $field);
            foreach ($value as $v) {
                add_post_meta($post_id, $field, $v);
            }
        } else {
            update_post_meta($post_id, $field, $value);
        }
    };

    /* ════════════════════════════════════════════
     *  1. Create FAQ items (domanda_faq CPT)
     * ════════════════════════════════════════════ */
    $faqs = [
        [
            'title'   => 'Qual è la politica di cancellazione?',
            'content' => 'La cancellazione è gratuita fino a 14 giorni prima del check-in. Per cancellazioni tardive o no-show, verrà addebitato il 50% del totale del soggiorno. In caso di forza maggiore, valutiamo ogni situazione individualmente.',
        ],
        [
            'title'   => 'A che ora posso fare il check-in e il check-out?',
            'content' => 'Il check-in è disponibile dalle ore 15:00 alle 20:00. Il check-out va effettuato entro le ore 10:00 del giorno di partenza. Per esigenze particolari, contattateci e faremo il possibile per venirvi incontro.',
        ],
        [
            'title'   => 'La struttura è adatta ai bambini?',
            'content' => 'Assolutamente sì. Torre San Bartolo è perfetta per famiglie: il grande giardino, la piscina (con zona bassa) e gli ampi spazi interni offrono ai bambini libertà e sicurezza. Forniamo lettini e seggioloni su richiesta.',
        ],
        [
            'title'   => 'Sono ammessi animali domestici?',
            'content' => 'Accogliamo con piacere cani di piccola e media taglia (max 2 per soggiorno). Vi chiediamo di comunicarlo al momento della prenotazione. Un supplemento di €15/giorno per animale viene applicato per la pulizia extra.',
        ],
        [
            'title'   => 'La proprietà viene affittata per intero?',
            'content' => 'Sì, Torre San Bartolo viene affittata esclusivamente per intero. Non condividerete gli spazi con altri ospiti: l\'intera villa, il giardino, la piscina e tutte le aree comuni saranno a vostra completa disposizione.',
        ],
        [
            'title'   => 'È disponibile il parcheggio?',
            'content' => 'Disponiamo di un parcheggio privato gratuito all\'interno della proprietà, con spazio per 3-4 auto. Il parcheggio è recintato e illuminato.',
        ],
        [
            'title'   => 'Fornite biancheria e prodotti per la casa?',
            'content' => 'La struttura è dotata di biancheria da letto e da bagno di alta qualità, asciugamani per la piscina, prodotti da bagno artigianali, e una cucina completamente attrezzata con stoviglie, pentole e accessori per la cottura.',
        ],
        [
            'title'   => 'C\'è la connessione Wi-Fi?',
            'content' => 'Sì, Wi-Fi ad alta velocità è disponibile in tutta la proprietà, comprese le aree esterne e la zona piscina. Ideale anche per chi necessita di lavorare durante il soggiorno.',
        ],
    ];

    $faq_ids = [];
    foreach ($faqs as $faq) {
        $existing = get_page_by_title($faq['title'], OBJECT, 'domanda_faq');
        if ($existing) {
            wp_update_post([
                'ID'           => $existing->ID,
                'post_content' => $faq['content'],
                'post_status'  => 'publish',
            ]);
            $faq_ids[] = $existing->ID;
        } else {
            $faq_ids[] = wp_insert_post([
                'post_type'    => 'domanda_faq',
                'post_title'   => $faq['title'],
                'post_content' => $faq['content'],
                'post_status'  => 'publish',
            ]);
        }
    }
    echo "FAQ create/aggiornate: " . count($faq_ids) . "\n";

    /* ════════════════════════════════════════════
     *  2. Create Camere (camera CPT)
     * ════════════════════════════════════════════ */
    $camere = [
        [
            'title'   => 'Camera della Torre',
            'content' => '<p>La camera più suggestiva della proprietà: ricavata nella torre originale del XVI secolo, offre muri in pietra a vista, soffitto con travi in legno e una finestra panoramica che abbraccia le colline fino al mare Adriatico. L\'arredamento mescola pezzi d\'antiquariato locale con comfort moderni.</p>',
            'fields'  => [
                'camera_guests'   => 2,
                'camera_size'     => '28 mq',
                'camera_beds'     => 'Letto matrimoniale king-size',
                'camera_bathroom' => 'Bagno privato con doccia in pietra e toilette separata',
                'camera_features' => "Wi-Fi ad alta velocità\nAria condizionata\nCassaforte\nAsciugacapelli\nProdotti da bagno biologici\nVista panoramica sulle colline\nTravi in legno originali",
                'camera_prezzo'   => 'da €160/notte',
            ],
        ],
        [
            'title'   => 'Camera del Giardino',
            'content' => '<p>Affacciata sul giardino degli ulivi, questa camera al piano terra offre accesso diretto al prato e alla piscina. Luminosa e spaziosa, con pavimento in cotto originale e una piccola zona lettura. Perfetta per chi ama svegliarsi e fare subito due passi all\'aperto.</p>',
            'fields'  => [
                'camera_guests'   => 2,
                'camera_size'     => '32 mq',
                'camera_beds'     => 'Letto matrimoniale queen-size',
                'camera_bathroom' => 'Bagno privato con vasca e doccia',
                'camera_features' => "Wi-Fi ad alta velocità\nAria condizionata\nAccesso diretto al giardino\nZona lettura\nCassaforte\nAsciugacapelli\nProdotti da bagno biologici\nPavimento in cotto originale",
                'camera_prezzo'   => 'da €140/notte',
            ],
        ],
        [
            'title'   => 'Camera delle Colline',
            'content' => '<p>Al primo piano, con un\'ampia finestra che incornicia il paesaggio collinare. Tonalità calde, tessuti naturali e un letto che invita al riposo. La camera più silenziosa della proprietà, ideale per chi cerca tranquillità assoluta.</p>',
            'fields'  => [
                'camera_guests'   => 2,
                'camera_size'     => '25 mq',
                'camera_beds'     => 'Letto matrimoniale',
                'camera_bathroom' => 'Bagno privato con doccia a pioggia',
                'camera_features' => "Wi-Fi ad alta velocità\nAria condizionata\nVista colline\nCassaforte\nAsciugacapelli\nProdotti da bagno biologici\nOscuranti totali",
                'camera_prezzo'   => 'da €130/notte',
            ],
        ],
        [
            'title'   => 'Camera Famiglia',
            'content' => '<p>La camera più ampia della proprietà, pensata per famiglie o chi desidera spazio extra. Due ambienti comunicanti — una zona notte matrimoniale e una con due letti singoli — condividono un bagno spazioso. Arredata con colori chiari e materiali naturali.</p>',
            'fields'  => [
                'camera_guests'   => 4,
                'camera_size'     => '45 mq',
                'camera_beds'     => 'Matrimoniale + 2 letti singoli',
                'camera_bathroom' => 'Bagno privato con doppio lavabo, doccia e vasca',
                'camera_features' => "Wi-Fi ad alta velocità\nAria condizionata\nDue ambienti comunicanti\nLettino bimbi su richiesta\nCassaforte\nAsciugacapelli\nProdotti da bagno biologici\nVista giardino",
                'camera_prezzo'   => 'da €200/notte',
            ],
        ],
    ];

    foreach ($camere as $cam) {
        $existing = get_page_by_title($cam['title'], OBJECT, 'camera');
        if ($existing) {
            $post_id = $existing->ID;
            wp_update_post([
                'ID'           => $post_id,
                'post_content' => $cam['content'],
                'post_status'  => 'publish',
            ]);
        } else {
            $post_id = wp_insert_post([
                'post_type'    => 'camera',
                'post_title'   => $cam['title'],
                'post_content' => $cam['content'],
                'post_status'  => 'publish',
            ]);
        }

        if ($post_id && ! is_wp_error($post_id)) {
            $pod = pods('camera', $post_id);
            if ($pod && $pod->exists()) {
                foreach ($cam['fields'] as $fname => $fval) {
                    $pod->save($fname, $fval);
                }
            }
        }
    }
    echo "Camere create/aggiornate: " . count($camere) . "\n";

    /* ════════════════════════════════════════════
     *  4. Populate PAGE fields
     * ════════════════════════════════════════════ */

    // Helper to find page by slug
    $page_id = function (string $slug): int {
        $p = get_page_by_path($slug);
        return $p ? $p->ID : 0;
    };

    // ── Homepage (ID 12, slug "home") ──
    $home_id = $page_id('home');
    if ($home_id) {
        $pod = pods('page', $home_id);
        if ($pod && $pod->exists()) {
            // Hero
            $save($pod, $home_id, 'hero_eyebrow', 'Torre San Bartolo');
            $save($pod, $home_id, 'hero_subtitle', 'Una villa esclusiva nel cuore delle Marche, dove il tempo si ferma e la bellezza vi circonda.');
            $save($pod, $home_id, 'hero_cta_text', 'Scopri Disponibilità');
            $save($pod, $home_id, 'hero_cta_url', '/contatti/');

            // Intro
            $save($pod, $home_id, 'intro_text', '<p>Benvenuti a <strong>Torre San Bartolo</strong>, un\'antica dimora ristrutturata con cura nel Parco Naturale del San Bartolo, tra Pesaro e Fano. Qui la campagna marchigiana incontra il mare Adriatico, e ogni soggiorno diventa un ricordo prezioso.</p><p>La proprietà viene affittata per intero: quattro camere eleganti, una piscina a sfioro, un grande giardino con ulivi e tutto il fascino di una torre cinquecentesca. Solo per voi.</p>');

            // Sezione A — Camere (testo + immagini)
            $save($pod, $home_id, 'hp_sa_title', 'Le nostre camere');
            $save($pod, $home_id, 'hp_sa_text', 'Quattro ambienti unici, arredati con cura mescolando pezzi d\'epoca e comfort contemporanei. Materassi di qualità, biancheria in lino e una vista che cambia con la luce del giorno.');
            $save($pod, $home_id, 'hp_sa_cta_text', 'Scopri le Camere');
            $save($pod, $home_id, 'hp_sa_cta_url', '/camere/');

            // Sezione B — Territorio (banner sfondo)
            $save($pod, $home_id, 'hp_sb_eyebrow', 'Il Territorio');
            $save($pod, $home_id, 'hp_sb_text', 'Nel cuore del Parco San Bartolo, tra colline, borghi e il mare Adriatico. Un luogo dove la storia si intreccia con la natura e ogni giornata diventa un\'avventura.');
            $save($pod, $home_id, 'hp_sb_cta_text', 'Scopri il Territorio');
            $save($pod, $home_id, 'hp_sb_cta_url', '/dove-siamo/');

            // Sezione C — Piscina (galleria centrata)
            $save($pod, $home_id, 'hp_sc_eyebrow', 'La Piscina');
            $save($pod, $home_id, 'hp_sc_text', 'Una piscina a sfioro immersa tra gli ulivi, con vista sulle colline marchigiane. Zona solarium, doccia esterna e aperitivi al tramonto.');
            $save($pod, $home_id, 'hp_sc_cta_text', 'Scopri la Piscina');
            $save($pod, $home_id, 'hp_sc_cta_url', '/piscina/');

            // Newsletter
            $save($pod, $home_id, 'newsletter_title', 'Restate in contatto');
            $save($pod, $home_id, 'newsletter_text', 'Iscrivetevi per ricevere offerte esclusive, consigli sul territorio e novità dalla Torre.');
            $save($pod, $home_id, 'newsletter_btn', 'Iscriviti Ora');
        }

        wp_update_post([
            'ID'           => $home_id,
            'post_title'   => 'Torre San Bartolo',
            'post_content' => '',
        ]);

        echo "Homepage popolata.\n";
    }

    // ── Camere page ──
    $camere_id = $page_id('camere');
    if ($camere_id) {
        $pod = pods('page', $camere_id);
        if ($pod && $pod->exists()) {
            $save($pod, $camere_id, 'hero_eyebrow', 'Le Camere');
            $save($pod, $camere_id, 'hero_subtitle', 'Quattro ambienti unici, tutti con vista sulle colline marchigiane.');
            $save($pod, $camere_id, 'hero_cta_text', 'Verifica Disponibilità');
            $save($pod, $camere_id, 'hero_cta_url', '/contatti/');
        }

        wp_update_post([
            'ID'           => $camere_id,
            'post_content' => '<p>Ogni camera di Torre San Bartolo racconta una storia diversa. Arredi d\'epoca, tessuti pregiati e una cura per i dettagli che trasforma ogni notte in un\'esperienza. Scegliete quella che fa per voi.</p>',
        ]);

        echo "Pagina Camere popolata.\n";
    }

    // ── Piscina page ──
    $piscina_id = $page_id('piscina');
    if ($piscina_id) {
        $pod = pods('page', $piscina_id);
        if ($pod && $pod->exists()) {
            $save($pod, $piscina_id, 'hero_eyebrow', 'La Piscina');
            $save($pod, $piscina_id, 'hero_subtitle', 'Un tuffo tra le colline: la nostra piscina a sfioro con vista sul mare.');
            $save($pod, $piscina_id, 'hero_cta_text', 'Prenota il Tuo Soggiorno');
            $save($pod, $piscina_id, 'hero_cta_url', '/contatti/');
        }

        wp_update_post([
            'ID'           => $piscina_id,
            'post_content' => '<p>La piscina a sfioro di Torre San Bartolo è il cuore del relax estivo. Circondata da ulivi e lavanda, offre una vista panoramica che spazia dalle colline al mare Adriatico.</p>',
        ]);

        echo "Pagina Piscina popolata.\n";
    }

    // ── Servizi page ──
    $servizi_id = $page_id('servizi');
    if ($servizi_id) {
        $pod = pods('page', $servizi_id);
        if ($pod && $pod->exists()) {
            $save($pod, $servizi_id, 'hero_eyebrow', 'Servizi & Esperienze');
            $save($pod, $servizi_id, 'hero_subtitle', 'Tutto ciò che serve per un soggiorno perfetto, e molto di più.');
            $save($pod, $servizi_id, 'hero_cta_text', 'Contattaci');
            $save($pod, $servizi_id, 'hero_cta_url', '/contatti/');
        }

        wp_update_post([
            'ID'           => $servizi_id,
            'post_content' => '<p>A Torre San Bartolo ogni dettaglio è pensato per rendere il vostro soggiorno indimenticabile. Dalla cucina attrezzata al servizio concierge, dalle esperienze sul territorio alla cura degli spazi: siamo qui per voi.</p>',
        ]);

        echo "Pagina Servizi popolata.\n";
    }

    // ── Contatti page ──
    $contatti_id = $page_id('contatti');
    if ($contatti_id) {
        $pod = pods('page', $contatti_id);
        if ($pod && $pod->exists()) {
            $save($pod, $contatti_id, 'hero_eyebrow', 'Contatti');
            $save($pod, $contatti_id, 'hero_subtitle', 'Iniziate a pianificare il vostro soggiorno a Torre San Bartolo.');
            $save($pod, $contatti_id, 'hero_cta_text', '');
            $save($pod, $contatti_id, 'hero_cta_url', '');

            // Contact info
            $save($pod, $contatti_id, 'contatti_telefono', '+39 0721 123 456');
            $save($pod, $contatti_id, 'contatti_email', 'info@torresanbartolo.it');
            $save($pod, $contatti_id, 'contatti_orari', "Check-in: 15:00 – 20:00\nCheck-out: entro le 10:00\nRiceviamo su appuntamento");

            // Address & map
            $save($pod, $contatti_id, 'address', 'Strada Panoramica San Bartolo, 24, 61121 Pesaro (PU), Italia');
            $save($pod, $contatti_id, 'maps_embed', '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2871.123!2d12.835!3d43.935!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sParco+San+Bartolo!5e0!3m2!1sit!2sit!4v1700000000000" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>');
        }

        wp_update_post([
            'ID'           => $contatti_id,
            'post_content' => '<p>Avete domande, desiderate un preventivo o volete verificare la disponibilità? Compilate il modulo qui sotto oppure contattateci direttamente. Rispondiamo entro 24 ore.</p>',
        ]);

        echo "Pagina Contatti popolata.\n";
    }

    // ── Dove Siamo page ──
    $dove_id = $page_id('dove-siamo');
    if ($dove_id) {
        $pod = pods('page', $dove_id);
        if ($pod && $pod->exists()) {
            $save($pod, $dove_id, 'hero_eyebrow', 'Dove Siamo');
            $save($pod, $dove_id, 'hero_subtitle', 'Nel cuore del Parco Naturale del San Bartolo, tra Pesaro e Fano.');
            $save($pod, $dove_id, 'hero_cta_text', 'Ottieni Indicazioni');
            $save($pod, $dove_id, 'hero_cta_url', 'https://maps.google.com/?q=Parco+San+Bartolo+Pesaro');

            $save($pod, $dove_id, 'address', 'Strada Panoramica San Bartolo, 24, 61121 Pesaro (PU), Italia');
            $save($pod, $dove_id, 'indicazioni_stradali', '<h3>In auto</h3><p>Dall\'autostrada A14: uscita Pesaro-Urbino, poi seguire le indicazioni per il Parco San Bartolo. La proprietà si trova a 15 minuti dall\'uscita autostradale.</p><h3>In treno</h3><p>Stazione di Pesaro (linea adriatica Bologna-Ancona). Dalla stazione, Torre San Bartolo dista 12 km (20 minuti in taxi). Su richiesta, organizziamo il transfer.</p><h3>In aereo</h3><p>Aeroporto di Rimini Federico Fellini (45 km, ~40 min) oppure Aeroporto di Ancona Falconara (70 km, ~50 min). Servizio navetta disponibile su prenotazione.</p>');
            $save($pod, $dove_id, 'maps_embed', '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2871.123!2d12.835!3d43.935!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sParco+San+Bartolo!5e0!3m2!1sit!2sit!4v1700000000000" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>');
        }

        wp_update_post([
            'ID'           => $dove_id,
            'post_content' => '<p>Torre San Bartolo sorge sulla strada panoramica del Parco Naturale del San Bartolo, uno dei tratti di costa più belli delle Marche. A pochi minuti dalle spiagge e dai borghi dell\'entroterra, la posizione è ideale per chi cerca mare e campagna in un unico soggiorno.</p>',
        ]);

        echo "Pagina Dove Siamo popolata.\n";
    }

    // ── FAQ page ──
    $faq_page_id = $page_id('faq');
    if ($faq_page_id) {
        $pod = pods('page', $faq_page_id);
        if ($pod && $pod->exists()) {
            $save($pod, $faq_page_id, 'hero_eyebrow', 'Domande Frequenti');
            $save($pod, $faq_page_id, 'hero_subtitle', 'Tutto quello che dovete sapere prima di prenotare.');
            $save($pod, $faq_page_id, 'hero_cta_text', 'Contattaci per Altre Domande');
            $save($pod, $faq_page_id, 'hero_cta_url', '/contatti/');

            if ($faq_ids) {
                $save($pod, $faq_page_id, 'faq_pagina', $faq_ids);
            }
        }

        wp_update_post([
            'ID'           => $faq_page_id,
            'post_content' => '<p>Abbiamo raccolto le domande più comuni dei nostri ospiti. Se non trovate la risposta che cercate, non esitate a contattarci direttamente.</p>',
        ]);

        echo "Pagina FAQ popolata.\n";
    }

    // ── Gallery page ──
    $gallery_id = $page_id('galleria');
    if ($gallery_id) {
        $pod = pods('page', $gallery_id);
        if ($pod && $pod->exists()) {
            $save($pod, $gallery_id, 'hero_eyebrow', 'Galleria Fotografica');
            $save($pod, $gallery_id, 'hero_subtitle', 'Lasciatevi ispirare: un\'anteprima della vostra prossima vacanza.');
            $save($pod, $gallery_id, 'hero_cta_text', 'Prenota Ora');
            $save($pod, $gallery_id, 'hero_cta_url', '/contatti/');
        }

        wp_update_post([
            'ID'           => $gallery_id,
            'post_content' => '',
        ]);

        echo "Pagina Galleria popolata.\n";
    }

    echo "\n✓ Seed completato con successo!\n";
}
