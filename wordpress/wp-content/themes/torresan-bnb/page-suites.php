<?php
/**
 * Template Name: Suites
 * Page slug: suites
 */

get_header();

$pid      = get_the_ID();
$hero_img = get_the_post_thumbnail_url($pid, 'hero-bg');
$eyebrow      = torresan_field('hero_eyebrow', $pid) ?: __('Accommodation', 'torresan-bnb');
$subtitle     = torresan_field('hero_subtitle', $pid);
$intro_text   = torresan_field('suites_intro_text', $pid);

$suites = get_posts([
    'post_type'      => 'camera',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
]);
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

    <!-- ── Intro text (sfondo bianco, stesso stile About story) ── -->
    <?php if (trim(wp_strip_all_tags((string) $intro_text))) : ?>
    <section class="about-story about-story-page_about">
        <div class="about-story-overlay about-story-overlay-page_about"></div>
        <div class="about-story-content container">
            <div class="about-story-page_about wysiwyg-content">
                <?php echo wp_kses_post($intro_text); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── Suite Hero Pairs ── -->
    <?php if (! empty($suites)) : ?>
    <div class="suites-list">
        <?php foreach ($suites as $suite) :
            $img        = get_the_post_thumbnail_url($suite->ID, 'hero-bg');
            $img_mobile = torresan_image_url('camera_hero_mobile', 'hero-bg', $suite->ID);
            $guests  = get_post_meta($suite->ID, 'camera_guests', true);
            $size    = get_post_meta($suite->ID, 'camera_size', true);
            $prezzo  = get_post_meta($suite->ID, 'camera_prezzo', true);
            $excerpt = get_the_excerpt($suite->ID);
        ?>
        <a href="<?php echo esc_url(get_permalink($suite->ID)); ?>" class="suite-hero"<?php echo torresan_bg_vars_attr('suite', $img, $img_mobile); ?> aria-label="<?php echo esc_attr(get_the_title($suite->ID)); ?>">
            <div class="suite-hero-overlay"></div>
            <div class="suite-hero-content">
                <p class="eyebrow suite-eyebrow"><?php esc_html_e('Suite', 'torresan-bnb'); ?></p>
                <h2><?php echo esc_html(get_the_title($suite->ID)); ?></h2>
                <?php if ($excerpt) : ?>
                    <p class="suite-excerpt"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>
                <div class="suite-meta">
                    <?php if ($guests) : ?><span><?php echo esc_html($guests); ?> <?php esc_html_e('guests', 'torresan-bnb'); ?></span><?php endif; ?>
                    <?php if ($size)   : ?><span><?php echo esc_html($size); ?></span><?php endif; ?>
                    <?php if ($prezzo) : ?><span class="suite-price"><?php echo esc_html($prezzo); ?></span><?php endif; ?>
                </div>
                <span class="suite-discover"><?php esc_html_e('Discover →', 'torresan-bnb'); ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else : ?>
    <section class="section-content">
        <div class="container">
            <p style="text-align:center;opacity:.6"><?php esc_html_e('No suites available yet.', 'torresan-bnb'); ?></p>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
