<?php
/**
 * Template Name: Artisti
 * Full roster of artists who play Alusonic instruments.
 */

get_header();

$pid      = get_the_ID();
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: __('Who plays Alusonic', 'torresan-bnb');
$subtitle = torresan_field('hero_subtitle', $pid);

$artist_ids = get_posts([
    'post_type'         => 'esperienza',
    'post_status'       => 'publish',
    'posts_per_page'    => -1,
    'orderby'           => ['menu_order' => 'ASC', 'date' => 'DESC'],
    'fields'            => 'ids',
    'suppress_filters'  => false,
]);
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
            <?php if ($artist_ids) : ?>
            <div class="artist-cards">
                <?php foreach ($artist_ids as $aid) :
                    $photo      = get_the_post_thumbnail_url($aid, 'gallery-thumb');
                    $photo_big  = get_the_post_thumbnail_url($aid, 'hero-bg') ?: $photo;
                    $band       = torresan_field('artist_band', $aid);
                    $quote      = torresan_field('artist_quote', $aid);
                    $bio        = torresan_field('artist_bio', $aid) ?: $quote;
                    $model      = torresan_field('artist_model', $aid);
                ?>
                <div class="artist-card"
                    data-artist-name="<?php echo esc_attr(get_the_title($aid)); ?>"
                    data-artist-band="<?php echo esc_attr($band); ?>"
                    data-artist-model="<?php echo esc_attr($model); ?>"
                    data-artist-bio="<?php echo esc_attr($bio); ?>"
                    data-artist-photo="<?php echo esc_url($photo_big); ?>">
                    <div class="artist-card-photo">
                        <?php if ($photo) : ?><img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr(get_the_title($aid)); ?>" loading="lazy"><?php endif; ?>
                    </div>
                    <div>
                        <div class="artist-card-name"><?php echo esc_html(get_the_title($aid)); ?></div>
                        <?php if ($band) : ?><div class="artist-card-band"><?php echo esc_html($band); ?></div><?php endif; ?>
                        <?php if ($model) : ?><div class="artist-card-quote"><strong><?php esc_html_e('Plays:', 'torresan-bnb'); ?></strong> <?php echo esc_html($model); ?></div><?php endif; ?>
                        <?php if ($quote) : ?><div class="artist-card-quote"><?php echo esc_html($quote); ?></div><?php endif; ?>
                        <div class="artist-card-more"><?php esc_html_e('Read more', 'torresan-bnb'); ?> &rarr;</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <p style="text-align:center;color:var(--muted,#999)"><?php esc_html_e('No artists published.', 'torresan-bnb'); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <section class="cta-band section-alt">
        <div class="container">
            <h2><?php esc_html_e('Your instrument can carry a signature too', 'torresan-bnb'); ?></h2>
            <p><?php esc_html_e('Let’s talk about a custom configuration or an artist collaboration.', 'torresan-bnb'); ?></p>
            <a class="btn btn-lg" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Get in touch', 'torresan-bnb'); ?></a>
        </div>
    </section>
</main>

<?php get_footer(); ?>
