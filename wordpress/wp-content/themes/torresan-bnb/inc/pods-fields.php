<?php
if (! defined('ABSPATH')) { exit; }

/*
 * Alusonic — Pods field definitions.
 *
 * Internal pod/post-type keys are kept as 'camera' (Models) and 'esperienza'
 * (Artists) for continuity with the original install, but every field, group
 * and label is Alusonic-specific.
 */

function torresan_ensure_group(object $api, int $pod_id, string $name, string $label, string $desc = ''): void
{
    try {
        $api->save_group(['pod_id' => $pod_id, 'name' => $name, 'label' => $label, 'description' => $desc]);
    } catch (\Throwable $e) {
        error_log('[Alusonic Pods] Group "' . $name . '" failed: ' . $e->getMessage());
    }
}

function torresan_ensure_field(object $api, array $params): void
{
    try { $api->save_field($params); }
    catch (\Throwable $e) {
        error_log('[Alusonic Pods] Field "' . ($params['name'] ?? '?') . '" failed: ' . $e->getMessage());
    }
}

function torresan_delete_field_if_exists(object $api, int $pod_id, string $name): void
{
    try {
        $f = $api->load_field(['pod_id' => $pod_id, 'name' => $name]);
        if ($f && ! empty($f['id'])) {
            $api->delete_field(['id' => $f['id'], 'pod_id' => $pod_id]);
        }
    } catch (\Throwable $e) { /* not present — fine */ }
}

function torresan_setup_pods(): void
{
    $version = 'alusonic-1.4';
    if (get_option('torresan_pods_v') === $version) { return; }
    if (! function_exists('pods_api')) { return; }

    $api = pods_api();

    try {
        /* ═══════════════ PAGE POD ═══════════════ */
        $pp = $api->load_pod(['name' => 'page']);
        $pid = $pp ? ($pp['id'] ?? 0) : 0;
        if (! $pid) {
            $pid = $api->save_pod(['name'=>'page','label'=>'Pagine','type'=>'post_type','object'=>'page','storage'=>'meta','create_extend'=>'extend']);
        }
        if (! $pid) { return; }

        // ── Hero (all pages) ──
        torresan_ensure_group($api, $pid, 'impostazioni_hero', '🖼️ Hero', 'Immagine sfondo = Immagine in Evidenza.');
        $w = 0;
        foreach ([
            ['hero_eyebrow','Eyebrow (etichetta piccola)','text'],
            ['hero_subtitle','Sottotitolo','paragraph'],
            ['hero_cta_text','Testo bottone principale','text'],
            ['hero_cta_url','Link bottone principale','text'],
            ['hero_cta2_text','Testo bottone secondario','text'],
            ['hero_cta2_url','Link bottone secondario','text'],
        ] as [$n,$l,$t]) {
            torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'impostazioni_hero','name'=>$n,'label'=>$l,'type'=>$t,'weight'=>$w++]);
        }
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'impostazioni_hero','name'=>'hero_bg_mobile','label'=>'Immagine sfondo (Mobile)','description'=>'Usata sotto i 900px al posto dell\'Immagine in Evidenza.','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);
        // Optional foreground hero image (e.g. instrument PNG next to hero text)
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'impostazioni_hero','name'=>'hero_foreground','label'=>'Immagine in primo piano (Hero)','description'=>'PNG trasparente mostrato accanto al testo dell\'hero (es. strumento o logo).','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);
        // Homepage-only: wordmark logo shown in place of the H1 title, and a looping video on the right (desktop/tablet)
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'impostazioni_hero','name'=>'hero_logo','label'=>'Logo hero (sostituisce il titolo — homepage)','description'=>'Logo Alusonic completo, versione chiara su sfondo scuro.','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'impostazioni_hero','name'=>'hero_video','label'=>'Video hero in loop (homepage, desktop/tablet)','description'=>'Video verticale mostrato a destra dell\'hero, muto e in loop. Nascosto su mobile.','type'=>'file','file_format_type'=>'single','file_type'=>'video','file_uploader'=>'attachment','weight'=>$w++]);

        // ── Homepage: Stats bar ──
        torresan_ensure_group($api, $pid, 'home_stats', '🏠 Homepage — Barra Statistiche', 'Una riga per statistica, formato: Valore|Etichetta (consigliate 4).');
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'home_stats','name'=>'home_stats','label'=>'Statistiche','description'=>'Una per riga, formato Valore|Etichetta. Esempio: 2010|Anno di fondazione','type'=>'paragraph','weight'=>0]);

        // ── Homepage: sezione Materiali ──
        torresan_ensure_group($api, $pid, 'home_materials', '🏠 Homepage — Materiali & Processo', 'Immagine + titolo + testo + bottone.');
        $w = 0;
        foreach ([
            ['home_mat_eyebrow','Eyebrow','text'],
            ['home_mat_title','Titolo','text'],
            ['home_mat_text','Testo','wysiwyg'],
            ['home_mat_cta_text','Testo bottone','text'],
            ['home_mat_cta_url','Link bottone','text'],
        ] as [$n,$l,$t]) {
            $p = ['pod_id'=>$pid,'group'=>'home_materials','name'=>$n,'label'=>$l,'type'=>$t,'weight'=>$w++];
            if ($t === 'wysiwyg') { $p['wysiwyg_wpautop'] = 0; }
            torresan_ensure_field($api, $p);
        }
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'home_materials','name'=>'home_mat_image','label'=>'Immagine','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);

        // ── Homepage: modelli in evidenza (ordine) ──
        torresan_ensure_group($api, $pid, 'home_models', '🏠 Homepage — Modelli in evidenza', 'Scegli e ordina i modelli mostrati in homepage (max 3).');
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'home_models','name'=>'home_models_order','label'=>'Modelli in evidenza (max 3)','description'=>'Se vuoto, mostra i primi 3 per ordine.','type'=>'pick','pick_object'=>'post_type','pick_val'=>'camera','pick_format_type'=>'multi','pick_format_multi'=>'list','pick_limit'=>3,'weight'=>0]);

        // ── Homepage: Instagram grid ──
        torresan_ensure_group($api, $pid, 'home_instagram', '🏠 Homepage — Instagram', 'Griglia immagini + link profilo.');
        $w = 0;
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'home_instagram','name'=>'home_ig_url','label'=>'URL profilo Instagram','type'=>'text','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'home_instagram','name'=>'home_ig_images','label'=>'Immagini (consigliate 5)','type'=>'file','file_format_type'=>'multi','file_type'=>'images','file_uploader'=>'attachment','file_limit'=>0,'weight'=>$w++]);

        // ── Homepage: CTA finale ──
        torresan_ensure_group($api, $pid, 'home_cta', '🏠 Homepage — CTA finale', 'Titolo + testo + bottone (link ai Contatti).');
        $w = 0;
        foreach ([
            ['home_cta_title','Titolo','text'],
            ['home_cta_text','Testo','paragraph'],
            ['home_cta_btn','Testo bottone','text'],
        ] as [$n,$l,$t]) {
            torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'home_cta','name'=>$n,'label'=>$l,'type'=>$t,'weight'=>$w++]);
        }

        // ── About / Chi Siamo ──
        torresan_ensure_group($api, $pid, 'pagina_about', 'ℹ️ Chi Siamo', 'Storia + processo produttivo.');
        $w = 0;
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_about','name'=>'about_story_text','label'=>'Testo storia','type'=>'wysiwyg','wysiwyg_wpautop'=>0,'weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_about','name'=>'about_story_image','label'=>'Immagine storia (ritratto/officina)','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_about','name'=>'about_process_title','label'=>'Titolo sezione processo','type'=>'text','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_about','name'=>'about_process_image','label'=>'Immagine processo (CNC)','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_about','name'=>'about_process_steps','label'=>'Passi del processo','description'=>'Uno per riga, formato: Titolo|Descrizione','type'=>'paragraph','weight'=>$w++]);

        // ── Contatti ──
        torresan_ensure_group($api, $pid, 'informazioni_contatto', '📞 Contatti', 'Recapiti + showroom.');
        $w = 0;
        foreach ([
            ['contatti_email','Email','text'],
            ['contatti_telefono','Telefono','text'],
            ['contatti_showroom','Showroom (indirizzo)','paragraph'],
            ['contatti_instagram','URL Instagram','text'],
        ] as [$n,$l,$t]) {
            torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'informazioni_contatto','name'=>$n,'label'=>$l,'type'=>$t,'weight'=>$w++]);
        }
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'informazioni_contatto','name'=>'contatti_image','label'=>'Foto showroom','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);

        // ── Showroom (pagina dedicata, due sedi) ──
        torresan_ensure_group($api, $pid, 'pagina_showroom', '🏪 Showroom', 'Una per sede: una riga per dato, formato Etichetta|Valore.');
        $w = 0;
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_showroom','name'=>'showroom_rimini_title','label'=>'Titolo sede 1','type'=>'text','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_showroom','name'=>'showroom_rimini_info','label'=>'Dati sede 1','description'=>'Una per riga, formato: Etichetta|Valore','type'=>'paragraph','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_showroom','name'=>'showroom_rimini_gallery','label'=>'Foto sede 1','type'=>'file','file_format_type'=>'multi','file_type'=>'images','file_uploader'=>'attachment','file_limit'=>0,'weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_showroom','name'=>'showroom_carrara_title','label'=>'Titolo sede 2','type'=>'text','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_showroom','name'=>'showroom_carrara_info','label'=>'Dati sede 2','description'=>'Una per riga, formato: Etichetta|Valore','type'=>'paragraph','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_showroom','name'=>'showroom_carrara_gallery','label'=>'Foto sede 2','type'=>'file','file_format_type'=>'multi','file_type'=>'images','file_uploader'=>'attachment','file_limit'=>0,'weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_showroom','name'=>'showroom_partners','label'=>'Rivenditori partner (estero)','description'=>'Uno per riga, formato: Titolo|Info|URL. Es: Yokohama / Giappone|Geek in Box|https://...','type'=>'paragraph','weight'=>$w++]);

        // ── Riparazioni ──
        torresan_ensure_group($api, $pid, 'pagina_repairs', '🔧 Riparazioni', 'Testo del servizio di setup/riparazione.');
        $w = 0;
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_repairs','name'=>'repairs_hero_image','label'=>'Foto jumbotron (in alto)','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_repairs','name'=>'repairs_intro_image','label'=>'Foto accanto al testo','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_repairs','name'=>'repairs_text','label'=>'Testo','type'=>'wysiwyg','wysiwyg_wpautop'=>0,'weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_repairs','name'=>'repairs_services','label'=>'Servizi offerti','description'=>'Uno per riga.','type'=>'paragraph','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_repairs','name'=>'repairs_gallery','label'=>'Foto servizi (con etichetta)','type'=>'file','file_format_type'=>'multi','file_type'=>'images','file_uploader'=>'attachment','file_limit'=>0,'weight'=>$w++]);

        // ── Video ──
        torresan_ensure_group($api, $pid, 'pagina_media', '🎬 Video', 'Video YouTube in evidenza + canali social.');
        $w = 0;
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_media','name'=>'media_videos','label'=>'Video in evidenza','description'=>'Uno per riga, formato: Link_o_ID_YouTube|Titolo (es. https://www.youtube.com/watch?v=dQw4w9WgXcQ|Alusonic Django Standard 4)','type'=>'paragraph','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_media','name'=>'media_facebook','label'=>'URL Facebook','type'=>'text','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_media','name'=>'media_instagram','label'=>'URL Instagram','type'=>'text','weight'=>$w++]);
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'pagina_media','name'=>'media_youtube','label'=>'URL YouTube','type'=>'text','weight'=>$w++]);

        // ── FAQ (predisposizione) ──
        torresan_ensure_group($api, $pid, 'domande_frequenti', '❓ Domande Frequenti', 'Solo pagina FAQ.');
        torresan_ensure_field($api, ['pod_id'=>$pid,'group'=>'domande_frequenti','name'=>'faq_pagina','label'=>'FAQ','type'=>'pick','pick_object'=>'post_type','pick_val'=>'domanda_faq','pick_format_type'=>'multi','pick_format_multi'=>'list','pick_limit'=>0,'weight'=>0]);

        // Remove obsolete BnB page field groups' fields we no longer use.
        foreach ([
            'hero_video_url','intro_text','intro_image','intro_image_mobile',
            'hp_sa_title','hp_sa_text','hp_sa_cta_text','hp_sa_cta_url','hp_sa_gallery','hp_sa_gallery_mobile',
            'hp_sb_eyebrow','hp_sb_text','hp_sb_cta_text','hp_sb_cta_url','hp_sb_bg','hp_sb_bg_mobile',
            'hp_sc_eyebrow','hp_sc_text','hp_sc_gallery','hp_sc_gallery_mobile','hp_sc_cta_text','hp_sc_cta_url',
            'newsletter_title','newsletter_text','newsletter_btn',
            'about_story_bg','about_carousel',
            'suites_intro_text','experiences_order','home_experiences_order',
            'pool_cta_text','pool_cta_url','pool_gallery','pool_gallery_mobile',
            'gallery_photos','contatti_orari','contact_shortcode',
            'address','indicazioni_stradali','maps_embed','map_static_image',
        ] as $obsolete) {
            torresan_delete_field_if_exists($api, $pid, $obsolete);
        }

        /* ═══════════════ MODEL CPT (camera) ═══════════════ */
        $cp = $api->load_pod(['name' => 'camera']);
        $cid = $cp ? ($cp['id'] ?? 0) : 0;
        if (! $cid) {
            $cid = $api->save_pod(['name'=>'camera','label'=>'Models','type'=>'post_type','object'=>'camera','storage'=>'meta','create_extend'=>'extend']);
        }
        if ($cid) {
            torresan_ensure_group($api, $cid, 'dettagli_modello', 'Model Details');
            $w = 0;
            torresan_ensure_field($api, ['pod_id'=>$cid,'group'=>'dettagli_modello','name'=>'model_category','label'=>'Categoria (etichetta)','description'=>'Es. "Basso · Custom Shop", "Chitarra · Hybrid".','type'=>'text','weight'=>$w++]);
            torresan_ensure_field($api, ['pod_id'=>$cid,'group'=>'dettagli_modello','name'=>'model_type','label'=>'Tipo (per filtro)','type'=>'pick','pick_object'=>'custom-simple','pick_custom'=>"basso|Bassi\nchitarra|Chitarre",'pick_format_type'=>'single','pick_format_single'=>'dropdown','weight'=>$w++]);
            torresan_ensure_field($api, ['pod_id'=>$cid,'group'=>'dettagli_modello','name'=>'model_lead','label'=>'Descrizione breve (intro)','type'=>'paragraph','weight'=>$w++]);
            torresan_ensure_field($api, ['pod_id'=>$cid,'group'=>'dettagli_modello','name'=>'model_specs','label'=>'Specifiche','description'=>'Una per riga, formato: Etichetta|Valore. Esempio: Corpo|Alluminio pieno','type'=>'paragraph','weight'=>$w++]);
            torresan_ensure_field($api, ['pod_id'=>$cid,'group'=>'dettagli_modello','name'=>'model_hero_mobile','label'=>'Immagine hero (Mobile)','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);
            torresan_ensure_field($api, ['pod_id'=>$cid,'group'=>'dettagli_modello','name'=>'model_gallery','label'=>'Galleria finiture (3 consigliate)','type'=>'file','file_format_type'=>'multi','file_type'=>'images','file_uploader'=>'attachment','file_limit'=>0,'weight'=>$w++]);
            torresan_ensure_field($api, ['pod_id'=>$cid,'group'=>'dettagli_modello','name'=>'model_detail_title','label'=>'Titolo sezione dettagli','type'=>'text','weight'=>$w++]);
            torresan_ensure_field($api, ['pod_id'=>$cid,'group'=>'dettagli_modello','name'=>'model_detail_image','label'=>'Immagine dettaglio costruttivo','type'=>'file','file_format_type'=>'single','file_type'=>'images','file_uploader'=>'attachment','weight'=>$w++]);

            foreach (['camera_guests','camera_size','camera_beds','camera_bathroom','camera_features','camera_prezzo','camera_gallery','camera_hero_mobile'] as $obsolete) {
                torresan_delete_field_if_exists($api, $cid, $obsolete);
            }
        }

        /* ═══════════════ ARTIST CPT (esperienza) ═══════════════ */
        $ep = $api->load_pod(['name' => 'esperienza']);
        $eid = $ep ? ($ep['id'] ?? 0) : 0;
        if (! $eid) {
            $eid = $api->save_pod(['name'=>'esperienza','label'=>'Artists','type'=>'post_type','object'=>'esperienza','storage'=>'meta','create_extend'=>'extend']);
        }
        if ($eid) {
            torresan_ensure_group($api, $eid, 'dettagli_artista', 'Artist Details');
            $w = 0;
            torresan_ensure_field($api, ['pod_id'=>$eid,'group'=>'dettagli_artista','name'=>'artist_band','label'=>'Band / Progetto','type'=>'text','weight'=>$w++]);
            torresan_ensure_field($api, ['pod_id'=>$eid,'group'=>'dettagli_artista','name'=>'artist_quote','label'=>'Citazione (breve, sulla card)','type'=>'paragraph','weight'=>$w++]);
            torresan_ensure_field($api, ['pod_id'=>$eid,'group'=>'dettagli_artista','name'=>'artist_bio','label'=>'Biografia estesa (nel popup)','type'=>'paragraph','weight'=>$w++]);
            torresan_ensure_field($api, ['pod_id'=>$eid,'group'=>'dettagli_artista','name'=>'artist_model','label'=>'Modello associato','type'=>'text','weight'=>$w++]);

            foreach (['exp_gallery','exp_hero_mobile','exp_duration','exp_price','exp_max_guests','exp_highlights'] as $obsolete) {
                torresan_delete_field_if_exists($api, $eid, $obsolete);
            }
        }

        update_option('torresan_pods_v', $version);
    } catch (\Throwable $e) {
        error_log('[Alusonic Pods] Setup error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    }
}
add_action('admin_init', 'torresan_setup_pods');
