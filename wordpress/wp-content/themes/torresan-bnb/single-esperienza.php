<?php
/**
 * Single Experience template
 * Mirrors the About page layout: page hero → story/content → gallery
 */

get_header();

$pid         = get_the_ID();
$hero_img    = get_the_post_thumbnail_url($pid, 'hero-bg');
$gallery_ids = torresan_gallery_ids('exp_gallery', $pid);
$duration    = get_post_meta($pid, 'exp_duration', true);
$price       = get_post_meta($pid, 'exp_price', true);
$max_guests  = get_post_meta($pid, 'exp_max_guests', true);
$highlights  = get_post_meta($pid, 'exp_highlights', true);
?>

<main>

    <!-- ── Page Identification Hero ── -->
    <section class="page-hero"<?php echo torresan_hero_style_attr($pid, 'exp_hero_mobile'); ?>>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <p class="eyebrow"><?php esc_html_e('Experience', 'torresan-bnb'); ?></p>
            <h1><?php the_title(); ?></h1>
        </div>
    </section>

    <!-- ── Experience Details ── -->
    <section class="section-content">
        <div class="container">
            <div class="experience-detail">
                <div class="experience-body wysiwyg-content">
                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; endif; ?>
                </div>

                <?php if ($duration || $price || $max_guests || $highlights) : ?>
                <aside class="camera-sidebar">
                    <h3><?php esc_html_e('Details', 'torresan-bnb'); ?></h3>
                    <?php if ($price) : ?>
                        <p class="camera-price"><?php echo esc_html($price); ?></p>
                    <?php endif; ?>
                    <?php if ($duration) : ?>
                        <p><strong><?php esc_html_e('Duration:', 'torresan-bnb'); ?></strong> <?php echo esc_html($duration); ?></p>
                    <?php endif; ?>
                    <?php if ($max_guests) : ?>
                        <p><strong><?php esc_html_e('Max Guests:', 'torresan-bnb'); ?></strong> <?php echo esc_html($max_guests); ?></p>
                    <?php endif; ?>
                    <?php if ($highlights) : ?>
                        <h4><?php esc_html_e('Highlights', 'torresan-bnb'); ?></h4>
                        <ul class="camera-features">
                            <?php foreach (array_filter(array_map('trim', explode("\n", $highlights))) as $hl) : ?>
                                <li><?php echo esc_html($hl); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn" style="margin-top:1.5rem"><?php esc_html_e('Book This Experience', 'torresan-bnb'); ?></a>
                </aside>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ── Gallery ── -->
    <?php if (! empty($gallery_ids)) : ?>
    <section class="section-content alt">
        <div class="container">
            <h2 style="text-align:center;margin-bottom:2rem"><?php esc_html_e('Gallery', 'torresan-bnb'); ?></h2>
            <div class="camera-gallery" data-lightbox-group="exp-gallery-<?php echo $pid; ?>">
                <?php foreach ($gallery_ids as $i => $img_id) :
                    $url_t = wp_get_attachment_image_url($img_id, $i === 0 ? 'large' : 'section-card');
                    $url_f = wp_get_attachment_image_url($img_id, 'large');
                    $alt   = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                    if (! $url_t) continue;
                    $cls = ($i === 0) ? 'camera-gallery-item camera-gallery-featured' : 'camera-gallery-item';
                ?>
                    <div class="<?php echo $cls; ?>" data-lightbox="<?php echo esc_url($url_f ?: $url_t); ?>">
                        <img src="<?php echo esc_url($url_t); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
