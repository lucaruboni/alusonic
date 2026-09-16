<?php
/**
 * Migrazione mirata (31/07/2026, seconda passata):
 *   ALU112          ← Alusoniccabinetritaglio.png
 *   StratOSonic     ← aludoomritaglio.png
 *   Materiali (home)← aludoomritaglio.png
 *
 * I PNG arrivano già scontornati, quindi vengono importati così come sono.
 * Esecuzione: docker exec alusonic_wp php -r 'require "/var/www/html/wp-load.php"; require "/tmp/mig.php";'
 */

if (! defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wordpress/wp-load.php';
}
require_once ABSPATH . 'wp-admin/includes/image.php';

$IMG_DIR = get_template_directory() . '/assets/img/';

function alu_import_asset(string $filename, string $title): int
{
    global $IMG_DIR;

    $existing = get_posts([
        'post_type'   => 'attachment',
        'post_status' => 'inherit',
        'meta_key'    => '_alu_src',
        'meta_value'  => $filename,
        'fields'      => 'ids',
        'numberposts' => 1,
    ]);
    if ($existing) { echo "  = già presente: $filename (#{$existing[0]})\n"; return (int) $existing[0]; }

    $src = $IMG_DIR . $filename;
    if (! file_exists($src)) { echo "  ! media mancante: $src\n"; return 0; }

    $up = wp_upload_bits($filename, null, file_get_contents($src));
    if (! empty($up['error'])) { echo "  ! upload err $filename: {$up['error']}\n"; return 0; }

    $ft = wp_check_filetype($up['file']);
    $id = wp_insert_attachment([
        'post_mime_type' => $ft['type'],
        'post_title'     => $title,
        'post_status'    => 'inherit',
    ], $up['file']);
    if (is_wp_error($id) || ! $id) { echo "  ! insert err $filename\n"; return 0; }

    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $up['file']));
    update_post_meta($id, '_alu_src', $filename);
    update_post_meta($id, '_wp_attachment_image_alt', $title);
    echo "  + importato: $filename (#$id)\n";
    return (int) $id;
}

function alu_set(string $type, int $post_id, string $field, $value): void
{
    if (function_exists('pods')) {
        $pod = pods($type, $post_id);
        if ($pod && $pod->exists()) { $pod->save($field, $value); }
    }
    update_post_meta($post_id, $field, $value);
}

function alu_model_by_title(string $title): int
{
    $p = get_posts(['post_type' => 'camera', 'post_status' => 'any',
                    'title' => $title, 'numberposts' => 1, 'fields' => 'ids']);
    return $p ? (int) $p[0] : 0;
}

echo "== Migrazione immagini (2a passata) ==\n";

$cab  = alu_import_asset('Alusoniccabinetritaglio.png', 'ALU112');
$doom = alu_import_asset('aludoomritaglio.png', 'Alusonic');

foreach (['ALU112' => $cab, 'StratOSonic' => $doom] as $title => $att) {
    $pid = alu_model_by_title($title);
    if (! $pid) { echo "  ! modello non trovato: $title\n"; continue; }
    if (! $att) { echo "  ! nessun media per: $title\n"; continue; }
    set_post_thumbnail($pid, $att);
    echo "  → $title (#$pid) ← media #$att (" . basename(get_attached_file($att)) . ")\n";
}

$home = (int) get_option('page_on_front');
if (! $home) { $p = get_page_by_path('home'); $home = $p ? $p->ID : 0; }
if ($home && $doom) {
    alu_set('page', $home, 'home_mat_image', $doom);
    echo "  → home_mat_image (#$home) ← media #$doom\n";
}

echo "== Fatto ==\n";
