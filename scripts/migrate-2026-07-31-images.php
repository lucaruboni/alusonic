<?php
/**
 * Migrazione mirata (31/07/2026) — non tocca nulla oltre a quanto elencato:
 *
 *  - importa in media library i 5 scontorni nuovi (assets/img/*-cutout.png)
 *  - rimescola le foto dei modelli:
 *      Django Supreme  ← alusupreme            (prima: IMG_4279)
 *      AluTele         ← IMG_4279              (la vecchia di Django Supreme)
 *      Cass Lewis Sig. ← IMG_4282              (la vecchia di AluTele)
 *      StratOSonic     ← alusonic1
 *      Doom            ← IMG_4278              (la vecchia di StratOSonic)
 *      ALU112          ← alusoniccabinet
 *  - crea il modello "Carbon" (4a card in home) con alusonic2
 *  - immagine della sezione Materiali in home ← alusonic3
 *
 * Esecuzione:
 *   docker exec alusonic_wp php /var/www/html/wp-content/themes/torresan-bnb/../../../../scripts/...
 * oppure copiare in webroot ed eseguire con `php`.
 */

if (! defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wordpress/wp-load.php';
}

require_once ABSPATH . 'wp-admin/includes/image.php';

$IMG_DIR = get_template_directory() . '/assets/img/';

/** Import idempotente: la stessa sorgente non viene mai duplicata. */
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
    if ($existing) {
        echo "  = già presente: $filename (#{$existing[0]})\n";
        return (int) $existing[0];
    }

    $src = $IMG_DIR . $filename;
    if (! file_exists($src)) {
        echo "  ! media mancante: $src\n";
        return 0;
    }

    $up = wp_upload_bits($filename, null, file_get_contents($src));
    if (! empty($up['error'])) {
        echo "  ! upload err $filename: {$up['error']}\n";
        return 0;
    }

    $ft = wp_check_filetype($up['file']);
    $id = wp_insert_attachment([
        'post_mime_type' => $ft['type'],
        'post_title'     => $title,
        'post_status'    => 'inherit',
    ], $up['file']);
    if (is_wp_error($id) || ! $id) {
        echo "  ! insert err $filename\n";
        return 0;
    }

    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $up['file']));
    update_post_meta($id, '_alu_src', $filename);
    update_post_meta($id, '_wp_attachment_image_alt', $title);
    echo "  + importato: $filename (#$id)\n";
    return (int) $id;
}

/** Salva su Pods e su post meta, come fa il seeder. */
function alu_set(string $type, int $post_id, string $field, $value): void
{
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

function alu_model_by_title(string $title): int
{
    $p = get_posts([
        'post_type'   => 'camera',
        'post_status' => 'any',
        'title'       => $title,
        'numberposts' => 1,
        'fields'      => 'ids',
    ]);
    return $p ? (int) $p[0] : 0;
}

echo "== Migrazione immagini modelli ==\n";

/* ── 1. Nuovi media ── */
$NEW = [
    'supreme' => alu_import_asset('alusupreme-cutout.png',       'Django Supreme'),
    'strato'  => alu_import_asset('alusonic1-cutout.png',        'StratOSonic'),
    'carbon'  => alu_import_asset('alusonic2-cutout.png',        'Carbon'),
    'cab'     => alu_import_asset('alusoniccabinet-cutout.png',  'ALU112'),
    'mat'     => alu_import_asset('alusonic3-cutout.png',        'Materiali & processo'),
];

/* ── 2. Foto attuali da riciclare (lette PRIMA di sovrascrivere) ── */
$M = [];
foreach (['Django Supreme', 'StratOSonic', 'Doom', 'Cass Lewis Signature', 'AluTele', 'ALU112'] as $t) {
    $M[$t] = alu_model_by_title($t);
}
$old_django  = $M['Django Supreme'] ? get_post_thumbnail_id($M['Django Supreme']) : 0;
$old_strato  = $M['StratOSonic'] ? get_post_thumbnail_id($M['StratOSonic']) : 0;
$old_alutele = $M['AluTele'] ? get_post_thumbnail_id($M['AluTele']) : 0;

/* ── 3. Riassegnazione ── */
$assign = [
    'Django Supreme'       => $NEW['supreme'],
    'StratOSonic'          => $NEW['strato'],
    'Doom'                 => $old_strato,
    'Cass Lewis Signature' => $old_alutele,
    'AluTele'              => $old_django,
    'ALU112'               => $NEW['cab'],
];
foreach ($assign as $title => $att) {
    $pid = $M[$title] ?? 0;
    if (! $pid) { echo "  ! modello non trovato: $title\n"; continue; }
    if (! $att) { echo "  ! nessun media per: $title\n"; continue; }
    set_post_thumbnail($pid, $att);
    echo "  → $title (#$pid) ← media #$att (" . basename(get_attached_file($att)) . ")\n";
}

/* ── 4. Nuovo modello "Carbon", quarta card in home ── */
$carbon = alu_model_by_title('Carbon');
if (! $carbon) {
    $carbon = wp_insert_post([
        'post_type'    => 'camera',
        'post_status'  => 'publish',
        'post_title'   => 'Carbon',
        'menu_order'   => 3,
        'post_content' => '',
    ]);
    echo "  + creato modello Carbon (#$carbon)\n";
    if (function_exists('pll_set_post_language')) {
        $def = function_exists('pll_default_language') ? (pll_default_language() ?: 'en') : 'en';
        pll_set_post_language($carbon, $def);
    }
} else {
    echo "  = modello Carbon già presente (#$carbon)\n";
}

if ($carbon && ! is_wp_error($carbon)) {
    if ($NEW['carbon']) { set_post_thumbnail($carbon, $NEW['carbon']); }
    alu_set('camera', $carbon, 'model_type', 'basso');
    alu_set('camera', $carbon, 'model_category', 'Basso · Custom Shop');
    alu_set('camera', $carbon, 'model_lead',
        'Top in fibra di carbonio su corpo in alluminio: il peso più contenuto della gamma, con un attacco secco e una definizione chirurgica sulle corde gravi.');
    alu_set('camera', $carbon, 'model_specs',
        "Top|Fibra di carbonio\nCorpo|Alluminio pieno\nCorde|4 / 5\nElettronica|Attiva custom");
    alu_set('camera', $carbon, 'model_detail_title', 'Leggerezza senza compromessi');
    wp_update_post([
        'ID'           => $carbon,
        'menu_order'   => 3,
        'post_content' => '<p>Il top in fibra di carbonio riduce il peso complessivo senza togliere rigidità alla struttura: il risultato è un basso che resta comodo per ore e mantiene l\'attacco tipico dell\'alluminio Alusonic.</p>',
    ]);
    if ($NEW['carbon']) { alu_set('camera', $carbon, 'model_detail_image', $NEW['carbon']); }
}

/* ── 5. Ordine: Carbon quarto, gli altri scalano ── */
$order = [
    'Django Supreme'       => 0,
    'StratOSonic'          => 1,
    'Doom'                 => 2,
    'Carbon'               => 3,
    'Cass Lewis Signature' => 4,
    'AluTele'              => 5,
    'ALU112'               => 6,
];
foreach ($order as $title => $n) {
    $pid = ($title === 'Carbon') ? $carbon : ($M[$title] ?? 0);
    if ($pid) { wp_update_post(['ID' => $pid, 'menu_order' => $n]); }
}
echo "  → ordine modelli aggiornato (Carbon in 4a posizione)\n";

/* ── 6. Immagine sezione Materiali in home ── */
$home = (int) get_option('page_on_front');
if (! $home) {
    $p = get_page_by_path('home');
    $home = $p ? $p->ID : 0;
}
if ($home && $NEW['mat']) {
    alu_set('page', $home, 'home_mat_image', $NEW['mat']);
    echo "  → home_mat_image (#$home) ← media #{$NEW['mat']}\n";
}

echo "== Fatto ==\n";
