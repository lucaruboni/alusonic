<?php
/**
 * Riallinea i ritagli hero già importati in WordPress.
 *
 * alu_import() (seed-alusonic.php) deduplica per nome file tramite la postmeta
 * _alu_src: se il CONTENUTO di un hero-<id>.png cambia ma il nome resta lo
 * stesso, un normale reseed NON reimporta nulla e il sito continua a servire i
 * vecchi byte. Questo script serve esattamente a quel caso: sovrascrive il
 * file originale dell'allegato e rigenera tutte le dimensioni derivate (le
 * card dei modelli usano un ridimensionamento, non l'originale).
 *
 * Uso:
 *   wp eval-file scripts/refresh-hero-cutouts.php --path=/var/www/html
 */

if (! defined('ABSPATH')) {
    exit;
}

$media_dir = '/var/www/html/_alu_media';
if (! is_dir($media_dir)) {
    // Percorso alternativo: la dir montata accanto a wordpress/
    $media_dir = dirname(ABSPATH) . '/_alu_media';
}

$files = glob($media_dir . '/hero-*.png') ?: [];
if (! $files) {
    echo "Nessun hero-*.png trovato in {$media_dir}\n";
    return;
}

require_once ABSPATH . 'wp-admin/includes/image.php';

$updated = 0;
$skipped = 0;
$missing = 0;

foreach ($files as $src) {
    $name = basename($src);

    $found = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [['key' => '_alu_src', 'value' => $name]],
    ]);

    if (! $found) {
        echo "[NON IMPORTATO] {$name} — lo prenderà il prossimo seed\n";
        $missing++;
        continue;
    }

    $id   = (int) $found[0];
    $dest = get_attached_file($id);

    if (! $dest) {
        echo "[SENZA FILE] {$name} (ID {$id})\n";
        $missing++;
        continue;
    }

    if (file_exists($dest) && md5_file($dest) === md5_file($src)) {
        $skipped++;
        continue;
    }

    if (! copy($src, $dest)) {
        echo "[ERRORE COPIA] {$name}\n";
        continue;
    }

    // Rigenera le dimensioni derivate: le card usano un ridimensionamento,
    // quindi sovrascrivere solo l'originale non basta.
    $meta = wp_generate_attachment_metadata($id, $dest);
    wp_update_attachment_metadata($id, $meta);

    echo "[AGGIORNATO] {$name} (ID {$id})\n";
    $updated++;
}

echo "\nAggiornati: {$updated}, invariati: {$skipped}, non importati: {$missing}\n";
