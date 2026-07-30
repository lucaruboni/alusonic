<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Seed CPT translations (camera, esperienza), translated homepage fields,
 * and language-specific nav menus.
 *
 * Gated by 'torresan_cpt_i18n_seeded_v1' — runs once per site.
 * Delete the option in WP Admin → Tools → Site Health → Info to re-run.
 */

add_action('admin_init', function (): void {
    if (
        ! get_option('torresan_cpt_i18n_seeded_v1') &&
        function_exists('pll_get_post_translations') &&
        get_option('torresan_translations_seeded')
    ) {
        torresan_seed_cpt_i18n();
    }
}, 40);

function torresan_seed_cpt_i18n(): void
{
    if (get_option('torresan_cpt_i18n_seeded_v1')) {
        return;
    }
    if (! function_exists('pll_set_post_language')) {
        return;
    }

    // ── 1. Ensure camera & esperienza are registered with Polylang ───────────
    $pll_opts = get_option('polylang', []);
    $pll_opts['post_types'] = array_unique(array_merge(
        $pll_opts['post_types'] ?? [],
        ['camera', 'esperienza']
    ));
    update_option('polylang', $pll_opts);

    // ── 2. Create CPT translations ───────────────────────────────────────────
    torresan_create_cpt_translations();

    // ── 3. Populate homepage translated fields ────────────────────────────────
    torresan_seed_homepage_fields();

    // ── 4. Create per-language menus ─────────────────────────────────────────
    torresan_create_language_menus();

    // ── 5. Flush rewrite rules ───────────────────────────────────────────────
    flush_rewrite_rules(true);

    update_option('torresan_cpt_i18n_seeded_v1', '1');
}

/* ─────────────────────────────────────────────────────────────────────────────
   CPT TRANSLATIONS
   ───────────────────────────────────────────────────────────────────────────── */
function torresan_create_cpt_translations(): void
{
    // ── CAMERA definitions ────────────────────────────────────────────────────
    $cameras = [
        98 => [ // The Tower Suite
            'en_slug'  => 'camera-della-torre',
            'neutral'  => ['camera_guests', 'camera_size', 'camera_prezzo', 'camera_gallery', '_thumbnail_id'],
            'langs' => [
                'it' => [
                    'title'    => 'La Suite della Torre',
                    'slug'     => 'la-suite-della-torre',
                    'content'  => '<p>La Suite della Torre occupa il piano superiore dell\'originale torre medievale. Le finestre dal pavimento al soffitto inquadrano viste ininterrotte sul Mare Adriatico e sul Parco Naturale del San Bartolo, mentre il soffitto a volta in pietra e i caldi pavimenti in cotto riportano alla storia secolare della tenuta.</p>',
                    'camera_beds'     => 'Letto matrimoniale king-size',
                    'camera_bathroom' => 'Bagno privato con doccia a pioggia in pietra e WC separato',
                    'camera_features' => "Wi-Fi ad alta velocità\nAria condizionata\nCassaforte\nAsciugacapelli\nProdotti da bagno biologici\nVista panoramica sul mare",
                ],
                'de' => [
                    'title'    => 'Die Turm-Suite',
                    'slug'     => 'die-turm-suite',
                    'content'  => '<p>Die Turm-Suite belegt das Obergeschoss des originalen mittelalterlichen Turms. Raumhohe Fenster rahmen den ungehinderten Blick auf die Adria und den Naturpark San Bartolo, während das gewölbte Steingewölbe und die warmen Terrakottaböden die jahrhundertealte Geschichte des Anwesens spürbar machen.</p>',
                    'camera_beds'     => 'King-size Doppelbett',
                    'camera_bathroom' => 'Eigenes Bad mit Steindusche und separatem WC',
                    'camera_features' => "Schnelles WLAN\nKlimaanlage\nTresor\nHaartrockner\nBiologische Badeprodukte\nPanorامablick auf das Meer",
                ],
                'fr' => [
                    'title'    => 'La Suite de la Tour',
                    'slug'     => 'la-suite-de-la-tour',
                    'content'  => '<p>La Suite de la Tour occupe l\'étage supérieur de la tour médiévale d\'origine. Des fenêtres du sol au plafond cadrent des vues dégagées sur la mer Adriatique et le Parc Naturel du San Bartolo, tandis que le plafond voûté en pierre et les chauds carrelages en terre cuite ancrent la chambre dans cinq siècles d\'histoire.</p>',
                    'camera_beds'     => 'Grand lit king-size',
                    'camera_bathroom' => 'Salle de bain privative avec douche en pierre et WC séparé',
                    'camera_features' => "Wi-Fi haut débit\nClimatisation\nCoffre-fort\nSèche-cheveux\nProduits de bain biologiques\nVue panoramique sur la mer",
                ],
                'es' => [
                    'title'    => 'La Suite de la Torre',
                    'slug'     => 'la-suite-de-la-torre',
                    'content'  => '<p>La Suite de la Torre ocupa la planta superior de la torre medieval original. Las ventanas de suelo a techo enmarcan vistas ininterrumpidas del mar Adriático y el Parque Natural de San Bartolo, mientras que el techo abovedado de piedra y los cálidos suelos de terracota conectan la habitación con cinco siglos de historia.</p>',
                    'camera_beds'     => 'Cama de matrimonio king-size',
                    'camera_bathroom' => 'Baño privado con ducha de lluvia de piedra y WC separado',
                    'camera_features' => "Wi-Fi de alta velocidad\nAire acondicionado\nCaja fuerte\nSecador de pelo\nProductos de baño ecológicos\nVistas panorámicas al mar",
                ],
            ],
        ],
        100 => [ // The Hill Suite
            'en_slug'  => 'camera-delle-colline',
            'neutral'  => ['camera_guests', 'camera_size', 'camera_prezzo', 'camera_gallery', '_thumbnail_id'],
            'langs' => [
                'it' => [
                    'title'    => 'La Suite delle Colline',
                    'slug'     => 'la-suite-delle-colline',
                    'content'  => '<p>Luminosa e ariosa, la Suite delle Colline si apre su una terrazza privata con vista sulle colline marchigiane. Arredi antichi accuratamente selezionati convivono con comodità moderne in un ambiente pensato per chi cerca relax e autenticità.</p>',
                    'camera_beds'     => 'Letto matrimoniale queen-size',
                    'camera_bathroom' => 'Bagno privato con doccia a pioggia',
                    'camera_features' => "Wi-Fi ad alta velocità\nAria condizionata\nTerrazza privata\nCassaforte\nAsciugacapelli\nProdotti da bagno biologici",
                ],
                'de' => [
                    'title'    => 'Die Hügel-Suite',
                    'slug'     => 'die-huegel-suite',
                    'content'  => '<p>Hell und luftig öffnet sich die Hügel-Suite auf eine private Terrasse mit Blick auf die sanften Hügel der Marken. Sorgfältig ausgewählte Antiquitäten treffen auf moderne Annehmlichkeiten in einem Ambiente, das Erholung und Authentizität vereint.</p>',
                    'camera_beds'     => 'Queen-size Doppelbett',
                    'camera_bathroom' => 'Eigenes Bad mit Regendusche',
                    'camera_features' => "Schnelles WLAN\nKlimaanlage\nPrivate Terrasse\nTresor\nHaartrockner\nBiologische Badeprodukte",
                ],
                'fr' => [
                    'title'    => 'La Suite des Collines',
                    'slug'     => 'la-suite-des-collines',
                    'content'  => '<p>Lumineuse et aérée, la Suite des Collines s\'ouvre sur une terrasse privée surplombant les douces collines des Marches. Des antiquités soigneusement sélectionnées côtoient les commodités modernes dans un cadre pensé pour le repos et l\'authenticité.</p>',
                    'camera_beds'     => 'Grand lit queen-size',
                    'camera_bathroom' => 'Salle de bain privative avec douche tropicale',
                    'camera_features' => "Wi-Fi haut débit\nClimatisation\nTerrasse privée\nCoffre-fort\nSèche-cheveux\nProduits de bain biologiques",
                ],
                'es' => [
                    'title'    => 'La Suite de las Colinas',
                    'slug'     => 'la-suite-de-las-colinas',
                    'content'  => '<p>Luminosa y aireada, la Suite de las Colinas se abre a una terraza privada con vistas a las suaves colinas de Las Marcas. Antigüedades cuidadosamente seleccionadas conviven con las comodidades modernas en un ambiente pensado para el descanso y la autenticidad.</p>',
                    'camera_beds'     => 'Cama de matrimonio queen-size',
                    'camera_bathroom' => 'Baño privado con ducha de lluvia',
                    'camera_features' => "Wi-Fi de alta velocidad\nAire acondicionado\nTerraza privada\nCaja fuerte\nSecador de pelo\nProductos de baño ecológicos",
                ],
            ],
        ],
        101 => [ // The Garden Suite
            'en_slug'  => 'camera-famiglia',
            'neutral'  => ['camera_guests', 'camera_size', 'camera_prezzo', 'camera_gallery', '_thumbnail_id'],
            'langs' => [
                'it' => [
                    'title'    => 'La Suite del Giardino',
                    'slug'     => 'la-suite-del-giardino',
                    'content'  => '<p>La Suite del Giardino è la camera più grande della tenuta, pensata appositamente per famiglie o ospiti che cercano spazio extra. Accede direttamente al giardino privato ed è arredata con gusto per rendere ogni momento di soggiorno memorabile.</p>',
                    'camera_beds'     => 'Letto matrimoniale king-size + 2 letti singoli',
                    'camera_bathroom' => 'Bagno privato con doppio lavandino, doccia e vasca',
                    'camera_features' => "Wi-Fi ad alta velocità\nAria condizionata\nAccesso diretto al giardino\nLettino disponibile su richiesta\nCassaforte\nAsciugacapelli\nProdotti da bagno biologici",
                ],
                'de' => [
                    'title'    => 'Die Garten-Suite',
                    'slug'     => 'die-garten-suite',
                    'content'  => '<p>Die Garten-Suite ist das größte Zimmer des Anwesens, eigens für Familien oder Gäste konzipiert, die mehr Platz benötigen. Mit direktem Zugang zum privaten Garten und geschmackvoller Einrichtung bietet sie unvergessliche Aufenthaltsmomente.</p>',
                    'camera_beds'     => 'King-size Bett + 2 Einzelbetten',
                    'camera_bathroom' => 'Eigenes Bad mit Doppelwaschbecken, Dusche und Badewanne',
                    'camera_features' => "Schnelles WLAN\nKlimaanlage\nDirekter Gartenzugang\nReisebett auf Anfrage\nTresor\nHaartrockner\nBiologische Badeprodukte",
                ],
                'fr' => [
                    'title'    => 'La Suite du Jardin',
                    'slug'     => 'la-suite-du-jardin',
                    'content'  => '<p>La Suite du Jardin est la plus grande chambre de la propriété, conçue spécifiquement pour les familles ou les hôtes en quête d\'espace supplémentaire. Avec accès direct au jardin privé et une décoration raffinée, chaque moment du séjour devient inoubliable.</p>',
                    'camera_beds'     => 'Grand lit king-size + 2 lits simples',
                    'camera_bathroom' => 'Salle de bain avec double vasque, douche et baignoire',
                    'camera_features' => "Wi-Fi haut débit\nClimatisation\nAccès direct au jardin\nLit bébé sur demande\nCoffre-fort\nSèche-cheveux\nProduits de bain biologiques",
                ],
                'es' => [
                    'title'    => 'La Suite del Jardín',
                    'slug'     => 'la-suite-del-jardin',
                    'content'  => '<p>La Suite del Jardín es la habitación más grande de la propiedad, diseñada específicamente para familias o huéspedes que buscan espacio adicional. Con acceso directo al jardín privado y una decoración exquisita, cada momento de la estancia se convierte en memorable.</p>',
                    'camera_beds'     => 'Cama king-size + 2 camas individuales',
                    'camera_bathroom' => 'Baño privado con doble lavabo, ducha y bañera',
                    'camera_features' => "Wi-Fi de alta velocidad\nAire acondicionado\nAcceso directo al jardín\nCuna disponible bajo petición\nCaja fuerte\nSecador de pelo\nProductos de baño ecológicos",
                ],
            ],
        ],
    ];

    // ── ESPERIENZA definitions ─────────────────────────────────────────────────
    $esperienze = [
        211 => [ // Truffle Hunting
            'en_slug' => 'truffle-hunting-in-the-hills',
            'neutral' => ['exp_price', 'exp_max_guests', 'exp_gallery', '_thumbnail_id'],
            'langs' => [
                'it' => [
                    'title'         => 'Caccia al Tartufo tra i Boschi',
                    'slug'          => 'caccia-al-tartufo-tra-i-boschi',
                    'content'       => '<p>Unisciti a un esperto cercatore di tartufi e al suo cane per una escursione di 3 ore nei boschi collinari intorno a Torre San Bartolo. Impara a riconoscere i segnali nascosti nella terra, poi assaggia i tuoi ritrovamenti con un abbinamento di vini locali.</p>',
                    'exp_duration'  => 'Mezza giornata (circa 3 ore)',
                    'exp_highlights'=> "Trasporto incluso\nDegustazione di tartufi con abbinamento vini\nTutta l'attrezzatura fornita\nEsperienza privata",
                ],
                'de' => [
                    'title'         => 'Trüffelsuche in den Hügeln',
                    'slug'          => 'trueffelsuche-in-den-huegeln',
                    'content'       => '<p>Begleiten Sie einen erfahrenen Trüffelsucher und seinen Hund auf einer 3-stündigen Expedition durch die Hügelwälder rund um Torre San Bartolo. Erlernen Sie, die verborgenen Zeichen im Boden zu lesen, und genießen Sie Ihre Funde bei einer Weinverkostung.</p>',
                    'exp_duration'  => 'Halbtag (ca. 3 Stunden)',
                    'exp_highlights'=> "Transport inklusive\nTrüffelverkostung mit Weinbegleitung\nGesamte Ausrüstung gestellt\nPrivates Erlebnis",
                ],
                'fr' => [
                    'title'         => 'Chasse aux Truffes en Forêt',
                    'slug'          => 'chasse-aux-truffes-en-foret',
                    'content'       => '<p>Rejoignez un expert trufficulteur et son chien pour une expédition de 3 heures dans les bois des collines autour de Torre San Bartolo. Apprenez à lire les signes cachés dans la terre, puis savourez vos trouvailles accompagnées de vins locaux.</p>',
                    'exp_duration'  => 'Demi-journée (environ 3 heures)',
                    'exp_highlights'=> "Transport inclus\nDégustation de truffes avec accord mets-vins\nTout l'équipement fourni\nExpérience privée",
                ],
                'es' => [
                    'title'         => 'Caza de Trufas en los Bosques',
                    'slug'          => 'caza-de-trufas-en-los-bosques',
                    'content'       => '<p>Únete a un experto buscador de trufas y su perro en una expedición de 3 horas por los bosques de colinas alrededor de Torre San Bartolo. Aprende a leer las señales ocultas en la tierra y luego saborea tus hallazgos con una cata de vinos locales.</p>',
                    'exp_duration'  => 'Media jornada (aprox. 3 horas)',
                    'exp_highlights'=> "Transporte incluido\nCata de trufas con maridaje de vinos\nTodo el equipo proporcionado\nExperiencia privada",
                ],
            ],
        ],
        212 => [ // Wine Tour
            'en_slug' => 'marche-wine-vineyard-tour',
            'neutral' => ['exp_price', 'exp_max_guests', 'exp_gallery', '_thumbnail_id'],
            'langs' => [
                'it' => [
                    'title'         => 'Tour dei Vigneti delle Marche',
                    'slug'          => 'tour-dei-vigneti-delle-marche',
                    'content'       => '<p>Le colline intorno a Torre San Bartolo producono alcuni dei migliori vini delle Marche. Questo tour privato vi porta in due cantine selezionate, dove incontrerete i produttori, esplorerete le vigne e degusterete cinque vini abbinati a prodotti locali.</p>',
                    'exp_duration'  => 'Mattina intera (circa 4 ore)',
                    'exp_highlights'=> "Prenotazioni private in due tenute\nVisita in vigna con il produttore\nDegustazione in cantina (5 vini)\nAbbinamento con prodotti locali",
                ],
                'de' => [
                    'title'         => 'Weinberg-Tour durch die Marken',
                    'slug'          => 'weinberg-tour-durch-die-marken',
                    'content'       => '<p>Die Hügel rund um Torre San Bartolo produzieren einige der besten Weine der Marken. Diese private Tour führt Sie zu zwei ausgewählten Weingütern, wo Sie die Winzer treffen, die Weinberge erkunden und fünf Weine zu lokalen Produkten verkosten.</p>',
                    'exp_duration'  => 'Ganzer Morgen (ca. 4 Stunden)',
                    'exp_highlights'=> "Private Reservierungen auf zwei Weingütern\nWeinbergführung mit Winzer\nKellerverkostung (5 Weine)\nBegleitung mit lokalen Produkten",
                ],
                'fr' => [
                    'title'         => 'Tour des Vignobles des Marches',
                    'slug'          => 'tour-des-vignobles-des-marches',
                    'content'       => '<p>Les collines autour de Torre San Bartolo produisent certains des meilleurs vins des Marches. Cette visite privée vous emmène dans deux domaines sélectionnés, où vous rencontrerez les vignerons, explorerez les vignes et dégusterez cinq vins accompagnés de produits locaux.</p>',
                    'exp_duration'  => 'Matinée complète (environ 4 heures)',
                    'exp_highlights'=> "Réservations privées dans deux domaines\nVisite du vignoble avec le vigneron\nDégustation en cave (5 vins)\nAccompagnement de produits locaux",
                ],
                'es' => [
                    'title'         => 'Tour de los Viñedos de Las Marcas',
                    'slug'          => 'tour-de-los-vinedos-de-las-marcas',
                    'content'       => '<p>Las colinas alrededor de Torre San Bartolo producen algunos de los mejores vinos de Las Marcas. Este recorrido privado le lleva a dos bodegas seleccionadas, donde conocerá a los productores, explorará los viñedos y degustará cinco vinos acompañados de productos locales.</p>',
                    'exp_duration'  => 'Mañana completa (aprox. 4 horas)',
                    'exp_highlights'=> "Reservas privadas en dos bodegas\nVisita al viñedo con el viticultor\nCata en bodega (5 vinos)\nAcompañamiento de productos locales",
                ],
            ],
        ],
        213 => [ // Cooking Class
            'en_slug' => 'marche-cooking-class',
            'neutral' => ['exp_price', 'exp_max_guests', 'exp_gallery', '_thumbnail_id'],
            'langs' => [
                'it' => [
                    'title'         => 'Corso di Cucina Marchigiana',
                    'slug'          => 'corso-di-cucina-marchigiana',
                    'content'       => '<p>Una mattinata pratica nella cucina in pietra della fattoria della tenuta. Sotto la guida di una cuoca locale, imparerete a preparare un menu marchigiano completo di quattro portate: pasta fresca, secondo di stagione, verdure dell\'orto e dolce tradizionale.</p>',
                    'exp_duration'  => 'Mezza giornata (circa 4 ore)',
                    'exp_highlights'=> "Tutti gli ingredienti inclusi\nMenu completo di 4 portate\nAbbinamento vini\nRicettario da portare a casa",
                ],
                'de' => [
                    'title'         => 'Kochkurs der Marken-Küche',
                    'slug'          => 'kochkurs-der-marken-kueche',
                    'content'       => '<p>Ein praktischer Kursmorgen in der Steinbauernküche des Anwesens. Unter Anleitung einer lokalen Köchin lernen Sie, ein vollständiges marchigianisches 4-Gänge-Menü zuzubereiten: frische Pasta, saisonales Hauptgericht, Gartengemüse und traditionelles Dessert.</p>',
                    'exp_duration'  => 'Halbtag (ca. 4 Stunden)',
                    'exp_highlights'=> "Alle Zutaten inklusive\nVollständiges 4-Gänge-Menü\nWeinbegleitung\nRezeptheft zum Mitnehmen",
                ],
                'fr' => [
                    'title'         => 'Cours de Cuisine des Marches',
                    'slug'          => 'cours-de-cuisine-des-marches',
                    'content'       => '<p>Une matinée pratique dans la cuisine en pierre de la ferme de la propriété. Sous la guidance d\'une cuisinière locale, vous apprendrez à préparer un menu complet des Marches en quatre plats : pâtes fraîches, plat principal de saison, légumes du potager et dessert traditionnel.</p>',
                    'exp_duration'  => 'Demi-journée (environ 4 heures)',
                    'exp_highlights'=> "Tous les ingrédients inclus\nMenu complet en 4 plats\nAccord mets-vins\nLivre de recettes à emporter",
                ],
                'es' => [
                    'title'         => 'Clase de Cocina de Las Marcas',
                    'slug'          => 'clase-de-cocina-de-las-marcas',
                    'content'       => '<p>Una mañana práctica en la cocina de piedra de la granja de la finca. Bajo la guía de una cocinera local, aprenderá a preparar un menú marchigiano completo de cuatro platos: pasta fresca, plato principal de temporada, verduras del huerto y postre tradicional.</p>',
                    'exp_duration'  => 'Media jornada (aprox. 4 horas)',
                    'exp_highlights'=> "Todos los ingredientes incluidos\nMenú completo de 4 platos\nMaridaje de vinos\nRecetario para llevar a casa",
                ],
            ],
        ],
        214 => [ // Sunrise Yoga
            'en_slug' => 'sunrise-yoga-by-the-pool',
            'neutral' => ['exp_price', 'exp_max_guests', 'exp_gallery', '_thumbnail_id'],
            'langs' => [
                'it' => [
                    'title'         => 'Yoga all\'Alba in Piscina',
                    'slug'          => 'yoga-allalba-in-piscina',
                    'content'       => '<p>Inizia la giornata con una sessione privata di yoga di 90 minuti a bordo piscina, mentre il sole sorge sull\'Adriatico. Un istruttore certificato ti guiderà in un percorso personalizzato adatto a tutti i livelli, seguito da una colazione leggera con prodotti locali.</p>',
                    'exp_duration'  => '90 minuti + colazione',
                    'exp_highlights'=> "Tutta l'attrezzatura fornita\nIstruttore certificato privato\nColazione leggera inclusa\nPersonalizzato per tutti i livelli",
                ],
                'de' => [
                    'title'         => 'Sonnenaufgangs-Yoga am Pool',
                    'slug'          => 'sonnenaufgangs-yoga-am-pool',
                    'content'       => '<p>Beginnen Sie den Tag mit einer privaten 90-minütigen Yoga-Session am Pool, während die Sonne über der Adria aufgeht. Ein zertifizierter Instructor begleitet Sie durch eine auf Ihr Niveau abgestimmte Einheit, gefolgt von einem leichten Frühstück mit lokalen Produkten.</p>',
                    'exp_duration'  => '90 Minuten + Frühstück',
                    'exp_highlights'=> "Gesamte Ausrüstung gestellt\nPrivater zertifizierter Instructor\nLeichtes Frühstück inklusive\nFür alle Niveaus angepasst",
                ],
                'fr' => [
                    'title'         => 'Yoga au Lever du Soleil à la Piscine',
                    'slug'          => 'yoga-au-lever-du-soleil-a-la-piscine',
                    'content'       => '<p>Commencez la journée avec une session privée de yoga de 90 minutes au bord de la piscine, au moment où le soleil se lève sur l\'Adriatique. Un instructeur certifié vous guidera dans un parcours personnalisé adapté à tous les niveaux, suivi d\'un petit-déjeuner léger avec des produits locaux.</p>',
                    'exp_duration'  => '90 minutes + petit-déjeuner',
                    'exp_highlights'=> "Tout l'équipement fourni\nInstructeur certifié privé\nPetit-déjeuner léger inclus\nPersonnalisé pour tous les niveaux",
                ],
                'es' => [
                    'title'         => 'Yoga al Amanecer junto a la Piscina',
                    'slug'          => 'yoga-al-amanecer-junto-a-la-piscina',
                    'content'       => '<p>Comienza el día con una sesión privada de yoga de 90 minutos junto a la piscina, mientras el sol sale sobre el Adriático. Un instructor certificado te guiará por un recorrido personalizado adaptado a todos los niveles, seguido de un desayuno ligero con productos locales.</p>',
                    'exp_duration'  => '90 minutos + desayuno',
                    'exp_highlights'=> "Todo el equipo proporcionado\nInstructor certificado privado\nDesayuno ligero incluido\nPersonalizado para todos los niveles",
                ],
            ],
        ],
    ];

    // ── Create camera translations ─────────────────────────────────────────────
    foreach ($cameras as $en_id => $def) {
        // Assign EN language to original
        pll_set_post_language($en_id, 'en');
        $translation_ids = ['en' => $en_id];

        foreach ($def['langs'] as $lang => $data) {
            // Check if translation already exists
            $existing = pll_get_post($en_id, $lang);
            if ($existing) {
                $translation_ids[$lang] = $existing;
                continue;
            }

            $new_id = wp_insert_post([
                'post_type'    => 'camera',
                'post_status'  => 'publish',
                'post_title'   => $data['title'],
                'post_name'    => $data['slug'],
                'post_content' => $data['content'],
            ]);

            if (is_wp_error($new_id)) continue;

            pll_set_post_language($new_id, $lang);
            $translation_ids[$lang] = $new_id;

            // Copy neutral fields
            foreach ($def['neutral'] as $key) {
                $val = get_post_meta($en_id, $key, true);
                if ($val !== '' && $val !== false) {
                    update_post_meta($new_id, $key, $val);
                }
            }

            // Set translated fields
            foreach (['camera_beds', 'camera_bathroom', 'camera_features'] as $field) {
                if (isset($data[$field])) {
                    update_post_meta($new_id, $field, $data[$field]);
                }
            }
        }

        if (count($translation_ids) > 1) {
            pll_save_post_translations($translation_ids);
        }
    }

    // ── Create esperienza translations ────────────────────────────────────────
    foreach ($esperienze as $en_id => $def) {
        pll_set_post_language($en_id, 'en');
        $translation_ids = ['en' => $en_id];

        foreach ($def['langs'] as $lang => $data) {
            $existing = pll_get_post($en_id, $lang);
            if ($existing) {
                $translation_ids[$lang] = $existing;
                continue;
            }

            $new_id = wp_insert_post([
                'post_type'    => 'esperienza',
                'post_status'  => 'publish',
                'post_title'   => $data['title'],
                'post_name'    => $data['slug'],
                'post_content' => $data['content'],
            ]);

            if (is_wp_error($new_id)) continue;

            pll_set_post_language($new_id, $lang);
            $translation_ids[$lang] = $new_id;

            // Copy neutral fields
            foreach ($def['neutral'] as $key) {
                $val = get_post_meta($en_id, $key, true);
                if ($val !== '' && $val !== false) {
                    update_post_meta($new_id, $key, $val);
                }
            }

            // Set translated fields
            foreach (['exp_duration', 'exp_highlights'] as $field) {
                if (isset($data[$field])) {
                    update_post_meta($new_id, $field, $data[$field]);
                }
            }
        }

        if (count($translation_ids) > 1) {
            pll_save_post_translations($translation_ids);
        }
    }
}

/* ─────────────────────────────────────────────────────────────────────────────
   HOMEPAGE TRANSLATED FIELDS
   ───────────────────────────────────────────────────────────────────────────── */
function torresan_seed_homepage_fields(): void
{
    $en_id = 12;
    $tr     = pll_get_post_translations($en_id);

    // Image/gallery fields — language-neutral, copy from EN
    $neutral_keys = [
        '_thumbnail_id', 'intro_image', 'intro_image_1', 'intro_image_2',
        'photo_gallery', 'hp_sa_gallery', 'hp_sb_bg', 'hp_sc_gallery',
    ];

    // Translatable content per language
    $content = [
        'it' => [
            'hero_cta_text'    => 'Verifica Disponibilità',
            'hero_cta_url'     => '/it/contatti/',
            'intro_text'       => '<em>Immersa lungo la protetta costa del Parco del San Bartolo, Torre San Bartolo è una tenuta del XV secolo che domina l\'Adriatico dalle colline delle Marche. Tre suite, una piscina a sfioro e sessantamila metri quadri di uliveti, boschi e panorami mozzafiato: una dimora esclusiva per chi cerca autenticità e bellezza lontano dalla massa.</em>',
            'hp_sa_title'      => 'Le Suite',
            'hp_sa_text'       => 'Tre camere uniche, arredate con mobili antichi e comfort contemporanei. Ogni suite ha personalità propria, dalla Torre che domina il mare al Giardino che abbraccia la natura.',
            'hp_sa_cta_text'   => 'Scopri le Suite',
            'hp_sa_cta_url'    => '/it/suite/',
            'hp_sb_eyebrow'    => 'Il Territorio',
            'hp_sb_text'       => 'Tra il mare d\'argento e le colline verdi del Parco San Bartolo, la tenuta si trova in una delle zone meno conosciute e più affascinanti del centro Italia.',
            'hp_sb_cta_text'   => 'Scopri la Zona',
            'hp_sb_cta_url'    => '/it/come-arrivare/',
            'hp_sc_eyebrow'    => 'La Piscina',
            'hp_sc_text'       => 'Una piscina a sfioro sospesa sopra gli ulivi, con vista sull\'Adriatico. Riservata esclusivamente agli ospiti della villa.',
            'hp_sc_cta_text'   => 'Scopri la Piscina',
            'hp_sc_cta_url'    => '/it/piscina/',
            'newsletter_title' => 'Rimani Aggiornato',
            'newsletter_text'  => 'Iscriviti per ricevere offerte esclusive, aggiornamenti stagionali e notizie dalla tenuta.',
            'newsletter_btn'   => 'Iscriviti',
        ],
        'de' => [
            'hero_cta_text'    => 'Verfügbarkeit prüfen',
            'hero_cta_url'     => '/de/kontakt/',
            'intro_text'       => '<em>Eingebettet an der geschützten Küste des Parco del San Bartolo, ist Torre San Bartolo ein Anwesen aus dem 15. Jahrhundert, das die Adria von den Hügeln der Marken aus überblickt. Drei Suiten, ein Infinity-Pool und sechzigtausend Quadratmeter Olivenhaine, Wälder und atemberaubende Panoramen: ein exklusives Refugium für alle, die Authentizität und Schönheit fernab des Massentourismus suchen.</em>',
            'hp_sa_title'      => 'Die Suiten',
            'hp_sa_text'       => 'Drei einzigartige Zimmer, eingerichtet mit antiken Möbeln und zeitgemäßem Komfort. Jede Suite hat ihre eigene Persönlichkeit, vom Turm mit Meerblick bis zum Garten, der die Natur umarmt.',
            'hp_sa_cta_text'   => 'Die Suiten entdecken',
            'hp_sa_cta_url'    => '/de/suiten/',
            'hp_sb_eyebrow'    => 'Die Region',
            'hp_sb_text'       => 'Zwischen dem silbernen Meer und den grünen Hügeln des Parco San Bartolo liegt das Anwesen in einer der unbekanntesten und faszinierendsten Gegenden Mittelitaliens.',
            'hp_sb_cta_text'   => 'Die Umgebung entdecken',
            'hp_sb_cta_url'    => '/de/anfahrt/',
            'hp_sc_eyebrow'    => 'Der Pool',
            'hp_sc_text'       => 'Ein Infinity-Pool, scheinbar über den Olivenbäumen schwebend, mit Blick auf die Adria. Ausschließlich für Villengäste reserviert.',
            'hp_sc_cta_text'   => 'Den Pool entdecken',
            'hp_sc_cta_url'    => '/de/pool-de/',
            'newsletter_title' => 'Bleiben Sie informiert',
            'newsletter_text'  => 'Abonnieren Sie unseren Newsletter für exklusive Angebote, saisonale Neuigkeiten und Nachrichten vom Anwesen.',
            'newsletter_btn'   => 'Abonnieren',
        ],
        'fr' => [
            'hero_cta_text'    => 'Vérifier la Disponibilité',
            'hero_cta_url'     => '/fr/contact-fr/',
            'intro_text'       => '<em>Nichée le long du littoral protégé du Parco del San Bartolo, Torre San Bartolo est un domaine du XV<sup>e</sup> siècle dominant l\'Adriatique depuis les collines des Marches. Trois suites, une piscine à débordement et soixante mille mètres carrés d\'oliveraies, de forêts et de panoramas à couper le souffle : une demeure exclusive pour ceux qui recherchent authenticité et beauté loin des foules.</em>',
            'hp_sa_title'      => 'Les Suites',
            'hp_sa_text'       => 'Trois chambres uniques, meublées d\'antiquités et de conforts contemporains. Chaque suite a sa propre personnalité, de la Tour dominant la mer au Jardin embrassant la nature.',
            'hp_sa_cta_text'   => 'Découvrir les Suites',
            'hp_sa_cta_url'    => '/fr/suites-fr/',
            'hp_sb_eyebrow'    => 'Le Territoire',
            'hp_sb_text'       => 'Entre la mer argentée et les collines verdoyantes du Parco San Bartolo, la propriété se trouve dans l\'une des zones les moins connues et les plus fascinantes de l\'Italie centrale.',
            'hp_sb_cta_text'   => 'Découvrir la Région',
            'hp_sb_cta_url'    => '/fr/comment-arriver/',
            'hp_sc_eyebrow'    => 'La Piscine',
            'hp_sc_text'       => 'Une piscine à débordement semblant suspendue au-dessus des oliviers, avec vue sur l\'Adriatique. Réservée exclusivement aux hôtes de la villa.',
            'hp_sc_cta_text'   => 'Découvrir la Piscine',
            'hp_sc_cta_url'    => '/fr/piscine/',
            'newsletter_title' => 'Restez Informé',
            'newsletter_text'  => 'Abonnez-vous pour recevoir des offres exclusives, des actualités saisonnières et des nouvelles du domaine.',
            'newsletter_btn'   => 'S\'abonner',
        ],
        'es' => [
            'hero_cta_text'    => 'Verificar Disponibilidad',
            'hero_cta_url'     => '/es/contacto/',
            'intro_text'       => '<em>Enclavada a lo largo de la protegida costa del Parco del San Bartolo, Torre San Bartolo es una finca del siglo XV que domina el Adriático desde las colinas de Las Marcas. Tres suites, una piscina de borde infinito y sesenta mil metros cuadrados de olivares, bosques y panoramas impresionantes: una residencia exclusiva para quienes buscan autenticidad y belleza lejos de las masas.</em>',
            'hp_sa_title'      => 'Las Suites',
            'hp_sa_text'       => 'Tres habitaciones únicas, amuebladas con antigüedades y comodidades contemporáneas. Cada suite tiene su propia personalidad, desde la Torre que domina el mar hasta el Jardín que abraza la naturaleza.',
            'hp_sa_cta_text'   => 'Descubrir las Suites',
            'hp_sa_cta_url'    => '/es/suites-es/',
            'hp_sb_eyebrow'    => 'El Territorio',
            'hp_sb_text'       => 'Entre el plateado mar y las verdes colinas del Parco San Bartolo, la finca se encuentra en una de las zonas menos conocidas y más fascinantes del centro de Italia.',
            'hp_sb_cta_text'   => 'Descubrir la Zona',
            'hp_sb_cta_url'    => '/es/como-llegar/',
            'hp_sc_eyebrow'    => 'La Piscina',
            'hp_sc_text'       => 'Una piscina de borde infinito aparentemente suspendida sobre los olivos, con vistas al Adriático. Reservada exclusivamente para los huéspedes de la villa.',
            'hp_sc_cta_text'   => 'Descubrir la Piscina',
            'hp_sc_cta_url'    => '/es/piscina/',
            'newsletter_title' => 'Mantente Informado',
            'newsletter_text'  => 'Suscríbete para recibir ofertas exclusivas, noticias de temporada y novedades de la finca.',
            'newsletter_btn'   => 'Suscribirse',
        ],
    ];

    foreach ($tr as $lang => $pid) {
        if ($lang === 'en') continue;

        // Copy neutral image fields
        foreach ($neutral_keys as $key) {
            $val = get_post_meta($en_id, $key, true);
            if ($val !== '' && $val !== false) {
                update_post_meta($pid, $key, $val);
            }
        }

        // Set translated text fields
        if (! isset($content[$lang])) continue;
        foreach ($content[$lang] as $key => $val) {
            update_post_meta($pid, $key, $val);
        }
    }
}

/* ─────────────────────────────────────────────────────────────────────────────
   LANGUAGE-SPECIFIC NAV MENUS
   ───────────────────────────────────────────────────────────────────────────── */
function torresan_create_language_menus(): void
{
    // Primary nav items per language: [page_id, title]
    $primary_items = [
        'en' => [
            [12,  'Torre San Bartolo'],
            [180, 'About'],
            [182, 'Suites'],
            [184, 'Pool'],
            [186, 'Experiences'],
            [188, 'Gallery'],
            [190, 'Contact'],
        ],
        'it' => [
            [230, 'Torre San Bartolo'],
            [239, 'Chi Siamo'],
            [247, 'Suite'],
            [255, 'Piscina'],
            [263, 'Esperienze'],
            [271, 'Galleria'],
            [287, 'Contatti'],
        ],
        'de' => [
            [232, 'Torre San Bartolo'],
            [241, 'Über Uns'],
            [249, 'Suiten'],
            [257, 'Pool'],
            [265, 'Erlebnisse'],
            [273, 'Galerie'],
            [289, 'Kontakt'],
        ],
        'fr' => [
            [234, 'Torre San Bartolo'],
            [243, 'À Propos'],
            [251, 'Suites'],
            [259, 'Piscine'],
            [267, 'Expériences'],
            [275, 'Galerie'],
            [291, 'Contact'],
        ],
        'es' => [
            [236, 'Torre San Bartolo'],
            [245, 'Acerca de'],
            [253, 'Suites'],
            [261, 'Piscina'],
            [269, 'Experiencias'],
            [277, 'Galería'],
            [293, 'Contacto'],
        ],
    ];

    // Footer nav items per language
    $footer_items = [
        'en' => [
            [180, 'About'],
            [182, 'Suites'],
            [184, 'Pool'],
            [186, 'Experiences'],
            [188, 'Gallery'],
            [190, 'Contact'],
            [192, 'Location'],
            [209, 'FAQ'],
        ],
        'it' => [
            [239, 'Chi Siamo'],
            [247, 'Suite'],
            [255, 'Piscina'],
            [263, 'Esperienze'],
            [271, 'Galleria'],
            [287, 'Contatti'],
            [295, 'Come Arrivare'],
            [279, 'FAQ'],
        ],
        'de' => [
            [241, 'Über Uns'],
            [249, 'Suiten'],
            [257, 'Pool'],
            [265, 'Erlebnisse'],
            [273, 'Galerie'],
            [289, 'Kontakt'],
            [297, 'Anfahrt'],
            [281, 'FAQ'],
        ],
        'fr' => [
            [243, 'À Propos'],
            [251, 'Suites'],
            [259, 'Piscine'],
            [267, 'Expériences'],
            [275, 'Galerie'],
            [291, 'Contact'],
            [299, 'Comment Arriver'],
            [283, 'FAQ'],
        ],
        'es' => [
            [245, 'Acerca de'],
            [253, 'Suites'],
            [261, 'Piscina'],
            [269, 'Experiencias'],
            [277, 'Galería'],
            [293, 'Contacto'],
            [301, 'Cómo Llegar'],
            [285, 'FAQ'],
        ],
    ];

    // Footer-legal items per language
    $legal_items = [
        'en' => [[3,   'Privacy Policy']],
        'it' => [[311, 'Privacy Policy'], [319, 'Cookie Policy']],
        'de' => [[313, 'Datenschutzrichtlinie'], [321, 'Cookie-Richtlinie']],
        'fr' => [[315, 'Politique de Confidentialité'], [323, 'Politique des Cookies']],
        'es' => [[317, 'Política de Privacidad'], [325, 'Política de Cookies']],
    ];

    $menu_ids   = [];
    $theme_slug = get_option('stylesheet'); // 'torresan-bnb'

    $build_menu = function (string $name, array $items): int {
        // Delete existing menu with the same name to avoid duplicates
        $existing = wp_get_nav_menu_object($name);
        if ($existing) {
            return (int) $existing->term_id;
        }

        $menu_id = wp_create_nav_menu($name);
        if (is_wp_error($menu_id)) {
            return 0;
        }

        foreach ($items as [$page_id, $title]) {
            $post_type = get_post_type($page_id);
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-object-id' => $page_id,
                'menu-item-object'    => $post_type ?: 'page',
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
                'menu-item-title'     => $title,
            ]);
        }

        return (int) $menu_id;
    };

    // Build all menus
    foreach (['en', 'it', 'de', 'fr', 'es'] as $lang) {
        $menu_ids['primary'][$lang]       = $build_menu("Primary — {$lang}", $primary_items[$lang]);
        $menu_ids['footer'][$lang]        = $build_menu("Footer — {$lang}", $footer_items[$lang]);
        $menu_ids['footer-legal'][$lang]  = $build_menu("Footer Legal — {$lang}", $legal_items[$lang] ?? []);

        // Set language on each menu term
        foreach (['primary', 'footer', 'footer-legal'] as $loc) {
            $mid = $menu_ids[$loc][$lang] ?? 0;
            if ($mid && function_exists('pll_set_term_language')) {
                pll_set_term_language($mid, $lang);
            }
        }
    }

    // Configure Polylang nav_menus option
    $pll_opts = get_option('polylang', []);
    foreach (['primary', 'footer', 'footer-legal'] as $loc) {
        foreach (['en', 'it', 'de', 'fr', 'es'] as $lang) {
            $mid = $menu_ids[$loc][$lang] ?? 0;
            if ($mid) {
                $pll_opts['nav_menus'][$theme_slug][$loc][$lang] = $mid;
            }
        }
    }
    update_option('polylang', $pll_opts);
}
