<?php
/**
 * Template Name: Gallery
 * Page slug: gallery
 *
 * Hero identification → masonry photo grid with lightbox.
 * Also handles old slug 'galleria'.
 */

get_header();

$pid         = get_the_ID();
$hero_img    = get_the_post_thumbnail_url($pid, 'hero-bg');
$eyebrow     = torresan_field('hero_eyebrow', $pid) ?: 'Gallery';
$gallery_ids = torresan_gallery_ids('gallery_photos', $pid);
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

    <!-- ── Full Gallery Grid with Lightbox ── -->
    <?php if (! empty($gallery_ids)) : ?>
    <section class="section-content">
        <div class="gallery-masonry" data-lightbox-group="gallery-main">
            <?php foreach ($gallery_ids as $i => $img_id) :
                $url_thumb = wp_get_attachment_image_url($img_id, 'section-card');
                $url_full  = wp_get_attachment_image_url($img_id, 'large');
                $alt       = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                if (! $url_thumb) continue;
                // Vary aspect ratios for visual rhythm
                $span_class = ($i % 7 === 0) ? 'gallery-masonry-wide' : (($i % 5 === 0) ? 'gallery-masonry-tall' : '');
            ?>
            <div class="gallery-masonry-item <?php echo esc_attr($span_class); ?>" data-lightbox="<?php echo esc_url($url_full ?: $url_thumb); ?>">
                <img src="<?php echo esc_url($url_thumb); ?>" alt="<?php echo esc_attr($alt); ?>" loading="<?php echo $i < 6 ? 'eager' : 'lazy'; ?>">
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php else : ?>
    <section class="section-content">
        <div class="container">
            <p style="text-align:center;opacity:.6"><?php esc_html_e('Gallery coming soon.', 'torresan-bnb'); ?></p>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>

