<?php
/**
 * Template Name: Location / Getting Here
 * Page slug: location
 *
 * Hero → directions content → map embed.
 */

get_header();

$pid      = get_the_ID();
$hero_img = get_the_post_thumbnail_url($pid, 'hero-bg');
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: 'Getting Here';
$settings = torresan_get_site_settings();
$contact_page = get_page_by_path('contact');
$contact_id = $contact_page ? (int) $contact_page->ID : 0;
$address  = torresan_field('address', $pid) ?: ($contact_id ? torresan_field('address', $contact_id) : '') ?: ($settings['footer_address'] ?? '');
$phone    = torresan_field('contatti_telefono', $pid) ?: ($contact_id ? torresan_field('contatti_telefono', $contact_id) : '') ?: ($settings['footer_phone'] ?? '');
$email    = torresan_field('contatti_email', $pid) ?: ($contact_id ? torresan_field('contatti_email', $contact_id) : '') ?: ($settings['footer_email'] ?? '');
$hours    = torresan_field('contatti_orari', $pid) ?: ($contact_id ? torresan_field('contatti_orari', $contact_id) : '');
$how_to   = torresan_field('indicazioni_stradali', $pid);
$page_content = get_post_field('post_content', $pid);
$maps_raw = torresan_field('maps_embed', $pid) ?: ($contact_id ? torresan_field('maps_embed', $contact_id) : '');
$default_map = '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2882.7018108519846!2d12.568986475661868!3d43.73752104697389!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x132cf796025dc18f%3A0xdaea035c60e4a0f5!2sTorre%20San%20Bartolo!5e0!3m2!1sit!2sit!4v1779551573161!5m2!1sit!2sit" width="100%" height="520" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
$map_html = $maps_raw ?: $default_map;
$allowed_map_html = [
    'iframe' => [
        'src' => true,
        'width' => true,
        'height' => true,
        'style' => true,
        'allowfullscreen' => true,
        'loading' => true,
        'referrerpolicy' => true,
    ],
];
$map_html_safe = wp_kses($map_html, $allowed_map_html);
$static_map_url = torresan_image_url('map_static_image', 'hero-bg', $pid)
    ?: ($contact_id ? torresan_image_url('map_static_image', 'hero-bg', $contact_id) : '');
?>

<main>

    <!-- ── Page Identification Hero ── -->
    <section class="page-hero"<?php echo torresan_hero_style_attr(); ?>>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <h1><?php the_title(); ?></h1>
        </div>
    </section>

    <!-- ── Location hero map (70/30, full-height) ── -->
    <section class="location-hero-map">
        <div class="location-map location-map--hero" data-map-html="<?php echo esc_attr(base64_encode($map_html_safe)); ?>">
            <?php if ($static_map_url) : ?>
                <img class="location-map-fallback" src="<?php echo esc_url($static_map_url); ?>" alt="<?php esc_attr_e('Map location (interactive map available after accepting cookies)', 'torresan-bnb'); ?>">
            <?php else : ?>
                <div class="location-map-fallback location-map-fallback--placeholder" aria-hidden="true"></div>
            <?php endif; ?>
        </div>

        <aside class="location-contact-panel location-contact-panel--hero">
            <h2><?php esc_html_e('Location', 'torresan-bnb'); ?></h2>
            <ul class="location-contact-list">
                <?php if ($address) : ?>
                <li class="location-contact-item">
                    <strong><?php esc_html_e('Address', 'torresan-bnb'); ?></strong>
                    <address class="location-address"><?php echo esc_html($address); ?></address>
                    <a href="https://maps.google.com/?q=<?php echo esc_attr(urlencode($address)); ?>" target="_blank" rel="noopener noreferrer nofollow" class="btn btn-outline"><?php esc_html_e('Open in Maps', 'torresan-bnb'); ?></a>
                </li>
                <?php endif; ?>
                <?php if ($phone) : ?>
                <li class="location-contact-item">
                    <strong><?php esc_html_e('Phone', 'torresan-bnb'); ?></strong>
                    <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                </li>
                <?php endif; ?>
                <?php if ($email) : ?>
                <li class="location-contact-item">
                    <strong><?php esc_html_e('Email', 'torresan-bnb'); ?></strong>
                    <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                </li>
                <?php endif; ?>
                <?php if ($hours) : ?>
                <li class="location-contact-item">
                    <strong><?php esc_html_e('Hours', 'torresan-bnb'); ?></strong>
                    <?php echo nl2br(esc_html($hours)); ?>
                </li>
                <?php endif; ?>
                <?php if (! empty($settings['checkin_time']) || ! empty($settings['checkout_time'])) : ?>
                <li class="location-contact-item">
                    <strong><?php esc_html_e('Check-in / Check-out', 'torresan-bnb'); ?></strong>
                    <?php if (! empty($settings['checkin_time'])) : ?>
                        <span><?php esc_html_e('Check-in:', 'torresan-bnb'); ?> <?php echo esc_html($settings['checkin_time']); ?></span><br>
                    <?php endif; ?>
                    <?php if (! empty($settings['checkout_time'])) : ?>
                        <span><?php esc_html_e('Check-out:', 'torresan-bnb'); ?> <?php echo esc_html($settings['checkout_time']); ?></span>
                    <?php endif; ?>
                </li>
                <?php endif; ?>
            </ul>
        </aside>
    </section>

    <?php if ($how_to) : ?>
    <section class="section-content">
        <div class="container">
            <div class="wysiwyg-content location-directions">
                <?php echo wp_kses_post($how_to); ?>
            </div>
        </div>
    </section>
    <?php elseif (trim(wp_strip_all_tags((string) $page_content))) : ?>
    <section class="section-content">
        <div class="container">
            <div class="wysiwyg-content location-directions"><?php echo apply_filters('the_content', $page_content); ?></div>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
