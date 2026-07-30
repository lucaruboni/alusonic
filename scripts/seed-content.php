<?php
/**
 * Content seeding script — Torre San Bartolo
 * Run: docker compose exec -T wordpress php /var/www/html/wp-content/themes/torresan-bnb/seed-content.php
 *
 * Guard: torresan_content_seeded_v1
 */

$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require '/var/www/html/wp-load.php';

$guard = 'torresan_content_seeded_v1';
if (get_option($guard)) {
    echo "Already seeded. Delete the option '$guard' to re-run.\n";
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// Helper: update_post_meta alias
// ─────────────────────────────────────────────────────────────────────────────
function seed_meta(int $id, string $key, $value): void
{
    update_post_meta($id, $key, $value);
}

// Pods multi-file field: expects serialized array of int IDs as _pods_<field>,
// and first ID as un-prefixed meta key (Pods convention).
function seed_gallery(int $post_id, string $field, array $ids): void
{
    if (empty($ids)) { return; }
    seed_meta($post_id, $field, $ids[0]);
    seed_meta($post_id, '_pods_' . $field, $ids);
}

// ─────────────────────────────────────────────────────────────────────────────
// Media pool (already uploaded)
// ─────────────────────────────────────────────────────────────────────────────
// 118-179 available. Group them by feel/use.
// hotel_image_2  = exterior/villa shots: 119, 141, 144, 147, 158, 171
// hotel_image_3  = interior/rooms:      142, 143, 145, 148, 159, 172
// hotel_image_5  = landscape/garden:    146, 149, 156, 160, 173
// hotel_image_6  = pool area:           150, 161, 174
// hotel_image_7  = detail/lounge:       151, 162, 169, 175
// hotel_image_8  = dining/kitchen:      152, 176
// hotel_image_10 = outdoor/terrace:     155, 165, 166, 177
// hotel_image_11 = bedroom close-ups:   153, 163, 168, 178
// hotel_image_12 = bathroom/spa:        154, 164, 167, 179
// upscaledmedia  = hero (140)

$EXTERIOR  = [119, 141, 144, 147, 158, 171];
$INTERIOR  = [142, 143, 145, 148, 159, 172];
$LANDSCAPE = [146, 149, 156, 160, 173];
$POOL      = [150, 161, 174];
$LOUNGE    = [151, 162, 169, 175];
$DINING    = [152, 176];
$TERRACE   = [155, 165, 166, 177];
$BEDROOM   = [153, 163, 168, 178];
$BATHROOM  = [154, 164, 167, 179];
$HERO_IMG  = 140;

// ─────────────────────────────────────────────────────────────────────────────
// 1. HOME PAGE (ID: 12)
// ─────────────────────────────────────────────────────────────────────────────
echo "Seeding home page...\n";
$home_id = 12;
set_post_thumbnail($home_id, $HERO_IMG);

seed_meta($home_id, 'hero_eyebrow',   'Torre San Bartolo');
seed_meta($home_id, 'hero_subtitle',  'An exclusive Marche villa where time slows and the Adriatic meets the hills.');
seed_meta($home_id, 'hero_cta_text',  'Check Availability');
seed_meta($home_id, 'hero_cta_url',   '/contact/');
seed_meta($home_id, 'hero_video_url', '');

seed_meta($home_id, 'intro_text', '<em>Nestled within the protected Parco del Conero coastline, Torre San Bartolo is a lovingly restored 18th-century farmhouse offering an entirely private retreat for up to eight guests. Three elegantly furnished suites, a panoramic infinity pool and 12 acres of olive groves and vineyards combine to create an unforgettable stay in the heart of Le Marche.</em>');

seed_gallery($home_id, 'intro_image', [$EXTERIOR[0]]);

// Section A — Suites
seed_meta($home_id, 'hp_sa_title',    'The Suites');
seed_meta($home_id, 'hp_sa_text',     'Three unique rooms, each dressed in antique furniture and contemporary comfort. Every detail — from hand-embroidered linens to original terracotta floors — tells the story of a timeless Marche estate.');
seed_meta($home_id, 'hp_sa_cta_text', 'Explore the Suites');
seed_meta($home_id, 'hp_sa_cta_url',  '/suites/');
seed_gallery($home_id, 'hp_sa_gallery', [$BEDROOM[0], $INTERIOR[0]]);

// Section B — Territory banner
seed_meta($home_id, 'hp_sb_eyebrow',  'The Territory');
seed_meta($home_id, 'hp_sb_text',     'Between the silver sea and green hills of the Parco San Bartolo, a landscape of rare beauty awaits. Truffles, wine, medieval villages and untouched coastline — all within reach.');
seed_meta($home_id, 'hp_sb_cta_text', 'Discover the Area');
seed_meta($home_id, 'hp_sb_cta_url',  '/location/');
seed_gallery($home_id, 'hp_sb_bg', [$LANDSCAPE[0]]);

// Section C — Pool
seed_meta($home_id, 'hp_sc_eyebrow',  'The Infinity Pool');
seed_meta($home_id, 'hp_sc_text',     'An oversized infinity pool seemingly suspended above the olive groves, with sweeping views to the Adriatic. Available exclusively for guests of the villa.');
seed_meta($home_id, 'hp_sc_cta_text', 'Discover the Pool');
seed_meta($home_id, 'hp_sc_cta_url',  '/pool/');
seed_gallery($home_id, 'hp_sc_gallery', [$POOL[0], $POOL[1], $POOL[2]]);

// Newsletter
seed_meta($home_id, 'newsletter_title', 'Stay in Touch');
seed_meta($home_id, 'newsletter_text',  'Subscribe to receive exclusive offers, seasonal news and behind-the-scenes stories from Torre San Bartolo.');
seed_meta($home_id, 'newsletter_btn',   'Subscribe');

// ─────────────────────────────────────────────────────────────────────────────
// 2. ABOUT PAGE (ID: 180)
// ─────────────────────────────────────────────────────────────────────────────
echo "Seeding about page...\n";
$about_id = 180;
set_post_thumbnail($about_id, $EXTERIOR[0]);

seed_meta($about_id, 'hero_eyebrow', 'Our Story');

$about_story = '<p>Torre San Bartolo takes its name from the ancient watch tower that has stood sentinel over this stretch of the Adriatic coast since the 15th century. For centuries the surrounding farmstead produced olive oil, wine and grain for the nobility of Pesaro. In 2008, the property was gently restored by its current owners — preserving original stone walls, hand-painted ceilings and terracotta floors while weaving in every modern comfort.</p>
<p>Today, Torre San Bartolo opens its gates exclusively to a single group of guests at a time. You will have the entire property to yourselves: three suites, the gardens, the infinity pool, the outdoor kitchen and the original cantina. There are no other guests, no queues for breakfast, no strangers by the pool. Only you and the hills.</p>
<p>Our philosophy is that of a private home, not a hotel. Breakfast arrives at whatever hour you choose. The cantina is stocked with Marche DOC wines selected from neighbours just down the road. If you would like a private chef, a guided truffle hunt or a sunrise yoga session beside the pool, we will arrange it. Everything is possible; nothing is mandatory.</p>';

seed_meta($about_id, 'about_story_text', $about_story);
seed_gallery($about_id, 'about_story_bg', [$EXTERIOR[1]]);
seed_gallery($about_id, 'about_carousel', [$INTERIOR[0], $TERRACE[0], $LANDSCAPE[0], $DINING[0], $LOUNGE[0], $EXTERIOR[2]]);

// ─────────────────────────────────────────────────────────────────────────────
// 3. SUITES PAGE (ID: 182)
// ─────────────────────────────────────────────────────────────────────────────
echo "Seeding suites page...\n";
$suites_id = 182;
set_post_thumbnail($suites_id, $BEDROOM[0]);

seed_meta($suites_id, 'hero_eyebrow', 'Accommodation');
seed_meta($suites_id, 'hero_subtitle', 'Three suites. One private estate. Entirely yours.');

// ─────────────────────────────────────────────────────────────────────────────
// 4. POOL PAGE (ID: 184)
// ─────────────────────────────────────────────────────────────────────────────
echo "Seeding pool page...\n";
$pool_id = 184;
set_post_thumbnail($pool_id, $POOL[0]);

seed_meta($pool_id, 'hero_eyebrow', 'The Pool');
seed_meta($pool_id, 'hero_subtitle', 'Infinity water, boundless views.');
seed_meta($pool_id, 'pool_cta_text', 'Request Availability');
seed_meta($pool_id, 'pool_cta_url',  '/contact/');
seed_gallery($pool_id, 'pool_gallery', [$POOL[0], $POOL[1], $POOL[2], $TERRACE[0], $TERRACE[1], $LANDSCAPE[1]]);

// ─────────────────────────────────────────────────────────────────────────────
// 5. EXPERIENCES PAGE (ID: 186)
// ─────────────────────────────────────────────────────────────────────────────
echo "Seeding experiences page...\n";
$exp_page_id = 186;
set_post_thumbnail($exp_page_id, $LANDSCAPE[0]);

seed_meta($exp_page_id, 'hero_eyebrow', 'Experiences');
seed_meta($exp_page_id, 'hero_subtitle', 'Taste, discover and explore the best of Le Marche.');

// ─────────────────────────────────────────────────────────────────────────────
// 6. GALLERY PAGE (ID: 188)
// ─────────────────────────────────────────────────────────────────────────────
echo "Seeding gallery page...\n";
$gallery_id = 188;
set_post_thumbnail($gallery_id, $EXTERIOR[0]);

seed_meta($gallery_id, 'hero_eyebrow', 'Gallery');
seed_gallery($gallery_id, 'photo_gallery', array_merge($EXTERIOR, $INTERIOR, $POOL, $LANDSCAPE, $LOUNGE, $TERRACE, $BEDROOM, $BATHROOM));

// ─────────────────────────────────────────────────────────────────────────────
// 7. CONTACT PAGE (ID: 190)
// ─────────────────────────────────────────────────────────────────────────────
echo "Seeding contact page...\n";
$contact_id = 190;
set_post_thumbnail($contact_id, $TERRACE[0]);

seed_meta($contact_id, 'hero_eyebrow',      'Get in Touch');
seed_meta($contact_id, 'hero_subtitle',     'We would love to host you. Tell us about your ideal stay.');
seed_meta($contact_id, 'contatti_telefono', '+39 0721 910540');
seed_meta($contact_id, 'contatti_email',    'info@torresanbartolo.it');
seed_meta($contact_id, 'contatti_orari',    "Mon – Sat: 09:00 – 19:00\nSun: 10:00 – 17:00\n\nCheck-in: from 15:00\nCheck-out: by 11:00");
seed_meta($contact_id, 'address',           "Torre San Bartolo\nVia Torre San Bartolo 1\n61100 Pesaro PU — Italy");
seed_meta($contact_id, 'maps_embed',        '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2872.0!2d12.893!3d43.985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sTorre+San+Bartolo%2C+Pesaro!5e0!3m2!1sen!2sit!4v1700000000000!5m2!1sen!2sit" width="100%" height="450" style="border:0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Torre San Bartolo on Google Maps"></iframe>');

// ─────────────────────────────────────────────────────────────────────────────
// 8. LOCATION PAGE (ID: 192)
// ─────────────────────────────────────────────────────────────────────────────
echo "Seeding location page...\n";
$location_id = 192;
set_post_thumbnail($location_id, $LANDSCAPE[2]);

seed_meta($location_id, 'hero_eyebrow',        'Getting Here');
seed_meta($location_id, 'address',             "Torre San Bartolo\nVia Torre San Bartolo 1\n61100 Pesaro PU — Italy");
seed_meta($location_id, 'indicazioni_stradali',
    '<h3>By Car</h3>
<p>Follow the SS16 Adriatica north towards Pesaro. Exit at <strong>Pesaro Nord</strong> and take the SP13 towards Gabicce Monte. Follow signs to Torre San Bartolo — the estate entrance is on the left after the second hairpin.</p>
<p>Journey times: Pesaro 15 min · Rimini 35 min · Bologna 1h 45min · Rome 4h</p>
<h3>By Train</h3>
<p>The nearest railway stations are <strong>Pesaro</strong> (14 km) and <strong>Gabicce Mare</strong> (6 km), both on the Bologna–Rimini–Ancona main line. We recommend booking a private transfer in advance — we can arrange this for you.</p>
<h3>By Air</h3>
<p>The closest airport is <strong>Federico Fellini International (RMI)</strong> in Rimini, 40 km away. Bologna Airport (BLQ) is 130 km and connects to all major European hubs. Pre-arranged private transfers are available on request.</p>');
seed_meta($location_id, 'maps_embed',
    '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2872.0!2d12.893!3d43.985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sTorre+San+Bartolo%2C+Pesaro!5e0!3m2!1sen!2sit!4v1700000000000!5m2!1sen!2sit" width="100%" height="450" style="border:0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Torre San Bartolo on Google Maps"></iframe>');

// ─────────────────────────────────────────────────────────────────────────────
// 9. FAQ PAGE (ID: 209) — will link to domanda_faq posts after they are created
// ─────────────────────────────────────────────────────────────────────────────
seed_meta(209, 'hero_eyebrow', 'FAQs');
set_post_thumbnail(209, $LANDSCAPE[1]);

// ─────────────────────────────────────────────────────────────────────────────
// 10. CAMERA CPT — translate existing posts to English
// ─────────────────────────────────────────────────────────────────────────────
echo "Updating camera posts to English...\n";

// Camera della Torre (ID: 98)
wp_update_post(['ID' => 98, 'post_title' => 'The Tower Suite', 'post_content' =>
    '<p>The Tower Suite occupies the upper floor of the original medieval watch tower. Exposed stone walls, a king-size bed dressed in Egyptian cotton and a private bathroom with rain shower and separate WC create a space that feels both ancient and luxurious. The suite\'s windows frame an uninterrupted panorama of the hills rolling down to the sea.</p>']);
seed_meta(98, 'camera_guests',   2);
seed_meta(98, 'camera_size',     '28 m²');
seed_meta(98, 'camera_beds',     'King-size double bed');
seed_meta(98, 'camera_bathroom', 'En-suite with stone rain shower and separate WC');
seed_meta(98, 'camera_features', "High-speed Wi-Fi\nAir conditioning\nSafe\nHair dryer\nOrganic bath products\nPanoramic hill views\nOriginal exposed beams");
seed_meta(98, 'camera_prezzo',   'from €160/night');
set_post_thumbnail(98, $BEDROOM[0]);

// Camera delle Colline (ID: 100)
wp_update_post(['ID' => 100, 'post_title' => 'The Hill Suite', 'post_content' =>
    '<p>Bright and airy, The Hill Suite opens onto a private terrace overlooking the estate\'s olive groves and the hills beyond. Warm terracotta floors, hand-painted timber shutters and a generous double bed make this the most romantic room in the house.</p>']);
seed_meta(100, 'camera_guests',   2);
seed_meta(100, 'camera_size',     '25 m²');
seed_meta(100, 'camera_beds',     'Queen-size double bed');
seed_meta(100, 'camera_bathroom', 'En-suite with rainfall shower');
seed_meta(100, 'camera_features', "High-speed Wi-Fi\nAir conditioning\nPrivate terrace\nSafe\nHair dryer\nOrganic bath products\nHill views\nBlackout shutters");
seed_meta(100, 'camera_prezzo',   'from €130/night');
set_post_thumbnail(100, $BEDROOM[1]);

// Camera Famiglia (ID: 101)
wp_update_post(['ID' => 101, 'post_title' => 'The Garden Suite', 'post_content' =>
    '<p>The Garden Suite is the largest room in the estate, purpose-designed for families or guests who simply love space. Two interconnected rooms open directly onto the private garden; the master features a king-size bed while a second room sleeps two more single beds — perfect for children or additional adults.</p>']);
seed_meta(101, 'camera_guests',   4);
seed_meta(101, 'camera_size',     '45 m²');
seed_meta(101, 'camera_beds',     'King-size bed + 2 single beds');
seed_meta(101, 'camera_bathroom', 'En-suite with double vanity, shower and bath');
seed_meta(101, 'camera_features', "High-speed Wi-Fi\nAir conditioning\nDirect garden access\nCot available on request\nSafe\nHair dryer\nOrganic bath products\nGarden & pool views");
seed_meta(101, 'camera_prezzo',   'from €200/night');
set_post_thumbnail(101, $BATHROOM[0]);

// ─────────────────────────────────────────────────────────────────────────────
// 11. ESPERIENZA CPT — create English experience posts
// ─────────────────────────────────────────────────────────────────────────────
echo "Creating esperienza posts...\n";

$experiences = [
    [
        'title'    => 'Truffle Hunting in the Hills',
        'content'  => '<p>Join an expert truffle hunter and his dog on a 3-hour foray through the oak forests of the Apennine foothills. Learn to read the landscape, discover how to identify and harvest the prized black Périgord and white Alba truffle, and finish with a tasting back at the villa paired with local Verdicchio wine.</p>',
        'excerpt'  => 'Forage for prized Marche truffles with an expert hunter and his dog, then taste your finds paired with local wine.',
        'duration' => 'Half day (approx. 3 hours)',
        'price'    => '€90 per person',
        'guests'   => 8,
        'features' => "Transport included\nTruffle tasting with wine pairing\nAll equipment provided\nPrivate booking for villa guests",
        'thumb'    => $LANDSCAPE[0],
        'gallery'  => [$LANDSCAPE[1], $LANDSCAPE[2], $DINING[0]],
    ],
    [
        'title'    => 'Marche Wine & Vineyard Tour',
        'content'  => '<p>The slopes surrounding Torre San Bartolo produce some of central Italy\'s most characterful wines — Verdicchio dei Castelli di Jesi, Rosso Conero and Bianchello del Metauro. Spend a full morning visiting two outstanding estates, walking their vineyards, meeting the winemakers and sampling library vintages not available in any shop.</p>',
        'excerpt'  => 'Explore two legendary Marche wine estates, walk the vineyards, and taste gallery vintages with the winemaker.',
        'duration' => 'Full morning (approx. 4 hours)',
        'price'    => '€85 per person',
        'guests'   => 8,
        'features' => "Private reservations at two estates\nVineyard tour with winemaker\nCellar tasting (5–6 wines)\nTransport included",
        'thumb'    => $LANDSCAPE[1],
        'gallery'  => [$LANDSCAPE[2], $DINING[0], $LOUNGE[0]],
    ],
    [
        'title'    => 'Marche Cooking Class',
        'content'  => '<p>A hands-on morning in the estate\'s stone farmhouse kitchen with a local chef who has cooked Marche cuisine his entire life. You will prepare a full four-course lunch — fresh handmade pasta, slow-braised rabbit, grilled seasonal vegetables and a classic crostata — then sit down together to eat what you have made, accompanied by estate wines.</p>',
        'excerpt'  => 'Learn the secrets of authentic Marche cuisine from a local chef, then sit down to enjoy the four-course meal you prepared.',
        'duration' => 'Half day (approx. 4 hours)',
        'price'    => '€110 per person',
        'guests'   => 8,
        'features' => "All ingredients included\nFull 4-course menu\nWine pairing\nRecipe booklet to take home\nPrivate to villa guests",
        'thumb'    => $DINING[0],
        'gallery'  => [$DINING[1], $LOUNGE[0], $INTERIOR[0]],
    ],
    [
        'title'    => 'Sunrise Yoga by the Pool',
        'content'  => '<p>Begin your day with a private 90-minute yoga session on the poolside terrace as the sun rises over the Adriatic. Our resident instructor tailors each session to the group\'s level and intention — whether you seek energising Vinyasa flow or a restorative Yin practice. Followed by fresh fruit, smoothies and coffee at the outdoor table.</p>',
        'excerpt'  => 'Private sunrise yoga on the infinity pool terrace, tailored to your group, followed by a light breakfast.',
        'duration' => '90 minutes + breakfast',
        'price'    => '€60 per person',
        'guests'   => 8,
        'features' => "All equipment provided\nPrivate certified instructor\nLight breakfast included\nCustomisable style and level",
        'thumb'    => $TERRACE[0],
        'gallery'  => [$TERRACE[1], $POOL[0], $LANDSCAPE[0]],
    ],
];

$exp_ids = [];
foreach ($experiences as $exp) {
    // Check if a post with this title already exists
    $existing = get_posts(['post_type' => 'esperienza', 'post_status' => 'any', 'title' => $exp['title'], 'numberposts' => 1]);
    if ($existing) {
        $eid = $existing[0]->ID;
        echo "  Updating existing: {$exp['title']} (ID:$eid)\n";
        wp_update_post(['ID' => $eid, 'post_title' => $exp['title'], 'post_content' => $exp['content'], 'post_excerpt' => $exp['excerpt'], 'post_status' => 'publish']);
    } else {
        $eid = wp_insert_post([
            'post_type'    => 'esperienza',
            'post_title'   => $exp['title'],
            'post_content' => $exp['content'],
            'post_excerpt' => $exp['excerpt'],
            'post_status'  => 'publish',
        ]);
        echo "  Created: {$exp['title']} (ID:$eid)\n";
    }
    seed_meta($eid, 'exp_duration',   $exp['duration']);
    seed_meta($eid, 'exp_price',      $exp['price']);
    seed_meta($eid, 'exp_max_guests', $exp['guests']);
    seed_meta($eid, 'exp_highlights', $exp['features']);
    set_post_thumbnail($eid, $exp['thumb']);
    seed_gallery($eid, 'exp_gallery', $exp['gallery']);
    $exp_ids[] = $eid;
}

// ─────────────────────────────────────────────────────────────────────────────
// 12. DOMANDA_FAQ CPT — translate existing posts to English + add 2 new
// ─────────────────────────────────────────────────────────────────────────────
echo "Updating FAQ posts to English...\n";

$faq_data = [
    78 => [
        'title'   => 'What is the cancellation policy?',
        'content' => '<p>Free cancellation up to 14 days before check-in. For cancellations between 14 and 7 days, 50% of the total booking is charged. No-shows or cancellations within 7 days of arrival are charged in full.</p>',
    ],
    79 => [
        'title'   => 'What are the check-in and check-out times?',
        'content' => '<p>Check-in is available from 15:00 and we ask guests to check out by 11:00. Early check-in and late check-out may be arranged subject to availability — please get in touch before your arrival.</p>',
    ],
    80 => [
        'title'   => 'Is Torre San Bartolo suitable for children?',
        'content' => '<p>Absolutely. The estate is wonderful for families: the large garden, infinity pool (with a shallow shelf for small children), indoor fireplaces and outdoor kitchen make it an ideal retreat for all ages. Travel cots and high chairs are available on request.</p>',
    ],
    81 => [
        'title'   => 'Are pets welcome?',
        'content' => '<p>Small and medium-sized dogs (maximum two per booking) are very welcome. We simply ask that they are kept off the beds and that any garden messes are cleaned up. Please let us know when booking so we can prepare accordingly.</p>',
    ],
    82 => [
        'title'   => 'Is the property rented exclusively?',
        'content' => '<p>Yes. Torre San Bartolo is available for exclusive hire only — you will never share the estate with other guests. All three suites, the garden, pool, cantina and outdoor kitchen are entirely yours for the duration of your stay.</p>',
    ],
    83 => [
        'title'   => 'Is parking available?',
        'content' => '<p>There is a private gated car park within the estate grounds with space for up to four vehicles, available to guests free of charge.</p>',
    ],
    84 => [
        'title'   => 'Is the villa fully equipped and self-catering?',
        'content' => '<p>The villa comes fully equipped with premium bed linens, towels, bath robes and pool towels. The farmhouse kitchen is stocked with local olive oil, condiments and coffee. A complimentary welcome basket with fresh bread, local cheeses and a bottle of estate wine greets every arrival. A full grocery shopping service and private chef can be arranged for an additional fee.</p>',
    ],
    85 => [
        'title'   => 'Is Wi-Fi available throughout the property?',
        'content' => '<p>Yes. High-speed Wi-Fi (300 Mbps) is available throughout the villa, suites and all outdoor terraces including the pool area.</p>',
    ],
];

// Two additional FAQ entries
$extra_faqs = [
    [
        'title'   => 'How far is the nearest beach?',
        'content' => '<p>The estate sits 3 km inland from the Adriatic coast. The nearest sandy beach — Baia Flaminia — is a 10-minute drive. Pesaro\'s main beach promenade is 15 minutes away. We can arrange private beach transfers on request.</p>',
    ],
    [
        'title'   => 'Can you arrange additional services during our stay?',
        'content' => '<p>Absolutely. Our concierge service can organise private chef dinners, truffle hunts, winery tours, cycling excursions, in-villa massage and spa treatments, horseback riding, boat day trips and much more. Simply let us know in advance and we will take care of everything.</p>',
    ],
];

$faq_ids = [];
foreach ($faq_data as $post_id => $data) {
    wp_update_post(['ID' => $post_id, 'post_title' => $data['title'], 'post_content' => $data['content'], 'post_status' => 'publish']);
    $faq_ids[] = $post_id;
    echo "  Updated FAQ #$post_id\n";
}
foreach ($extra_faqs as $faq) {
    $existing = get_posts(['post_type' => 'domanda_faq', 'post_status' => 'any', 'title' => $faq['title'], 'numberposts' => 1]);
    if ($existing) {
        $fid = $existing[0]->ID;
        wp_update_post(['ID' => $fid, 'post_title' => $faq['title'], 'post_content' => $faq['content']]);
    } else {
        $fid = wp_insert_post(['post_type' => 'domanda_faq', 'post_title' => $faq['title'], 'post_content' => $faq['content'], 'post_status' => 'publish']);
        echo "  Created FAQ: {$faq['title']} (ID:$fid)\n";
    }
    $faq_ids[] = $fid;
}

// Assign FAQ posts to FAQ page via Pods relationship
$faq_page_id = 209;
seed_meta($faq_page_id, 'hero_eyebrow', 'FAQs');

// Use Pods API to correctly save the multi-pick relationship
if (function_exists('pods')) {
    $faq_pod = pods('page', $faq_page_id);
    if ($faq_pod) {
        $faq_pod->save(['faq_pagina' => $faq_ids]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 13. Update home page faq_pagina and section URLs to new English slugs
// ─────────────────────────────────────────────────────────────────────────────
seed_meta($home_id, 'hp_sa_cta_url',  '/suites/');
seed_meta($home_id, 'hp_sb_cta_url',  '/location/');
seed_meta($home_id, 'hp_sc_cta_url',  '/pool/');
seed_meta($home_id, 'hero_cta_url',   '/contact/');
// faq on home page (links a FAQ item)
if (!empty($faq_ids)) {
    seed_meta($home_id, 'faq_pagina', $faq_ids[0]);
}

// ─────────────────────────────────────────────────────────────────────────────
// Done
// ─────────────────────────────────────────────────────────────────────────────
update_option($guard, true);
echo "\nAll content seeded successfully.\n";
