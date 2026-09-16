<?php
/**
 * Alusonic content seeder — run once via:  wp eval-file scripts/seed-alusonic.php
 * Idempotent-ish: wipes existing pages/models/artists/faq then recreates.
 *
 * Data-driven: real model specs live in scripts/data/models.json, real
 * artists in scripts/data/artists.json — edit those (not this file) to
 * update content. Photos are pulled from wordpress/_alu_media/:
 *   hero-<model-id>.png        — cutout hero/thumbnail (see scripts/cutout/)
 *   gal-<model-id>-NN.<ext>    — gallery photos (original, not cut out)
 *   artist-<filename>          — artist photos
 */

if (! defined('ABSPATH')) { exit; }

/* ── Media importer (idempotent by _alu_src meta) ── */
function alu_import(string $filename, string $title): int {
    $existing = get_posts([
        'post_type'   => 'attachment',
        'post_status' => 'inherit',
        'meta_key'    => '_alu_src',
        'meta_value'  => $filename,
        'fields'      => 'ids',
        'numberposts' => 1,
    ]);
    if ($existing) { return (int) $existing[0]; }

    $src = ABSPATH . '_alu_media/' . $filename;
    if (! file_exists($src)) { echo "  ! media mancante: $filename\n"; return 0; }

    $up = wp_upload_bits($filename, null, file_get_contents($src));
    if (! empty($up['error'])) { echo "  ! upload err $filename: {$up['error']}\n"; return 0; }

    $ft = wp_check_filetype($up['file']);
    $id = wp_insert_attachment([
        'post_mime_type' => $ft['type'],
        'post_title'     => $title,
        'post_status'    => 'inherit',
    ], $up['file']);
    if (is_wp_error($id) || ! $id) { return 0; }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $up['file']));
    update_post_meta($id, '_alu_src', $filename);
    update_post_meta($id, '_wp_attachment_image_alt', $title);
    return (int) $id;
}

/* ── Gallery file finder: gal-<id>-*.<ext> in _alu_media, sorted ── */
function alu_glob_gallery(string $model_id): array {
    $dir = ABSPATH . '_alu_media/';
    $found = glob($dir . 'gal-' . $model_id . '-*.*') ?: [];
    sort($found);
    return array_map('basename', $found);
}

/* ── Pods/meta savers ── */
function alu_save(string $type, int $post_id, string $field, $value): void {
    if (function_exists('pods')) {
        $pod = pods($type, $post_id);
        if ($pod && $pod->exists()) { $pod->save($field, $value); }
    }
    if (is_array($value)) {
        delete_post_meta($post_id, $field);
        foreach ($value as $v) { add_post_meta($post_id, $field, $v); }
    } else {
        update_post_meta($post_id, $field, $value);
    }
}
function alu_lang(int $post_id): void {
    if (function_exists('pll_set_post_language')) {
        $def = function_exists('pll_default_language') ? (pll_default_language() ?: 'en') : 'en';
        pll_set_post_language($post_id, $def);
    }
}
function alu_specs_string(array $pairs): string {
    return implode("\n", array_map(fn($p) => $p[0] . '|' . $p[1], $pairs));
}

echo "== Alusonic seed ==\n";

/* ═══ 1. Site identity ═══ */
update_option('blogname', 'Alusonic');
update_option('blogdescription', 'Aluminium Instruments');
update_option('timezone_string', 'Europe/Rome');

/* ═══ 2. Ensure Pods fields exist (force rebuild) ═══ */
if (function_exists('torresan_setup_pods')) {
    delete_option('torresan_pods_v');
    torresan_setup_pods();
    echo "Pods fields ensured.\n";
}

/* ═══ 3. Import site-wide media (logo, hero bg, foto riparazioni, foto showroom) ═══ */
$logo    = alu_import('logo-ritagliato.png', 'Alusonic');
$herobg  = alu_import('IMG_4283.jpeg', 'Officina Alusonic');
$setup_img       = alu_import('setup.jpg', 'Setup');
$elettronica_img = alu_import('elettronica.jpg', 'Elettronica');
$rettifiche_img  = alu_import('rettifiche.jpg', 'Rettifiche');
$lucidatura_img  = alu_import('lucidatura.jpg', 'Lucidatura');
$verniciatura_img= alu_import('verniciatura.jpg', 'Verniciatura');
$cnc_img         = alu_import('cnc.jpg', 'Fresatura CNC');
$repairs_imgs = array_values(array_filter([
    $setup_img, $elettronica_img, $rettifiche_img, $lucidatura_img, $verniciatura_img, $cnc_img,
]));
$repairs_hero  = alu_import('handmade-05.jpg', 'Laboratorio Alusonic');
$repairs_intro = alu_import('handmade-01.jpg', 'Laboratorio Alusonic — dettaglio');

/* Foto reali "Chi Siamo" dal sito originale: Andrea "Polly" Pollice + lavorazione CNC */
$polly_img  = alu_import('polly.jpg', 'Andrea "Polly" Pollice — Fondatore Alusonic');
$about_cnc1 = alu_import('about-01.jpg', 'Lavorazione CNC — dettaglio');
$about_cnc2 = alu_import('about-03.jpg', 'Lavorazione CNC — top alluminio');

$rimini_imgs = array_values(array_filter([
    alu_import('showroom-rimini-01.jpg', 'Showroom Rimini'),
    alu_import('showroom-rimini-02.jpg', 'Showroom Rimini'),
    alu_import('showroom-rimini-03.jpg', 'Showroom Rimini'),
    alu_import('showroom-rimini-04.jpg', 'Showroom Rimini'),
    alu_import('showroom-rimini-05.jpg', 'Showroom Rimini'),
    alu_import('showroom-rimini-06.jpg', 'Showroom Rimini'),
    alu_import('showroom-rimini-07.jpg', 'Showroom Rimini'),
    alu_import('showroom-rimini-08.jpg', 'Showroom Rimini'),
    alu_import('showroom-rimini-09.jpg', 'Showroom Rimini'),
    alu_import('showroom-rimini-10.jpg', 'Showroom Rimini'),
]));
$carrara_imgs = array_values(array_filter([
    alu_import('showroom-carrara-01.jpg', 'Showroom Carrara'),
    alu_import('showroom-carrara-02.jpg', 'Showroom Carrara'),
    alu_import('showroom-carrara-03.jpg', 'Showroom Carrara'),
    alu_import('showroom-carrara-04.jpg', 'Showroom Carrara'),
]));
echo "Media di sito importati.\n";

$settings = get_option('torresan_site_settings', []);
if (! is_array($settings)) { $settings = []; }
$settings['logo_image_id'] = $logo;
update_option('torresan_site_settings', $settings);

/* ═══ 4. Wipe existing pages / models / artists / faq ═══ */
foreach (['page','camera','esperienza','domanda_faq'] as $pt) {
    $ids = get_posts(['post_type'=>$pt,'post_status'=>'any','numberposts'=>-1,'fields'=>'ids','suppress_filters'=>true]);
    foreach ($ids as $id) { wp_delete_post($id, true); }
    echo "Cancellati ".count($ids)." '$pt'.\n";
}

/* ═══ 5. Create MODELS (camera) from scripts/data/models.json ═══ */
$models_data = json_decode(file_get_contents(__DIR__ . '/data/models.json'), true)['models'];

$order = 0;
$model_hero_ids = []; // json id => hero attachment id (for homepage picks)
$model_post_ids = []; // json id => post id
foreach ($models_data as $m) {
    $hero = alu_import('hero-' . $m['id'] . '.png', $m['title']);

    $gallery_ids = [];
    foreach (alu_glob_gallery($m['id']) as $gf) {
        $gid = alu_import($gf, $m['title'] . ' — dettaglio');
        if ($gid) { $gallery_ids[] = $gid; }
    }
    $detail_img = $gallery_ids[0] ?? $hero;

    $id = wp_insert_post([
        'post_type'   => 'camera',
        'post_status' => 'publish',
        'post_title'  => $m['title'],
        'post_content'=> $m['detail_html'],
        'menu_order'  => $order++,
    ]);
    if (is_wp_error($id) || ! $id) { continue; }
    alu_lang($id);
    if ($hero) { set_post_thumbnail($id, $hero); $model_hero_ids[$m['id']] = $hero; }
    alu_save('camera', $id, 'model_category', $m['category_label']);
    alu_save('camera', $id, 'model_type', $m['type']);
    alu_save('camera', $id, 'model_lead', $m['lead']);
    alu_save('camera', $id, 'model_specs', alu_specs_string($m['specs']));
    alu_save('camera', $id, 'model_detail_title', $m['detail_title']);
    if ($detail_img) { alu_save('camera', $id, 'model_detail_image', $detail_img); }
    if ($gallery_ids) { alu_save('camera', $id, 'model_gallery', $gallery_ids); }
    $model_post_ids[$m['id']] = $id;
}
echo "Creati " . count($model_post_ids) . " modelli.\n";

/* ═══ 6. Create ARTISTS (esperienza) from scripts/data/artists.json ═══ */
$artists_data = json_decode(file_get_contents(__DIR__ . '/data/artists.json'), true)['artists'];

$order = 0;
$artist_count = 0;
foreach ($artists_data as $a) {
    $photo_id = alu_import('artist-' . $a['photo'], $a['name']);
    $id = wp_insert_post([
        'post_type'   => 'esperienza',
        'post_status' => 'publish',
        'post_title'  => $a['name'],
        'post_content'=> '<p>' . esc_html($a['name']) . ' suona Alusonic.</p>',
        'menu_order'  => $order++,
    ]);
    if (is_wp_error($id) || ! $id) { continue; }
    alu_lang($id);
    if ($photo_id) { set_post_thumbnail($id, $photo_id); }
    alu_save('esperienza', $id, 'artist_band', $a['band']);
    alu_save('esperienza', $id, 'artist_quote', $a['quote']);
    alu_save('esperienza', $id, 'artist_bio', $a['bio'] ?? $a['quote']);
    alu_save('esperienza', $id, 'artist_model', $a['model']);
    $artist_count++;
}
echo "Creati $artist_count artisti.\n";

/* ═══ 7. Create PAGES ═══ */
function alu_page(string $title, string $slug, string $template, string $content, int $order): int {
    $id = wp_insert_post([
        'post_type'=>'page','post_status'=>'publish','post_title'=>$title,
        'post_name'=>$slug,'post_content'=>$content,'menu_order'=>$order,
    ]);
    if (is_wp_error($id) || ! $id) { return 0; }
    if ($template) { update_post_meta($id, '_wp_page_template', $template); }
    alu_lang($id);
    return (int) $id;
}

$home       = alu_page('Alluminio. Nato per suonare diverso.', 'home', '', '', 0);
$pmodels    = alu_page('Tutti i Modelli', 'models', 'page-models.php',
    '<p>Corpi scavati da un blocco pieno di alluminio aeronautico, camerati per il bilanciamento tonale. Django, Django GTS/H, The Doom, J-Special e chitarre: ogni linea Alusonic in un unico catalogo.</p>', 1);
$partists   = alu_page('Artisti', 'artists', 'page-artists.php', '', 2);
$pgallery   = alu_page('Foto', 'gallery', 'page-gallery.php', '', 3);
$pvideo     = alu_page('Video', 'video', 'page-video.php', '', 4);
$pshowrooms = alu_page('Showroom', 'showrooms', 'page-showrooms.php', '', 5);
$prepairs   = alu_page('Riparazioni', 'repairs', 'page-repairs.php', '', 6);
$pabout     = alu_page('Dal 2010, un\'idea fuori dagli schemi', 'about', 'page-about.php', '', 7);
$pcontact   = alu_page('Parliamo del tuo prossimo strumento', 'contact', 'page-contact.php', '', 8);

/* Front page */
if ($home) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $home);
}

/* Home fields */
if ($home) {
    alu_save('page',$home,'hero_eyebrow','Dal 2010 · Made in Morciano di Romagna');
    alu_save('page',$home,'hero_subtitle','Corpi scavati da un blocco pieno di alluminio aeronautico. Pickup e preamp proprietari. Ogni basso Alusonic è un pezzo unico, costruito a mano per chi cerca un suono che il legno non può dare.');
    alu_save('page',$home,'hero_cta_text','Scopri i Modelli');
    alu_save('page',$home,'hero_cta_url', get_permalink($pmodels));
    alu_save('page',$home,'hero_cta2_text','La Nostra Storia');
    alu_save('page',$home,'hero_cta2_url', get_permalink($pabout));
    if ($herobg) { set_post_thumbnail($home, $herobg); }
    if ($logo) { alu_save('page',$home,'hero_foreground', $logo); }

    alu_save('page',$home,'home_stats', "2010|Anno di fondazione\n100%|Fatto a mano in Italia\nAero|Alluminio aeronautico\nUnico|Ogni pezzo numerato");

    alu_save('page',$home,'home_mat_eyebrow','Materiali & processo');
    alu_save('page',$home,'home_mat_title','Scavato da un blocco pieno. Non assemblato: scolpito.');
    alu_save('page',$home,'home_mat_text','<p>Ogni corpo nasce da un singolo blocco di alluminio aeronautico, fresato a CNC e cavo internamente per il miglior bilanciamento tonale. Il risultato: attacco immediato, sustain infinito, un timbro che il legno non riproduce.</p><p>Pickup "Hybrid" Alnico5/Neodimio, preamp e hardware sviluppati internamente: nessun componente è lasciato al caso.</p>');
    alu_save('page',$home,'home_mat_cta_text','Il processo completo');
    alu_save('page',$home,'home_mat_cta_url', get_permalink($pabout));
    $mat_img = $about_cnc2 ?: $cnc_img ?: $herobg;
    if ($mat_img) { alu_save('page',$home,'home_mat_image', $mat_img); }

    // Featured models: Django Supreme Carbon, The Doom, T-Special/P90
    $featured = array_values(array_filter([
        $model_post_ids['django-supreme-carbon'] ?? 0,
        $model_post_ids['the-doom'] ?? 0,
        $model_post_ids['t-specialp90'] ?? 0,
    ]));
    if ($featured) { alu_save('page',$home,'home_models_order', $featured); }

    alu_save('page',$home,'home_ig_url','https://www.instagram.com/alusonic/');
    $ig_images = array_values(array_filter([
        $model_hero_ids['django-supreme-carbon'] ?? 0,
        $model_hero_ids['t-specialp90'] ?? 0,
        $model_hero_ids['the-doom'] ?? 0,
        $model_hero_ids['pinkhage-bass'] ?? 0,
        $model_hero_ids['alu112-mk2'] ?? 0,
    ]));
    alu_save('page',$home,'home_ig_images', $ig_images);

    alu_save('page',$home,'home_cta_title','Pronto a suonare diverso?');
    alu_save('page',$home,'home_cta_text','Richiedi un preventivo, prenota una prova in showroom o parla direttamente con noi per un modello custom.');
    alu_save('page',$home,'home_cta_btn','Contattaci Ora');
}

/* Models page fields */
if ($pmodels) {
    alu_save('page',$pmodels,'hero_eyebrow','Catalogo');
    alu_save('page',$pmodels,'hero_subtitle','Ogni strumento è scavato da un blocco pieno di alluminio, numerato e costruito su richiesta. Nessun basso è uguale all\'altro.');
}

/* Artists page fields */
if ($partists) {
    alu_save('page',$partists,'hero_eyebrow','Chi suona Alusonic');
    alu_save('page',$partists,'hero_subtitle','Da Skunk Anansie agli Editors, passando per session musician e docenti: gli artisti che hanno scelto il suono Alusonic.');
}

/* Gallery page fields */
if ($pgallery) {
    alu_save('page',$pgallery,'hero_eyebrow','Galleria');
    alu_save('page',$pgallery,'hero_subtitle','Finiture, dettagli costruttivi, hardware: ogni strumento Alusonic raccontato per immagini.');
}

/* Video page fields — real videos from the official Alusonic YouTube channel */
if ($pvideo) {
    alu_save('page',$pvideo,'hero_eyebrow','Video');
    alu_save('page',$pvideo,'hero_subtitle','Demo, cover e dietro le quinte dal canale YouTube ufficiale Alusonic.');
    alu_save('page',$pvideo,'media_facebook','https://www.facebook.com/alusonicaluminiuminstruments');
    alu_save('page',$pvideo,'media_instagram','https://www.instagram.com/alusonic');
    alu_save('page',$pvideo,'media_youtube','https://www.youtube.com/user/Alusonic');
    alu_save('page',$pvideo,'media_videos',
        "rW0WGeKdDDQ|Alusonic Marok Signature Bass\n".
        "z2AHLbwL2q0|DOOM ETERNAL - The Only Thing They Fear is You (Bass Cover)\n".
        "FheKNWb7u08|Alusonic Django Standard 4 V2\n".
        "PkHECOrZsbE|Alusonic Django Standard 5 V2\n".
        "exxuRh7xwic|Alusonic Django Deluxe 4 - Upula Madhushanka\n".
        "wFooBg26FsM|Alusonic Pinkhage Signature\n".
        "0jjcw0U8UUU|Alusonic Hybrid T90/S Swamp Ash - Maple\n".
        "bqGzcE4AQAU|Alusonic PentaFleX\n".
        "PeLAgzu0ol4|Alusonic QuadraFleX\n".
        "iS6TTrc1vuU|Alusonic J-Special Supreme 5\n".
        "zmiN-hGXSdk|Alusonic J-Special Deluxe 5\n".
        "bxqLr_Ojcd4|Alusonic J-Special Deluxe 4"
    );
}

/* Showrooms page fields */
if ($pshowrooms) {
    alu_save('page',$pshowrooms,'hero_eyebrow','Showroom');
    alu_save('page',$pshowrooms,'hero_subtitle','Vieni a provare uno strumento Alusonic dal vivo: due sedi in Italia, su appuntamento, più una rete di rivenditori partner nel mondo.');
    alu_save('page',$pshowrooms,'showroom_rimini_title','Rimini · Sede');
    alu_save('page',$pshowrooms,'showroom_rimini_info',"Indirizzo|Via Serrata, 4a — 47833 Morciano di Romagna (RN), Italia\nTelefono|+39 366 2089806\nEmail|info@alusonic.com\nVisite|Su appuntamento");
    if ($rimini_imgs) { alu_save('page',$pshowrooms,'showroom_rimini_gallery', $rimini_imgs); }
    alu_save('page',$pshowrooms,'showroom_carrara_title','Carrara');
    alu_save('page',$pshowrooms,'showroom_carrara_info',"Responsabile|Luca Silvestri\nTelefono|+39 340 5213189\nEmail|lucasilvestri27@yahoo.it\nFacebook|facebook.com/lucasilvestri27");
    if ($carrara_imgs) { alu_save('page',$pshowrooms,'showroom_carrara_gallery', $carrara_imgs); }
    alu_save('page',$pshowrooms,'showroom_partners',
        "Yokohama · Giappone|Geek in Box — rivenditore autorizzato Alusonic|http://geekinbox.jp/alusonic-aluminium-instruments/\n".
        "Regno Unito|Bass Direct — specialista bassi, rivenditore Alusonic|https://www.bassdirect.co.uk/bass_guitar_specialists/Alusonic.html"
    );
}

/* Repairs page fields */
if ($prepairs) {
    alu_save('page',$prepairs,'hero_eyebrow','Setup & Riparazioni');
    alu_save('page',$prepairs,'hero_subtitle','Servizio professionale di setup e riparazione su tutte le marche e tipologie di chitarre e bassi elettrici.');
    alu_save('page',$prepairs,'repairs_text','<p>Da oltre 15 anni con la liuteria Alusonic progettiamo, disegniamo e realizziamo strumenti musicali altamente innovativi, unici al mondo. Nel corso degli anni abbiamo realizzato centinaia di bassi e chitarre per clienti privati e per alcuni degli artisti più famosi al mondo, come Muse, Skunk Anansie, Editors, Max Gazzè, Battiato, De André e tanti altri.</p><p>Offriamo al musicista la possibilità di usufruire della nostra esperienza, professionalità e know-how riconosciuti in tutto il mondo, con un servizio completo e specializzato di manutenzione e riparazione di bassi, chitarre, amplificatori ed equipaggiamento vario.</p><p>Nel nostro showroom troverai un ambiente accogliente dove valutare insieme tipologia, modalità e tempistiche dell\'intervento, affidando il tuo strumento nelle mani di un professionista esperto e apprezzato.</p>');
    alu_save('page',$prepairs,'repairs_services',"Setup\nElettronica\nRettifiche\nLucidatura\nVerniciatura\nFresatura CNC");
    if ($repairs_imgs)  { alu_save('page',$prepairs,'repairs_gallery', $repairs_imgs); }
    if ($repairs_hero)  { alu_save('page',$prepairs,'repairs_hero_image', $repairs_hero); set_post_thumbnail($prepairs, $repairs_hero); }
    if ($repairs_intro) { alu_save('page',$prepairs,'repairs_intro_image', $repairs_intro); }
}

/* About page fields */
if ($pabout) {
    alu_save('page',$pabout,'hero_eyebrow','La nostra storia');
    alu_save('page',$pabout,'about_story_text','<p>Dal 2010 Alusonic sviluppa, progetta e realizza strumenti musicali innovativi. Negli anni abbiamo costruito centinaia di bassi e chitarre per clienti privati e per alcuni degli artisti più famosi al mondo: la nostra missione è realizzare uno strumento che soddisfi ogni esigenza timbrica del musicista, con il gusto, la bellezza e la passione della cultura italiana.</p><p>Nella ricerca di strumenti artigianali di altissima qualità abbiamo progettato componenti proprietari — pickup, preamp, ponte — lavorando con i migliori produttori di parti custom al mondo per sviluppare componenti originali, mai visti prima su uno strumento musicale.</p><p>Tutto questo per offrirti uno strumento unico e una nuova esperienza sonora…</p><p><em>— Andrea "Polly" Pollice, Fondatore e Responsabile Vendite Alusonic</em></p>');
    if ($polly_img) { alu_save('page',$pabout,'about_story_image', $polly_img); }
    alu_save('page',$pabout,'about_process_title','Dal blocco pieno allo strumento finito');
    $proc_img = $about_cnc1 ?: $rettifiche_img ?: $herobg;
    if ($proc_img) { alu_save('page',$pabout,'about_process_image', $proc_img); }
    alu_save('page',$pabout,'about_process_steps', "Selezione della lega|Alluminio aeronautico ad alta densità, scelto per stabilità e risposta tonale.\nFresatura CNC|Il blocco viene scavato in un unico pezzo, cavo internamente per il bilanciamento.\nFinitura a mano|Spazzolatura, anodizzazione o carbonio, rifinita artigianalmente uno strumento alla volta.\nAssemblaggio & collaudo|Elettronica e hardware montati e testati singolarmente prima della consegna.");
}

/* Contact page fields */
if ($pcontact) {
    alu_save('page',$pcontact,'hero_eyebrow','Contatti');
    alu_save('page',$pcontact,'hero_subtitle','Richiedi un preventivo, prenota una prova in showroom o chiedi informazioni su una configurazione custom.');
    alu_save('page',$pcontact,'contatti_email','info@alusonic.com');
    alu_save('page',$pcontact,'contatti_telefono','+39 366 2089806');
    alu_save('page',$pcontact,'contatti_showroom',"Via Serrata, 4a — 47833 Morciano di Romagna (RN), Italia\nVisite su appuntamento");
    alu_save('page',$pcontact,'contatti_instagram','https://www.instagram.com/alusonic/');
    if ($herobg) { alu_save('page',$pcontact,'contatti_image', $herobg); }
}
echo "Create pagine: home=$home models=$pmodels artists=$partists gallery=$pgallery video=$pvideo showrooms=$pshowrooms repairs=$prepairs about=$pabout contact=$pcontact.\n";

/* ═══ 8. Menus ═══
 * Ogni voce: [page_id, etichetta, figli opzionali]. "Galleria" è un genitore
 * con link proprio (va alla pagina Foto) e un sottomenu a tendina Foto/Video. */
function alu_menu_items(int $pmodels, int $partists, int $pgallery, int $pvideo, int $pshowrooms, int $prepairs, int $pabout, int $pcontact): array {
    return [
        [$pmodels, 'Modelli', []],
        [$partists, 'Artisti', []],
        [$pgallery, 'Galleria', [[$pgallery, 'Foto'], [$pvideo, 'Video']]],
        [$pshowrooms, 'Showroom', []],
        [$prepairs, 'Riparazioni', []],
        [$pabout, 'Chi Siamo', []],
        [$pcontact, 'Contatti', []],
    ];
}

$alu_menu_ids = [];
foreach (['primary'=>'Menu Principale','footer'=>'Menu Footer'] as $loc=>$mname) {
    $existing = wp_get_nav_menu_object($mname);
    if ($existing) { wp_delete_nav_menu($existing->term_id); }
    $menu_id = wp_create_nav_menu($mname);
    if (is_wp_error($menu_id)) { continue; }
    $alu_menu_ids[$loc] = $menu_id;

    $items = alu_menu_items($pmodels, $partists, $pgallery, $pvideo, $pshowrooms, $prepairs, $pabout, $pcontact);

    foreach ($items as [$pgid, $label, $children]) {
        if (! $pgid) { continue; }
        $parent_item_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'=>$label,'menu-item-object'=>'page',
            'menu-item-object-id'=>$pgid,'menu-item-type'=>'post_type','menu-item-status'=>'publish',
        ]);
        foreach ($children as [$cgid, $clabel]) {
            if (! $cgid || is_wp_error($parent_item_id)) { continue; }
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'=>$clabel,'menu-item-object'=>'page',
                'menu-item-object-id'=>$cgid,'menu-item-type'=>'post_type','menu-item-status'=>'publish',
                'menu-item-parent-id'=>$parent_item_id,
            ]);
        }
    }
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations[$loc] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}
echo "Menu creati e assegnati.\n";

/* Polylang keeps its own per-language menu-location map (option
 * 'polylang' > nav_menus > <theme> > <location> > <lang>), separate from
 * the standard theme_mod. If it's not repointed at the fresh menu IDs
 * above, the header nav renders empty. Point every configured language at
 * our single-language content so the nav always shows. */
if (function_exists('pll_languages_list') && ! empty($alu_menu_ids)) {
    $langs = pll_languages_list();
    $poly  = get_option('polylang', []);
    if (is_array($poly)) {
        $theme = get_stylesheet();
        foreach ($alu_menu_ids as $loc => $mid) {
            foreach ($langs as $lang) {
                $poly['nav_menus'][$theme][$loc][$lang] = $mid;
            }
        }
        update_option('polylang', $poly);
        echo "Menu Polylang ricollegati per: " . implode(', ', $langs) . "\n";
    }
}

/* ═══ 9. Flush ═══ */
flush_rewrite_rules(false);
echo "== FATTO ==\n";
