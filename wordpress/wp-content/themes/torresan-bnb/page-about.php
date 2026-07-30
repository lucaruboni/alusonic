<?php
/**
 * Template Name: Chi Siamo
 * Page slug: about
 */

get_header();

$pid          = get_the_ID();
$eyebrow      = torresan_field('hero_eyebrow', $pid) ?: __('La nostra storia', 'torresan-bnb');
$story_text   = torresan_field('about_story_text', $pid);
$story_img    = torresan_image_url('about_story_image', 'hero-bg', $pid);
$proc_title   = torresan_field('about_process_title', $pid);
$proc_img     = torresan_image_url('about_process_image', 'hero-bg', $pid);
$proc_steps   = alusonic_parse_pairs(torresan_field('about_process_steps', $pid));

/* Artists with quotes */
$artist_ids = get_posts([
    'post_type'      => 'esperienza',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
    'fields'         => 'ids',
    'suppress_filters' => false,
]);
?>

<main>

    <!-- ── STORY (intro split) ── -->
    <section class="section about-intro">
        <div class="container split-2">
            <div class="split-body">
                <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
                <h1 style="font-size:clamp(34px,4vw,58px);margin-bottom:24px"><?php the_title(); ?></h1>
                <?php if ($story_text) : ?>
                    <div class="wysiwyg-content"><?php echo wp_kses_post(wpautop($story_text)); ?></div>
                <?php endif; ?>
            </div>
            <?php if ($story_img) : ?>
            <div class="split-media"><img src="<?php echo esc_url($story_img); ?>" alt="" loading="lazy"></div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ── PROCESS ── -->
    <?php if ($proc_steps || $proc_img) : ?>
    <section class="section section-alt about-process">
        <div class="container split-2">
            <?php if ($proc_img) : ?>
            <div class="split-media"><img src="<?php echo esc_url($proc_img); ?>" alt="" loading="lazy"></div>
            <?php endif; ?>
            <div class="split-body">
                <p class="eyebrow"><?php esc_html_e('Materiali & processo', 'torresan-bnb'); ?></p>
                <?php if ($proc_title) : ?><h2><?php echo esc_html($proc_title); ?></h2><?php endif; ?>
                <div class="process-list">
                    <?php foreach ($proc_steps as $i => [$title, $desc]) : ?>
                    <div class="process-step">
                        <div class="step-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></div>
                        <div>
                            <div class="step-title"><?php echo esc_html($title); ?></div>
                            <?php if ($desc) : ?><div class="step-desc"><?php echo esc_html($desc); ?></div><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
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
            <div class="artist-cards">
                <?php foreach ($artist_ids as $aid) :
                    $photo = get_the_post_thumbnail_url($aid, 'gallery-thumb');
                    $band  = torresan_field('artist_band', $aid);
                    $quote = torresan_field('artist_quote', $aid);
                ?>
                <div class="artist-card">
                    <div class="artist-card-photo">
                        <?php if ($photo) : ?><img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr(get_the_title($aid)); ?>" loading="lazy"><?php endif; ?>
                    </div>
                    <div>
                        <div class="artist-card-name"><?php echo esc_html(get_the_title($aid)); ?></div>
                        <?php if ($band) : ?><div class="artist-card-band"><?php echo esc_html($band); ?></div><?php endif; ?>
                        <?php if ($quote) : ?><div class="artist-card-quote"><?php echo esc_html($quote); ?></div><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── CTA ── -->
    <section class="cta-band section-alt">
        <div class="container">
            <h2><?php esc_html_e('Vieni a conoscerci', 'torresan-bnb'); ?></h2>
            <p><?php esc_html_e('Il nostro showroom è aperto su appuntamento: vieni a provare i modelli e parlare del tuo strumento su misura.', 'torresan-bnb'); ?></p>
            <a class="btn btn-lg" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Contattaci', 'torresan-bnb'); ?></a>
        </div>
    </section>

</main>

<?php get_footer(); ?>
