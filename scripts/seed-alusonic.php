<?php
/**
 * Alusonic content seeder — run once via:  wp eval-file seed-alusonic.php
 * Idempotent-ish: wipes existing pages/models/artists/faq then recreates.
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

/* ═══ 3. Import media ═══ */
$M = [
    'django'  => alu_import('IMG_4279-transparent.png', 'Django Supreme'),
    'strato'  => alu_import('IMG_4278-transparent.png', 'StratOSonic'),
    'doom'    => alu_import('IMG_4281-transparent.png', 'Doom'),
    'lewis'   => alu_import('IMG_4283-transparent.png', 'Cass Lewis Signature'),
    'tele'    => alu_import('IMG_4282-transparent.png', 'AluTele'),
    'block'   => alu_import('IMG_4280-transparent.png', 'Alluminio — dettaglio'),
    'herobg'  => alu_import('IMG_4283.jpeg', 'Officina Alusonic'),
    'logo'    => alu_import('logo-ritagliato-clean.png', 'Alusonic logo'),
    'logofg'  => alu_import('logo-ritagliato.png', 'Alusonic'),
];
echo "Media importati.\n";

/* Logo in site settings */
$settings = get_option('torresan_site_settings', []);
if (! is_array($settings)) { $settings = []; }
$settings['logo_image_id'] = $M['logo'];
update_option('torresan_site_settings', $settings);

/* ═══ 4. Wipe existing pages / models / artists / faq ═══ */
foreach (['page','camera','esperienza','domanda_faq'] as $pt) {
    $ids = get_posts(['post_type'=>$pt,'post_status'=>'any','numberposts'=>-1,'fields'=>'ids','suppress_filters'=>true]);
    foreach ($ids as $id) { wp_delete_post($id, true); }
    echo "Cancellati ".count($ids)." '$pt'.\n";
}

/* ═══ 5. Create MODELS (camera) ═══ */
$models = [
    ['Django Supreme', 'basso', 'Basso · Custom Shop', $M['django'],
     'Il modello di punta Alusonic. Corpo scavato da un blocco pieno di alluminio aeronautico, camerato per il bilanciamento tonale perfetto. Preamp attivo custom, sustain praticamente infinito.',
     "Corpo|Alluminio pieno\nCorde|4 / 5 / 6\nManico|Acero avvitato\nElettronica|Attiva custom",
     'Ogni pezzo è numerato e unico',
     '<p>Manico avvitato in acero, tastiera in ebano, hardware sviluppato internamente. Il corpo in alluminio viene finito a mano con spazzolatura o anodizzazione a scelta.</p><p>Disponibile in configurazione 4, 5 o 6 corde, con elettronica passiva o attiva a seconda delle esigenze.</p>',
     $M['block'], [$M['block'],$M['tele'],$M['lewis']]],
    ['StratOSonic', 'chitarra', 'Chitarra · Hybrid', $M['strato'],
     'Il classico single-cut reinterpretato: top in alluminio spazzolato su corpo in ontano, per un ibrido che unisce calore del legno e brillantezza metallica.',
     "Top|Alluminio 3mm\nCorpo|Ontano\nManico|Acero / palissandro\nPickup|3x single coil",
     'Il top che cambia tutto',
     '<p>Il top in alluminio da 3mm è fresato e spazzolato a mano, poi accoppiato al corpo in ontano per bilanciare peso e risonanza. Manico in acero con tastiera in palissandro.</p><p>Disponibile in finitura naturale spazzolata o anodizzata in vari colori.</p>',
     $M['strato'], [$M['lewis'],$M['doom'],$M['tele']]],
    ['Doom', 'basso', 'Basso · Signature', $M['doom'],
     'Aggressivo e tagliente, pensato per bassisti metal e prog. Corpo in alluminio con camere di risonanza dedicate, per un attacco immediato e definizione estrema.',
     "Corpo|Alluminio pieno\nCorde|5 / 6\nManico|Acero\nElettronica|Attiva custom",
     'Costruito per spingere', '<p>Un basso nato per generi estremi: risposta rapida, medi presenti e un sustain che non perdona.</p>',
     $M['doom'], []],
    ['Cass Lewis Signature', 'basso', 'Basso · Signature', $M['lewis'],
     'Sviluppato insieme a Cass Lewis (Skunk Anansie): un signature model che unisce groove e definizione, con la firma sonora dell\'alluminio Alusonic.',
     "Corpo|Alluminio pieno\nCorde|4\nManico|Acero avvitato\nElettronica|Attiva custom",
     'La firma di Cass Lewis', '<p>Ogni dettaglio scelto insieme all\'artista, dalla configurazione dei pickup alla finitura del corpo.</p>',
     $M['lewis'], []],
    ['AluTele', 'chitarra', 'Chitarra · Hybrid', $M['tele'],
     'Twang cristallino e sustain metallico: top in alluminio spazzolato su corpo in ontano, per un ibrido dal carattere inconfondibile.',
     "Top|Alluminio spazzolato\nCorpo|Ontano\nManico|Acero\nPickup|2x single coil",
     'Twang con un\'anima di metallo', '<p>Il classico single-cut rivisitato con il top in alluminio Alusonic.</p>',
     $M['tele'], []],
    ['ALU112', 'cabinet', 'Cabinet', $M['doom'],
     'Cassa in alluminio aeronautico con woofer al neodimio: leggera, rigida, senza risonanze parassite. Il suono del tuo ampli, più pulito e definito.',
     "Materiale|Alluminio aeronautico\nAltoparlante|1x12\" neodimio\nImpedenza|8 Ohm\nPotenza|300W",
     'Rigidità che si sente', '<p>Una cassa che non colora il suono: solo il tuo tono, restituito con precisione.</p>',
     $M['block'], []],
];
$order = 0;
foreach ($models as [$title,$type,$cat,$img,$lead,$specs,$dtitle,$content,$dimg,$gal]) {
    $id = wp_insert_post([
        'post_type'=>'camera','post_status'=>'publish','post_title'=>$title,
        'post_content'=>$content,'menu_order'=>$order++,
    ]);
    if (is_wp_error($id) || ! $id) { continue; }
    alu_lang($id);
    if ($img) { set_post_thumbnail($id, $img); }
    alu_save('camera',$id,'model_category',$cat);
    alu_save('camera',$id,'model_type',$type);
    alu_save('camera',$id,'model_lead',$lead);
    alu_save('camera',$id,'model_specs',$specs);
    alu_save('camera',$id,'model_detail_title',$dtitle);
    if ($dimg) { alu_save('camera',$id,'model_detail_image',$dimg); }
    if (! empty($gal)) { alu_save('camera',$id,'model_gallery', array_values(array_filter($gal))); }
}
echo "Creati ".count($models)." modelli.\n";

/* ═══ 6. Create ARTISTS (esperienza) ═══ */
$artists = [
    ['Cass Lewis','Skunk Anansie','Un suono che non avevo mai sentito da un basso.','Cass Lewis Signature'],
    ['Alberto Rigoni','Solo Bassist','Sustain e definizione a un livello superiore.','Django Supreme'],
    ['Justin Lockey','Editors','Uno strumento che è anche scultura.','StratOSonic'],
    ['David Caraccio','DavidSinRocks','Impossibile tornare al legno dopo Alusonic.','Doom'],
];
$order = 0;
foreach ($artists as [$name,$band,$quote,$model]) {
    $id = wp_insert_post([
        'post_type'=>'esperienza','post_status'=>'publish','post_title'=>$name,
        'post_content'=>'<p>'.esc_html($name).' suona Alusonic.</p>','menu_order'=>$order++,
    ]);
    if (is_wp_error($id) || ! $id) { continue; }
    alu_lang($id);
    alu_save('esperienza',$id,'artist_band',$band);
    alu_save('esperienza',$id,'artist_quote',$quote);
    alu_save('esperienza',$id,'artist_model',$model);
}
echo "Creati ".count($artists)." artisti.\n";

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

$home = alu_page('Alluminio. Nato per suonare diverso.', 'home', '', '', 0);
$pmodels = alu_page('Tutti i Modelli', 'models', 'page-models.php',
    '<p>Finiture, elettronica, misure: costruiamo lo strumento su misura per il tuo suono. Ogni strumento è scavato da un blocco pieno di alluminio, numerato e costruito su richiesta.</p>', 1);
$pabout = alu_page('Dal 2010, un\'idea fuori dagli schemi', 'about', 'page-about.php', '', 2);
$pcontact = alu_page('Parliamo del tuo prossimo strumento', 'contact', 'page-contact.php', '', 3);

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
    if ($M['herobg']) { set_post_thumbnail($home, $M['herobg']); }
    if ($M['logofg']) { alu_save('page',$home,'hero_foreground', $M['logofg']); }

    alu_save('page',$home,'home_stats', "2010|Anno di fondazione\n100%|Fatto a mano in Italia\nAero|Alluminio aeronautico\nUnico|Ogni pezzo numerato");

    alu_save('page',$home,'home_mat_eyebrow','Materiali & processo');
    alu_save('page',$home,'home_mat_title','Scavato da un blocco pieno. Non assemblato: scolpito.');
    alu_save('page',$home,'home_mat_text','<p>Ogni corpo nasce da un singolo blocco di alluminio aeronautico, fresato a CNC e cavo internamente per il miglior bilanciamento tonale. Il risultato: attacco immediato, sustain infinito, un timbro che il legno non riproduce.</p><p>Pickup "Hybrid" Alnico5/Neodimio, preamp e hardware sviluppati internamente: nessun componente è lasciato al caso.</p>');
    alu_save('page',$home,'home_mat_cta_text','Il processo completo');
    alu_save('page',$home,'home_mat_cta_url', get_permalink($pabout));
    if ($M['block']) { alu_save('page',$home,'home_mat_image', $M['block']); }

    alu_save('page',$home,'home_ig_url','https://www.instagram.com/alusonic/');
    alu_save('page',$home,'home_ig_images', array_values(array_filter([$M['django'],$M['strato'],$M['tele'],$M['lewis'],$M['doom']])));

    alu_save('page',$home,'home_cta_title','Pronto a suonare diverso?');
    alu_save('page',$home,'home_cta_text','Richiedi un preventivo, prenota una prova in showroom o parla direttamente con Andrea per un modello custom.');
    alu_save('page',$home,'home_cta_btn','Contattaci Ora');
}

/* Models page fields */
if ($pmodels) {
    alu_save('page',$pmodels,'hero_eyebrow','Catalogo');
    alu_save('page',$pmodels,'hero_subtitle','Ogni strumento è scavato da un blocco pieno di alluminio, numerato e costruito su richiesta. Nessun basso è uguale all\'altro.');
}

/* About page fields */
if ($pabout) {
    alu_save('page',$pabout,'hero_eyebrow','La nostra storia');
    alu_save('page',$pabout,'about_story_text','<p>Alusonic nasce a Morciano di Romagna dall\'intuizione di Andrea Pollice: costruire strumenti a corda usando l\'alluminio aeronautico al posto del legno, non per moda ma per inseguire un suono che nessun altro materiale può offrire.</p><p>Dieci anni di sperimentazione su leghe, geometrie e camere di risonanza hanno portato a una linea di bassi e chitarre riconosciuta da musicisti in tutto il mondo, ognuno costruito a mano, uno alla volta.</p>');
    if ($M['herobg']) { alu_save('page',$pabout,'about_story_image', $M['herobg']); }
    alu_save('page',$pabout,'about_process_title','Dal blocco pieno allo strumento finito');
    if ($M['block']) { alu_save('page',$pabout,'about_process_image', $M['block']); }
    alu_save('page',$pabout,'about_process_steps', "Selezione della lega|Alluminio aeronautico ad alta densità, scelto per stabilità e risposta tonale.\nFresatura CNC|Il blocco viene scavato in un unico pezzo, cavo internamente per il bilanciamento.\nFinitura a mano|Spazzolatura o anodizzazione, rifinita artigianalmente uno strumento alla volta.\nAssemblaggio & collaudo|Elettronica e hardware montati e testati singolarmente prima della consegna.");
}

/* Contact page fields */
if ($pcontact) {
    alu_save('page',$pcontact,'hero_eyebrow','Contatti');
    alu_save('page',$pcontact,'hero_subtitle','Richiedi un preventivo, prenota una prova in showroom o chiedi informazioni su una configurazione custom.');
    alu_save('page',$pcontact,'contatti_email','info@alusonic.it');
    alu_save('page',$pcontact,'contatti_showroom',"Morciano di Romagna (RN), Italia\nVisite su appuntamento");
    alu_save('page',$pcontact,'contatti_instagram','https://www.instagram.com/alusonic/');
    if ($M['herobg']) { alu_save('page',$pcontact,'contatti_image', $M['herobg']); }
}
echo "Create pagine: home=$home models=$pmodels about=$pabout contact=$pcontact.\n";

/* ═══ 8. Menus ═══ */
foreach (['primary'=>'Menu Principale','footer'=>'Menu Footer'] as $loc=>$mname) {
    $existing = wp_get_nav_menu_object($mname);
    if ($existing) { wp_delete_nav_menu($existing->term_id); }
    $menu_id = wp_create_nav_menu($mname);
    if (is_wp_error($menu_id)) { continue; }
    foreach ([[ $pmodels,'Modelli'],[ $pabout,'Chi Siamo'],[ $pcontact,'Contatti']] as [$pgid,$label]) {
        if (! $pgid) { continue; }
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'=>$label,'menu-item-object'=>'page',
            'menu-item-object-id'=>$pgid,'menu-item-type'=>'post_type','menu-item-status'=>'publish',
        ]);
    }
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations[$loc] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}
echo "Menu creati e assegnati.\n";

/* ═══ 9. Flush ═══ */
flush_rewrite_rules(false);
if (function_exists('pll_save_post_translations')) { /* no-op */ }
echo "== FATTO ==\n";
