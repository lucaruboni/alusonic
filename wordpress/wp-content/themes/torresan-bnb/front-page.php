<?php
/**
 * Template Name: Homepage
 * Alusonic homepage — hero, stats, featured models, materials, artists, IG, CTA.
 */

get_header();

$pid = get_the_ID();

$eyebrow   = torresan_field('hero_eyebrow', $pid);
$subtitle  = torresan_field('hero_subtitle', $pid);
$cta_text  = torresan_field('hero_cta_text', $pid);
$cta_url   = torresan_field('hero_cta_url', $pid);
$cta2_text = torresan_field('hero_cta2_text', $pid);
$cta2_url  = torresan_field('hero_cta2_url', $pid);
$hero_bg   = get_the_post_thumbnail_url($pid, 'hero-bg');
$hero_fg   = torresan_image_url('hero_foreground', 'full', $pid);

$stats = alusonic_parse_pairs(torresan_field('home_stats', $pid));

/* Featured models */
$model_ids = alusonic_model_ids(3, torresan_related_post_ids('home_models_order', $pid));

/* Materials */
$mat_eyebrow = torresan_field('home_mat_eyebrow', $pid);
$mat_title   = torresan_field('home_mat_title', $pid);
$mat_text    = torresan_field('home_mat_text', $pid);
$mat_cta_t   = torresan_field('home_mat_cta_text', $pid);
$mat_cta_u   = torresan_field('home_mat_cta_url', $pid);
$mat_img     = torresan_image_url('home_mat_image', 'hero-bg', $pid);

/* Artists (up to 4) */
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
        <?php if ($hero_bg) : ?>
        <div class="hero-bg"><img src="<?php echo esc_url($hero_bg); ?>" alt=""></div>
        <?php endif; ?>
        <div class="hero-overlay"></div>
        <div class="container hero-inner">
            <div class="hero-text">
                <?php if ($eyebrow) : ?><p class="eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
                <h1><?php echo nl2br(esc_html(get_the_title())); ?></h1>
                <?php if ($subtitle) : ?><p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
                <div class="hero-actions">
                    <?php if ($cta_text) : ?>
                        <a class="btn" href="<?php echo $cta_url ? esc_url($cta_url) : torresan_booking_url(); ?>"><?php echo esc_html($cta_text); ?></a>
                    <?php endif; ?>
                    <?php if ($cta2_text) : ?>
                        <a class="btn btn-outline" href="<?php echo $cta2_url ? esc_url($cta2_url) : '#'; ?>"><?php echo esc_html($cta2_text); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($hero_fg) : ?>
            <div class="hero-figure"><img src="<?php echo esc_url($hero_fg); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>"></div>
            <?php endif; ?>
        </div>
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
        $models_page = get_page_by_path('models') ?: get_page_by_path('modelli');
        $models_url  = $models_page ? get_permalink($models_page->ID) : home_url('/models/');
    ?>
    <section class="section-lg">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow"><?php esc_html_e('La collezione', 'torresan-bnb'); ?></p>
                    <h2><?php esc_html_e('Modelli in evidenza', 'torresan-bnb'); ?></h2>
                </div>
                <a class="link-underline" href="<?php echo esc_url($models_url); ?>"><?php esc_html_e('Vedi tutti i modelli', 'torresan-bnb'); ?> &rarr;</a>
            </div>
            <div class="models-grid">
                <?php foreach ($model_ids as $mid) { alusonic_render_model_card($mid); } ?>
            </div>
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
                <?php if ($mat_title) : ?><h2><?php echo esc_html($mat_title); ?></h2><?php endif; ?>
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
                <p class="eyebrow"><?php esc_html_e('Chi suona Alusonic', 'torresan-bnb'); ?></p>
                <h2><?php esc_html_e('Artisti & Endorsement', 'torresan-bnb'); ?></h2>
            </div>
            <div class="artists-grid">
                <?php foreach ($artist_ids as $aid) :
                    $photo = get_the_post_thumbnail_url($aid, 'gallery-thumb');
                    $band  = torresan_field('artist_band', $aid);
                ?>
                <div class="artist-tile">
                    <div class="artist-photo">
                        <?php if ($photo) : ?><img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr(get_the_title($aid)); ?>" loading="lazy"><?php endif; ?>
                    </div>
                    <div class="artist-name"><?php echo esc_html(get_the_title($aid)); ?></div>
                    <?php if ($band) : ?><div class="artist-band"><?php echo esc_html($band); ?></div><?php endif; ?>
                </div>
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
                    <h2><?php esc_html_e('Dal nostro Instagram', 'torresan-bnb'); ?></h2>
                </div>
                <?php if ($ig_url) : ?>
                <a class="link-underline" href="<?php echo esc_url($ig_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Seguici', 'torresan-bnb'); ?> ↗</a>
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
