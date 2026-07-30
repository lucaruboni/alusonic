<?php

if (! defined('ABSPATH')) {
    exit;
}

/* ─── Custom Post Types ─── */

function torresan_register_post_types(): void
{
    /* ── Models (instrument models) — internal key kept as 'camera' ── */
    register_post_type('camera', [
        'labels' => [
            'name'               => 'Models',
            'singular_name'      => 'Model',
            'add_new'            => 'Add Model',
            'add_new_item'       => 'Add New Model',
            'edit_item'          => 'Edit Model',
            'new_item'           => 'New Model',
            'view_item'          => 'View Model',
            'search_items'       => 'Search Models',
            'not_found'          => 'No models found',
            'not_found_in_trash' => 'No models in trash',
            'menu_name'          => 'Models',
        ],
        'public'        => true,
        'has_archive'   => false,
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-format-audio',
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'show_in_rest'  => true,
        'rewrite'       => ['slug' => 'model'],
    ]);

    /* ── Artists (endorsers) — internal key kept as 'esperienza' ── */
    register_post_type('esperienza', [
        'labels' => [
            'name'               => 'Artists',
            'singular_name'      => 'Artist',
            'add_new'            => 'Add Artist',
            'add_new_item'       => 'Add New Artist',
            'edit_item'          => 'Edit Artist',
            'new_item'           => 'New Artist',
            'view_item'          => 'View Artist',
            'search_items'       => 'Search Artists',
            'not_found'          => 'No artists found',
            'not_found_in_trash' => 'No artists in trash',
            'menu_name'          => 'Artists',
        ],
        'public'        => true,
        'has_archive'   => false,
        'menu_position' => 6,
        'menu_icon'     => 'dashicons-groups',
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'show_in_rest'  => true,
        'rewrite'       => ['slug' => 'artist'],
    ]);

    /* ── Domande FAQ ── */
    register_post_type('domanda_faq', [
        'labels' => [
            'name'               => 'FAQ',
            'singular_name'      => 'Domanda FAQ',
            'add_new'            => 'Aggiungi Domanda',
            'add_new_item'       => 'Aggiungi Nuova Domanda',
            'edit_item'          => 'Modifica Domanda',
            'new_item'           => 'Nuova Domanda',
            'view_item'          => 'Vedi Domanda',
            'search_items'       => 'Cerca Domande',
            'not_found'          => 'Nessuna domanda trovata',
            'not_found_in_trash' => 'Nessuna domanda nel cestino',
            'menu_name'          => 'FAQ',
        ],
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'has_archive'        => false,
        'exclude_from_search'=> true,
        'publicly_queryable' => false,
        'menu_position'      => 7,
        'menu_icon'          => 'dashicons-editor-help',
        'supports'           => ['title', 'editor', 'page-attributes'],
        'show_in_rest'       => true,
        'rewrite'            => false,
    ]);
}
add_action('init', 'torresan_register_post_types');

function torresan_flush_rewrite(): void
{
    torresan_register_post_types();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'torresan_flush_rewrite');
