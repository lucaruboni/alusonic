<?php
/**
 * Template Name: Experiences
 * Page slug: experiences
 *
 * Desktop: experience hero pairs (50vw × 100vh)
 * Mobile/tablet: 100vw × 100vh stacked
 */

get_header();

$pid      = get_the_ID();
$hero_img = get_the_post_thumbnail_url($pid, 'hero-bg');
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: __('Explore', 'torresan-bnb');
$subtitle = torresan_field('hero_subtitle', $pid);

$ordered_ids = torresan_related_post_ids('experiences_order', $pid);

if (! empty($ordered_ids)) {
    $experiences = get_posts([
        'post_type'        => 'esperienza',
        'posts_per_page'   => -1,
        'post_status'      => 'publish',
        'post__in'         => $ordered_ids,
        'orderby'          => 'post__in',
        'suppress_filters' => false,
    ]);
} else {
    $experiences = get_posts([
        'post_type'        => 'esperienza',
        'posts_per_page'   => -1,
        'post_status'      => 'publish',
        'orderby'          => 'menu_order',
        'order'            => 'ASC',
        'suppress_filters' => false,
    ]);
}
?>

<main>

    <!-- ── Page Identification Hero ── -->
    <section class="page-hero"<?php echo torresan_hero_style_attr(); ?>>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <h1><?php the_title(); ?></h1>
            <?php if ($subtitle) : ?>
                <p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <!-- ── Intro text (sfondo bianco, stesso stile About story / Pool) ── -->
    <?php if (have_posts()) : while (have_posts()) : the_post();
        if (trim(get_the_content())) : ?>
    <section class="about-story about-story-page_about">
        <div class="about-story-overlay about-story-overlay-page_about"></div>
        <div class="about-story-content container">
            <div class="about-story-page_about wysiwyg-content">
                <?php the_content(); ?>
            </div>
        </div>
    </section>
    <?php endif; endwhile; endif; ?>

    <!-- ── Experience Hero Pairs ── -->
    <?php if (! empty($experiences)) : ?>
    <div class="suites-list">
        <?php foreach ($experiences as $exp) :
            $img        = get_the_post_thumbnail_url($exp->ID, 'hero-bg');
            $img_mobile = torresan_image_url('exp_hero_mobile', 'hero-bg', $exp->ID);
            $duration  = get_post_meta($exp->ID, 'exp_duration', true);
            $price     = get_post_meta($exp->ID, 'exp_price', true);
            $max_g     = get_post_meta($exp->ID, 'exp_max_guests', true);
            $excerpt   = get_the_excerpt($exp->ID);
        ?>
        <a href="<?php echo esc_url(get_permalink($exp->ID)); ?>" class="suite-hero experience-hero"<?php echo torresan_bg_vars_attr('suite', $img, $img_mobile); ?> aria-label="<?php echo esc_attr(get_the_title($exp->ID)); ?>">
            <div class="suite-hero-overlay"></div>
            <div class="suite-hero-content">
                <p class="eyebrow suite-eyebrow"><?php esc_html_e('Experience', 'torresan-bnb'); ?></p>
                <h2><?php echo esc_html(get_the_title($exp->ID)); ?></h2>
                <?php if ($excerpt) : ?>
                    <p class="suite-excerpt"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>
                <div class="suite-meta">
                    <?php if ($duration) : ?><span><?php echo esc_html($duration); ?></span><?php endif; ?>
                    <?php if ($max_g)    : ?><span><?php printf(esc_html__('Up to %s guests', 'torresan-bnb'), esc_html($max_g)); ?></span><?php endif; ?>
                    <?php if ($price)    : ?><span class="suite-price"><?php echo esc_html($price); ?></span><?php endif; ?>
                </div>
                <span class="suite-discover"><?php esc_html_e('Discover →', 'torresan-bnb'); ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else : ?>
    <section class="section-content">
        <div class="container">
            <p style="text-align:center;opacity:.6"><?php esc_html_e('No experiences listed yet.', 'torresan-bnb'); ?></p>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
