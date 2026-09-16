<?php
/**
 * Template Name: Homepage
 * Alusonic homepage — hero, stats, featured models, materials, artists, IG, CTA.
 */

get_header();

$pid = get_the_ID();

$eyebrow    = torresan_field('hero_eyebrow', $pid);
$subtitle   = torresan_field('hero_subtitle', $pid);
$hero_logo  = torresan_image_url('hero_logo', 'full', $pid);
$hero_video = torresan_file_url('hero_video', $pid);

/* About / "Chi Siamo" (used on homepage below the featured models) */
$about_page  = torresan_localized_page('about', 'chi-siamo');
$about_url   = $about_page ? get_permalink($about_page->ID) : home_url('/about/');
$about_title = $about_page ? get_the_title($about_page->ID) : __('About Us', 'torresan-bnb');
$about_img   = $about_page ? torresan_image_url('about_story_image', 'hero-bg', $about_page->ID) : '';
$about_text  = $about_page ? torresan_field('about_story_text', $about_page->ID) : '';
$about_excerpt = $about_text ? wp_trim_words(wp_strip_all_tags($about_text), 42) : '';

$stats = alusonic_parse_pairs(torresan_field('home_stats', $pid));

/* Featured models */
$model_ids = alusonic_model_ids(3, torresan_related_post_ids('home_models_order', $pid));

/* Materials */
$mat_eyebrow = torresan_field('home_mat_eyebrow', $pid);
$mat_title   = torresan_field('home_mat_title', $pid);
$mat_text    = torresan_field('home_mat_text', $pid);
$mat_cta_t   = torresan_field('home_mat_cta_text', $pid);
$mat_cta_u   = torresan_localize_url(torresan_field('home_mat_cta_url', $pid));
$mat_img     = torresan_image_url('home_mat_image', 'hero-bg', $pid);

/* Artists (up to 4) */
$partists_page = torresan_localized_page('artists', 'artisti');
$partists_url  = $partists_page ? get_permalink($partists_page->ID) : home_url('/artists/');
$artist_ids = get_posts([
    'post_type'      => 'esperienza',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
    'fields'         => 'ids',
    'suppress_filters' => false,
]);

/* Instagram */
$ig_url    = torresan_field('home_ig_url', $pid);
$ig_images = torresan_gallery_ids('home_ig_images', $pid);

/* Final CTA */
$cta_title  = torresan_field('home_cta_title', $pid);
$cta_body   = torresan_field('home_cta_text', $pid);
$cta_btn    = torresan_field('home_cta_btn', $pid);
?>

<main>

    <!-- ── HERO ── -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="container hero-inner">
            <div class="hero-text">
                <?php if ($eyebrow) : ?><p class="eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
                <?php if ($hero_logo) : ?>
                    <img class="hero-logo" src="<?php echo esc_url($hero_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <?php else : ?>
                    <h1><?php echo nl2br(esc_html(get_the_title())); ?></h1>
                <?php endif; ?>
                <?php if ($subtitle) : ?><p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
            </div>
        </div>
        <?php if ($hero_video) : ?>
        <div class="hero-video-wrap">
            <video class="hero-video" autoplay muted loop playsinline preload="auto">
                <source src="<?php echo esc_url($hero_video); ?>" type="video/mp4">
            </video>
        </div>
        <?php endif; ?>
        <a class="hero-scroll-cue" href="#" aria-label="<?php esc_attr_e('Scroll to explore', 'torresan-bnb'); ?>">
            <span class="hero-scroll-cue-track"><span class="hero-scroll-cue-dot"></span></span>
            <span class="hero-scroll-cue-label"><?php esc_html_e('Find out more', 'torresan-bnb'); ?></span>
        </a>
    </section>

    <!-- ── STATS ── -->
    <?php if ($stats) : ?>
    <section class="stats-bar">
        <?php foreach ($stats as [$value, $label]) : ?>
        <div class="stat">
            <div class="stat-value"><?php echo esc_html($value); ?></div>
            <div class="stat-label"><?php echo esc_html($label); ?></div>
        </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- ── FEATURED MODELS ── -->
    <?php if ($model_ids) :
        $models_page = torresan_localized_page('models', 'modelli');
        $models_url  = $models_page ? get_permalink($models_page->ID) : home_url('/models/');
    ?>
    <section class="section-lg">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow"><?php esc_html_e('The collection', 'torresan-bnb'); ?></p>
                    <h2><a class="section-title-link" href="<?php echo esc_url($models_url); ?>"><?php esc_html_e('Featured models', 'torresan-bnb'); ?></a></h2>
                </div>
                <a class="link-underline" href="<?php echo esc_url($models_url); ?>"><?php esc_html_e('See all models', 'torresan-bnb'); ?> &rarr;</a>
            </div>
            <div class="models-grid">
                <?php foreach ($model_ids as $mid) { alusonic_render_model_card($mid); } ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── CHI SIAMO (text left, photo right — mirrored vs. the materials section below) ── -->
    <?php if ($about_img || $about_excerpt) : ?>
    <section class="section">
        <div class="container split-2">
            <div class="split-body">
                <h2><a class="section-title-link" href="<?php echo esc_url($about_url); ?>"><?php echo esc_html($about_title); ?></a></h2>
                <?php if ($about_excerpt) : ?><p class="about-excerpt"><?php echo esc_html($about_excerpt); ?></p><?php endif; ?>
            </div>
            <?php if ($about_img) : ?>
            <div class="split-media is-contain"><img src="<?php echo esc_url($about_img); ?>" alt="" loading="lazy"></div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── MATERIALS & PROCESS ── -->
    <?php if ($mat_title || $mat_text || $mat_img) : ?>
    <section class="section section-alt">
        <div class="container split-2">
            <?php if ($mat_img) : ?>
            <div class="split-media is-contain"><img src="<?php echo esc_url($mat_img); ?>" alt="" loading="lazy"></div>
            <?php endif; ?>
            <div class="split-body">
                <?php if ($mat_eyebrow) : ?><p class="eyebrow"><?php echo esc_html($mat_eyebrow); ?></p><?php endif; ?>
                <?php if ($mat_title) : ?><h2><a class="section-title-link" href="<?php echo esc_url($mat_cta_u ?: $about_url); ?>"><?php echo esc_html($mat_title); ?></a></h2><?php endif; ?>
                <?php if ($mat_text) : ?><div class="wysiwyg-content"><?php echo wp_kses_post(wpautop($mat_text)); ?></div><?php endif; ?>
                <?php if ($mat_cta_t) : ?>
                    <a class="btn btn-outline" href="<?php echo $mat_cta_u ? esc_url($mat_cta_u) : '#'; ?>"><?php echo esc_html($mat_cta_t); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── ARTISTS ── -->
    <?php if ($artist_ids) : ?>
    <section class="section">
        <div class="container">
            <div class="section-head-center">
                <p class="eyebrow"><?php esc_html_e('Who plays Alusonic', 'torresan-bnb'); ?></p>
                <h2><a class="section-title-link" href="<?php echo esc_url($partists_url); ?>"><?php esc_html_e('Artists & Endorsements', 'torresan-bnb'); ?></a></h2>
            </div>
            <div class="artists-grid">
                <?php foreach ($artist_ids as $aid) :
                    $photo = get_the_post_thumbnail_url($aid, 'gallery-thumb');
                    $band  = torresan_field('artist_band', $aid);
                ?>
                <a class="artist-tile" href="<?php echo esc_url($partists_url); ?>">
                    <div class="artist-photo">
                        <?php if ($photo) : ?><img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr(get_the_title($aid)); ?>" loading="lazy"><?php endif; ?>
                    </div>
                    <div class="artist-name"><?php echo esc_html(get_the_title($aid)); ?></div>
                    <?php if ($band) : ?><div class="artist-band"><?php echo esc_html($band); ?></div><?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── INSTAGRAM ── -->
    <?php if ($ig_images) : ?>
    <section class="section section-alt">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow"><?php echo esc_html($ig_url ? '@' . trim(basename(rtrim($ig_url, '/'))) : '@alusonic'); ?></p>
                    <h2>
                        <?php if ($ig_url) : ?>
                        <a class="section-title-link" href="<?php echo esc_url($ig_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('From our Instagram', 'torresan-bnb'); ?></a>
                        <?php else : ?>
                        <?php esc_html_e('From our Instagram', 'torresan-bnb'); ?>
                        <?php endif; ?>
                    </h2>
                </div>
                <?php if ($ig_url) : ?>
                <a class="link-underline" href="<?php echo esc_url($ig_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Follow us', 'torresan-bnb'); ?> ↗</a>
                <?php endif; ?>
            </div>
            <div class="ig-grid">
                <?php foreach (array_slice($ig_images, 0, 5) as $img_id) :
                    $u = wp_get_attachment_image_url($img_id, 'gallery-thumb');
                    if (! $u) continue;
                ?>
                <a class="ig-item" href="<?php echo esc_url($ig_url ?: '#'); ?>" <?php echo $ig_url ? 'target="_blank" rel="noopener"' : ''; ?>>
                    <img src="<?php echo esc_url($u); ?>" alt="" loading="lazy">
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── FINAL CTA ── -->
    <?php if ($cta_title || $cta_body || $cta_btn) : ?>
    <section class="cta-band">
        <div class="container">
            <?php if ($cta_title) : ?><h2><?php echo esc_html($cta_title); ?></h2><?php endif; ?>
            <?php if ($cta_body) : ?><p><?php echo esc_html($cta_body); ?></p><?php endif; ?>
            <?php if ($cta_btn) : ?><a class="btn btn-lg" href="<?php echo torresan_booking_url(); ?>"><?php echo esc_html($cta_btn); ?></a><?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php
get_footer();
