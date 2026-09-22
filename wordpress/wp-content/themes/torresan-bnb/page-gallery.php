<?php
/**
 * Template Name: Galleria
 * Every model's photos, grouped model by model with the same line/type
 * filter bar used on the catalogue — not one long mixed grid.
 */

get_header();

$pid      = get_the_ID();
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: __('Gallery', 'torresan-bnb');
$subtitle = torresan_field('hero_subtitle', $pid);

$model_ids = alusonic_model_ids();

$groups = [];
foreach ($model_ids as $mid) {
    $img_ids = torresan_gallery_ids('model_gallery', $mid);
    if (! $img_ids) { continue; }
    $groups[] = [
        'id'     => $mid,
        'title'  => get_the_title($mid),
        'url'    => get_permalink($mid),
        'type'   => alusonic_model_type($mid),
        'family' => (string) get_post_meta($mid, 'model_family', true),
        'images' => $img_ids,
    ];
}

// Stesso set di filtri della pagina Modelli, ma solo per le linee/tipi che
// hanno davvero foto in galleria.
$cats = [
    'basso'    => __('Basses', 'torresan-bnb'),
    'chitarra' => __('Guitars', 'torresan-bnb'),
];
$families = [
    'django'      => __('Django', 'torresan-bnb'),
    'django-gtsh' => __('Django GTS/H', 'torresan-bnb'),
    'the-doom'    => __('The Doom', 'torresan-bnb'),
    'j-special'   => __('J-Special', 'torresan-bnb'),
    'chitarre'    => __('Guitars', 'torresan-bnb'),
];
$present     = [];
$fam_present = [];
foreach ($groups as $g) {
    $present[$g['type']] = true;
    if ($g['family']) { $fam_present[$g['family']] = true; }
}
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
            <?php if ($groups) : ?>
            <div class="models-toolbar">
                <div class="models-search">
                    <input type="search" class="models-search-input" placeholder="<?php esc_attr_e('Search for a model…', 'torresan-bnb'); ?>" aria-label="<?php esc_attr_e('Search for a model', 'torresan-bnb'); ?>">
                </div>

                <button class="filters-toggle" type="button" aria-expanded="false" aria-controls="gallery-filters">
                    <span><?php esc_html_e('Filters', 'torresan-bnb'); ?></span>
                    <span class="filters-toggle-count" hidden></span>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" aria-hidden="true"><path d="M6 9.5 12 15.5 18 9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>

                <div class="models-filters" id="gallery-filters">
                    <?php if (count(array_intersect_key($families, $fam_present)) > 1) : ?>
                    <div class="filter-group">
                        <span class="filter-group-label"><?php esc_html_e('Line', 'torresan-bnb'); ?></span>
                        <div class="filter-bar" data-filter-group="family">
                            <button class="filter-btn is-active" data-filter="all"><?php esc_html_e('All', 'torresan-bnb'); ?></button>
                            <?php foreach ($families as $key => $label) : if (empty($fam_present[$key])) continue; ?>
                                <button class="filter-btn" data-filter="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (count(array_intersect_key($cats, $present)) > 1) : ?>
                    <div class="filter-group">
                        <span class="filter-group-label"><?php esc_html_e('Type', 'torresan-bnb'); ?></span>
                        <div class="filter-bar" data-filter-group="type">
                            <button class="filter-btn is-active" data-filter="all"><?php esc_html_e('All', 'torresan-bnb'); ?></button>
                            <?php foreach ($cats as $key => $label) : if (empty($present[$key])) continue; ?>
                                <button class="filter-btn" data-filter="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <button class="filters-reset" type="button" hidden><?php esc_html_e('Clear filters', 'torresan-bnb'); ?></button>
                </div>
            </div>

            <div class="gallery-groups">
                <?php foreach ($groups as $g) : ?>
                <section class="gallery-model-group"
                    data-family="<?php echo esc_attr($g['family']); ?>"
                    data-type="<?php echo esc_attr($g['type']); ?>"
                    data-name="<?php echo esc_attr(mb_strtolower($g['title'])); ?>">
                    <div class="gallery-model-heading">
                        <h2><a href="<?php echo esc_url($g['url']); ?>"><?php echo esc_html($g['title']); ?></a></h2>
                        <a class="gallery-model-link" href="<?php echo esc_url($g['url']); ?>"><?php esc_html_e('View model', 'torresan-bnb'); ?> &rarr;</a>
                    </div>
                    <div class="parallax-gallery" data-lightbox-group="gallery-<?php echo esc_attr($g['id']); ?>">
                        <?php foreach ($g['images'] as $img_id) :
                            $thumb = wp_get_attachment_image_url($img_id, 'section-card');
                            $full  = wp_get_attachment_image_url($img_id, 'full');
                            if (! $thumb) { continue; }
                        ?>
                        <div class="parallax-item" data-lightbox="<?php echo esc_url($full ?: $thumb); ?>">
                            <img class="parallax-item-media" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($g['title']); ?>" loading="lazy">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endforeach; ?>
            </div>
            <p class="models-empty" hidden><?php esc_html_e('No models match your search.', 'torresan-bnb'); ?></p>
            <?php else : ?>
            <p style="text-align:center;color:var(--muted,#999)"><?php esc_html_e('No photos available at the moment.', 'torresan-bnb'); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <section class="cta-band section-alt">
        <div class="container">
            <h2><?php esc_html_e('Want to see an instrument in person?', 'torresan-bnb'); ?></h2>
            <p><?php esc_html_e('Book a showroom visit, in Rimini or Carrara.', 'torresan-bnb'); ?></p>
            <a class="btn btn-lg" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Get in touch', 'torresan-bnb'); ?></a>
        </div>
    </section>
</main>

<?php get_footer(); ?>
