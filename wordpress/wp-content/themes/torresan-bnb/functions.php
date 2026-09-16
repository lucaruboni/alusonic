<?php

if (! defined('ABSPATH')) {
    exit;
}

/* Hide admin bar on frontend */
add_filter('show_admin_bar', '__return_false');

/*
 * Force Classic Editor for ALL post types that use Pods custom fields.
 * Gutenberg's meta-box compatibility layer does not pre-populate Pods field
 * values when opening a post for editing, making fields appear empty even
 * though the data is saved. We disable the block editor at every hook level
 * to be safe, regardless of whether the Classic Editor plugin is installed.
 */
$_torresan_pods_post_types = ['page', 'camera', 'domanda_faq', 'esperienza'];

add_filter('use_block_editor_for_post_type', function (bool $use, string $post_type) use ($_torresan_pods_post_types): bool {
    if (in_array($post_type, $_torresan_pods_post_types, true)) {
        return false;
    }
    return $use;
}, 10, 2);

// Per-post level (stronger than post_type filter)
add_filter('use_block_editor_for_post', function (bool $use, $post) use ($_torresan_pods_post_types): bool {
    $type = is_object($post) ? $post->post_type : get_post_type((int) $post);
    if (in_array($type, $_torresan_pods_post_types, true)) {
        return false;
    }
    return $use;
}, 10, 2);

// Gutenberg plugin-specific hooks (active even without the block editor UI)
add_filter('gutenberg_can_edit_post_type', function (bool $use, string $post_type) use ($_torresan_pods_post_types): bool {
    if (in_array($post_type, $_torresan_pods_post_types, true)) {
        return false;
    }
    return $use;
}, 10, 2);

add_filter('gutenberg_can_edit_post', function (bool $use, $post) use ($_torresan_pods_post_types): bool {
    $type = is_object($post) ? $post->post_type : get_post_type((int) $post);
    if (in_array($type, $_torresan_pods_post_types, true)) {
        return false;
    }
    return $use;
}, 10, 2);

/**
 * Ricarica le traduzioni del tema con il locale definitivo.
 *
 * load_theme_textdomain() gira su 'after_setup_theme', quando la lingua della
 * richiesta non è ancora decisa: determine_locale() restituisce il locale del
 * sito (en_GB) e WordPress cerca torresan-bnb-en_GB.mo, che non esiste. Non
 * carica nulla e, quando Polylang poco dopo imposta it_IT, niente riprova:
 * risultato, su /it/ eyebrow, titoli di sezione e CTA restavano in inglese.
 *
 * Qui il dominio viene scaricato e ricaricato una volta che la lingua è nota.
 */
function torresan_bnb_reload_textdomain(): void
{
    $locale = determine_locale();

    // Per l'inglese non serve nulla: i msgid nel codice sono già in inglese.
    if (str_starts_with($locale, 'en')) {
        return;
    }

    $mofile = get_template_directory() . '/languages/torresan-bnb-' . $locale . '.mo';

    if (! is_readable($mofile)) {
        return;
    }

    // load_textdomain() con percorso esplicito invece di load_theme_textdomain():
    // quest'ultima, su questa installazione, restituiva true senza però rendere
    // disponibili le traduzioni (verificato: __() continuava a restituire il
    // msgid). Passando direttamente il file il dominio si carica davvero.
    unload_textdomain('torresan-bnb');
    load_textdomain('torresan-bnb', $mofile);
}

// Polylang segnala qui che la lingua della richiesta è stata determinata.
add_action('pll_language_defined', 'torresan_bnb_reload_textdomain');

// Rete di sicurezza: se Polylang non è attivo o l'action cambia nome, si
// ricarica comunque prima che parta il rendering del template.
add_action('template_redirect', function (): void {
    if (! is_textdomain_loaded('torresan-bnb')) {
        torresan_bnb_reload_textdomain();
    }
}, 0);

function torresan_bnb_setup(): void
{
    load_theme_textdomain('torresan-bnb', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['height' => 80, 'width' => 250, 'flex-height' => true, 'flex-width' => true]);
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary'      => __('Main Menu', 'torresan-bnb'),
        'footer'       => __('Footer Menu', 'torresan-bnb'),
        'footer-legal' => __('Footer — Legal links', 'torresan-bnb'),
    ]);

    add_image_size('hero-bg', 1920, 1080, true);
    add_image_size('section-card', 800, 600, true);
    add_image_size('gallery-thumb', 600, 450, true);
    // Uncropped, tall — for transparent instrument cutouts (product shots),
    // never hard-cropped like hero-bg so the whole instrument stays visible.
    add_image_size('product-shot', 1000, 1600, false);
}
add_action('after_setup_theme', 'torresan_bnb_setup');

/**
 * Allow using standard WP tags on Media Library images.
 * Editors can tag service assets (logo, icons, technical) and keep them
 * out of the public gallery stream.
 */
function torresan_bnb_enable_media_tags(): void
{
    register_taxonomy_for_object_type('post_tag', 'attachment');
}
add_action('init', 'torresan_bnb_enable_media_tags');

/**
 * Show guidance next to Media tags field for gallery exclusion.
 */
function torresan_bnb_media_tag_help(array $form_fields, WP_Post $post): array
{
    if (! isset($form_fields['post_tag'])) {
        return $form_fields;
    }

    $hint = 'Per non mostrare questa immagine nella pagina Gallery, aggiungi il tag <strong>no-gallery</strong>.';

    if (! empty($form_fields['post_tag']['helps'])) {
        $form_fields['post_tag']['helps'] .= ' ' . $hint;
    } else {
        $form_fields['post_tag']['helps'] = $hint;
    }

    return $form_fields;
}
add_filter('attachment_fields_to_edit', 'torresan_bnb_media_tag_help', 10, 2);

/* ─── Enqueue Assets ─── */

function torresan_bnb_assets(): void
{
    wp_enqueue_style('torresan-bnb-fonts', 'https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&display=swap', [], null);
    $css_path = get_template_directory() . '/assets/dist/main.min.css';
    $js_path  = get_template_directory() . '/assets/dist/main.min.js';
    wp_enqueue_style('torresan-bnb-main', get_template_directory_uri() . '/assets/dist/main.min.css', ['torresan-bnb-fonts'], file_exists($css_path) ? (string) filemtime($css_path) : '5.0.0');
    wp_enqueue_script('torresan-bnb-main', get_template_directory_uri() . '/assets/dist/main.min.js', ['jquery'], file_exists($js_path) ? (string) filemtime($js_path) : '5.0.0', true);

    // Language switcher styles (Polylang)
    $lang_css = '
.lang-switcher { display:inline-flex; align-items:center; }
.lang-switcher-list { list-style:none; margin:0; padding:0; display:flex; gap:.35rem; align-items:center; }
.lang-switcher-list .lang-item { font-size:.65rem; letter-spacing:.1em; font-family:var(--font-body,"Montserrat",sans-serif); }
.lang-switcher-list .lang-item a,
.lang-switcher-list .lang-item span { display:inline-block; padding:.15rem .3rem; text-decoration:none; color:inherit; opacity:.55; transition:opacity .2s; }
.lang-switcher-list .lang-item a:hover { opacity:1; }
.lang-switcher-list .lang-item--active span { opacity:1; font-weight:600; border-bottom:1px solid currentColor; }
.lang-switcher-list .lang-item + .lang-item::before { content:"|"; opacity:.3; margin-right:.35rem; font-size:.55rem; }
/* Header switcher */
.lang-switcher--header { margin-right:.25rem; }
/* Header switcher — vive in .header-tools, visibile da tablet in su.
   Prima era nascosto e mostrato solo dentro il pannello mobile scorrevole
   (.site-nav.is-open), che non esiste più: su telefono il cambio lingua sta
   nel mega menu. */
.lang-switcher--nav { display:inline-flex; }
/* Mega menu switcher */
.lang-switcher--mega { margin-top:1.75rem; font-size:1rem; }
.lang-switcher--mega .lang-item { font-size:.8rem; }
/* Footer switcher */
.lang-switcher--footer { margin:0 auto; }
';
    wp_add_inline_style('torresan-bnb-main', $lang_css);
}
add_action('wp_enqueue_scripts', 'torresan_bnb_assets');

/**
 * Aggancia il filemtime del file all'URL di un allegato.
 *
 * I ritagli hero dei modelli vengono sostituiti sul posto — stesso nome file,
 * contenuto nuovo (vedi scripts/refresh-hero-cutouts.php, necessario perché
 * alu_import() deduplica per nome). L'URL quindi non cambia mai e i browser
 * continuano a servire i byte vecchi dalla cache anche dopo l'aggiornamento.
 * Legando la querystring al mtime, ogni sostituzione genera un URL diverso e
 * l'immagine nuova arriva da sola, senza chiedere un hard-refresh.
 */
function torresan_attachment_mtime_version(string $url): string
{
    if (! $url || strpos($url, '?') !== false) {
        return $url;
    }

    $uploads = wp_get_upload_dir();
    if (empty($uploads['baseurl']) || strpos($url, $uploads['baseurl']) !== 0) {
        return $url;
    }

    $path  = $uploads['basedir'] . substr($url, strlen($uploads['baseurl']));
    $mtime = is_file($path) ? filemtime($path) : false;

    return $mtime ? add_query_arg('v', $mtime, $url) : $url;
}

/**
 * Applica il versioning solo in front-end: in admin gli URL degli allegati
 * vengono anche riletti per risalire all'ID (attachment_url_to_postid), e una
 * querystring in più lì darebbe solo fastidio.
 */
function torresan_bnb_version_attachment_urls(): void
{
    if (is_admin()) {
        return;
    }

    add_filter('wp_get_attachment_url', 'torresan_attachment_mtime_version', 20);

    add_filter('wp_get_attachment_image_src', static function ($image) {
        if (is_array($image) && ! empty($image[0])) {
            $image[0] = torresan_attachment_mtime_version((string) $image[0]);
        }
        return $image;
    }, 20);

    add_filter('wp_calculate_image_srcset', static function ($sources) {
        if (is_array($sources)) {
            foreach ($sources as $w => $source) {
                if (! empty($source['url'])) {
                    $sources[$w]['url'] = torresan_attachment_mtime_version((string) $source['url']);
                }
            }
        }
        return $sources;
    }, 20);
}
add_action('init', 'torresan_bnb_version_attachment_urls');

function torresan_bnb_hide_contact_menu_items(array $items, stdClass $args): array
{
    return array_values(array_filter($items, static function ($item): bool {
        if (($item->object ?? '') !== 'page') {
            return true;
        }

        $page_id = absint($item->object_id ?? 0);
        if (! $page_id) {
            return true;
        }

        $template = get_page_template_slug($page_id);
        $slug     = get_post_field('post_name', $page_id);

        return $template !== 'page-contact.php' && ! in_array($slug, ['contact', 'contatti', 'kontakt', 'contacto'], true);
    }));
}
add_filter('wp_nav_menu_objects', 'torresan_bnb_hide_contact_menu_items', 10, 2);

function torresan_bnb_admin_assets(string $hook): void
{
    $screens = ['appearance_page_torresan-bnb-settings', 'post.php', 'post-new.php'];

    if (! in_array($hook, $screens, true)) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('torresan-bnb-admin', get_template_directory_uri() . '/assets/js/admin.js', ['jquery'], '2.0.0', true);

    // Keep all Pods field groups visible in page editor.
    // Previous slug-based filtering could hide fields unexpectedly.
}
add_action('admin_enqueue_scripts', 'torresan_bnb_admin_assets');


function torresan_bnb_security_headers(): void
{
    if (is_admin()) {
        return;
    }

    // X-XSS-Protection deliberately omitted: deprecated by all major browsers since 2019
    // and can introduce SSRF-style issues in legacy IE. Rely on CSP instead (OWASP A03).
    header('Cross-Origin-Resource-Policy: same-origin');
    header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
}
add_action('send_headers', 'torresan_bnb_security_headers');

function torresan_bnb_remove_version(): string
{
    return '';
}
add_filter('the_generator', 'torresan_bnb_remove_version');

add_filter('xmlrpc_enabled', '__return_false');

// ─── Head cleanup: remove information-leaking WordPress tags (OWASP A05) ───
add_action('init', function (): void {
    remove_action('wp_head', 'rsd_link');                              // Really Simple Discovery
    remove_action('wp_head', 'wlwmanifest_link');                     // Windows Live Writer manifest
    remove_action('wp_head', 'wp_generator');                         // WP version meta tag
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);             // ?p= shortlinks
    remove_action('wp_head', 'rest_output_link_wp_head', 10);         // REST API Link header
    remove_action('template_redirect', 'rest_output_link_header', 11);// REST Link response header
    remove_action('wp_head', 'feed_links', 2);                        // RSS feed links
    remove_action('wp_head', 'feed_links_extra', 3);                  // Extra feed links
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);  // prev/next rel
});

// Remove ?ver= fingerprint from enqueued script and style URLs (OWASP A05)
// — except our own theme bundle, whose ?ver is a filemtime cache-buster
// (not a software version number), so keeping it doesn't leak anything and
// lets browsers pick up new CSS/JS immediately after each deploy.
add_filter('script_loader_src', 'torresan_bnb_strip_asset_ver', 15, 2);
add_filter('style_loader_src', 'torresan_bnb_strip_asset_ver', 15, 2);
function torresan_bnb_strip_asset_ver(string $src, string $handle): string
{
    if ($handle === 'torresan-bnb-main') {
        return $src;
    }
    return $src ? remove_query_arg('ver', $src) : $src;
}

// Disable emoji (removes wp.emoji DNS prefetch + tracking pixel) (OWASP A05)
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
add_filter('tiny_mce_plugins', function (array $p): array {
    return array_diff($p, ['wpemoji']);
});
add_filter('wp_resource_hints', function (array $hints, string $rel): array {
    if ('dns-prefetch' === $rel) {
        $hints = array_filter($hints, fn($h) => ! isset($h['href']) || false === strpos($h['href'], 's.w.org'));
    }
    return $hints;
}, 10, 2);

// Disable pingbacks (can be abused for DDoS amplification) (OWASP A04)
add_filter('pre_ping', function (array &$links): void { $links = []; });
add_filter('xmlrpc_methods', function (array $methods): array {
    unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
    return $methods;
});

function torresan_bnb_disable_user_rest_enum($result, $server, $request)
{
    $route = $request->get_route();

    if (! is_user_logged_in() && strpos($route, '/wp/v2/users') === 0) {
        return new WP_Error('forbidden_users', __('User listing disabled.', 'torresan-bnb'), ['status' => 403]);
    }

    return $result;
}
add_filter('rest_pre_dispatch', 'torresan_bnb_disable_user_rest_enum', 10, 3);

function torresan_bnb_block_author_scan(): void
{
    if (is_author() && ! is_admin()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}
add_action('template_redirect', 'torresan_bnb_block_author_scan');

function torresan_bnb_add_nofollow_to_external_links(string $content): string
{
    return preg_replace_callback(
        '/<a\s[^>]*href="([^"]+)"[^>]*>/i',
        static function (array $matches): string {
            $tag = $matches[0];
            $url = $matches[1];

            if (str_starts_with($url, home_url('/')) || str_starts_with($url, '/')) {
                return $tag;
            }

            if (stripos($tag, 'rel=') === false) {
                return rtrim($tag, '>') . ' rel="noopener noreferrer nofollow">';
            }

            return $tag;
        },
        $content
    ) ?? $content;
}
add_filter('the_content', 'torresan_bnb_add_nofollow_to_external_links', 20);

/* ─── Helpers ─── */

function torresan_bnb_img_url($attachment_id, string $size = 'full'): string
{
    if (empty($attachment_id)) {
        return '';
    }

    if (is_numeric($attachment_id)) {
        $url = wp_get_attachment_image_url((int) $attachment_id, $size);
        return $url ? $url : '';
    }

    return esc_url($attachment_id);
}

/**
 * Get a Pods object for the given post, or null if Pods is unavailable.
 */
function torresan_pod(?int $post_id = null): ?object
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }

    if (! $post_id || ! function_exists('pods')) {
        return null;
    }

    $type = get_post_type($post_id);

    if (! $type) {
        return null;
    }

    try {
        $pod = pods($type, $post_id);
        return ($pod && $pod->exists()) ? $pod : null;
    } catch (\Exception $e) {
        return null;
    }
}

/**
 * Retrieve a simple text/paragraph Pods field value.
 */
function torresan_field(string $name, ?int $post_id = null): string
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }

    $pod = torresan_pod($post_id);

    if ($pod) {
        $val = $pod->field($name);
        return is_string($val) ? $val : (is_numeric($val) ? (string) $val : '');
    }

    return (string) get_post_meta($post_id, $name, true);
}

/**
 * Build a `style` attribute string exposing --{$prefix}-bg-desktop / --{$prefix}-bg-mobile
 * custom properties, consumed by the matching SCSS rule to swap background images
 * at the tablet breakpoint. Returns '' if there is no desktop image at all.
 */
function torresan_bg_vars_attr(string $prefix, string $desktop_url, string $mobile_url = ''): string
{
    if (! $desktop_url) {
        return '';
    }

    $mobile_url = $mobile_url ?: $desktop_url;

    $vars = sprintf(
        "--%s-bg-desktop:url('%s');--%s-bg-mobile:url('%s')",
        $prefix,
        esc_url($desktop_url),
        $prefix,
        esc_url($mobile_url)
    );

    return ' style="' . esc_attr($vars) . '"';
}

/**
 * Style attribute for the page/home hero background, with mobile fallback
 * to the `hero_bg_mobile` Pods field (falls back to the desktop image if unset).
 */
/**
 * Class list for a .page-hero section.
 *
 * Adds `has-bg` when the page actually has a hero photo behind it. The dark
 * scrim (.page-hero-overlay) is only justified over a photo: on the light
 * theme a hero without an image would otherwise render as a grey band across
 * an otherwise white page, so the stylesheet keys off this class to drop the
 * scrim and flip the text to dark instead.
 */
function torresan_hero_class(?int $post_id = null, string $mobile_field = 'hero_bg_mobile'): string
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }

    $has_bg = get_the_post_thumbnail_url($post_id, 'hero-bg')
        || torresan_image_url($mobile_field, 'hero-bg', $post_id);

    return $has_bg ? 'page-hero has-bg' : 'page-hero';
}

function torresan_hero_style_attr(?int $post_id = null, string $mobile_field = 'hero_bg_mobile'): string
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }

    $desktop = get_the_post_thumbnail_url($post_id, 'hero-bg') ?: '';
    $mobile  = torresan_image_url($mobile_field, 'hero-bg', $post_id);

    return torresan_bg_vars_attr('hero', $desktop, $mobile);
}

/**
 * Render a <picture> element for a Pods image with a distinct mobile crop,
 * falling back to the desktop image when no mobile image is set.
 */
function torresan_picture(string $desktop_url, string $mobile_url, string $alt = '', array $attrs = []): void
{
    if (! $desktop_url) {
        return;
    }

    $mobile_url = $mobile_url ?: $desktop_url;

    $attr_str = '';
    foreach ($attrs as $k => $v) {
        $attr_str .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
    }
    ?>
    <picture>
        <?php if ($mobile_url !== $desktop_url) : ?>
        <source media="(max-width: 900px)" srcset="<?php echo esc_url($mobile_url); ?>">
        <?php endif; ?>
        <img src="<?php echo esc_url($desktop_url); ?>" alt="<?php echo esc_attr($alt); ?>"<?php echo $attr_str; ?>>
    </picture>
    <?php
}

/**
 * Get the URL of a single-image Pods file field.
 */
function torresan_image_url(string $name, string $size = 'full', ?int $post_id = null): string
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }

    $pod = torresan_pod($post_id);

    if ($pod) {
        $val = $pod->field($name);

        if (is_array($val) && ! empty($val['ID'])) {
            return wp_get_attachment_image_url((int) $val['ID'], $size) ?: '';
        }

        if (is_numeric($val) && (int) $val > 0) {
            return wp_get_attachment_image_url((int) $val, $size) ?: '';
        }
        // Fall through to raw-meta fallback when Pods field was saved outside Pods API
    }

    $att_id = (int) get_post_meta($post_id, $name, true);
    return $att_id ? (wp_get_attachment_image_url($att_id, $size) ?: '') : '';
}

/**
 * Get the raw URL of a single-file Pods file field, whatever the mime type
 * (video, audio…) — unlike torresan_image_url() this doesn't go through an
 * image-size crop, since wp_get_attachment_image_url() only works for images.
 */
function torresan_file_url(string $name, ?int $post_id = null): string
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }

    $pod = torresan_pod($post_id);

    if ($pod) {
        $val = $pod->field($name);

        if (is_array($val) && ! empty($val['ID'])) {
            return wp_get_attachment_url((int) $val['ID']) ?: '';
        }

        if (is_numeric($val) && (int) $val > 0) {
            return wp_get_attachment_url((int) $val) ?: '';
        }
    }

    $att_id = (int) get_post_meta($post_id, $name, true);
    return $att_id ? (wp_get_attachment_url($att_id) ?: '') : '';
}

/**
 * Get an array of attachment IDs from a multi-file Pods field.
 *
 * @return int[]
 */
function torresan_gallery_ids(string $name, ?int $post_id = null): array
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }

    $pod = torresan_pod($post_id);

    if ($pod) {
        $val = $pod->field($name);

        if (is_array($val)) {
            /* Single file returned as assoc array */
            if (! empty($val['ID'])) {
                return [(int) $val['ID']];
            }

            /* Multi file: array of assoc arrays */
            $ids = array_filter(array_map(static function ($item) {
                if (is_array($item) && ! empty($item['ID'])) {
                    return (int) $item['ID'];
                }
                if (is_numeric($item)) {
                    return (int) $item;
                }
                return 0;
            }, $val));

            if (! empty($ids)) {
                return $ids;
            }
        }

        /* Comma-separated fallback */
        if (is_string($val) && $val !== '') {
            return array_map('intval', explode(',', $val));
        }
        // Fall through to raw-meta fallback when Pods field was saved outside Pods API
    }

    $raw = get_post_meta($post_id, $name, true);

    if (is_array($raw)) {
        return array_map('intval', $raw);
    }

    if (is_string($raw) && $raw !== '') {
        return array_map('intval', explode(',', $raw));
    }

    return [];
}

/**
 * Get image attachment IDs directly from the WordPress Media Library.
 *
 * @return int[]
 */
function torresan_media_library_image_ids(int $limit = 200): array
{
    $query_args = [
        'post_type'              => 'attachment',
        'post_mime_type'         => 'image',
        'post_status'            => 'inherit',
        'posts_per_page'         => max(1, $limit),
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'fields'                 => 'ids',
        'suppress_filters'       => true,
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ];

    $excluded_tag_slugs = apply_filters('torresan_gallery_excluded_media_tags', ['no-gallery']);
    $excluded_tag_slugs = array_values(array_filter(array_map('sanitize_title', (array) $excluded_tag_slugs)));

    if (! empty($excluded_tag_slugs) && taxonomy_exists('post_tag')) {
        $query_args['tax_query'] = [[
            'taxonomy' => 'post_tag',
            'field'    => 'slug',
            'terms'    => $excluded_tag_slugs,
            'operator' => 'NOT IN',
        ]];
    }

    $ids = get_posts($query_args);

    return array_values(array_unique(array_filter(array_map('intval', $ids))));
}

/**
 * Get ordered related post IDs from a Pods relationship/pick field.
 *
 * @return int[]
 */
function torresan_related_post_ids(string $field_name, ?int $post_id = null): array
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }
    if (! $post_id) {
        return [];
    }

    $ids = [];
    $push_id = static function ($value) use (&$ids): void {
        if (! is_numeric($value)) {
            return;
        }

        $id = (int) $value;
        if ($id > 0 && ! in_array($id, $ids, true)) {
            $ids[] = $id;
        }
    };

    $collect = null;
    $collect = static function ($value) use (&$collect, $push_id): void {
        if (is_numeric($value)) {
            $push_id($value);
            return;
        }

        if (is_array($value)) {
            if (isset($value['ID'])) {
                $push_id($value['ID']);
            }
            if (isset($value['id'])) {
                $push_id($value['id']);
            }
            if (isset($value['post_id'])) {
                $push_id($value['post_id']);
            }

            foreach ($value as $item) {
                $collect($item);
            }
            return;
        }

        if (is_object($value)) {
            if (isset($value->ID)) {
                $push_id($value->ID);
            }
            if (isset($value->id)) {
                $push_id($value->id);
            }
            if (isset($value->post_id)) {
                $push_id($value->post_id);
            }
            return;
        }

        if (is_string($value) && $value !== '') {
            $decoded = maybe_unserialize($value);
            if ($decoded !== $value) {
                $collect($decoded);
            }

            $json = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $collect($json);
            }

            if (str_contains($value, ',')) {
                foreach (array_map('trim', explode(',', $value)) as $part) {
                    $push_id($part);
                }
            }
        }
    };

    foreach ((array) get_post_meta($post_id, $field_name) as $raw) {
        $collect($raw);
    }

    if (empty($ids)) {
        $pod = torresan_pod($post_id);
        if ($pod) {
            $collect($pod->field($field_name));
        }
    }

    return $ids;
}

/* ─── Includes ─── */

require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/pods-fields.php';
require_once get_template_directory() . '/inc/seed-pages.php';
// NOTA: seed-translations.php, seed-cpt-i18n.php, translation-sync.php e
// inc/translations/*.php sono stati rimossi. Contenevano le traduzioni del
// vecchio sito Torre San Bartolo indicizzate per ID di post: su questa
// installazione quegli ID appartengono ora ad allegati Alusonic e — peggio —
// agli oggetti di configurazione Pods (il pod "Models" e i suoi campi).
// L'import, che partiva da solo su admin_init, avrebbe sovrascritto lo schema
// Pods con testi da hotel. Il multilingua ora si configura con
// scripts/i18n-setup.php (solo EN + IT).
require_once get_template_directory() . '/inc/site-settings.php';
require_once get_template_directory() . '/inc/media-sync.php';
require_once get_template_directory() . '/inc/admin-pods-visibility.php';

/* ─── Register custom CPTs with Polylang ─── */
add_filter('pll_get_post_types', function (array $types, bool $is_settings): array {
    $types['camera']      = 'camera';
    $types['esperienza']  = 'esperienza';
    $types['domanda_faq'] = 'domanda_faq';
    return $types;
}, 10, 2);

/*
 * Polylang: in admin post-list tables default to the primary language so
 * that editors see only the main-language items (not all translations mixed
 * together).  The language switcher bar in the list still allows choosing
 * another language or "All languages".
 */
add_action('pre_get_posts', function (WP_Query $q): void {
    if (! is_admin() || ! $q->is_main_query()) {
        return;
    }
    if (! function_exists('pll_default_language')) {
        return;
    }
    $pt = $q->get('post_type') ?: ($GLOBALS['typenow'] ?? '');
    // Note: domanda_faq is intentionally excluded — FAQ posts may lack a language
    // assignment on existing installs; let users see all of them and assign manually.
    $managed = ['page', 'camera', 'esperienza'];
    if (! in_array($pt, $managed, true)) {
        return;
    }
    // Only apply when no language is already chosen by the user
    if (! isset($_GET['lang'])) { // phpcs:ignore WordPress.Security.NonceVerification
        $q->set('lang', pll_default_language());
    }
});

/*
 * Polylang: register nav-menu locations so each language gets its own menu
 * assignment. Polylang handles the per-language menu switching automatically
 * once the locations are declared with 'lang' support.
 */
add_filter('pll_get_nav_menus_locations', function (array $locations): array {
    // Tell Polylang to synchronise all registered menu locations per language
    foreach (array_keys(get_registered_nav_menus()) as $location) {
        $locations[] = $location;
    }
    return array_unique($locations);
});

/*
 * Conditional Pods meta-box visibility.
 * Runs at priority 99 so Pods has already registered all meta boxes.
 * Walks $wp_meta_boxes directly to find and remove groups that do not
 * belong to the page being edited, regardless of the exact ID format
 * Pods uses internally.
 */
add_action('add_meta_boxes', function (): void {
    $screen = get_current_screen();
    if (! $screen || $screen->id !== 'page') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification
    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    if (! $post_id) {
        return;
    }
    $post = get_post($post_id);
    if (! $post) {
        return;
    }

    $template = get_post_meta($post->ID, '_wp_page_template', true);
    $is_front  = (string) $post->ID === (string) get_option('page_on_front');

    $map = [
        'front-page.php'        => ['impostazioni_hero', 'home_stats', 'home_materials', 'home_models', 'home_instagram', 'home_cta'],
        'page-about.php'        => ['impostazioni_hero', 'pagina_about'],
        'page-models.php'       => ['impostazioni_hero'],
        'page-faq.php'          => ['impostazioni_hero', 'domande_frequenti'],
        'page-contact.php'      => ['impostazioni_hero', 'informazioni_contatto'],
    ];

    if ($is_front && ! isset($map[$template])) {
        $template = 'front-page.php';
    }

    $allowed = $map[$template] ?? ['impostazioni_hero'];

    // Group slug → human-readable label substrings to match against meta box title
    $group_labels = [
        'impostazioni_hero'     => 'Hero',
        'pagina_about'          => 'Chi Siamo',
        'home_stats'            => 'Statistiche',
        'home_materials'        => 'Materiali',
        'home_models'           => 'Modelli',
        'home_instagram'        => 'Instagram',
        'home_cta'              => 'CTA finale',
        'informazioni_contatto' => 'Contatti',
        'domande_frequenti'     => 'Frequenti',
    ];

    global $wp_meta_boxes;
    if (empty($wp_meta_boxes['page'])) {
        return;
    }

    foreach ($wp_meta_boxes['page'] as $context => $priorities) {
        foreach ($priorities as $priority => $boxes) {
            foreach ($boxes as $id => $box) {
                if (strpos((string) $id, 'pods-meta-') !== 0) {
                    continue;
                }
                // Determine which group this box belongs to:
                // try name-based match (pods-meta-page-{group})
                $matched_group = null;
                foreach ($group_labels as $group => $label) {
                    if (
                        strpos((string) $id, str_replace('_', '-', $group)) !== false ||
                        strpos((string) $id, $group) !== false
                    ) {
                        $matched_group = $group;
                        break;
                    }
                }
                // Fallback: match by box title substring
                if (! $matched_group && ! empty($box['title'])) {
                    foreach ($group_labels as $group => $label) {
                        if (stripos((string) $box['title'], $label) !== false) {
                            $matched_group = $group;
                            break;
                        }
                    }
                }
                if ($matched_group && ! in_array($matched_group, $allowed, true)) {
                    remove_meta_box($id, 'page', $context);
                }
            }
        }
    }
}, 99);

/* ─── FAQ & Rendering Helpers ─── */

/**
 * Get related FAQ post IDs from the relationship field.
 *
 * @return int[]
 */
function torresan_get_faq(?int $post_id = null): array
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }
    if (! $post_id) {
        return [];
    }

    // Read raw post meta to bypass any Polylang filtering inside Pods'
    // relationship query — Pods stores pick/relationship as multiple
    // postmeta rows (one per linked ID) or a serialised value.
    $raw_meta = get_post_meta($post_id, 'faq_pagina');
    $ids      = [];

    $collect_id = null;
    $collect_id = static function ($value) use (&$ids, &$collect_id): void {
        if (is_numeric($value) && (int) $value > 0) {
            $ids[] = (int) $value;
            return;
        }

        if (is_array($value)) {
            if (! empty($value['ID']) && is_numeric($value['ID'])) {
                $ids[] = (int) $value['ID'];
                return;
            }

            if (! empty($value['id']) && is_numeric($value['id'])) {
                $ids[] = (int) $value['id'];
                return;
            }

            if (! empty($value['post_id']) && is_numeric($value['post_id'])) {
                $ids[] = (int) $value['post_id'];
                return;
            }

            foreach ($value as $sub) {
                $collect_id($sub);
            }
            return;
        }

        if (is_object($value)) {
            if (! empty($value->ID) && is_numeric($value->ID)) {
                $ids[] = (int) $value->ID;
                return;
            }

            if (! empty($value->id) && is_numeric($value->id)) {
                $ids[] = (int) $value->id;
                return;
            }

            if (! empty($value->post_id) && is_numeric($value->post_id)) {
                $ids[] = (int) $value->post_id;
                return;
            }
        }
    };

    if (! empty($raw_meta)) {
        foreach ($raw_meta as $item) {
            $collect_id($item);

            if (is_string($item) && $item !== '') {
                $decoded = maybe_unserialize($item);
                if ($decoded !== $item) {
                    $collect_id($decoded);
                }

                $json = json_decode($item, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    $collect_id($json);
                }

                if (str_contains($item, ',')) {
                    $collect_id(array_map('trim', explode(',', $item)));
                }
            }
        }
    }

    // Fallback: use Pods API (which may be Polylang-filtered, but better than nothing)
    if (empty($ids)) {
        $pod = torresan_pod($post_id);
        if ($pod) {
            $val = $pod->field('faq_pagina');
            if (! empty($val)) {
                $collect_id($val);
            }
        }
    }

    $faq_query = [
        'post_type'        => 'domanda_faq',
        'post_status'      => 'publish',
        'fields'           => 'ids',
        'posts_per_page'   => -1,
        'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
        'suppress_filters' => false,
    ];

    $current_lang = '';
    if (function_exists('pll_current_language')) {
        $current_lang = (string) pll_current_language();
    }
    if ($current_lang !== '') {
        $faq_query['lang'] = $current_lang;
    }

    // Merge in all published FAQs in current language so newly added items
    // appear even before manual relationship linking.
    $ids = array_merge($ids, get_posts($faq_query));

    // Last-resort fallback: if nothing found in current language, load all published FAQs.
    if (empty(array_filter($ids))) {
        $ids = get_posts([
            'post_type'        => 'domanda_faq',
            'post_status'      => 'publish',
            'fields'           => 'ids',
            'posts_per_page'   => -1,
            'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
            'suppress_filters' => false,
        ]);
    }

    $ids = array_unique(array_filter($ids));
    if (empty($ids)) {
        return [];
    }

    // If Polylang is active and manages domanda_faq, translate each ID
    // to the version in the current language (falls back to original if
    // no translation exists — avoids showing nothing at all).
    if (function_exists('pll_get_post') && function_exists('pll_current_language')) {
        $translated = [];
        $lang = pll_current_language();
        foreach ($ids as $id) {
            $tr = pll_get_post($id, $lang);
            if ($tr) {
                $translated[] = $tr;
                continue;
            }

            // Keep unassigned-language FAQs visible.
            if (function_exists('pll_get_post_language')) {
                $post_lang = pll_get_post_language($id);
                if (empty($post_lang)) {
                    $translated[] = $id;
                }
            }
        }
        return array_unique(array_filter($translated));
    }

    return $ids;
}

/**
 * Render FAQ accordion from linked domanda_faq posts.
 */
function torresan_render_faq(?int $post_id = null, string $heading = 'FAQ'): void
{
    $ids = torresan_get_faq($post_id);

    if (empty($ids)) {
        return;
    }
    ?>
    <section class="section-faq">
        <div class="container container-narrow">
            <?php if ($heading) : ?>
                <h2><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>

            <?php foreach ($ids as $fid) :
                $faq = get_post($fid);
                if (! $faq || $faq->post_status !== 'publish') continue;
                $question = get_the_title($fid);
                $answer   = apply_filters('the_content', $faq->post_content);
                if (! $question || ! trim($faq->post_content)) continue;
            ?>
            <div class="faq-item">
                <button class="faq-trigger" type="button">
                    <span><?php echo esc_html($question); ?></span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-content"><?php echo $answer; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

/**
 * Render a photo gallery grid from a multi-file field.
 * Supports lightbox via data-lightbox attributes.
 *
 * @param string $field_name  Pods field name
 * @param int|null $post_id
 * @param string $group_id   data-lightbox-group value (auto-generated if empty)
 */
function torresan_render_gallery(string $field_name = 'photo_gallery', ?int $post_id = null, string $group_id = ''): void
{
    $ids = torresan_gallery_ids($field_name, $post_id);

    if (empty($ids)) {
        return;
    }

    if (! $group_id) {
        $group_id = 'gallery-' . ($post_id ?: get_the_ID());
    }
    ?>
    <section class="section-content">
        <div class="container">
            <div class="photo-gallery" data-lightbox-group="<?php echo esc_attr($group_id); ?>">
                <?php foreach ($ids as $img_id) :
                    $url_thumb = wp_get_attachment_image_url($img_id, 'section-card');
                    $url_full  = wp_get_attachment_image_url($img_id, 'large');
                    $alt       = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                    if (! $url_thumb) continue;
                ?>
                <div class="gallery-item" data-lightbox="<?php echo esc_url($url_full ?: $url_thumb); ?>">
                    <img src="<?php echo esc_url($url_thumb); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Render page hero section (reusable across all page templates).
 */
function torresan_render_page_hero(?int $post_id = null): void
{
    if (! $post_id) {
        $post_id = get_the_ID();
    }

    $hero_img = get_the_post_thumbnail_url($post_id, 'hero-bg');
    $eyebrow  = torresan_field('hero_eyebrow', $post_id);
    $subtitle = torresan_field('hero_subtitle', $post_id);
    $btn_text = torresan_field('hero_cta_text', $post_id);
    $btn_url  = torresan_localize_url(torresan_field('hero_cta_url', $post_id));
    ?>
    <section class="<?php echo esc_attr($hero_img ? 'page-hero has-bg' : 'page-hero'); ?>" <?php if ($hero_img) : ?>style="background-image:url('<?php echo esc_url($hero_img); ?>')"<?php endif; ?>>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <?php if ($eyebrow) : ?>
                <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>
            <h1><?php echo esc_html(get_the_title($post_id)); ?></h1>
            <?php if ($subtitle) : ?>
                <p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>
            <?php if ($btn_text && $btn_url) : ?>
                <a class="btn" href="<?php echo esc_url($btn_url); ?>"><?php echo esc_html($btn_text); ?></a>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

/**
 * Render the WYSIWYG editor content if not empty.
 */
function torresan_render_editor_content(): void
{
    if (have_posts()) :
        while (have_posts()) : the_post();
            if (trim(get_the_content())) :
                ?>
                <section class="section-content">
                    <div class="container wysiwyg-content">
                        <?php the_content(); ?>
                    </div>
                </section>
                <?php
            endif;
        endwhile;
    endif;
}

/* ─── Alusonic helpers ─── */

/**
 * Riporta un URL interno alla lingua corrente.
 *
 * I campi CTA (hero_cta_url, home_mat_cta_url…) contengono URL assoluti salvati
 * una volta sola: la home italiana finiva così a puntare alle pagine inglesi.
 * Qui l'URL viene risolto al post corrispondente e sostituito con il permalink
 * della sua traduzione nella lingua corrente.
 *
 * Gli URL esterni (Instagram, YouTube, microsito StratOSonic) e quelli non
 * risolvibili vengono restituiti invariati.
 */
function torresan_localize_url(string $url): string
{
    $url = trim($url);
    if ($url === '' || ! function_exists('pll_get_post')) {
        return $url;
    }

    // Solo link interni: confronto sull'host del sito.
    $home_host = wp_parse_url(home_url(), PHP_URL_HOST);
    $url_host  = wp_parse_url($url, PHP_URL_HOST);
    if ($url_host && $url_host !== $home_host) {
        return $url;
    }

    $post_id = url_to_postid($url);
    if (! $post_id) {
        return $url;
    }

    $lang = function_exists('pll_current_language') ? pll_current_language() : '';
    if (! $lang) {
        return $url;
    }

    $translated = pll_get_post($post_id, $lang);

    return $translated ? get_permalink($translated) : $url;
}

/**
 * Trova una pagina per slug e restituisce la versione nella lingua corrente.
 *
 * get_page_by_path() non conosce le lingue: con italiano e inglese che
 * condividono la stessa struttura restituisce sempre la prima pagina trovata,
 * cioè quella inglese. È così che, sulla home italiana, la sezione "Chi siamo"
 * mostrava titolo e testo in inglese.
 *
 * Accetta più slug (es. 'about', 'chi-siamo') e prova in ordine.
 */
function torresan_localized_page(string ...$slugs): ?WP_Post
{
    foreach ($slugs as $slug) {
        $page = get_page_by_path($slug);
        if (! $page) {
            continue;
        }

        if (function_exists('pll_get_post')) {
            $lang = function_exists('pll_current_language') ? pll_current_language() : '';
            if ($lang) {
                $translated = pll_get_post($page->ID, $lang);
                if ($translated) {
                    return get_post($translated);
                }
            }
        }

        return $page;
    }

    return null;
}

/**
 * Parse a textarea field of "Left|Right" lines into an array of [left, right] pairs.
 *
 * @return array<int, array{0:string,1:string}>
 */
function alusonic_parse_pairs(string $raw): array
{
    $pairs = [];
    foreach (preg_split('/\r\n|\r|\n/', trim($raw)) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = array_map('trim', explode('|', $line, 2));
        $pairs[] = [$parts[0] ?? '', $parts[1] ?? ''];
    }
    return $pairs;
}

/**
 * Extract a YouTube video ID from either a bare ID or a full URL
 * (watch?v=, youtu.be/, embed/, shorts/).
 */
function alusonic_youtube_id(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $raw)) {
        return $raw;
    }
    if (preg_match('#(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})#', $raw, $m)) {
        return $m[1];
    }
    return '';
}

/**
 * Machine type of a model (basso|chitarra), for filtering.
 */
function alusonic_model_type(int $post_id): string
{
    $t = torresan_field('model_type', $post_id);
    return $t !== '' ? sanitize_title($t) : 'altro';
}

/**
 * Render a single model card (used on homepage and Models listing).
 */
function alusonic_render_model_card(int $post_id): void
{
    $type      = alusonic_model_type($post_id);
    $type_label = [
        'basso'    => __('Bass', 'torresan-bnb'),
        'chitarra' => __('Guitar', 'torresan-bnb'),
    ][$type] ?? '';
    $thumb = get_post_thumbnail_id($post_id);
    $img   = $thumb ? wp_get_attachment_image_url($thumb, 'large') : '';
    $title = get_the_title($post_id);

    // Linea di prodotto e flag signature: chiavi macchina impostate da
    // scripts/set-model-family.php, su cui lavorano i filtri del catalogo.
    $family    = (string) get_post_meta($post_id, 'model_family', true);
    $signature = get_post_meta($post_id, 'model_signature', true) ? '1' : '0';
    ?>
    <a class="model-card" data-tilt data-type="<?php echo esc_attr($type); ?>" data-family="<?php echo esc_attr($family); ?>" data-signature="<?php echo esc_attr($signature); ?>" data-name="<?php echo esc_attr(mb_strtolower($title)); ?>" href="<?php echo esc_url(get_permalink($post_id)); ?>">
        <div class="mc-stage">
            <div class="mc-bg"></div>
            <div class="mc-glow"></div>
            <div class="mc-frame"></div>
            <?php if ($img) : ?>
            <div class="mc-subject">
                <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
            </div>
            <?php endif; ?>
            <div class="mc-caption">
                <?php if ($type_label) : ?><div class="model-card-cat"><?php echo esc_html($type_label); ?></div><?php endif; ?>
                <div class="model-card-name"><?php echo esc_html($title); ?></div>
                <span class="model-card-more"><?php esc_html_e('Discover', 'torresan-bnb'); ?> &rarr;</span>
            </div>
        </div>
    </a>
    <?php
}

/**
 * Query model post IDs (optionally a curated ordered subset).
 *
 * @return int[]
 */
function alusonic_model_ids(int $limit = -1, array $preferred = []): array
{
    $preferred = array_values(array_filter(array_map('intval', $preferred)));
    if (! empty($preferred)) {
        $q = new WP_Query([
            'post_type'      => 'camera',
            'post_status'    => 'publish',
            'post__in'       => $preferred,
            'orderby'        => 'post__in',
            'posts_per_page' => $limit,
            'fields'         => 'ids',
            'suppress_filters' => false,
        ]);
        return $q->posts;
    }

    $q = new WP_Query([
        'post_type'      => 'camera',
        'post_status'    => 'publish',
        'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
        'posts_per_page' => $limit,
        'fields'         => 'ids',
        'suppress_filters' => false,
    ]);
    return $q->posts;
}

/* ─── Carousel Helper ─── */

/**
 * Render an auto-advancing carousel from an array of attachment IDs.
 *
 * @param int[] $ids
 * @param string $group_id For lightbox grouping
 * @param int[] $mobile_ids Optional mobile crop per slide, paired by position. Falls back to $ids when missing/shorter.
 */
function torresan_render_carousel(array $ids, string $group_id = 'carousel', array $mobile_ids = []): void
{
    if (empty($ids)) {
        return;
    }
    ?>
    <div class="carousel" data-lightbox-group="<?php echo esc_attr($group_id); ?>">
        <div class="carousel-track">
            <?php foreach ($ids as $i => $img_id) :
                $url_full   = wp_get_attachment_image_url($img_id, 'hero-bg');
                $url_thumb  = wp_get_attachment_image_url($img_id, 'large');
                $alt        = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                if (! $url_full) continue;
                $url_mobile = isset($mobile_ids[$i]) ? (wp_get_attachment_image_url($mobile_ids[$i], 'hero-bg') ?: $url_full) : $url_full;
            ?>
                <div class="carousel-slide" data-lightbox="<?php echo esc_url($url_full); ?>">
                    <?php torresan_picture($url_full, $url_mobile, $alt, ['loading' => $i === 0 ? 'eager' : 'lazy']); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-prev" aria-label="<?php esc_attr_e('Previous slide', 'torresan-bnb'); ?>">&#8592;</button>
        <button class="carousel-next" aria-label="<?php esc_attr_e('Next slide', 'torresan-bnb'); ?>">&#8594;</button>
        <div class="carousel-dots">
            <?php foreach ($ids as $i => $img_id) : ?>
                <button class="dot<?php echo $i === 0 ? ' active' : ''; ?>" aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'torresan-bnb'), $i + 1)); ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/* ─── Language Switcher (Polylang) ─── */

/*
 * L'auto-registrazione delle lingue su admin_init è stata rimossa: creava
 * EN, IT, DE, FR ed ES (qui servono solo inglese e italiano) e, impostando
 * l'opzione torresan_pll_languages_seeded, faceva da innesco agli import
 * delle traduzioni del vecchio sito B&B — vedi la nota nel blocco Includes.
 * La configurazione ora è esplicita e ripetibile: scripts/i18n-setup.php.
 */

/* ─── Polylang Security Hardening ─────────────────────────────────────────
 * OWASP A01 — Broken Access Control
 * Block unauthenticated access to the Polylang REST namespace.
 * The /wp-json/pll/v1/languages endpoint exposes internal term IDs, plugin
 * paths, flag URLs and page_on_front IDs to anonymous visitors.
 * ─────────────────────────────────────────────────────────────────────── */
add_filter('rest_pre_dispatch', function ($result, $server, WP_REST_Request $request) {
    if (str_starts_with($request->get_route(), '/pll/v1') && ! is_user_logged_in()) {
        return new WP_Error('forbidden', 'Forbidden.', ['status' => 403]);
    }
    return $result;
}, 10, 3);

/* ─── Polylang Cookie Security ─────────────────────────────────────────────
 * OWASP A02 — Cryptographic Failures / A05 — Misconfiguration
 * The pll_language cookie is used to remember the visitor's chosen language.
 * Enforce SameSite=Strict and (if on HTTPS) Secure flag so it cannot be
 * sent in cross-site requests or intercepted over plain HTTP.
 * ─────────────────────────────────────────────────────────────────────── */
add_action('send_headers', function (): void {
    if (isset($_COOKIE['pll_language']) && ! headers_sent()) {
        $is_https = is_ssl();
        $flags    = 'SameSite=Strict; Path=/; HttpOnly';
        if ($is_https) {
            $flags .= '; Secure';
        }
        header(
            'Set-Cookie: pll_language=' . rawurlencode(sanitize_key((string) $_COOKIE['pll_language'])) . '; ' . $flags,
            false
        );
    }
});

/**
 * Output a compact inline language switcher.
 * Renders only when Polylang is active and more than one language exists.
 *
 * @param string $class  Additional CSS class for the wrapper element.
 */
function torresan_language_switcher(string $class = ''): void
{
    if (! function_exists('pll_the_languages')) {
        return;
    }

    $raw = pll_the_languages(['raw' => 1]);
    if (empty($raw) || count($raw) < 2) {
        return;
    }

    $wrapper_class = trim('lang-switcher ' . $class);
    $is_dropdown = strpos($class, 'dropdown') !== false;
    echo '<div class="' . esc_attr($wrapper_class) . '" aria-label="' . esc_attr__('Select language', 'torresan-bnb') . '">';
    if ($is_dropdown) {
        // Dropdown version
        echo '<button class="lang-switcher-toggle" aria-haspopup="listbox" aria-expanded="false">';
        foreach ($raw as $lang) {
            if (!empty($lang['current_lang'])) {
                echo esc_html(strtoupper($lang['slug']));
            }
        }
        echo ' <span class="lang-switcher-caret">&#9662;</span></button>';
        echo '<ul class="lang-switcher-list" tabindex="-1" role="listbox" hidden>';
        foreach ($raw as $lang) {
            $current = !empty($lang['current_lang']);
            $url     = esc_url($lang['url']);
            $name    = esc_html(strtoupper($lang['slug']));
            $full    = esc_attr($lang['name']);
            $active  = $current ? ' aria-current="true"' : '';
            $cls     = $current ? 'lang-item lang-item--active' : 'lang-item';
            echo '<li class="' . esc_attr($cls) . '" role="option"' . ($current ? ' aria-selected="true"' : '') . '>';
            if ($current) {
                echo '<span' . $active . ' title="' . $full . '">' . $name . '</span>';
            } else {
                echo '<a href="' . $url . '" title="' . $full . '" hreflang="' . esc_attr($lang['locale']) . '">' . $name . '</a>';
            }
            echo '</li>';
        }
        echo '</ul>';
    } else {
        // Inline list version (default)
        echo '<ul class="lang-switcher-list">';
        foreach ($raw as $lang) {
            $current = !empty($lang['current_lang']);
            $url     = esc_url($lang['url']);
            $name    = esc_html(strtoupper($lang['slug']));
            $full    = esc_attr($lang['name']);
            $active  = $current ? ' aria-current="true"' : '';
            $cls     = $current ? 'lang-item lang-item--active' : 'lang-item';
            echo '<li class="' . esc_attr($cls) . '">';
            if ($current) {
                echo '<span' . $active . ' title="' . $full . '">' . $name . '</span>';
            } else {
                echo '<a href="' . $url . '" title="' . $full . '" hreflang="' . esc_attr($lang['locale']) . '">' . $name . '</a>';
            }
            echo '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
}

/* ─── Structured Data (JSON-LD) ─── */

add_action('wp_head', function (): void {
    // Only output sitewide schema once (not duplicating Yoast per-page schema)
    if (! is_front_page() && ! is_page()) {
        return;
    }

    // Indirizzo dalle Impostazioni Sito. Il fallback precedente era ancora
    // quello del vecchio sito Torre San Bartolo e finiva nei dati strutturati.
    $settings     = torresan_get_site_settings();
    $address_text = trim((string) ($settings['footer_address'] ?? ''));
    $address_text = $address_text !== ''
        ? preg_replace('/\s*\n\s*/', ', ', $address_text)
        : 'Via Serrata, 4a, 47833 Morciano di Romagna (RN), Italy';

    // Telefono/email dalla pagina Contatti nella lingua corrente
    $contact_id = 0;
    $con_page = torresan_localized_page('contact', 'contatti');
    if ($con_page) $contact_id = $con_page->ID;

    $phone = $contact_id ? get_post_meta($contact_id, 'contatti_telefono', true) : '';
    $email = $contact_id ? get_post_meta($contact_id, 'contatti_email', true) : get_option('admin_email');

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => ['BedAndBreakfast', 'LodgingBusiness'],
        '@id'         => home_url('/#property'),
        'name'        => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url'         => home_url('/'),
        'logo'        => ['@type' => 'ImageObject', 'url' => get_theme_file_uri('assets/img/logo.png')],
        'image'       => get_the_post_thumbnail_url(get_option('page_on_front'), 'hero-bg') ?: '',
        'priceRange'  => '€€€',
        'address'     => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Pesaro',
            'addressRegion'   => 'PU',
            'addressCountry'  => 'IT',
            'streetAddress'   => $address_text,
        ],
        'amenityFeature' => [
            ['@type' => 'LocationFeatureSpecification', 'name' => 'Private Pool',        'value' => true],
            ['@type' => 'LocationFeatureSpecification', 'name' => 'Private Property',    'value' => true],
            ['@type' => 'LocationFeatureSpecification', 'name' => 'Sea View',            'value' => true],
            ['@type' => 'LocationFeatureSpecification', 'name' => 'Air Conditioning',    'value' => true],
            ['@type' => 'LocationFeatureSpecification', 'name' => 'Free Parking',        'value' => true],
            ['@type' => 'LocationFeatureSpecification', 'name' => 'Wi-Fi',               'value' => true],
        ],
    ];

    if ($phone) $schema['telephone'] = $phone;
    if ($email) $schema['email']     = $email;

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>' . "\n";
}, 20);

/* ─── LLMs.txt Endpoint ─── */

add_action('init', function (): void {
    add_rewrite_rule('^llms\.txt$',      'index.php?torresan_llms=summary', 'top');
    add_rewrite_rule('^llms-full\.txt$', 'index.php?torresan_llms=full',    'top');
});

add_filter('query_vars', function (array $vars): array {
    $vars[] = 'torresan_llms';
    return $vars;
});

add_action('template_redirect', function (): void {
    $mode = get_query_var('torresan_llms');
    if (! $mode) {
        return;
    }

    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: public, max-age=86400');

    $name = get_bloginfo('name');
    $desc = get_bloginfo('description');
    $url  = home_url('/');

    $suites = get_posts(['post_type' => 'camera', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'menu_order', 'order' => 'ASC']);
    $exps   = get_posts(['post_type' => 'esperienza', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'menu_order', 'order' => 'ASC']);

    echo "# {$name}\n\n";
    echo "> {$desc}\n\n";

    if ($mode === 'full') {
        echo "{$name} is a premium bed & breakfast offering full-property rental in the Pesaro-Urbino area of Italy. Guests rent the entire property exclusively — no shared spaces with strangers.\n\n";
    }

    echo "## Pages\n\n";
    $nav_pages = [
        ['About',       '/about',       'History and story of Torre San Bartolo'],
        ['Suites',      '/suites',      'Our guest suites and accommodation options'],
        ['Pool',        '/pool',        'Private pool and outdoor amenities'],
        ['Experiences', '/experiences', 'Local activities and curated experiences'],
        ['Gallery',     '/gallery',     'Photo gallery of the property'],
        ['FAQ',         '/faq',         'Frequently asked questions'],
        ['Contact',     '/contact',     'Enquiry and availability request form'],
        ['Location',    '/location',    'How to reach us, address and directions'],
    ];
    foreach ($nav_pages as [$label, $path, $blurb]) {
        echo "- [{$label}]({$url}{$path}): {$blurb}\n";
    }

    if (! empty($suites)) {
        echo "\n## Suites\n\n";
        foreach ($suites as $suite) {
            $guests = get_post_meta($suite->ID, 'camera_guests', true);
            $price  = get_post_meta($suite->ID, 'camera_prezzo', true);
            $link   = get_permalink($suite->ID);
            echo "- [{$suite->post_title}]({$link})";
            if ($guests || $price) {
                echo " — ";
                if ($guests) echo "up to {$guests} guests";
                if ($guests && $price) echo ", ";
                if ($price)  echo "from {$price}";
            }
            echo "\n";
            if ($mode === 'full' && $suite->post_excerpt) {
                echo "  " . wp_strip_all_tags($suite->post_excerpt) . "\n";
            }
        }
    }

    if (! empty($exps)) {
        echo "\n## Experiences\n\n";
        foreach ($exps as $exp) {
            $link = get_permalink($exp->ID);
            $dur  = get_post_meta($exp->ID, 'exp_duration', true);
            echo "- [{$exp->post_title}]({$link})";
            if ($dur) echo " — {$dur}";
            echo "\n";
            if ($mode === 'full' && $exp->post_excerpt) {
                echo "  " . wp_strip_all_tags($exp->post_excerpt) . "\n";
            }
        }
    }

    echo "\n## Contact\n\n";
    echo "- Enquiry form: [{$url}contact]({$url}contact)\n";
    echo "- Full address: Torre San Bartolo, Pesaro-Urbino, Italy\n";

    exit;
});

/* ─── Contact Form AJAX Handler ─── */

add_action('wp_ajax_nopriv_torresan_contact', 'torresan_handle_contact');
add_action('wp_ajax_torresan_contact',        'torresan_handle_contact');

function torresan_handle_contact(): void
{
    // Verify nonce (OWASP A01 — CSRF protection)
    if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'torresan_contact')) {
        wp_send_json_error(['message' => 'Security check failed. Please refresh and try again.'], 403);
    }

    // Honeypot: bots populate hidden fields, humans leave them empty (OWASP A04)
    if (! empty($_POST['tsb_hp'])) {
        wp_send_json_error(['message' => 'Submission rejected.'], 422);
    }

    // IP-based rate limiting: max 5 submissions per hour (OWASP A04 — insecure design)
    $ip       = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
    $rate_key = 'tsb_cf_' . md5($ip . gmdate('YmdH'));
    $attempts = (int) get_transient($rate_key);
    if ($attempts >= 5) {
        wp_send_json_error(['message' => 'Too many requests. Please try again in an hour.'], 429);
    }
    set_transient($rate_key, $attempts + 1, HOUR_IN_SECONDS);

    $name     = sanitize_text_field(wp_unslash($_POST['contact_name']    ?? ''));
    $email    = sanitize_email(wp_unslash($_POST['contact_email']   ?? ''));
    $phone    = sanitize_text_field(wp_unslash($_POST['contact_phone']   ?? ''));
    $interest = sanitize_text_field(wp_unslash($_POST['contact_interest'] ?? ''));
    $message  = sanitize_textarea_field(wp_unslash($_POST['contact_message'] ?? ''));

    if (! $name || ! $email || ! is_email($email)) {
        wp_send_json_error(['message' => 'Please enter a valid name and email address.'], 422);
    }

    if (! $message) {
        wp_send_json_error(['message' => 'Please include a message.'], 422);
    }

    $to      = get_option('admin_email');
    $subject = '[' . get_bloginfo('name') . '] Enquiry from ' . $name;
    $body    = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n";
    if ($interest) $body .= "Interest: {$interest}\n";
    $body .= "\nMessage:\n{$message}";

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>',
    ];

    $sent = wp_mail($to, $subject, $body, $headers);

    if ($sent) {
        wp_send_json_success(['message' => 'Thank you! We will get back to you shortly.']);
    } else {
        wp_send_json_error(['message' => 'There was an error sending your message. Please try again later.'], 500);
    }
}

/* Localise AJAX URL for the contact form */
add_action('wp_enqueue_scripts', function (): void {
    wp_localize_script('torresan-bnb-main', 'torresanAjax', [
        'url'   => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('torresan_contact'),
    ]);
}, 20);

/* ═══════════════════════════════════════════════════════
   Primary CTA target — Alusonic uses a contact / quote flow
   (no online booking). All former "Book" CTAs point here.
   ══════════════════════════════════════════════════════ */

/**
 * Returns the primary CTA URL (contact / quote request page).
 * Alusonic instruments are custom-built, so every CTA leads to Contact.
 *
 * @param  string $fallback  URL used if no contact page exists yet.
 */
function torresan_booking_url(string $fallback = ''): string
{
    $page = torresan_localized_page('contact', 'contatti');
    if ($page) {
        return esc_url(get_permalink($page->ID));
    }
    return $fallback ?: esc_url(home_url('/contact/'));
}

/**
 * Alias kept for backward-compatibility with existing call sites.
 */
function torresan_reserve_url(string $fallback = ''): string
{
    return torresan_booking_url($fallback);
}

/**
 * Renders a VikBooking shortcode stored in the theme's booking settings.
 * Returns the rendered HTML string or an empty string if the option is not set.
 *
 * @param  string $key        Option key: 'tsb_sc_search' | 'tsb_sc_rooms' |
 *                             'tsb_sc_reservation' | 'tsb_sc_confirm' | 'tsb_sc_info'
 * @param  string $wrapper    Optional CSS class for the wrapper div.
 */
function torresan_sc(string $key, string $wrapper = 'vikbooking-wrap'): string
{
    $shortcode = trim(get_option($key, ''));
    if (! $shortcode) {
        return '';
    }
    // Only allow shortcode-like strings (square-bracket notation)
    if (! preg_match('/^\[[\w]/', $shortcode)) {
        return '';
    }
    try {
        $html = do_shortcode($shortcode);
    } catch (\Throwable $e) {
        // VikBooking throws RuntimeException for views missing default.xml.
        // Fail silently so the page doesn't crash.
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions
            error_log('torresan_sc(' . $key . '): ' . $e->getMessage());
        }
        return '';
    }
    return $wrapper ? '<div class="' . esc_attr($wrapper) . '">' . $html . '</div>' : $html;
}

/* ── Admin Settings Page ── */

add_action('admin_menu', function (): void {
    add_menu_page(
        __('Booking', 'torresan-bnb'),
        __('Booking', 'torresan-bnb'),
        'manage_options',
        'tsb-booking',
        'tsb_booking_settings_render',
        'dashicons-calendar-alt',
        59   // position: just before Appearance
    );
});

add_action('admin_init', function (): void {
    $options = [
        'tsb_url_book'       => ['Availability page URL',            'URL of the <em>Check Availability</em> page (page-availability.php).<br>Used by: header BOOK button &middot; hero CTAs &middot; pool CTA &middot; experiences CTA &middot; suite &ldquo;Check Availability&rdquo;.'],
        'tsb_url_reserve'    => ['Reservation / Payment page URL',    'URL of the <em>Complete Your Reservation</em> page (page-book.php).<br>Used by: &ldquo;Book This Suite&rdquo; buttons on suite detail pages.'],
        'tsb_sc_search'      => ['Shortcode — Availability search',  'Shortcode che mostra il widget di ricerca disponibilità.<br><strong>Usa:</strong> <code>[vikbooking view="availability"]</code><br>Mostrato: nella pagina Availability e nella sidebar delle suite.'],
        'tsb_sc_rooms'       => ['Shortcode — Rooms list',            'Shortcode che mostra le camere disponibili dopo la ricerca.<br><strong>Usa:</strong> <code>[vikbooking view="roomslist"]</code><br>Mostrato: nella pagina Availability dopo la selezione date.'],
        'tsb_sc_reservation' => ['Shortcode — Reservation / checkout','Shortcode per il checkout e il pagamento.<br><strong>Usa:</strong> <code>[vikbooking view="booking"]</code><br>Mostrato: nella pagina Book (gestisce anche la conferma).'],
        'tsb_sc_confirm'     => ['Shortcode — Booking confirmation',  'Solitamente gestito automaticamente da <code>[vikbooking view="booking"]</code>. Lascia vuoto se usi <code>booking</code> anche per la conferma.<br><strong>Oppure:</strong> usa un&rsquo;altra pagina dedicata alla conferma.'],
        'tsb_sc_info'        => ['Shortcode — Info / quote request',  'Shortcode per il form contatto / richiesta preventivo di VikBooking.<br><strong>Usa:</strong> <code>[vikbooking view="quote"]</code><br>Quando impostato, sostituisce il form personalizzato nella pagina Contact.'],
    ];

    add_settings_section('tsb_booking_main', '', '__return_false', 'tsb-booking');

    foreach ($options as $key => [$label, $desc]) {
        register_setting('tsb_booking', $key, [
            'sanitize_callback' => function ($v) {
                $v = sanitize_text_field(wp_unslash($v ?? ''));
                // For shortcode fields: strip anything that isn't a shortcode or URL
                return $v;
            },
        ]);
        add_settings_field($key, $label, function () use ($key, $desc): void {
            $val = esc_attr(get_option($key, ''));
            if (str_starts_with($key, 'tsb_url')) {
                echo "<input type=\"url\" name=\"{$key}\" id=\"{$key}\" value=\"{$val}\" class=\"regular-text\"> <p class=\"description\">{$desc}</p>";
            } else {
                echo "<input type=\"text\" name=\"{$key}\" id=\"{$key}\" value=\"{$val}\" class=\"regular-text\" placeholder=\"[vikbooking ...]\"> <p class=\"description\">{$desc}</p>";
            }
        }, 'tsb-booking', 'tsb_booking_main', ['label_for' => $key]);
    }
});

function tsb_booking_settings_render(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['settings-updated'])) {
        add_settings_error('tsb_booking_messages', 'tsb_booking_saved', __('Settings saved.', 'torresan-bnb'), 'updated');
    }

    settings_errors('tsb_booking_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?> — VikBooking</h1>

        <div style="background:#fff;border:1px solid #ccd0d4;border-radius:3px;padding:1.2rem 1.5rem;margin:1rem 0 1.5rem;max-width:760px">
            <h3 style="margin-top:0">📋 Placement guide</h3>
            <table class="widefat striped" style="border:none">
                <thead><tr><th>Where</th><th>Uses</th></tr></thead>
                <tbody>
                    <tr><td>Header <em>BOOK</em> button</td><td><code>tsb_url_book</code> &rarr; /book/</td></tr>
                    <tr><td>Homepage hero CTA</td><td><code>tsb_url_book</code> &rarr; /book/</td></tr>
                    <tr><td>Pool page CTA</td><td><code>tsb_url_book</code> &rarr; /book/</td></tr>
                    <tr><td>Experiences page CTA</td><td><code>tsb_url_book</code> &rarr; /book/</td></tr>
                    <tr><td>Suite sidebar &ldquo;Book This Suite&rdquo;</td><td><code>tsb_url_book</code> &rarr; /book/</td></tr>
                    <tr><td>Book page — VikBooking widget</td><td><code>tsb_sc_search</code> (<code>[vikbooking view="availability"]</code>)</td></tr>
                    <tr><td>Book page — conditions info bar</td><td>Edit the <strong>Book</strong> page in WP Admin &rarr; fill Booking Conditions fields</td></tr>
                </tbody>
            </table>
        </div>

        <form action="options.php" method="post" style="max-width:760px">
            <?php
            settings_fields('tsb_booking');
            do_settings_sections('tsb-booking');
            submit_button(__('Save Settings', 'torresan-bnb'));
            ?>
        </form>
    </div>
    <?php
}

/* ═══════════════════════════════════════════════════════
   Booking Conditions meta box (for the "Book" page)
   ═══════════════════════════════════════════════════════ */

add_action('add_meta_boxes', function (): void {
    add_meta_box(
        'tsb_book_conditions',
        '🗓 Booking Conditions',
        'tsb_book_conditions_render',
        'page',
        'normal',
        'high'
    );
});

function tsb_book_conditions_render(\WP_Post $post): void
{
    // Only show on pages using the Book template
    $template = get_post_meta($post->ID, '_wp_page_template', true);
    if ($template !== 'page-book.php') {
        echo '<p style="color:#888;font-size:.85rem;">This meta box is only active on the <strong>Book</strong> page template.</p>';
        return;
    }

    wp_nonce_field('tsb_book_conditions_save', 'tsb_book_conditions_nonce');

    $fields = [
        'book_min_nights'   => ['Minimum stay',        'text',     'e.g. 2 nights'],
        'book_checkin_from' => ['Check-in from',        'text',     'e.g. 15:00'],
        'book_checkout_by'  => ['Check-out by',         'text',     'e.g. 11:00'],
        'book_pets'         => ['Pet policy',           'text',     'e.g. Not allowed / Allowed on request'],
        'book_breakfast'    => ['Breakfast',            'text',     'e.g. Not included / Continental – €15/person'],
        'book_house_rules'  => ['House rules',          'textarea', "One rule per line.\ne.g. No smoking indoors\nQuiet hours 22:00–08:00"],
        'book_cancellation' => ['Cancellation policy',  'textarea', 'e.g. Free cancellation up to 7 days before arrival. 100% charge for later cancellations.'],
    ];

    echo '<table class="form-table" role="presentation"><tbody>';
    foreach ($fields as $key => [$label, $type, $placeholder]) {
        $val = esc_attr(get_post_meta($post->ID, $key, true));
        echo "<tr><th scope='row'><label for='{$key}'>{$label}</label></th><td>";
        if ($type === 'textarea') {
            echo "<textarea id='{$key}' name='{$key}' rows='4' class='large-text' placeholder='" . esc_attr($placeholder) . "'>" . esc_textarea(get_post_meta($post->ID, $key, true)) . "</textarea>";
        } else {
            echo "<input type='text' id='{$key}' name='{$key}' value='{$val}' class='regular-text' placeholder='" . esc_attr($placeholder) . "'>";
        }
        echo '</td></tr>';
    }
    echo '</tbody></table>';
}

add_action('save_post_page', function (int $post_id): void {
    if (! isset($_POST['tsb_book_conditions_nonce'])) {
        return;
    }
    if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tsb_book_conditions_nonce'])), 'tsb_book_conditions_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_page', $post_id)) {
        return;
    }

    $fields = ['book_min_nights', 'book_checkin_from', 'book_checkout_by', 'book_pets', 'book_breakfast', 'book_house_rules', 'book_cancellation'];
    foreach ($fields as $key) {
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $key, sanitize_textarea_field(wp_unslash($_POST[$key])));
        }
    }
});

/* ═══════════════════════════════════════════════════════
   GDPR-ready legal pages support
   ═══════════════════════════════════════════════════════ */

/**
 * Registers the footer legal menu location and outputs iubenda shortcodes
 * when Iubenda is active on legal page templates.
 */
add_action('init', function (): void {
    register_nav_menus([
        'footer-legal' => __('Footer — Legal links', 'torresan-bnb'),
    ]);
});

add_filter('pods_html_allow_tags', function($tags, $pod, $field) {
    if ($field['name'] === 'maps_embed') {
        $tags['iframe'] = [
            'src'             => true,
            'width'           => true,
            'height'          => true,
            'frameborder'     => true,
            'style'           => true,
            'allowfullscreen' => true,
            'allow'           => true,
            'title'           => true,
            'loading'         => true,
            'referrerpolicy'  => true,
            'sandbox'         => true,
        ];
    }
    return $tags;
}, 10, 3);
