<?php
/**
 * seed-translations.php
 *
 * Comprehensive Polylang translation seeder.
 * Links EN pages with their IT/DE/FR/ES translations,
 * fixes slugs, populates post titles/content and all Pods meta fields,
 * creates translated domanda_faq CPT items, flushes rewrite rules.
 *
 * Run: docker compose exec -T wordpress php /var/www/html/wp-content/../../../scripts/seed-translations.php
 * Or:  docker compose exec -T wordpress php /scripts/seed-translations.php
 */

$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
define('ABSPATH', '/var/www/html/');
require '/var/www/html/wp-load.php';

// ── Helpers ───────────────────────────────────────────────────────────────────

function ts_update_page(int $id, string $title, string $slug, string $content = ''): void
{
    wp_update_post([
        'ID'           => $id,
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $content,
        'post_status'  => 'publish',
    ]);
}

function ts_set_meta(int $id, array $fields): void
{
    foreach ($fields as $key => $value) {
        update_post_meta($id, $key, $value);
    }
}

/** Remove all existing faq_pagina rows then insert fresh ones. */
function ts_set_faq_ids(int $page_id, array $faq_ids): void
{
    global $wpdb;
    $wpdb->delete($wpdb->postmeta, ['post_id' => $page_id, 'meta_key' => 'faq_pagina']);
    foreach ($faq_ids as $fid) {
        add_post_meta($page_id, 'faq_pagina', $fid, false);
    }
}

/** Create a domanda_faq post. Returns the new post ID. */
function ts_create_faq(string $question, string $answer, string $lang_slug): int
{
    $pid = wp_insert_post([
        'post_type'    => 'domanda_faq',
        'post_status'  => 'publish',
        'post_title'   => $question,
        'post_content' => $answer,
    ]);
    if ($pid && function_exists('pll_set_post_language')) {
        pll_set_post_language($pid, $lang_slug);
    }
    return (int) $pid;
}

echo "=== Torre San Bartolo – Translation Seeder ===\n\n";

// ── Publish EN privacy policy (currently draft) ────────────────────────────
wp_update_post(['ID' => 3, 'post_status' => 'publish', 'post_title' => 'Privacy Policy', 'post_name' => 'privacy-policy']);
echo "✓ Published EN Privacy Policy (ID 3)\n";

// ─────────────────────────────────────────────────────────────────────────────
// STEP 1 – Link Polylang translation groups
// ─────────────────────────────────────────────────────────────────────────────

$translation_groups = [
    'home'        => ['en' => 12,  'it' => 230, 'de' => 232, 'fr' => 234, 'es' => 236],
    'about'       => ['en' => 180, 'it' => 239, 'de' => 241, 'fr' => 243, 'es' => 245],
    'suites'      => ['en' => 182, 'it' => 247, 'de' => 249, 'fr' => 251, 'es' => 253],
    'pool'        => ['en' => 184, 'it' => 255, 'de' => 257, 'fr' => 259, 'es' => 261],
    'experiences' => ['en' => 186, 'it' => 263, 'de' => 265, 'fr' => 267, 'es' => 269],
    'gallery'     => ['en' => 188, 'it' => 271, 'de' => 273, 'fr' => 275, 'es' => 277],
    'faq'         => ['en' => 209, 'it' => 279, 'de' => 281, 'fr' => 283, 'es' => 285],
    'contact'     => ['en' => 190, 'it' => 287, 'de' => 289, 'fr' => 291, 'es' => 293],
    'location'    => ['en' => 192, 'it' => 295, 'de' => 297, 'fr' => 299, 'es' => 301],
    'book'        => ['en' => 224, 'it' => 303, 'de' => 305, 'fr' => 307, 'es' => 309],
    'privacy'     => ['en' => 3,   'it' => 311, 'de' => 313, 'fr' => 315, 'es' => 317],
    'cookie'      => ['en' => 226, 'it' => 319, 'de' => 321, 'fr' => 323, 'es' => 325],
];

echo "── Linking translation groups ──\n";
foreach ($translation_groups as $group_name => $ids) {
    pll_save_post_translations($ids);
    echo "  ✓ {$group_name}\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// STEP 2 – Shared values
// ─────────────────────────────────────────────────────────────────────────────

$maps_embed = '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2872.0!2d12.893!3d43.985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sTorre+San+Bartolo%2C+Pesaro!5e0!3m2!1sen!2sit!4v1700000000000!5m2!1sen!2sit" width="100%" height="450" style="border:0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Torre San Bartolo on Google Maps"></iframe>';

$address_it = "Torre San Bartolo\nVia Torre San Bartolo 1\n61100 Pesaro PU — Italy";
$address_fr = "Torre San Bartolo\nVia Torre San Bartolo 1\n61100 Pesaro PU — Italie";
$address_es = "Torre San Bartolo\nVia Torre San Bartolo 1\n61100 Pesaro PU — Italia";

// ─────────────────────────────────────────────────────────────────────────────
// STEP 3 – Italian pages
// ─────────────────────────────────────────────────────────────────────────────

echo "\n── Italian pages ──\n";

// Home IT (230)
ts_update_page(230, 'Torre San Bartolo', 'home');
ts_set_meta(230, [
    'hero_eyebrow'       => 'Torre San Bartolo',
    'hero_subtitle'      => "Una villa esclusiva nelle Marche, dove il tempo rallenta e l'Adriatico incontra le colline.",
    'hero_cta_text'      => 'Verifica Disponibilità',
    'hero_cta_url'       => '/it/prenota/',
    'contatti_telefono'  => '+39 0721 910540',
    'contatti_email'     => 'info@torresanbartolo.it',
    'contatti_orari'     => "Check-in ore 15:00 – Check-out ore 10:00",
]);
echo "  ✓ Home\n";

// About IT (239)
ts_update_page(239, 'Chi Siamo', 'chi-siamo', '<p>Torre San Bartolo prende il nome dall\'antica torre di avvistamento che veglia su questo tratto della costa adriatica dal XV secolo. Nel 2008 la proprietà è stata amorevolmente restaurata dai proprietari attuali — preservando le originali pareti in pietra, i soffitti dipinti a mano e i pavimenti in terracotta, intrecciando ogni comfort moderno.</p>
<p>Oggi la tenuta apre i suoi cancelli esclusivamente a un singolo gruppo di ospiti alla volta. Avrete l\'intera proprietà per voi: tre suite, il giardino, la piscina a sfioro, la cucina esterna e la cantina originale. Non ci sono altri ospiti, nessuna fila per la colazione, nessuno sconosciuto in piscina.</p>
<p>La nostra filosofia è quella di una casa privata, non di un hotel. La colazione arriva all\'ora che preferite. La cantina è fornita di vini DOC delle Marche selezionati dai vicini poco più in là. Se desiderate uno chef privato, una caccia al tartufo guidata o una sessione di yoga all\'alba accanto alla piscina, lo organizzeremo noi. Tutto è possibile; niente è obbligatorio.</p>');
ts_set_meta(239, [
    'hero_eyebrow'  => 'La Nostra Storia',
    'hero_subtitle' => "Dal XV secolo sull'Adriatico.",
]);
echo "  ✓ About\n";

// Suites IT (247)
ts_update_page(247, 'Suite', 'suite');
ts_set_meta(247, [
    'hero_eyebrow'  => 'Sistemazioni',
    'hero_subtitle' => "Tre suite. Un'unica villa privata. Tutta per voi.",
]);
echo "  ✓ Suites\n";

// Pool IT (255)
ts_update_page(255, 'Piscina', 'piscina', '<p>La piscina a sfioro di Torre San Bartolo è una delle caratteristiche distintive della tenuta. Arroccata sulla terrazza collinare, l\'acqua forma una linea di orizzonte continua sopra gli uliveti e il brillante Adriatico sottostante. Con le sue dimensioni di 12 × 5 metri, la piscina riscaldata è riservata esclusivamente agli ospiti della villa — niente prenotazione degli asciugamani, nessun estraneo, nessuna fila per le sdraio.</p>
<p>Trascorrete pigre mattinate al sole, fate un tuffo all\'ora d\'oro quando le colline si tingono di ambra, o nuotate sotto un baldacchino di stelle. Lettini, ombrelloni, asciugamani e un servizio bevande refrigerate sono forniti senza costi aggiuntivi. Un tavolo da pranzo all\'aperto si trova a bordo piscina — perfetto per cene a lume di candela servite dal nostro chef in villa.</p>');
ts_set_meta(255, [
    'hero_eyebrow'  => 'La Piscina',
    'hero_subtitle' => 'Acqua infinita, panorami senza confini.',
    'pool_cta_text' => 'Richiedi Disponibilità',
    'pool_cta_url'  => '/it/contatti/',
]);
echo "  ✓ Pool\n";

// Experiences IT (263)
ts_update_page(263, 'Esperienze', 'esperienze');
ts_set_meta(263, [
    'hero_eyebrow'  => 'Esperienze',
    'hero_subtitle' => 'Assapora, scopri ed esplora il meglio delle Marche.',
]);
echo "  ✓ Experiences\n";

// Gallery IT (271)
ts_update_page(271, 'Galleria', 'galleria');
ts_set_meta(271, ['hero_eyebrow' => 'Galleria']);
echo "  ✓ Gallery\n";

// Contact IT (287)
ts_update_page(287, 'Contatti', 'contatti');
ts_set_meta(287, [
    'hero_eyebrow'      => 'Contattaci',
    'hero_subtitle'     => 'Saremo lieti di ospitarvi. Parlateci del vostro soggiorno ideale.',
    'contatti_telefono' => '+39 0721 910540',
    'contatti_email'    => 'info@torresanbartolo.it',
    'contatti_orari'    => "Lun \xe2\x80\x93 Sab: 09:00 \xe2\x80\x93 19:00\nDom: 10:00 \xe2\x80\x93 17:00\n\nCheck-in: dalle 15:00\nCheck-out: entro le 10:00",
    'address'           => $address_it,
    'maps_embed'        => $maps_embed,
]);
echo "  ✓ Contact\n";

// Location IT (295)
ts_update_page(295, 'Come Arrivare', 'come-arrivare');
ts_set_meta(295, [
    'hero_eyebrow'         => 'Come Arrivare',
    'address'              => $address_it,
    'maps_embed'           => $maps_embed,
    'indicazioni_stradali' => "<h3>In Auto</h3>\n<p>Percorrere la SS16 Adriatica verso nord in direzione Pesaro. Uscire a <strong>Pesaro Nord</strong> e prendere la SP13 verso Gabicce Monte. Seguire le indicazioni per Torre San Bartolo \xe2\x80\x94 l'ingresso alla tenuta \xc3\xa8 sulla sinistra dopo il secondo tornante.</p>\n<p>Tempi di percorrenza: Pesaro 15 min \xc2\xb7 Rimini 35 min \xc2\xb7 Bologna 1h 45min \xc2\xb7 Roma 4h</p>\n<h3>In Treno</h3>\n<p>Le stazioni ferroviarie pi\xc3\xb9 vicine sono <strong>Pesaro</strong> (14 km) e <strong>Gabicce Mare</strong> (6 km), entrambe sulla linea principale Bologna\xe2\x80\x93Rimini\xe2\x80\x93Ancona. Consigliamo di prenotare in anticipo un trasferimento privato \xe2\x80\x94 possiamo organizzarlo per voi.</p>\n<h3>In Aereo</h3>\n<p>L'aeroporto pi\xc3\xb9 vicino \xc3\xa8 il <strong>Federico Fellini International (RMI)</strong> di Rimini, a 40 km. L'aeroporto di Bologna (BLQ) \xc3\xa8 a 130 km e collega tutti i principali hub europei. Sono disponibili trasferimenti privati su prenotazione.</p>",
]);
echo "  ✓ Location\n";

// FAQ IT (279) – Create FAQ items first
$faq_it = [
    ["Qual \xc3\xa8 la politica di cancellazione?",                                    "<p>Cancellazione gratuita fino a 14 giorni prima del check-in. Per cancellazioni tra 14 e 7 giorni, viene addebitato il 50% del totale della prenotazione. Mancate presenze o cancellazioni entro 7 giorni dall'arrivo sono addebitate per intero.</p>"],
    ['Quali sono gli orari di check-in e check-out?',                                   "<p>Il check-in \xc3\xa8 disponibile dalle 15:00 e chiediamo agli ospiti di fare il check-out entro le 11:00. Check-in anticipato e check-out posticipato possono essere concordati previo disponibilit\xc3\xa0 \xe2\x80\x94 si prega di contattarci prima dell'arrivo.</p>"],
    ['Torre San Bartolo \xc3\xa8 adatta ai bambini?',                                  "<p>Assolutamente s\xc3\xac. La villa \xc3\xa8 meravigliosa per le famiglie: il grande giardino, la piscina a sfioro (con zona bassa per i pi\xc3\xb9 piccoli), i caminetti interni e la cucina esterna la rendono un rifugio ideale per tutti.</p>"],
    ['Sono benvenuti gli animali domestici?',                                            "<p>I cani di taglia piccola e media (massimo due per prenotazione) sono i benvenuti. Chiediamo semplicemente di tenerli lontani dai letti e di pulire eventuali imprevisti in giardino. Si prega di comunicarcelo al momento della prenotazione.</p>"],
    ['La propriet\xc3\xa0 \xc3\xa8 affittata in modo esclusivo?',                     "<p>S\xc3\xac. Torre San Bartolo \xc3\xa8 disponibile esclusivamente per affitto completo \xe2\x80\x94 non condividerete mai la villa con altri ospiti. Tutte e tre le suite, il giardino, la piscina, la cantina e la cucina esterna sono interamente vostri.</p>"],
    ["\xc3\x88 disponibile il parcheggio?",                                             "<p>C'\xc3\xa8 un parcheggio privato recintato all'interno della tenuta con spazio per quattro veicoli, disponibile agli ospiti gratuitamente.</p>"],
    ['La villa \xc3\xa8 completamente attrezzata e self-catering?',                    "<p>La villa \xc3\xa8 completamente attrezzata con biancheria da letto premium, asciugamani, accappatoi e teli da piscina. La cucina \xc3\xa8 fornita di olio d'oliva locale, condimenti e caff\xc3\xa8. Troverete un cesto di benvenuto con prodotti locali al vostro arrivo.</p>"],
    ['Il Wi-Fi \xc3\xa8 disponibile in tutta la propriet\xc3\xa0?',                   "<p>S\xc3\xac. Il Wi-Fi ad alta velocit\xc3\xa0 (300 Mbps) \xc3\xa8 disponibile in tutta la villa, nelle suite e in tutte le terrazze esterne inclusa l'area piscina.</p>"],
    ['Quanto dista la spiaggia pi\xc3\xb9 vicina?',                                    "<p>La tenuta si trova a 3 km dall'entroterra rispetto alla costa adriatica. La spiaggia sabbiosa pi\xc3\xb9 vicina \xe2\x80\x94 Baia Flaminia \xe2\x80\x94 \xc3\xa8 a 10 minuti in auto. Il lungomare di Pesaro \xc3\xa8 a 15 minuti. Possiamo organizzare trasferimenti privati su richiesta.</p>"],
    ['Potete organizzare servizi aggiuntivi durante il soggiorno?',                     "<p>Assolutamente s\xc3\xac. Il nostro servizio di concierge pu\xc3\xb2 organizzare cene con chef privato, caccia al tartufo, tour di cantine, escursioni in bicicletta, massaggi e trattamenti spa in villa, equitazione, gite in barca e molto altro. Contattateci con anticipo.</p>"],
];
$faq_it_ids = [];
foreach ($faq_it as [$q, $a]) {
    $faq_it_ids[] = ts_create_faq($q, $a, 'it');
}
ts_update_page(279, 'Domande Frequenti', 'domande-frequenti', '<p>Tutte le domande che i nostri ospiti ci hanno posto nel corso degli anni trovano risposta qui di seguito. Se non riuscite a trovare quello che cercate, non esitate a contattarci direttamente — rispondiamo nel giro di poche ore.</p>');
ts_set_meta(279, ['hero_eyebrow' => 'Domande Frequenti']);
ts_set_faq_ids(279, $faq_it_ids);
echo "  ✓ FAQ (" . count($faq_it_ids) . " items)\n";

// Book IT (303)
ts_update_page(303, 'Prenota', 'prenota');
ts_set_meta(303, [
    'hero_eyebrow'  => 'Prenota il Tuo Soggiorno',
    'hero_subtitle' => 'Contattaci per verificare la disponibilità e ricevere un preventivo personalizzato.',
    'hero_cta_text' => 'Contattaci',
    'hero_cta_url'  => '/it/contatti/',
]);
echo "  ✓ Book\n";

// Privacy IT (311)
ts_update_page(311, 'Privacy Policy', 'privacy-policy');
ts_set_meta(311, ['hero_eyebrow' => 'Privacy Policy']);
echo "  ✓ Privacy\n";

// Cookie IT (319)
ts_update_page(319, 'Cookie Policy', 'cookie-policy');
ts_set_meta(319, ['hero_eyebrow' => 'Cookie Policy']);
echo "  ✓ Cookie\n";

// ─────────────────────────────────────────────────────────────────────────────
// STEP 4 – German pages
// ─────────────────────────────────────────────────────────────────────────────

echo "\n── German pages ──\n";

// Home DE (232)
ts_update_page(232, 'Torre San Bartolo', 'home');
ts_set_meta(232, [
    'hero_eyebrow'      => 'Torre San Bartolo',
    'hero_subtitle'     => 'Eine exklusive Villa in den Marken, wo die Zeit langsamer wird und die Adria auf die Hügel trifft.',
    'hero_cta_text'     => 'Verfügbarkeit prüfen',
    'hero_cta_url'      => '/de/buchen/',
    'contatti_telefono' => '+39 0721 910540',
    'contatti_email'    => 'info@torresanbartolo.it',
    'contatti_orari'    => "Check-in ab 15:00 – Check-out bis 10:00",
]);
echo "  ✓ Home\n";

// About DE (241)
ts_update_page(241, 'Über Uns', 'ueber-uns', '<p>Torre San Bartolo verdankt seinen Namen dem alten Wachturm, der seit dem 15. Jahrhundert über diesen Abschnitt der Adriaküste wacht. Im Jahr 2008 wurde das Anwesen von seinen jetzigen Eigentümern liebevoll restauriert — dabei wurden originale Steinwände, handbemalte Decken und Terrakottaböden erhalten und moderner Komfort eingewoben.</p>
<p>Heute öffnet das Anwesen seine Pforten ausschließlich für eine einzelne Gästegruppe zur gleichen Zeit. Die gesamte Anlage gehört Ihnen: drei Suiten, der Garten, der Infinity-Pool, die Außenküche und die originale Cantina. Keine anderen Gäste, keine Frühstückswarteschlangen, keine Fremden am Pool.</p>
<p>Unsere Philosophie ist die eines Privathauses, nicht eines Hotels. Das Frühstück kommt zu welcher Stunde auch immer Sie möchten. Die Cantina ist mit Marken DOC-Weinen bestückt, ausgewählt von Nachbarn gleich die Straße entlang. Ob privater Koch, geführte Trüffelsuche oder Sonnenaufgangs-Yoga neben dem Pool — wir arrangieren alles. Alles ist möglich; nichts ist obligatorisch.</p>');
ts_set_meta(241, [
    'hero_eyebrow'  => 'Unsere Geschichte',
    'hero_subtitle' => 'Seit dem 15. Jahrhundert an der Adria.',
]);
echo "  ✓ About\n";

// Suites DE (249)
ts_update_page(249, 'Suiten', 'suiten');
ts_set_meta(249, [
    'hero_eyebrow'  => 'Unterkunft',
    'hero_subtitle' => 'Drei Suiten. Ein privates Anwesen. Ganz für Sie allein.',
]);
echo "  ✓ Suites\n";

// Pool DE (257)
ts_update_page(257, 'Pool', 'pool', '<p>Der Infinity-Pool von Torre San Bartolo ist eines der prägenden Merkmale des Anwesens. Auf der Hangterrasse gelegen, bildet das Wasser eine nahtlose Horizontlinie über den Olivenhainen und dem funkelnden Adriatischen Meer darunter. Mit seinen Maßen von 12 × 5 Metern ist der beheizte Pool ausschließlich für Villagäste reserviert — keine Handtuchreservierungen, keine Fremden, keine Warteschlange für Sonnenliegen.</p>
<p>Verbringen Sie faule Morgenstunden in der frühen Sonne, tauchen Sie bei der goldenen Stunde ein, wenn die Hügel bernsteinfarben leuchten, oder schwimmen Sie unter einem Sternenzelt. Poolliegen, Sonnenschirme, Handtücher und ein gekühlter Getränkeservice sind ohne Aufpreis inklusive. Ein privater Esstisch am Wasser lädt zu Kerzenlichtdinners ein, serviert von unserem In-Villa-Koch.</p>');
ts_set_meta(257, [
    'hero_eyebrow'  => 'Der Pool',
    'hero_subtitle' => 'Unendliches Wasser, grenzenlose Aussichten.',
    'pool_cta_text' => 'Verfügbarkeit anfragen',
    'pool_cta_url'  => '/de/kontakt/',
]);
echo "  ✓ Pool\n";

// Experiences DE (265)
ts_update_page(265, 'Erlebnisse', 'erlebnisse');
ts_set_meta(265, [
    'hero_eyebrow'  => 'Erlebnisse',
    'hero_subtitle' => 'Genießen, entdecken und erkunden Sie das Beste der Marken.',
]);
echo "  ✓ Experiences\n";

// Gallery DE (273)
ts_update_page(273, 'Galerie', 'galerie');
ts_set_meta(273, ['hero_eyebrow' => 'Galerie']);
echo "  ✓ Gallery\n";

// Contact DE (289)
ts_update_page(289, 'Kontakt', 'kontakt');
ts_set_meta(289, [
    'hero_eyebrow'      => 'Kontakt',
    'hero_subtitle'     => 'Wir freuen uns, Sie zu empfangen. Erzählen Sie uns von Ihrem idealen Aufenthalt.',
    'contatti_telefono' => '+39 0721 910540',
    'contatti_email'    => 'info@torresanbartolo.it',
    'contatti_orari'    => "Mo \xe2\x80\x93 Sa: 09:00 \xe2\x80\x93 19:00\nSo: 10:00 \xe2\x80\x93 17:00\n\nCheck-in: ab 15:00\nCheck-out: bis 10:00",
    'address'           => $address_it,
    'maps_embed'        => $maps_embed,
]);
echo "  ✓ Contact\n";

// Location DE (297)
ts_update_page(297, 'Anfahrt', 'anfahrt');
ts_set_meta(297, [
    'hero_eyebrow'         => 'Anreise',
    'address'              => $address_it,
    'maps_embed'           => $maps_embed,
    'indicazioni_stradali' => "<h3>Mit dem Auto</h3>\n<p>Nehmen Sie die SS16 Adriatica Richtung Norden nach Pesaro. Verlassen Sie die Stra\xc3\x9fe bei <strong>Pesaro Nord</strong> und nehmen Sie die SP13 Richtung Gabicce Monte. Folgen Sie den Schildern nach Torre San Bartolo \xe2\x80\x94 der Eingang zum Anwesen befindet sich auf der linken Seite nach der zweiten Haarnadelkurve.</p>\n<p>Fahrtzeiten: Pesaro 15 Min \xc2\xb7 Rimini 35 Min \xc2\xb7 Bologna 1h 45min \xc2\xb7 Rom 4h</p>\n<h3>Mit dem Zug</h3>\n<p>Die n\xc3\xa4chstgelegenen Bahnh\xc3\xb6fe sind <strong>Pesaro</strong> (14 km) und <strong>Gabicce Mare</strong> (6 km), beide an der Hauptlinie Bologna\xe2\x80\x93Rimini\xe2\x80\x93Ancona. Wir empfehlen, im Voraus einen privaten Transfer zu buchen \xe2\x80\x94 wir k\xc3\xb6nnen dies f\xc3\xbcr Sie arrangieren.</p>\n<h3>Mit dem Flugzeug</h3>\n<p>Der n\xc3\xa4chstgelegene Flughafen ist der <strong>Federico Fellini International (RMI)</strong> in Rimini, 40 km entfernt. Der Flughafen Bologna (BLQ) ist 130 km entfernt und verbindet alle wichtigen europ\xc3\xa4ischen Drehkreuze. Private Transfers sind auf Anfrage erh\xc3\xa4ltlich.</p>",
]);
echo "  ✓ Location\n";

// FAQ DE (281)
$faq_de = [
    ['Wie lautet die Stornierungsrichtlinie?',                                          '<p>Kostenlose Stornierung bis 14 Tage vor dem Check-in. Bei Stornierungen zwischen 14 und 7 Tagen werden 50% des Gesamtbetrags berechnet. Nichterscheinen oder Stornierungen innerhalb von 7 Tagen vor Anreise werden vollständig berechnet.</p>'],
    ['Wie sind die Check-in- und Check-out-Zeiten?',                                    '<p>Check-in ist ab 15:00 Uhr möglich und wir bitten die Gäste, bis 11:00 Uhr auszuchecken. Früher Check-in und späteres Check-out können je nach Verfügbarkeit arrangiert werden — bitte kontaktieren Sie uns vor Ihrer Anreise.</p>'],
    ['Ist Torre San Bartolo für Kinder geeignet?',                                      '<p>Absolut. Das Anwesen ist wunderbar für Familien: der große Garten, der Infinity-Pool (mit einem flachen Bereich für kleine Kinder), die Innenkamine und die Außenküche machen es zu einem idealen Rückzugsort für alle.</p>'],
    ['Sind Haustiere willkommen?',                                                      '<p>Kleine und mittelgroße Hunde (maximal zwei pro Buchung) sind herzlich willkommen. Wir bitten lediglich, sie von den Betten fernzuhalten und eventuelle Unordnung im Garten aufzuräumen. Bitte teilen Sie es uns bei der Buchung mit.</p>'],
    ['Wird das Anwesen exklusiv vermietet?',                                            '<p>Ja. Torre San Bartolo ist ausschließlich für exklusive Miete verfügbar — Sie teilen das Anwesen niemals mit anderen Gästen. Alle drei Suiten, der Garten, der Pool, die Cantina und die Außenküche gehören ganz Ihnen.</p>'],
    ['Steht Parken zur Verfügung?',                                                     '<p>Es gibt einen privaten eingezäunten Parkplatz auf dem Anwesen mit Platz für bis zu vier Fahrzeuge, kostenlos für die Gäste zur Verfügung.</p>'],
    ['Ist die Villa voll ausgestattet und zur Selbstversorgung?',                       '<p>Die Villa ist vollständig ausgestattet mit erstklassiger Bettwäsche, Handtüchern, Bademänteln und Poolhandtüchern. Die Landhausküche ist mit lokalem Olivenöl, Gewürzen und Kaffee bestückt. Bei Ihrer Ankunft erwartet Sie ein Willkommenskorb mit lokalen Produkten.</p>'],
    ['Steht überall auf dem Anwesen WLAN zur Verfügung?',                              '<p>Ja. Hochgeschwindigkeits-WLAN (300 Mbit/s) ist überall in der Villa, in den Suiten und auf allen Außenterrassen einschließlich des Poolbereichs verfügbar.</p>'],
    ['Wie weit ist der nächste Strand?',                                                '<p>Das Anwesen liegt 3 km im Landesinneren von der Adriaküste. Der nächste Sandstrand — Baia Flaminia — ist 10 Fahrminuten entfernt. Die Strandpromenade von Pesaro ist 15 Minuten entfernt. Private Transfers können auf Anfrage arrangiert werden.</p>'],
    ['Können Sie während unseres Aufenthalts zusätzliche Dienstleistungen arrangieren?', '<p>Absolut. Unser Concierge-Service kann private Kochdinners, Trüffelsuchen, Weinkellerei-Touren, Radausflüge, Massagen und Spa-Behandlungen in der Villa, Reiten, Bootsausflüge und vieles mehr organisieren. Kontaktieren Sie uns im Voraus.</p>'],
];
$faq_de_ids = [];
foreach ($faq_de as [$q, $a]) {
    $faq_de_ids[] = ts_create_faq($q, $a, 'de');
}
ts_update_page(281, 'Häufige Fragen', 'haeufige-fragen', '<p>Alle Fragen, die unsere Gäste im Laufe der Jahre gestellt haben, werden im Folgenden beantwortet. Wenn Sie nicht finden, was Sie suchen, zögern Sie nicht, uns direkt zu kontaktieren — wir antworten innerhalb weniger Stunden.</p>');
ts_set_meta(281, ['hero_eyebrow' => 'Häufige Fragen']);
ts_set_faq_ids(281, $faq_de_ids);
echo "  ✓ FAQ (" . count($faq_de_ids) . " items)\n";

// Book DE (305)
ts_update_page(305, 'Buchen', 'buchen');
ts_set_meta(305, [
    'hero_eyebrow'  => 'Ihren Aufenthalt Buchen',
    'hero_subtitle' => 'Kontaktieren Sie uns, um Verfügbarkeit zu prüfen und ein persönliches Angebot zu erhalten.',
    'hero_cta_text' => 'Kontakt aufnehmen',
    'hero_cta_url'  => '/de/kontakt/',
]);
echo "  ✓ Book\n";

// Privacy DE (313)
ts_update_page(313, 'Datenschutzrichtlinie', 'datenschutz');
ts_set_meta(313, ['hero_eyebrow' => 'Datenschutz']);
echo "  ✓ Privacy\n";

// Cookie DE (321)
ts_update_page(321, 'Cookie-Richtlinie', 'cookie-richtlinie');
ts_set_meta(321, ['hero_eyebrow' => 'Cookie-Richtlinie']);
echo "  ✓ Cookie\n";

// ─────────────────────────────────────────────────────────────────────────────
// STEP 5 – French pages
// ─────────────────────────────────────────────────────────────────────────────

echo "\n── French pages ──\n";

// Home FR (234)
ts_update_page(234, 'Torre San Bartolo', 'home');
ts_set_meta(234, [
    'hero_eyebrow'      => 'Torre San Bartolo',
    'hero_subtitle'     => "Une villa exclusive dans les Marches, où le temps ralentit et l'Adriatique rencontre les collines.",
    'hero_cta_text'     => 'Vérifier les disponibilités',
    'hero_cta_url'      => '/fr/reserver/',
    'contatti_telefono' => '+39 0721 910540',
    'contatti_email'    => 'info@torresanbartolo.it',
    'contatti_orari'    => "Arrivée à partir de 15h00 – Départ avant 10h00",
]);
echo "  ✓ Home\n";

// About FR (243)
ts_update_page(243, 'À Propos', 'a-propos', "<p>Torre San Bartolo tire son nom de l'ancienne tour de guet qui veille sur ce tronçon de la côte adriatique depuis le XVe siècle. En 2008, la propriété a été restaurée avec amour par ses propriétaires actuels — préservant les murs en pierre d'origine, les plafonds peints à la main et les sols en terre cuite, tout en intégrant tout le confort moderne.</p>\n<p>Aujourd'hui, le domaine ouvre ses portes exclusivement à un seul groupe d'invités à la fois. Vous disposerez de l'intégralité de la propriété : trois suites, le jardin, la piscine à débordement, la cuisine extérieure et l'ancienne cantina. Aucun autre client, aucune file d'attente pour le petit-déjeuner, aucun inconnu au bord de la piscine.</p>\n<p>Notre philosophie est celle d'une maison privée, et non d'un hôtel. Le petit-déjeuner arrive à l'heure qui vous convient. La cantina est garnie de vins DOC des Marches sélectionnés chez des voisins juste en bas de la route. Si vous souhaitez un chef privé, une chasse aux truffes guidée ou une séance de yoga au lever du soleil près de la piscine, nous l'arrangerons. Tout est possible ; rien n'est obligatoire.</p>");
ts_set_meta(243, [
    'hero_eyebrow'  => 'Notre Histoire',
    'hero_subtitle' => "Depuis le XVe siècle sur l'Adriatique.",
]);
echo "  ✓ About\n";

// Suites FR (251)
ts_update_page(251, 'Suites', 'suites');
ts_set_meta(251, [
    'hero_eyebrow'  => 'Hébergement',
    'hero_subtitle' => 'Trois suites. Un domaine privé. Entièrement pour vous.',
]);
echo "  ✓ Suites\n";

// Pool FR (259)
ts_update_page(259, 'Piscine', 'piscine', "<p>La piscine à débordement de Torre San Bartolo est l'une des caractéristiques emblématiques du domaine. Perchée sur la terrasse de la colline, l'eau forme une ligne d'horizon continue au-dessus des oliveraies et de l'Adriatique scintillante en contrebas. Avec ses 12 × 5 mètres, la piscine chauffée est réservée exclusivement aux hôtes de la villa — pas de réservation de serviettes, pas d'inconnus, pas de file d'attente pour les chaises longues.</p>\n<p>Profitez de matinées paresseuses au soleil, plongez à l'heure dorée quand les collines se teintent d'ambre, ou nagez sous un baldaquin d'étoiles. Chaises longues, parasols, serviettes et un service de boissons fraîches sont fournis sans supplément. Une table à manger privée au bord de l'eau est parfaite pour des dîners aux chandelles servis par notre chef en villa.</p>");
ts_set_meta(259, [
    'hero_eyebrow'  => 'La Piscine',
    'hero_subtitle' => 'Eau infinie, vues sans limite.',
    'pool_cta_text' => 'Demander disponibilités',
    'pool_cta_url'  => '/fr/contact/',
]);
echo "  ✓ Pool\n";

// Experiences FR (267)
ts_update_page(267, 'Expériences', 'experiences');
ts_set_meta(267, [
    'hero_eyebrow'  => 'Expériences',
    'hero_subtitle' => 'Savourez, découvrez et explorez le meilleur des Marches.',
]);
echo "  ✓ Experiences\n";

// Gallery FR (275)
ts_update_page(275, 'Galerie', 'galerie');
ts_set_meta(275, ['hero_eyebrow' => 'Galerie']);
echo "  ✓ Gallery\n";

// Contact FR (291)
ts_update_page(291, 'Contact', 'contact');
ts_set_meta(291, [
    'hero_eyebrow'      => 'Nous Contacter',
    'hero_subtitle'     => "Nous serions ravis de vous accueillir. Parlez-nous de votre séjour idéal.",
    'contatti_telefono' => '+39 0721 910540',
    'contatti_email'    => 'info@torresanbartolo.it',
    'contatti_orari'    => "Lun \xe2\x80\x93 Sam: 09:00 \xe2\x80\x93 19:00\nDim: 10:00 \xe2\x80\x93 17:00\n\nArrivée: à partir de 15:00\nDépart: avant 10:00",
    'address'           => $address_fr,
    'maps_embed'        => $maps_embed,
]);
echo "  ✓ Contact\n";

// Location FR (299)
ts_update_page(299, 'Comment Arriver', 'comment-arriver');
ts_set_meta(299, [
    'hero_eyebrow'         => 'Comment Nous Trouver',
    'address'              => $address_fr,
    'maps_embed'           => $maps_embed,
    'indicazioni_stradali' => "<h3>En Voiture</h3>\n<p>Prendre la SS16 Adriatica vers le nord en direction de Pesaro. Sortir à <strong>Pesaro Nord</strong> et prendre la SP13 vers Gabicce Monte. Suivre les panneaux Torre San Bartolo — l'entrée du domaine se trouve sur la gauche après le deuxième virage en épingle.</p>\n<p>Temps de trajet: Pesaro 15 min · Rimini 35 min · Bologne 1h 45min · Rome 4h</p>\n<h3>En Train</h3>\n<p>Les gares les plus proches sont <strong>Pesaro</strong> (14 km) et <strong>Gabicce Mare</strong> (6 km), toutes deux sur la ligne principale Bologne–Rimini–Ancône. Nous recommandons de réserver un transfert privé à l'avance — nous pouvons l'organiser pour vous.</p>\n<h3>En Avion</h3>\n<p>L'aéroport le plus proche est le <strong>Federico Fellini International (RMI)</strong> à Rimini, à 40 km. L'aéroport de Bologne (BLQ) est à 130 km et relie tous les principaux hubs européens. Des transferts privés sont disponibles sur demande.</p>",
]);
echo "  ✓ Location\n";

// FAQ FR (283)
$faq_fr = [
    ["Quelle est la politique d'annulation?",                                           "<p>Annulation gratuite jusqu'à 14 jours avant l'arrivée. Pour les annulations entre 14 et 7 jours, 50% du montant total est facturé. Les non-présentations ou annulations dans les 7 jours précédant l'arrivée sont facturées en totalité.</p>"],
    ["Quels sont les horaires d'arrivée et de départ?",                                 "<p>L'arrivée est possible à partir de 15h00 et nous demandons aux clients de partir avant 11h00. Un départ anticipé ou tardif peut être arrangé sous réserve de disponibilité — veuillez nous contacter avant votre arrivée.</p>"],
    ['Torre San Bartolo convient-elle aux enfants?',                                    "<p>Absolument. Le domaine est merveilleux pour les familles: le grand jardin, la piscine à débordement (avec un espace peu profond pour les petits enfants), les cheminées intérieures et la cuisine extérieure en font un refuge idéal pour tous.</p>"],
    ['Les animaux de compagnie sont-ils acceptés?',                                     '<p>Les chiens de petite et moyenne taille (deux maximum par réservation) sont les bienvenus. Nous demandons simplement de les tenir éloignés des lits et de ramasser tout désordre dans le jardin. Veuillez nous informer lors de la réservation.</p>'],
    ['La propriété est-elle louée exclusivement?',                                      "<p>Oui. Torre San Bartolo est disponible uniquement en location exclusive — vous ne partagerez jamais le domaine avec d'autres clients. Les trois suites, le jardin, la piscine, la cantina et la cuisine extérieure vous appartiennent entièrement.</p>"],
    ['Y a-t-il un parking disponible?',                                                 '<p>Il y a un parking privé clôturé dans le domaine avec de la place pour quatre véhicules, disponible gratuitement pour les clients.</p>'],
    ['La villa est-elle entièrement équipée en self-catering?',                         "<p>La villa est entièrement équipée avec du linge de lit haut de gamme, des serviettes, des peignoirs et des serviettes de piscine. La cuisine de ferme est approvisionnée avec de l'huile d'olive locale, des condiments et du café. Un panier de bienvenue avec des produits locaux vous attend à votre arrivée.</p>"],
    ['Le Wi-Fi est-il disponible partout dans la propriété?',                           '<p>Oui. Le Wi-Fi haut débit (300 Mbps) est disponible dans toute la villa, les suites et toutes les terrasses extérieures y compris la zone piscine.</p>'],
    ['Quelle est la distance avec la plage la plus proche?',                            "<p>Le domaine se trouve à 3 km à l'intérieur des terres de la côte adriatique. La plage de sable la plus proche — Baia Flaminia — est à 10 minutes en voiture. Le front de mer de Pesaro est à 15 minutes. Des transferts privés peuvent être organisés sur demande.</p>"],
    ['Pouvez-vous organiser des services supplémentaires pendant notre séjour?',        "<p>Absolument. Notre service de conciergerie peut organiser des dîners avec chef privé, des chasses aux truffes, des visites de caves, des excursions à vélo, des massages et traitements spa en villa, de l'équitation, des excursions en bateau et bien plus encore. Contactez-nous à l'avance.</p>"],
];
$faq_fr_ids = [];
foreach ($faq_fr as [$q, $a]) {
    $faq_fr_ids[] = ts_create_faq($q, $a, 'fr');
}
ts_update_page(283, 'Questions Fréquentes', 'questions-frequentes', '<p>Toutes les questions que nos clients ont posées au fil des années trouvent une réponse ci-dessous. Si vous ne trouvez pas ce que vous cherchez, n\'hésitez pas à nous contacter directement — nous répondons en quelques heures.</p>');
ts_set_meta(283, ['hero_eyebrow' => 'Questions Fréquentes']);
ts_set_faq_ids(283, $faq_fr_ids);
echo "  ✓ FAQ (" . count($faq_fr_ids) . " items)\n";

// Book FR (307)
ts_update_page(307, 'Réserver', 'reserver');
ts_set_meta(307, [
    'hero_eyebrow'  => 'Réserver votre Séjour',
    'hero_subtitle' => 'Contactez-nous pour vérifier les disponibilités et recevoir un devis personnalisé.',
    'hero_cta_text' => 'Nous contacter',
    'hero_cta_url'  => '/fr/contact/',
]);
echo "  ✓ Book\n";

// Privacy FR (315)
ts_update_page(315, 'Politique de Confidentialité', 'confidentialite');
ts_set_meta(315, ['hero_eyebrow' => 'Confidentialité']);
echo "  ✓ Privacy\n";

// Cookie FR (323)
ts_update_page(323, 'Politique des Cookies', 'cookies');
ts_set_meta(323, ['hero_eyebrow' => 'Cookies']);
echo "  ✓ Cookie\n";

// ─────────────────────────────────────────────────────────────────────────────
// STEP 6 – Spanish pages
// ─────────────────────────────────────────────────────────────────────────────

echo "\n── Spanish pages ──\n";

// Home ES (236)
ts_update_page(236, 'Torre San Bartolo', 'home');
ts_set_meta(236, [
    'hero_eyebrow'      => 'Torre San Bartolo',
    'hero_subtitle'     => 'Una villa exclusiva en Las Marcas, donde el tiempo se ralentiza y el Adriático se encuentra con las colinas.',
    'hero_cta_text'     => 'Verificar Disponibilidad',
    'hero_cta_url'      => '/es/reservar/',
    'contatti_telefono' => '+39 0721 910540',
    'contatti_email'    => 'info@torresanbartolo.it',
    'contatti_orari'    => "Check-in a partir de las 15:00 – Check-out antes de las 10:00",
]);
echo "  ✓ Home\n";

// About ES (245)
ts_update_page(245, 'Acerca de', 'acerca-de', '<p>Torre San Bartolo toma su nombre de la antigua torre de vigilancia que ha estado vigilando este tramo de la costa adriática desde el siglo XV. En 2008, la propiedad fue restaurada con amor por sus propietarios actuales, preservando las paredes de piedra originales, los techos pintados a mano y los suelos de terracota, al tiempo que se incorporaban todas las comodidades modernas.</p>
<p>Hoy, la finca abre sus puertas exclusivamente a un único grupo de huéspedes a la vez. Tendrán la propiedad entera para ustedes: tres suites, el jardín, la piscina infinita, la cocina exterior y la cantina original. Sin otros huéspedes, sin colas para el desayuno, sin desconocidos junto a la piscina.</p>
<p>Nuestra filosofía es la de una casa privada, no la de un hotel. El desayuno llega a la hora que elijan. La cantina está provista de vinos DOC de Las Marcas seleccionados de vecinos que viven justo al final del camino. Si desean un chef privado, una búsqueda de trufas guiada o una sesión de yoga al amanecer junto a la piscina, lo organizaremos. Todo es posible; nada es obligatorio.</p>');
ts_set_meta(245, [
    'hero_eyebrow'  => 'Nuestra Historia',
    'hero_subtitle' => 'Desde el siglo XV en el Adriático.',
]);
echo "  ✓ About\n";

// Suites ES (253)
ts_update_page(253, 'Suites', 'suites');
ts_set_meta(253, [
    'hero_eyebrow'  => 'Alojamiento',
    'hero_subtitle' => 'Tres suites. Una finca privada. Completamente vuestra.',
]);
echo "  ✓ Suites\n";

// Pool ES (261)
ts_update_page(261, 'Piscina', 'piscina', '<p>La piscina infinita de Torre San Bartolo es una de las características emblemáticas de la finca. Encaramada en la terraza de la ladera, el agua forma una línea de horizonte continua sobre los olivares y el brillante Adriático más abajo. Con sus 12 × 5 metros, la piscina climatizada está reservada exclusivamente para los huéspedes de la villa — sin reservas de toallas, sin desconocidos, sin colas para las tumbonas.</p>
<p>Disfruten de perezosas mañanas tomando el sol, dense un chapuzón a la hora dorada cuando las colinas se tiñen de ámbar, o naden bajo un manto de estrellas. Tumbonas, sombrillas, toallas y un servicio de bebidas frías se proporcionan sin cargo adicional. Una mesa de comedor privada junto al agua es perfecta para cenas a la luz de las velas servidas por nuestro chef en villa.</p>');
ts_set_meta(261, [
    'hero_eyebrow'  => 'La Piscina',
    'hero_subtitle' => 'Agua infinita, vistas sin límites.',
    'pool_cta_text' => 'Solicitar disponibilidad',
    'pool_cta_url'  => '/es/contacto/',
]);
echo "  ✓ Pool\n";

// Experiences ES (269)
ts_update_page(269, 'Experiencias', 'experiencias');
ts_set_meta(269, [
    'hero_eyebrow'  => 'Experiencias',
    'hero_subtitle' => 'Saborea, descubre y explora lo mejor de Las Marcas.',
]);
echo "  ✓ Experiences\n";

// Gallery ES (277)
ts_update_page(277, 'Galería', 'galeria');
ts_set_meta(277, ['hero_eyebrow' => 'Galería']);
echo "  ✓ Gallery\n";

// Contact ES (293)
ts_update_page(293, 'Contacto', 'contacto');
ts_set_meta(293, [
    'hero_eyebrow'      => 'Contáctenos',
    'hero_subtitle'     => 'Nos encantaría recibirle. Cuéntenos sobre su estancia ideal.',
    'contatti_telefono' => '+39 0721 910540',
    'contatti_email'    => 'info@torresanbartolo.it',
    'contatti_orari'    => "Lun \xe2\x80\x93 Sáb: 09:00 \xe2\x80\x93 19:00\nDom: 10:00 \xe2\x80\x93 17:00\n\nCheck-in: desde las 15:00\nCheck-out: antes de las 10:00",
    'address'           => $address_es,
    'maps_embed'        => $maps_embed,
]);
echo "  ✓ Contact\n";

// Location ES (301)
ts_update_page(301, 'Cómo Llegar', 'como-llegar');
ts_set_meta(301, [
    'hero_eyebrow'         => 'Cómo Llegar',
    'address'              => $address_es,
    'maps_embed'           => $maps_embed,
    'indicazioni_stradali' => "<h3>En Coche</h3>\n<p>Tomar la SS16 Adriatica hacia el norte en dirección a Pesaro. Salir en <strong>Pesaro Norte</strong> y tomar la SP13 hacia Gabicce Monte. Seguir las señales hacia Torre San Bartolo — la entrada a la finca está a la izquierda después del segundo giro en horquilla.</p>\n<p>Tiempos de viaje: Pesaro 15 min · Rímini 35 min · Bolonia 1h 45min · Roma 4h</p>\n<h3>En Tren</h3>\n<p>Las estaciones de tren más cercanas son <strong>Pesaro</strong> (14 km) y <strong>Gabicce Mare</strong> (6 km), ambas en la línea principal Bolonia–Rímini–Ancona. Recomendamos reservar un traslado privado con antelación — podemos organizarlo por usted.</p>\n<h3>En Avión</h3>\n<p>El aeropuerto más cercano es el <strong>Federico Fellini International (RMI)</strong> en Rímini, a 40 km. El aeropuerto de Bolonia (BLQ) está a 130 km y conecta con todos los principales centros europeos. Los traslados privados están disponibles bajo petición.</p>",
]);
echo "  ✓ Location\n";

// FAQ ES (285)
$faq_es = [
    ['¿Cuál es la política de cancelación?',                                            '<p>Cancelación gratuita hasta 14 días antes del check-in. Para cancelaciones entre 14 y 7 días, se cobra el 50% del total de la reserva. Las no presentaciones o cancelaciones dentro de los 7 días previos a la llegada se cobran íntegramente.</p>'],
    ['¿Cuáles son los horarios de check-in y check-out?',                              '<p>El check-in está disponible a partir de las 15:00 y pedimos a los huéspedes que hagan el check-out antes de las 11:00. El check-in anticipado y el check-out tardío pueden organizarse según disponibilidad — contáctenos antes de su llegada.</p>'],
    ['¿Torre San Bartolo es adecuada para niños?',                                     '<p>Absolutamente. La villa es maravillosa para familias: el amplio jardín, la piscina infinita (con zona poco profunda para niños pequeños), las chimeneas interiores y la cocina exterior la convierten en un refugio ideal para todos.</p>'],
    ['¿Se admiten mascotas?',                                                           '<p>Los perros de tamaño pequeño y mediano (máximo dos por reserva) son muy bienvenidos. Solo pedimos que no suban a las camas y que se limpie cualquier desorden en el jardín. Por favor, infórmenos al hacer la reserva.</p>'],
    ['¿La propiedad se alquila de forma exclusiva?',                                   '<p>Sí. Torre San Bartolo está disponible únicamente para alquiler exclusivo — nunca compartirá la villa con otros huéspedes. Las tres suites, el jardín, la piscina, la cantina y la cocina exterior son completamente vuestros.</p>'],
    ['¿Hay aparcamiento disponible?',                                                   '<p>Hay un aparcamiento privado cerrado dentro de la finca con espacio para hasta cuatro vehículos, disponible para los huéspedes de forma gratuita.</p>'],
    ['¿La villa está completamente equipada y es self-catering?',                       '<p>La villa viene completamente equipada con ropa de cama premium, toallas, albornoces y toallas de piscina. La cocina de granja está abastecida con aceite de oliva local, condimentos y café. Una cesta de bienvenida con productos locales le espera a su llegada.</p>'],
    ['¿Hay Wi-Fi disponible en toda la propiedad?',                                    '<p>Sí. Wi-Fi de alta velocidad (300 Mbps) disponible en toda la villa, suites y todas las terrazas exteriores incluyendo el área de la piscina.</p>'],
    ['¿A qué distancia está la playa más cercana?',                                    '<p>La finca se encuentra a 3 km del interior de la costa adriática. La playa de arena más cercana — Baia Flaminia — está a 10 minutos en coche. El paseo marítimo de Pesaro está a 15 minutos. Podemos organizar traslados privados a petición.</p>'],
    ['¿Pueden organizar servicios adicionales durante nuestra estancia?',               '<p>Por supuesto. Nuestro servicio de conserjería puede organizar cenas con chef privado, búsqueda de trufas, visitas a bodegas, excursiones en bicicleta, masajes y tratamientos spa en la villa, equitación, excursiones en barco y mucho más. Contáctenos con antelación.</p>'],
];
$faq_es_ids = [];
foreach ($faq_es as [$q, $a]) {
    $faq_es_ids[] = ts_create_faq($q, $a, 'es');
}
ts_update_page(285, 'Preguntas Frecuentes', 'preguntas-frecuentes', '<p>Todas las preguntas que nuestros huéspedes han hecho a lo largo de los años tienen respuesta a continuación. Si no puede encontrar lo que busca, no dude en contactarnos directamente — respondemos en pocas horas.</p>');
ts_set_meta(285, ['hero_eyebrow' => 'Preguntas Frecuentes']);
ts_set_faq_ids(285, $faq_es_ids);
echo "  ✓ FAQ (" . count($faq_es_ids) . " items)\n";

// Book ES (309)
ts_update_page(309, 'Reservar', 'reservar');
ts_set_meta(309, [
    'hero_eyebrow'  => 'Reserve su Estancia',
    'hero_subtitle' => 'Contáctenos para verificar la disponibilidad y recibir un presupuesto personalizado.',
    'hero_cta_text' => 'Contáctenos',
    'hero_cta_url'  => '/es/contacto/',
]);
echo "  ✓ Book\n";

// Privacy ES (317)
ts_update_page(317, 'Política de Privacidad', 'privacidad');
ts_set_meta(317, ['hero_eyebrow' => 'Privacidad']);
echo "  ✓ Privacy\n";

// Cookie ES (325)
ts_update_page(325, 'Política de Cookies', 'cookies-es');
ts_set_meta(325, ['hero_eyebrow' => 'Cookies']);
echo "  ✓ Cookie\n";

// ─────────────────────────────────────────────────────────────────────────────
// STEP 7 – Flush rewrite rules
// ─────────────────────────────────────────────────────────────────────────────

echo "\n── Flushing rewrite rules ──\n";
flush_rewrite_rules(true);
echo "  ✓ Rewrite rules flushed\n";

// ─────────────────────────────────────────────────────────────────────────────
// STEP 8 – Verify a sample
// ─────────────────────────────────────────────────────────────────────────────

echo "\n── Verification sample ──\n";
global $wpdb;
$sample = $wpdb->get_results("
    SELECT p.ID, p.post_name, p.post_title,
        (SELECT name FROM {$wpdb->terms} WHERE term_id=(
            SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy='language'
            AND term_taxonomy_id=(
                SELECT term_taxonomy_id FROM {$wpdb->term_relationships} WHERE object_id=p.ID AND term_taxonomy_id IN (
                    SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy='language'
                ) LIMIT 1
            )
        )) as lang,
        (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=p.ID AND meta_key='_pll_trid' LIMIT 1) as trid,
        (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=p.ID AND meta_key='hero_eyebrow' LIMIT 1) as eyebrow
    FROM {$wpdb->posts} p
    WHERE p.ID IN (12, 180, 239, 241, 243, 245, 247, 279, 281)
    ORDER BY p.ID
");
printf("%-5s %-10s %-30s %-12s %-8s\n", 'ID', 'lang', 'slug', 'trid', 'eyebrow');
echo str_repeat('-', 75) . "\n";
foreach ($sample as $r) {
    printf("%-5d %-10s %-30s %-12s %-8s\n",
        $r->ID, $r->lang, $r->post_name,
        $r->trid ? substr($r->trid, 0, 10) : '(empty)',
        $r->eyebrow ? substr($r->eyebrow, 0, 20) : '(empty)'
    );
}

echo "\n=== Done ===\n";
