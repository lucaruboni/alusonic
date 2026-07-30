<?php
/**
 * CLI helper: trigger the translation seed immediately.
 * Run: docker compose exec -T wordpress php /var/www/html/wp-content/themes/torresan-bnb/inc/run-translations-seed.php
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied.');
}

$_SERVER['HTTP_HOST']   = 'localhost:8080';
$_SERVER['REQUEST_URI'] = '/wp-admin/';

// wp-load.php already loads functions.php which includes seed-translations.php
require '/var/www/html/wp-load.php';

$already = get_option('torresan_translations_seeded');
if ($already) {
    echo 'Translations already seeded (option set). To re-run, delete the option first.' . PHP_EOL;
    exit(0);
}

if (!function_exists('pll_set_post_language')) {
    echo 'ERROR: Polylang not active or pll_set_post_language not available.' . PHP_EOL;
    exit(1);
}

$langs = pll_languages_list(['fields' => 'slug']);
echo 'Languages registered: ' . implode(', ', $langs) . PHP_EOL;

echo 'Seeding translated pages...' . PHP_EOL;
torresan_seed_translations();

$done = get_option('torresan_translations_seeded');
if ($done) {
    echo 'Done! Translated pages created successfully.' . PHP_EOL;
} else {
    echo 'WARNING: Seed function ran but option was not set (check for errors above).' . PHP_EOL;
}
