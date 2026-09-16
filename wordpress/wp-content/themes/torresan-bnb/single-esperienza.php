<?php
/**
 * Single Artist (internal post type key: esperienza).
 */

get_header();

while (have_posts()) : the_post();
    $pid   = get_the_ID();
    $band  = torresan_field('artist_band', $pid);
    $quote = torresan_field('artist_quote', $pid);
    $model = torresan_field('artist_model', $pid);
    $photo = get_the_post_thumbnail_url($pid, 'hero-bg');

    $about = torresan_localized_page('about', 'chi-siamo');
    $about_url = $about ? get_permalink($about->ID) : home_url('/');
?>

<main>
    <section class="container model-back">
        <a href="<?php echo esc_url($about_url); ?>">&larr; <?php esc_html_e('About Us', 'torresan-bnb'); ?></a>
    </section>

    <section class="section">
        <div class="container split-2">
            <?php if ($photo) : ?>
            <div class="split-media"><img src="<?php echo esc_url($photo); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" style="height:480px;object-fit:cover"></div>
            <?php endif; ?>
            <div class="split-body">
                <?php if ($band) : ?><p class="eyebrow"><?php echo esc_html($band); ?></p><?php endif; ?>
                <h1 style="font-size:clamp(34px,4vw,58px);margin-bottom:20px"><?php the_title(); ?></h1>
                <?php if ($quote) : ?><p style="font-size:18px;color:var(--accent);font-style:italic;margin-bottom:24px">“<?php echo esc_html($quote); ?>”</p><?php endif; ?>
                <div class="wysiwyg-content"><?php the_content(); ?></div>
                <?php if ($model) : ?>
                    <p style="margin-top:24px"><strong style="color:#fff"><?php esc_html_e('Plays:', 'torresan-bnb'); ?></strong> <?php echo esc_html($model); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="cta-band section-alt">
        <div class="container">
            <h2><?php esc_html_e('Play Different', 'torresan-bnb'); ?></h2>
            <p><?php esc_html_e('Explore the Alusonic models and find your sound.', 'torresan-bnb'); ?></p>
            <a class="btn btn-lg" href="<?php echo esc_url(get_permalink(torresan_localized_page('models', 'modelli'))); ?>"><?php esc_html_e('Explore the Models', 'torresan-bnb'); ?></a>
        </div>
    </section>
</main>

<?php
endwhile;
get_footer();
