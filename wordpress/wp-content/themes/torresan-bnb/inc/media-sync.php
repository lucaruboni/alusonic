<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Standalone admin tool: propagate every NON-TEXT field from the English
 * version of each page / suite (camera) / experience (esperienza) / FAQ
 * (domanda_faq) into its already-existing Polylang translation for every
 * other language.
 *
 * "Non-text" = featured image, galleries, background images, static maps,
 * URLs, phone/email, prices, sizes, guest counts, menu order, and the
 * relationship/order fields (faq_pagina, experiences_order,
 * home_experiences_order — whose referenced IDs are translated so a
 * translated page points at the translated FAQs/experiences).
 *
 * Curated translated TEXT (title, content, excerpt and the known text
 * meta fields) is deliberately LEFT UNTOUCHED, so the client can re-run
 * this after changing photos or reordering the homepage experiences
 * WITHOUT overwriting the translations imported via translation-sync.php.
 *
 * Triggered manually from WP Admin → Tools → Sync Media/Struttura.
 * Safe to run repeatedly (idempotent).
 */

const TORRESAN_MEDIA_SYNC_POST_TYPES = ['page', 'camera', 'esperienza', 'domanda_faq'];

// Order/relationship meta whose stored IDs point at other posts and must be
// remapped to the same-language translation of each referenced post.
const TORRESAN_MEDIA_SYNC_RELATIONSHIP_KEYS = ['faq_pagina', 'experiences_order', 'home_experiences_order'];

// Text meta that holds curated translations — NEVER overwrite these from EN.
// Keep in sync with the translatable fields in translation-sync.php plus the
// free-form text fields seen in the content export (hero_heading, etc.).
const TORRESAN_MEDIA_SYNC_PRESERVE_TEXT = [
    // page — hero / sections / newsletter / info
    'hero_eyebrow', 'hero_subtitle', 'hero_heading', 'hero_cta_text',
    'intro_text', 'about_story_text', 'suites_intro_text', 'pool_cta_text',
    'experiences_intro_text',
    'hp_sa_title', 'hp_sa_text', 'hp_sa_cta_text',
    'hp_sb_eyebrow', 'hp_sb_text', 'hp_sb_cta_text',
    'hp_sc_eyebrow', 'hp_sc_text', 'hp_sc_cta_text',
    'newsletter_title', 'newsletter_text', 'newsletter_btn',
    'contatti_orari', 'indicazioni_stradali',
    // page — booking info block
    'book_min_nights', 'book_checkin_from', 'book_checkout_by',
    'book_pets', 'book_breakfast', 'book_house_rules', 'book_cancellation',
    // camera
    'camera_beds', 'camera_bathroom', 'camera_features',
    // esperienza
    'exp_duration', 'exp_highlights',
];

function torresan_media_sync_menu(): void
{
    add_management_page(
        __('Sync Media/Struttura', 'torresan-bnb'),
        __('Sync Media/Struttura', 'torresan-bnb'),
        'manage_options',
        'torresan-media-sync',
        'torresan_media_sync_page'
    );
}
add_action('admin_menu', 'torresan_media_sync_menu');

function torresan_media_sync_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $report = null;

    if (
        isset($_POST['torresan_media_sync_run']) &&
        check_admin_referer('torresan_media_sync', 'torresan_media_sync_nonce')
    ) {
        $report = torresan_media_sync_run();
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Sync Media/Struttura EN → Altre Lingue', 'torresan-bnb'); ?></h1>

        <p><?php esc_html_e('Copia dalla versione inglese di ogni Pagina, Suite, Esperienza e FAQ verso le rispettive traduzioni già esistenti nelle altre lingue SOLO le parti non testuali: immagine in evidenza, gallerie, immagini di sfondo, mappe, URL, telefono/email, prezzi, dimensioni, numero ospiti, ordine dei post e i collegamenti (FAQ della pagina, ordine delle esperienze in home). Gli ID dei collegamenti vengono tradotti, così ogni pagina punta ai contenuti nella lingua giusta.', 'torresan-bnb'); ?></p>
        <p><strong><?php esc_html_e('I testi tradotti NON vengono toccati.', 'torresan-bnb'); ?></strong> <?php esc_html_e('Puoi rilanciare questo strumento ogni volta che il cliente cambia foto o riordina le esperienze in home: le modifiche vengono propagate a tutte le lingue senza sovrascrivere le traduzioni.', 'torresan-bnb'); ?></p>
        <p><?php esc_html_e('Se una traduzione non esiste ancora, viene saltata e segnalata sotto (va creata a mano collegandola dalla pagina di modifica in Polylang).', 'torresan-bnb'); ?></p>

        <form method="post">
            <?php wp_nonce_field('torresan_media_sync', 'torresan_media_sync_nonce'); ?>
            <?php submit_button(__('Sincronizza media e struttura EN → altre lingue', 'torresan-bnb'), 'primary', 'torresan_media_sync_run'); ?>
        </form>

        <?php if ($report !== null) : ?>
            <h2><?php esc_html_e('Risultato', 'torresan-bnb'); ?></h2>
            <table class="widefat striped" style="max-width:900px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Tipo', 'torresan-bnb'); ?></th>
                        <th><?php esc_html_e('Originale (EN)', 'torresan-bnb'); ?></th>
                        <th><?php esc_html_e('Sincronizzate', 'torresan-bnb'); ?></th>
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
function torresan_media_sync_run(): array
{
    if (! function_exists('pll_languages_list') || ! function_exists('pll_get_post')) {
        return [];
    }

    $all_langs    = pll_languages_list(['fields' => 'slug']);
    $target_langs = array_values(array_diff($all_langs, ['en']));

    if (empty($target_langs)) {
        return [];
    }

    $report = [];

    foreach (TORRESAN_MEDIA_SYNC_POST_TYPES as $post_type) {
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

                torresan_media_sync_copy($en_post->ID, (int) $target_id, $lang);
                $row['updated'][] = $lang;
            }

            $report[] = $row;
        }
    }

    return $report;
}

/**
 * Copy featured image, menu order and every non-text meta field from the
 * English post to one translation, translating relationship IDs. Curated
 * translated text (title/content/excerpt + PRESERVE_TEXT meta) is left as-is.
 */
function torresan_media_sync_copy(int $en_id, int $target_id, string $lang): void
{
    $en_post = get_post($en_id);
    if (! $en_post) {
        return;
    }

    // Featured image.
    $thumb_id = get_post_thumbnail_id($en_id);
    if ($thumb_id) {
        set_post_thumbnail($target_id, $thumb_id);
    } else {
        delete_post_thumbnail($target_id);
    }

    // Post ordering (e.g. order of pages / experiences within their type).
    if ((int) $en_post->menu_order !== (int) get_post_field('menu_order', $target_id)) {
        wp_update_post([
            'ID'         => $target_id,
            'menu_order' => (int) $en_post->menu_order,
        ]);
    }

    // Non-text meta, key by key.
    $all_meta = get_post_meta($en_id);

    foreach ($all_meta as $key => $values) {
        // Skip WP/Polylang internal meta (featured image already handled).
        if (str_starts_with((string) $key, '_')) {
            continue;
        }
        // Never overwrite curated translated text.
        if (in_array($key, TORRESAN_MEDIA_SYNC_PRESERVE_TEXT, true)) {
            continue;
        }

        delete_post_meta($target_id, $key);

        $is_relationship = in_array($key, TORRESAN_MEDIA_SYNC_RELATIONSHIP_KEYS, true);

        foreach ($values as $raw_value) {
            $value = maybe_unserialize($raw_value);

            if ($is_relationship) {
                $value = torresan_media_sync_translate_ids($value, $lang);
            }

            add_post_meta($target_id, $key, $value, false);
        }
    }
}

/**
 * Remap post ID(s) to their same-language translation. Handles a single
 * numeric ID or an array of IDs (Pods relationship / order fields).
 *
 * @param mixed $value
 * @return mixed
 */
function torresan_media_sync_translate_ids($value, string $lang)
{
    if (is_array($value)) {
        return array_map(
            static fn($item) => torresan_media_sync_translate_ids($item, $lang),
            $value
        );
    }

    if (is_numeric($value) && function_exists('pll_get_post')) {
        $translated = pll_get_post((int) $value, $lang);
        return $translated ?: $value;
    }

    return $value;
}
