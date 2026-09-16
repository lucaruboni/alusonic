<?php
/**
 * Template Name: Galleria
 * Full photo gallery aggregated from every published model, with
 * scroll+mouse parallax, enter/exit reveal, and fullscreen lightbox.
 */

get_header();

$pid      = get_the_ID();
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: __('Gallery', 'torresan-bnb');
$subtitle = torresan_field('hero_subtitle', $pid);

$model_ids = alusonic_model_ids();

$photos = [];
foreach ($model_ids as $mid) {
    $title = get_the_title($mid);
    foreach (torresan_gallery_ids('model_gallery', $mid) as $img_id) {
        $photos[] = ['id' => $img_id, 'caption' => $title];
    }
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

    <section class="section-tight">
        <div class="container">
            <?php if ($photos) : ?>
            <div class="parallax-gallery" data-lightbox-group="site-gallery">
                <?php foreach ($photos as $p) :
                    $thumb = wp_get_attachment_image_url($p['id'], 'section-card');
                    $full  = wp_get_attachment_image_url($p['id'], 'full');
                    if (! $thumb) { continue; }
                ?>
                <div class="parallax-item" data-lightbox="<?php echo esc_url($full ?: $thumb); ?>">
                    <img class="parallax-item-media" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($p['caption']); ?>" loading="lazy">
                    <div class="parallax-item-caption"><?php echo esc_html($p['caption']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <p style="text-align:center;color:var(--muted,#999)"><?php esc_html_e('No photos available at the moment.', 'torresan-bnb'); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <section class="cta-band section-alt">
        <div class="container">
            <h2><?php esc_html_e('Want to see an instrument in person?', 'torresan-bnb'); ?></h2>
            <p><?php esc_html_e('Book a showroom visit, in Rimini or Carrara.', 'torresan-bnb'); ?></p>
            <a class="btn btn-lg" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Get in touch', 'torresan-bnb'); ?></a>
        </div>
    </section>
</main>

<?php get_footer(); ?>
