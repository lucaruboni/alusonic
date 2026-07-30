<?php
/**
 * One-time Polylang language setup script — CLI ONLY.
 * Executed via: docker compose exec -T wordpress php /var/www/html/wp-content/themes/torresan-bnb/inc/polylang-setup.php
 *
 * NOTE: This file is guarded to run ONLY from the command line.
 * Language registration is also handled automatically via the admin_init hook
 * in functions.php — you normally do not need to run this script manually.
 */

// Security: block direct web access (OWASP A05)
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied. This script must be run from the command line.');
}

define('ABSPATH', dirname(__FILE__, 5) . '/');
define('WPINC', 'wp-includes');

// Boot WordPress in a minimal way
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '/';

require_once ABSPATH . 'wp-load.php';

$languages = [
    ['name' => 'English',  'slug' => 'en', 'locale' => 'en_US', 'rtl' => 0, 'term_group' => 0],
    ['name' => 'Italiano', 'slug' => 'it', 'locale' => 'it_IT', 'rtl' => 0, 'term_group' => 0],
    ['name' => 'Deutsch',  'slug' => 'de', 'locale' => 'de_DE', 'rtl' => 0, 'term_group' => 0],
    ['name' => 'Français', 'slug' => 'fr', 'locale' => 'fr_FR', 'rtl' => 0, 'term_group' => 0],
    ['name' => 'Español',  'slug' => 'es', 'locale' => 'es_ES', 'rtl' => 0, 'term_group' => 0],
];

if (! function_exists('PLL')) {
    echo "ERROR: Polylang is not active or not installed.\n";
    exit(1);
}

$pll = PLL();

if (! $pll || ! method_exists($pll->model, 'add_language')) {
    echo "ERROR: Polylang model not available. Make sure Polylang is activated.\n";
    exit(1);
}

foreach ($languages as $lang) {
    $existing = $pll->model->get_language($lang['slug']);
    if ($existing) {
        echo "Already exists: {$lang['name']} ({$lang['slug']})\n";
        continue;
    }

    $result = $pll->model->add_language($lang);
    if (is_wp_error($result)) {
        echo "Error ({$lang['slug']}): " . $result->get_error_message() . "\n";
    } else {
        echo "Added: {$lang['name']} ({$lang['slug']})\n";
    }
}

// Set English as the default language
$en = $pll->model->get_language('en');
if ($en) {
    update_option('polylang', array_merge(
        (array) get_option('polylang', []),
        ['default_lang' => 'en']
    ));
    echo "Default language set to English (en).\n";
}

echo "Done.\n";
