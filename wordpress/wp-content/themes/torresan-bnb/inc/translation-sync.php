<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * One-off admin tool: copy every field (title, content, excerpt, featured
 * image, all Pods/custom meta) from the English version of each page /
 * suite (camera) / experience (esperienza) / FAQ (domanda_faq) into its
 * already-existing Polylang translation for every other configured
 * language. Menus are intentionally left untouched (already set up).
 *
 * Relationship/pick fields that store references to OTHER posts
 * (faq_pagina, experiences_order, home_experiences_order) get their
 * referenced IDs translated too, so a translated page points at the
 * translated FAQs/experiences instead of the English ones.
 *
 * Triggered manually from WP Admin → Tools → Sync Traduzioni. Safe to run
 * more than once (idempotent: it always overwrites the target with
 * whatever is currently in the English source).
 */

const TORRESAN_I18N_SYNC_POST_TYPES = ['page', 'camera', 'esperienza', 'domanda_faq'];

/**
 * Force the custom post types to be translatable by Polylang so the import
 * below can create/link FAQ (domanda_faq) translations — the original seed
 * only registered 'camera' and 'esperienza'.
 *
 * Scoped to the admin only: the front end keeps whatever Polylang already
 * has saved, so this maintenance tool can never affect the live site.
 */
if (is_admin()) {
    add_filter('pll_get_post_types', static function ($post_types) {
        if (! is_array($post_types)) {
            return $post_types;
        }
        foreach (['camera', 'esperienza', 'domanda_faq'] as $type) {
            $post_types[$type] = $type;
        }
        return $post_types;
    }, 10, 1);
}

// Meta keys that reference other posts by ID and need translation.
const TORRESAN_I18N_RELATIONSHIP_KEYS = ['faq_pagina', 'experiences_order', 'home_experiences_order'];

// Internal/transient WP meta that should never be copied across posts.
const TORRESAN_I18N_META_BLACKLIST = [
    '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_old_date',
    '_wp_desired_post_slug', '_wp_trash_meta_status', '_wp_trash_meta_time',
    '_wp_trash_meta_comments_status',
];

function torresan_i18n_sync_menu(): void
{
    add_management_page(
        __('Sync Traduzioni', 'torresan-bnb'),
        __('Sync Traduzioni', 'torresan-bnb'),
        'manage_options',
        'torresan-i18n-sync',
        'torresan_i18n_sync_page'
    );
}
add_action('admin_menu', 'torresan_i18n_sync_menu');

function torresan_i18n_sync_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $report = null;
    $export = null;
    $import_report = null;

    if (
        isset($_POST['torresan_i18n_sync_run']) &&
        check_admin_referer('torresan_i18n_sync', 'torresan_i18n_sync_nonce')
    ) {
        $report = torresan_i18n_run_sync();
    }

    if (
        isset($_POST['torresan_i18n_import_run']) &&
        check_admin_referer('torresan_i18n_sync', 'torresan_i18n_sync_nonce')
    ) {
        $import_report = torresan_i18n_run_import();
    }

    if (
        isset($_POST['torresan_i18n_export_run']) &&
        check_admin_referer('torresan_i18n_sync', 'torresan_i18n_sync_nonce')
    ) {
        $export = torresan_i18n_export_en_content();
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Sync Traduzioni EN → Altre Lingue', 'torresan-bnb'); ?></h1>

        <h2><?php esc_html_e('1. Esporta i contenuti inglesi', 'torresan-bnb'); ?></h2>
        <p><?php esc_html_e('Genera un testo con tutto il contenuto inglese attuale (pagine, suite, esperienze, FAQ), da copiare e incollare per farlo tradurre.', 'torresan-bnb'); ?></p>
        <form method="post">
            <?php wp_nonce_field('torresan_i18n_sync', 'torresan_i18n_sync_nonce'); ?>
            <?php submit_button(__('Esporta contenuti EN', 'torresan-bnb'), 'secondary', 'torresan_i18n_export_run'); ?>
        </form>
        <?php if ($export !== null) : ?>
            <p><strong><?php esc_html_e('Seleziona tutto il testo qui sotto (clic dentro + Ctrl/Cmd+A + Ctrl/Cmd+C) e incollalo.', 'torresan-bnb'); ?></strong></p>
            <textarea readonly rows="25" style="width:100%;max-width:900px;font-family:monospace;font-size:12px;" onclick="this.select()"><?php echo esc_textarea($export); ?></textarea>
        <?php endif; ?>

        <hr style="margin:2rem 0;">

        <h2><?php esc_html_e('2. Importa le traduzioni (IT, DE, FR, ES)', 'torresan-bnb'); ?></h2>
        <p><?php esc_html_e('Per ogni pagina, suite, esperienza e FAQ: copia prima dalla versione inglese i campi non testuali (immagini, gallerie, collegamenti FAQ/esperienze), poi sovrascrive titolo, contenuto e campi di testo con la traduzione presente in inc/translations/{lingua}.php. L\'inglese non viene toccato. Idempotente: si può rilanciare.', 'torresan-bnb'); ?></p>
        <form method="post">
            <?php wp_nonce_field('torresan_i18n_sync', 'torresan_i18n_sync_nonce'); ?>
            <?php submit_button(__('Importa traduzioni nelle altre lingue', 'torresan-bnb'), 'primary', 'torresan_i18n_import_run'); ?>
        </form>

        <?php if ($import_report !== null) : ?>
            <h3><?php esc_html_e('Risultato import', 'torresan-bnb'); ?></h3>
            <table class="widefat striped" style="max-width:900px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Lingua', 'torresan-bnb'); ?></th>
                        <th><?php esc_html_e('Contenuto (EN)', 'torresan-bnb'); ?></th>
                        <th><?php esc_html_e('Esito', 'torresan-bnb'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($import_report as $row) : ?>
                        <tr>
                            <td><?php echo esc_html($row['lang']); ?></td>
                            <td><?php echo esc_html($row['title']); ?></td>
                            <td><?php echo esc_html($row['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <hr style="margin:2rem 0;">

        <h2><?php esc_html_e('3. Copia grezza EN → altre lingue (fallback)', 'torresan-bnb'); ?></h2>
        <p><?php esc_html_e('Copia titolo, contenuto, immagine in evidenza e tutti i campi Pods dalla versione inglese di ogni Pagina, Suite, Esperienza e FAQ verso le rispettive traduzioni già esistenti nelle altre lingue configurate in Polylang. I menu non vengono toccati. Usa questo solo se non vuoi aspettare le traduzioni vere: riempie tutto con il testo inglese as-is.', 'torresan-bnb'); ?></p>
        <p><strong><?php esc_html_e('Nota:', 'torresan-bnb'); ?></strong> <?php esc_html_e('sovrascrive il contenuto già presente nelle pagine tradotte. Se una traduzione non esiste ancora, viene saltata e segnalata sotto (va creata a mano collegandola dalla pagina di modifica).', 'torresan-bnb'); ?></p>

        <form method="post">
            <?php wp_nonce_field('torresan_i18n_sync', 'torresan_i18n_sync_nonce'); ?>
            <?php submit_button(__('Copia tutto da Inglese alle altre lingue (senza tradurre)', 'torresan-bnb'), 'primary', 'torresan_i18n_sync_run'); ?>
        </form>

        <?php if ($report !== null) : ?>
            <h2><?php esc_html_e('Risultato', 'torresan-bnb'); ?></h2>
            <table class="widefat striped" style="max-width:900px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Tipo', 'torresan-bnb'); ?></th>
                        <th><?php esc_html_e('Originale (EN)', 'torresan-bnb'); ?></th>
                        <th><?php esc_html_e('Aggiornate', 'torresan-bnb'); ?></th>
                        <th><?php esc_html_e('Mancanti (da creare a mano)', 'torresan-bnb'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report as $row) : ?>
                        <tr>
                            <td><?php echo esc_html($row['type']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_post_link($row['id'])); ?>">
                                    <?php echo esc_html($row['title']); ?>
                                </a>
                            </td>
                            <td><?php echo $row['updated'] ? esc_html(implode(', ', $row['updated'])) : '—'; ?></td>
                            <td><?php echo $row['missing'] ? esc_html(implode(', ', $row['missing'])) : '—'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * @return array<int, array{type:string, id:int, title:string, updated:string[], missing:string[]}>
 */
function torresan_i18n_run_sync(): array
{
    if (! function_exists('pll_languages_list') || ! function_exists('pll_get_post')) {
        return [];
    }

    $all_langs   = pll_languages_list(['fields' => 'slug']);
    $target_langs = array_values(array_diff($all_langs, ['en']));

    if (empty($target_langs)) {
        return [];
    }

    $report = [];

    foreach (TORRESAN_I18N_SYNC_POST_TYPES as $post_type) {
        $en_posts = get_posts([
            'post_type'        => $post_type,
            'post_status'      => ['publish', 'draft', 'private'],
            'posts_per_page'   => -1,
            'lang'             => 'en',
            'suppress_filters' => false,
        ]);

        foreach ($en_posts as $en_post) {
            $row = [
                'type'    => $post_type,
                'id'      => $en_post->ID,
                'title'   => $en_post->post_title,
                'updated' => [],
                'missing' => [],
            ];

            foreach ($target_langs as $lang) {
                $target_id = pll_get_post($en_post->ID, $lang);

                if (! $target_id) {
                    $row['missing'][] = $lang;
                    continue;
                }

                torresan_i18n_copy_post($en_post->ID, (int) $target_id, $lang);
                $row['updated'][] = $lang;
            }

            $report[] = $row;
        }
    }

    return $report;
}

function torresan_i18n_copy_post(int $en_id, int $target_id, string $target_lang): void
{
    $en_post = get_post($en_id);
    if (! $en_post) {
        return;
    }

    // Title, content, excerpt.
    wp_update_post([
        'ID'           => $target_id,
        'post_title'   => $en_post->post_title,
        'post_content' => $en_post->post_content,
        'post_excerpt' => $en_post->post_excerpt,
    ]);

    // All postmeta, key by key (a Pods multi/pick field can have several
    // rows sharing the same key, so we operate on the raw array form).
    $all_meta = get_post_meta($en_id);

    foreach ($all_meta as $key => $values) {
        if (in_array($key, TORRESAN_I18N_META_BLACKLIST, true)) {
            continue;
        }

        delete_post_meta($target_id, $key);

        $is_relationship = in_array($key, TORRESAN_I18N_RELATIONSHIP_KEYS, true);

        foreach ($values as $raw_value) {
            $value = maybe_unserialize($raw_value);

            if ($is_relationship && is_numeric($value)) {
                $translated = function_exists('pll_get_post') ? pll_get_post((int) $value, $target_lang) : 0;
                $value = $translated ?: $value;
            }

            add_post_meta($target_id, $key, $value, false);
        }
    }
}

// Languages with a translation file in inc/translations/{lang}.php.
const TORRESAN_I18N_IMPORT_LANGS = ['it', 'de', 'fr', 'es'];

/**
 * Import curated translations: for each translated post, first copy every
 * non-text field from the English original (images, galleries, translated
 * relationship IDs — via torresan_i18n_copy_post), then overwrite title,
 * content, excerpt and text meta with the curated translation.
 *
 * @return array<int, array{lang:string, title:string, status:string}>
 */
function torresan_i18n_run_import(): array
{
    if (! function_exists('pll_get_post')) {
        return [['lang' => '—', 'title' => 'Polylang non attivo', 'status' => 'errore']];
    }

    $report = [];

    foreach (TORRESAN_I18N_IMPORT_LANGS as $lang) {
        $file = get_template_directory() . "/inc/translations/{$lang}.php";

        if (! file_exists($file)) {
            $report[] = [
                'lang'   => $lang,
                'title'  => "inc/translations/{$lang}.php",
                'status' => 'file mancante — caricalo via FTP',
            ];
            continue;
        }

        $translations = include $file;

        if (! is_array($translations)) {
            $report[] = ['lang' => $lang, 'title' => "inc/translations/{$lang}.php", 'status' => 'file non valido'];
            continue;
        }

        foreach ($translations as $en_id => $tr) {
            $en_post = get_post($en_id);
            $label   = $en_post ? $en_post->post_title : "post #{$en_id}";

            if (! $en_post) {
                $report[] = ['lang' => $lang, 'title' => $label, 'status' => 'originale EN non trovato'];
                continue;
            }

            $target_id = pll_get_post($en_id, $lang);
            $created   = false;

            if (! $target_id) {
                $target_id = torresan_i18n_ensure_translation($en_id, $lang);
                $created   = (bool) $target_id;
            }

            if (! $target_id) {
                $report[] = ['lang' => $lang, 'title' => $label, 'status' => 'impossibile creare la traduzione (Polylang?)'];
                continue;
            }

            // Base: copy media/relationship/other meta from EN.
            torresan_i18n_copy_post($en_id, (int) $target_id, $lang);

            // Overlay: curated translated text.
            $update = ['ID' => $target_id];
            if (isset($tr['title'])) {
                $update['post_title'] = $tr['title'];
            }
            if (isset($tr['content'])) {
                $update['post_content'] = $tr['content'];
            }
            if (isset($tr['excerpt'])) {
                $update['post_excerpt'] = $tr['excerpt'];
            }
            if (count($update) > 1) {
                wp_update_post($update);
            }

            foreach (($tr['meta'] ?? []) as $meta_key => $meta_value) {
                update_post_meta($target_id, $meta_key, $meta_value);
            }

            $report[] = ['lang' => $lang, 'title' => $label, 'status' => $created ? 'creato e tradotto' : 'aggiornato'];
        }
    }

    return $report;
}

/**
 * Ensure a Polylang translation of $en_id exists for $lang, creating and
 * linking it if missing (following the theme's seed-cpt-i18n.php pattern).
 * The new post starts as a copy of the English one; the caller then
 * overwrites it with the curated translation. Returns the translation ID,
 * or 0 on failure.
 */
function torresan_i18n_ensure_translation(int $en_id, string $lang): int
{
    if (! function_exists('pll_set_post_language') || ! function_exists('pll_save_post_translations')) {
        return 0;
    }

    $existing = pll_get_post($en_id, $lang);
    if ($existing) {
        return (int) $existing;
    }

    $en_post = get_post($en_id);
    if (! $en_post) {
        return 0;
    }

    // Make sure the English original is tagged as EN before linking.
    if (! function_exists('pll_get_post_language') || ! pll_get_post_language($en_id)) {
        pll_set_post_language($en_id, 'en');
    }

    $new_id = wp_insert_post([
        'post_type'    => $en_post->post_type,
        'post_status'  => $en_post->post_status,
        'post_title'   => $en_post->post_title,
        'post_content' => $en_post->post_content,
        'post_excerpt' => $en_post->post_excerpt,
        'post_parent'  => $en_post->post_parent,
        'menu_order'   => $en_post->menu_order,
    ], true);

    if (is_wp_error($new_id) || ! $new_id) {
        return 0;
    }

    pll_set_post_language($new_id, $lang);

    // Link EN + any already-existing translations + the new one together.
    $translations = function_exists('pll_get_post_translations')
        ? pll_get_post_translations($en_id)
        : [];
    $translations['en']  = $en_id;
    $translations[$lang] = $new_id;
    pll_save_post_translations($translations);

    return (int) $new_id;
}

// Known text-type Pods fields worth translating, per post type. Kept explicit
// (rather than a pure heuristic) so nothing translatable is mislabeled as
// skippable — anything NOT in this list but still string-shaped is dumped
// too, under "Altri campi testuali", so nothing gets silently missed.
const TORRESAN_I18N_TRANSLATABLE_FIELDS = [
    'page' => [
        'hero_eyebrow', 'hero_subtitle', 'hero_cta_text',
        'about_story_text', 'suites_intro_text', 'pool_cta_text', 'intro_text',
        'hp_sa_title', 'hp_sa_text', 'hp_sa_cta_text',
        'hp_sb_eyebrow', 'hp_sb_text', 'hp_sb_cta_text',
        'hp_sc_eyebrow', 'hp_sc_text', 'hp_sc_cta_text',
        'newsletter_title', 'newsletter_text', 'newsletter_btn',
        'contatti_orari', 'indicazioni_stradali',
        'book_min_nights', 'book_checkin_from', 'book_checkout_by',
        'book_pets', 'book_breakfast', 'book_house_rules', 'book_cancellation',
    ],
    'camera' => ['camera_beds', 'camera_bathroom', 'camera_features'],
    'esperienza' => ['exp_duration', 'exp_highlights'],
    'domanda_faq' => [],
];

// Fields that look like text but should NOT be sent for translation
// (URLs, embed codes, raw data, IDs).
const TORRESAN_I18N_SKIP_FIELDS = [
    'hero_cta_url', 'pool_cta_url', 'hp_sa_cta_url', 'hp_sb_cta_url', 'hp_sc_cta_url',
    'contatti_telefono', 'contatti_email', 'contact_shortcode', 'address',
    'maps_embed', 'map_static_image', 'hero_bg_mobile', 'intro_image', 'intro_image_mobile',
    'camera_guests', 'camera_size', 'camera_prezzo', 'camera_gallery', 'camera_gallery_mobile', 'camera_hero_mobile',
    'exp_price', 'exp_max_guests', 'exp_gallery', 'exp_hero_mobile',
    'photo_gallery', 'gallery_photos', 'pool_gallery', 'pool_gallery_mobile',
    'hp_sa_gallery', 'hp_sa_gallery_mobile', 'hp_sb_bg', 'hp_sb_bg_mobile', 'hp_sc_gallery', 'hp_sc_gallery_mobile',
    'about_story_bg', 'about_carousel', 'faq_pagina', 'experiences_order', 'home_experiences_order',
];

function torresan_i18n_export_en_content(): string
{
    $out = [];
    $out[] = '# Contenuti EN — Torre San Bartolo';
    $out[] = '# Generato ' . current_time('mysql');
    $out[] = '';

    foreach (TORRESAN_I18N_SYNC_POST_TYPES as $post_type) {
        $en_posts = get_posts([
            'post_type'        => $post_type,
            'post_status'      => ['publish', 'draft', 'private'],
            'posts_per_page'   => -1,
            'lang'             => 'en',
            'orderby'          => 'menu_order title',
            'order'            => 'ASC',
            'suppress_filters' => false,
        ]);

        foreach ($en_posts as $post) {
            $out[] = str_repeat('=', 70);
            $out[] = "[{$post_type} #{$post->ID}] {$post->post_title}";
            $out[] = str_repeat('=', 70);

            if (trim((string) $post->post_content)) {
                $out[] = '--- content ---';
                $out[] = trim((string) $post->post_content);
                $out[] = '';
            }
            if (trim((string) $post->post_excerpt)) {
                $out[] = '--- excerpt ---';
                $out[] = trim((string) $post->post_excerpt);
                $out[] = '';
            }

            $known = TORRESAN_I18N_TRANSLATABLE_FIELDS[$post_type] ?? [];
            $all_meta = get_post_meta($post->ID);
            $shown = [];

            foreach ($known as $key) {
                $val = $all_meta[$key][0] ?? '';
                $val = trim((string) maybe_unserialize($val));
                if ($val === '') {
                    continue;
                }
                $out[] = "--- {$key} ---";
                $out[] = $val;
                $out[] = '';
                $shown[$key] = true;
            }

            $others = [];
            foreach ($all_meta as $key => $values) {
                if (isset($shown[$key]) || in_array($key, TORRESAN_I18N_SKIP_FIELDS, true)) {
                    continue;
                }
                if (str_starts_with((string) $key, '_')) {
                    continue;
                }
                $val = trim((string) maybe_unserialize($values[0] ?? ''));
                if ($val === '' || is_numeric($val)) {
                    continue;
                }
                $others[$key] = $val;
            }

            if (! empty($others)) {
                $out[] = '--- Altri campi testuali (verifica se da tradurre) ---';
                foreach ($others as $key => $val) {
                    $out[] = "[{$key}] {$val}";
                }
                $out[] = '';
            }
        }
    }

    return implode("\n", $out);
}
