<?php
/**
 * Single Model (internal post type key: camera).
 */

get_header();

while (have_posts()) : the_post();
    $pid = get_the_ID();

    $category   = torresan_field('model_category', $pid);
    $lead       = torresan_field('model_lead', $pid);
    $specs      = alusonic_parse_pairs(torresan_field('model_specs', $pid));
    $hero_img   = get_the_post_thumbnail_url($pid, 'product-shot');
    $gallery    = torresan_gallery_ids('model_gallery', $pid);
    $detail_ttl = torresan_field('model_detail_title', $pid);
    $detail_img = torresan_image_url('model_detail_image', 'product-shot', $pid);
    $detail_txt = get_post_field('post_content', $pid);

    $models_page = torresan_localized_page('models', 'modelli');
    $models_url  = $models_page ? get_permalink($models_page->ID) : home_url('/models/');
?>

<main>
    <section class="container model-back">
        <a href="<?php echo esc_url($models_url); ?>">&larr; <?php esc_html_e('All models', 'torresan-bnb'); ?></a>
    </section>

    <section class="container model-hero">
        <div class="model-hero-media" data-type="<?php echo esc_attr(alusonic_model_type($pid)); ?>">
            <div class="model-hero-tilt">
                <?php if ($hero_img) : ?>
                    <img src="<?php echo esc_url($hero_img); ?>" alt="<?php the_title_attribute(); ?>">
                <?php endif; ?>
            </div>
        </div>
        <div class="model-hero-body">
            <?php if ($category) : ?><p class="eyebrow"><?php echo esc_html($category); ?></p><?php endif; ?>
            <h1><?php the_title(); ?></h1>
            <?php if ($lead) : ?><p class="model-lead"><?php echo esc_html($lead); ?></p><?php endif; ?>

            <?php if ($specs) : ?>
            <details class="spec-more">
                <summary><?php esc_html_e('Technical specification', 'torresan-bnb'); ?></summary>
                <div class="spec-table">
                    <?php foreach ($specs as [$label, $value]) : ?>
                    <div class="spec-row">
                        <span class="spec-label"><?php echo esc_html($label); ?></span>
                        <span class="spec-value"><?php echo esc_html($value); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </details>
            <?php endif; ?>

            <a class="btn" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Request a Quote', 'torresan-bnb'); ?></a>
        </div>
    </section>

    <?php if ($gallery) : ?>
    <section class="section-alt" style="padding:80px 0">
        <div class="container">
            <div class="section-head-center">
                <p class="eyebrow"><?php esc_html_e('Gallery', 'torresan-bnb'); ?></p>
                <h2><?php esc_html_e('Every detail', 'torresan-bnb'); ?></h2>
            </div>
            <div class="parallax-gallery" data-lightbox-group="model-<?php echo esc_attr($pid); ?>">
                <?php foreach ($gallery as $img_id) :
                    $thumb = wp_get_attachment_image_url($img_id, 'section-card');
                    $full  = wp_get_attachment_image_url($img_id, 'full');
                    if (! $thumb) continue;
                ?>
                <div class="parallax-item" data-lightbox="<?php echo esc_url($full ?: $thumb); ?>">
                    <img class="parallax-item-media" src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($detail_ttl || trim(wp_strip_all_tags((string) $detail_txt))) : ?>
    <section class="section">
        <div class="container split-2">
            <div class="split-body">
                <p class="eyebrow"><?php esc_html_e('Construction details', 'torresan-bnb'); ?></p>
                <?php if ($detail_ttl) : ?><h2><?php echo esc_html($detail_ttl); ?></h2><?php endif; ?>
                <div class="wysiwyg-content"><?php echo apply_filters('the_content', $detail_txt); ?></div>
            </div>
            <?php if ($detail_img) : ?>
            <div class="split-media is-contain"><img src="<?php echo esc_url($detail_img); ?>" alt="" loading="lazy"></div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="cta-band section-alt">
        <div class="container">
            <h2><?php esc_html_e('Want to try it in person?', 'torresan-bnb'); ?></h2>
            <p><?php esc_html_e('Book a trial in our showroom, or request a quote for a custom configuration.', 'torresan-bnb'); ?></p>
            <a class="btn btn-lg" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Get in touch', 'torresan-bnb'); ?></a>
        </div>
    </section>
</main>

<?php
endwhile;
get_footer();
