<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Create the default pages on theme activation.
 *
 * Each page is created with the correct slug so that WordPress
 * automatically matches the page-{slug}.php template hierarchy.
 * The template is also assigned explicitly as fallback.
 */
function torresan_seed_pages(): void
{
    if (get_option('torresan_pages_seeded_v3')) {
        return;
    }

    $pages = [
        ['title' => 'Torre San Bartolo', 'slug' => 'home',        'template' => ''],
        ['title' => 'About',             'slug' => 'about',       'template' => 'page-about.php'],
        ['title' => 'Suites',            'slug' => 'suites',      'template' => 'page-suites.php'],
        ['title' => 'Pool',              'slug' => 'pool',        'template' => 'page-pool.php'],
        ['title' => 'Experiences',       'slug' => 'experiences', 'template' => 'page-experiences.php'],
        ['title' => 'Gallery',           'slug' => 'gallery',     'template' => 'page-gallery.php'],
        ['title' => 'FAQ',               'slug' => 'faq',         'template' => 'page-faq.php'],
        ['title' => 'Location',          'slug' => 'location',    'template' => 'page-location.php'],
    ];

    $home_id = 0;
    $order   = 0;

    foreach ($pages as $page) {
        $existing = get_page_by_path($page['slug']);

        if ($existing) {
            if ($page['slug'] === 'home') {
                $home_id = $existing->ID;
            }
            /* Ensure the template is set on existing pages */
            if ($page['template'] && get_page_template_slug($existing->ID) !== $page['template']) {
                update_post_meta($existing->ID, '_wp_page_template', $page['template']);
            }
            continue;
        }

        $args = [
            'post_title'  => $page['title'],
            'post_name'   => $page['slug'],
            'post_status' => 'publish',
            'post_type'   => 'page',
            'menu_order'  => $order++,
        ];

        if ($page['template']) {
            $args['page_template'] = $page['template'];
        }

        $id = wp_insert_post($args);

        if (! is_wp_error($id) && $page['slug'] === 'home') {
            $home_id = $id;
        }
    }

    /* Set the static front page */
    if ($home_id) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);
    }

    update_option('torresan_pages_seeded_v3', true);
}
add_action('after_switch_theme', 'torresan_seed_pages');

/* Also run on admin_init for first-time setup when theme is already active */
add_action('admin_init', function (): void {
    if (! get_option('torresan_pages_seeded_v3')) {
        torresan_seed_pages();
    }
});
