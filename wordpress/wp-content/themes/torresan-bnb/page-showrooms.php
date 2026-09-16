<?php
/**
 * Template Name: Showroom
 * Two locations: Rimini (sede) and Carrara.
 */

get_header();

$pid      = get_the_ID();
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: __('Showroom', 'torresan-bnb');
$subtitle = torresan_field('hero_subtitle', $pid);

$rimini_title   = torresan_field('showroom_rimini_title', $pid) ?: __('Rimini · Head office', 'torresan-bnb');
$rimini_info    = alusonic_parse_pairs(torresan_field('showroom_rimini_info', $pid));
$rimini_gallery = torresan_gallery_ids('showroom_rimini_gallery', $pid);

$carrara_title   = torresan_field('showroom_carrara_title', $pid) ?: __('Carrara', 'torresan-bnb');
$carrara_info    = alusonic_parse_pairs(torresan_field('showroom_carrara_info', $pid));
$carrara_gallery = torresan_gallery_ids('showroom_carrara_gallery', $pid);

$partners = [];
foreach (preg_split('/\r\n|\r|\n/', torresan_field('showroom_partners', $pid)) as $line) {
    $line = trim($line);
    if ($line === '') { continue; }
    $parts = array_map('trim', explode('|', $line, 3));
    $partners[] = ['title' => $parts[0] ?? '', 'info' => $parts[1] ?? '', 'url' => $parts[2] ?? ''];
}
?>

<main>
    <section class="page-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <?php if ($eyebrow) : ?><p class="eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
            <h1><?php the_title(); ?></h1>
            <?php if ($subtitle) : ?><p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
        </div>
    </section>

    <section class="section">
        <div class="container split-2">
            <?php if ($rimini_gallery) : $first = array_shift($rimini_gallery); $img = wp_get_attachment_image_url($first, 'hero-bg'); ?>
            <div class="split-media"><?php if ($img) : ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($rimini_title); ?>" loading="lazy"><?php endif; ?></div>
            <?php endif; ?>
            <div class="split-body">
                <p class="eyebrow"><?php esc_html_e('Italy', 'torresan-bnb'); ?></p>
                <h2><?php echo esc_html($rimini_title); ?></h2>
                <?php if ($rimini_info) : ?>
                <div class="contact-block">
                    <?php foreach ($rimini_info as [$label, $value]) : ?>
                        <p><strong><?php echo esc_html($label); ?>:</strong> <?php echo wp_kses_post(nl2br(esc_html($value))); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <a class="btn" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Book a visit', 'torresan-bnb'); ?></a>
            </div>
        </div>
        <?php if ($rimini_gallery) : ?>
        <div class="container">
            <div class="photo-gallery" data-lightbox-group="showroom-rimini" style="margin-top:40px">
                <?php foreach ($rimini_gallery as $img_id) :
                    $thumb = wp_get_attachment_image_url($img_id, 'section-card');
                    $full  = wp_get_attachment_image_url($img_id, 'full');
                    if (! $thumb) continue;
                ?>
                <div class="gallery-item" data-lightbox="<?php echo esc_url($full ?: $thumb); ?>">
                    <img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <section class="section section-alt">
        <div class="container split-2">
            <div class="split-body">
                <p class="eyebrow"><?php esc_html_e('Italy', 'torresan-bnb'); ?></p>
                <h2><?php echo esc_html($carrara_title); ?></h2>
                <?php if ($carrara_info) : ?>
                <div class="contact-block">
                    <?php foreach ($carrara_info as [$label, $value]) : ?>
                        <p><strong><?php echo esc_html($label); ?>:</strong> <?php echo wp_kses_post(nl2br(esc_html($value))); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($carrara_gallery) : $first = array_shift($carrara_gallery); $img = wp_get_attachment_image_url($first, 'hero-bg'); ?>
            <div class="split-media"><?php if ($img) : ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($carrara_title); ?>" loading="lazy"><?php endif; ?></div>
            <?php endif; ?>
        </div>
        <?php if ($carrara_gallery) : ?>
        <div class="container">
            <div class="photo-gallery" data-lightbox-group="showroom-carrara" style="margin-top:40px">
                <?php foreach ($carrara_gallery as $img_id) :
                    $thumb = wp_get_attachment_image_url($img_id, 'section-card');
                    $full  = wp_get_attachment_image_url($img_id, 'full');
                    if (! $thumb) continue;
                ?>
                <div class="gallery-item" data-lightbox="<?php echo esc_url($full ?: $thumb); ?>">
                    <img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <?php if ($partners) : ?>
    <section class="section">
        <div class="container">
            <div class="section-head-center">
                <p class="eyebrow"><?php esc_html_e('Worldwide', 'torresan-bnb'); ?></p>
                <h2><?php esc_html_e('Partner dealers', 'torresan-bnb'); ?></h2>
                <p class="hero-subtitle"><?php esc_html_e('Alusonic is also distributed through selected dealers outside Italy.', 'torresan-bnb'); ?></p>
            </div>
            <div class="artist-cards" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr))">
                <?php foreach ($partners as $p) : ?>
                <div class="contact-block">
                    <div class="contact-block-title"><?php echo esc_html($p['title']); ?></div>
                    <p><?php echo esc_html($p['info']); ?></p>
                    <?php if ($p['url']) : ?><p><a class="is-accent" href="<?php echo esc_url($p['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html(parse_url($p['url'], PHP_URL_HOST) ?: $p['url']); ?> ↗</a></p><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="cta-band section-alt">
        <div class="container">
            <h2><?php esc_html_e('We look forward to seeing you', 'torresan-bnb'); ?></h2>
            <p><?php esc_html_e('Visits by appointment: write to us to arrange a meeting at the showroom nearest to you.', 'torresan-bnb'); ?></p>
            <a class="btn btn-lg" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Get in touch', 'torresan-bnb'); ?></a>
        </div>
    </section>
</main>

<?php get_footer(); ?>
