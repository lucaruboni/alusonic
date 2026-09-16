<?php
/**
 * Template Name: Riparazioni
 * Setup & repair service page.
 */

get_header();

$pid      = get_the_ID();
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: __('Repairs', 'torresan-bnb');
$subtitle = torresan_field('hero_subtitle', $pid);

$hero_img  = torresan_image_url('repairs_hero_image', 'hero-bg', $pid);
$intro_img = torresan_image_url('repairs_intro_image', 'hero-bg', $pid);
$text      = torresan_field('repairs_text', $pid);
$service_lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', torresan_field('repairs_services', $pid)))));
$gallery   = torresan_gallery_ids('repairs_gallery', $pid);

$contact_page = torresan_localized_page('contact', 'contatti');
$contact_url  = $contact_page ? get_permalink($contact_page->ID) : torresan_booking_url();
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

    <?php if ($hero_img) : ?>
    <section class="container">
        <div class="repairs-jumbotron">
            <img src="<?php echo esc_url($hero_img); ?>" alt="<?php esc_attr_e('The Alusonic workshop', 'torresan-bnb'); ?>" loading="lazy">
        </div>
    </section>
    <?php endif; ?>

    <section class="section-tight">
        <div class="container split-2">
            <div class="split-body">
                <?php if ($text) : ?>
                <div class="wysiwyg-content"><?php echo wp_kses_post(wpautop($text)); ?></div>
                <?php endif; ?>

                <?php if ($service_lines) : ?>
                <div class="process-list" style="margin-top:2.5rem">
                    <?php foreach ($service_lines as $i => $line) : ?>
                    <div class="process-step">
                        <div class="step-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></div>
                        <div class="step-title"><?php echo esc_html($line); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($intro_img) : ?>
            <div class="split-media"><img src="<?php echo esc_url($intro_img); ?>" alt="" loading="lazy"></div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($gallery) : ?>
    <section class="section-tight section-alt">
        <div class="container">
            <div class="section-head-center">
                <p class="eyebrow"><?php esc_html_e('Our services', 'torresan-bnb'); ?></p>
                <h2><?php esc_html_e('Where instruments are born and reborn', 'torresan-bnb'); ?></h2>
            </div>
            <div class="photo-gallery photo-gallery--wide" data-lightbox-group="repairs-gallery">
                <?php foreach ($gallery as $img_id) :
                    $thumb = wp_get_attachment_image_url($img_id, 'section-card');
                    $full  = wp_get_attachment_image_url($img_id, 'full');
                    if (! $thumb) continue;
                ?>
                <div class="gallery-item gallery-item--wide" data-lightbox="<?php echo esc_url($full ?: $thumb); ?>">
                    <img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="cta-band section-alt">
        <div class="container">
            <h2><?php esc_html_e('Put your instrument in professional hands', 'torresan-bnb'); ?></h2>
            <p><?php esc_html_e('Setup, maintenance and repair for all makes of guitars, basses and amplifiers.', 'torresan-bnb'); ?></p>
            <a class="btn btn-lg" href="<?php echo esc_url($contact_url); ?>"><?php esc_html_e('Request Information', 'torresan-bnb'); ?></a>
        </div>
    </section>
</main>

<?php get_footer(); ?>
