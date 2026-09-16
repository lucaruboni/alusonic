<?php
/**
 * Template Name: Modelli (catalogo)
 * Lists every instrument model with a category filter.
 */

get_header();

$pid      = get_the_ID();
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: __('Catalogue', 'torresan-bnb');
$subtitle = torresan_field('hero_subtitle', $pid);

$model_ids = alusonic_model_ids();

// Build the set of category filters actually present.
$cats = [
    'basso'    => __('Basses', 'torresan-bnb'),
    'chitarra' => __('Guitars', 'torresan-bnb'),
];
$present = [];
foreach ($model_ids as $mid) { $present[alusonic_model_type($mid)] = true; }

// Linee di prodotto, nell'ordine in cui compaiono nel menu di alusonic.com.
$families = [
    'django'      => __('Django', 'torresan-bnb'),
    'django-gtsh' => __('Django GTS/H', 'torresan-bnb'),
    'the-doom'    => __('The Doom', 'torresan-bnb'),
    'j-special'   => __('J-Special', 'torresan-bnb'),
    'chitarre'    => __('Guitars', 'torresan-bnb'),
];

$fam_present   = [];
$has_signature = false;
foreach ($model_ids as $mid) {
    $f = (string) get_post_meta($mid, 'model_family', true);
    if ($f) {
        $fam_present[$f] = true;
    }
    if (get_post_meta($mid, 'model_signature', true)) {
        $has_signature = true;
    }
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
            <div class="models-toolbar">
                <div class="models-search">
                    <input type="search" class="models-search-input" placeholder="<?php esc_attr_e('Search for a model…', 'torresan-bnb'); ?>" aria-label="<?php esc_attr_e('Search for a model', 'torresan-bnb'); ?>">
                </div>

                <?php // Su telefono i filtri stanno in un pannello richiudibile:
                      // tre gruppi affiancati occuperebbero mezzo schermo. ?>
                <button class="filters-toggle" type="button" aria-expanded="false" aria-controls="models-filters">
                    <span><?php esc_html_e('Filters', 'torresan-bnb'); ?></span>
                    <span class="filters-toggle-count" hidden></span>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" aria-hidden="true"><path d="M6 9.5 12 15.5 18 9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>

                <div class="models-filters" id="models-filters">
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

                    <?php if ($has_signature) : ?>
                    <div class="filter-group">
                        <span class="filter-group-label"><?php esc_html_e('Artist', 'torresan-bnb'); ?></span>
                        <div class="filter-bar">
                            <button class="filter-btn filter-btn--toggle" data-filter-signature>
                                <?php esc_html_e('Signature only', 'torresan-bnb'); ?>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <button class="filters-reset" type="button" hidden><?php esc_html_e('Clear filters', 'torresan-bnb'); ?></button>
                </div>
            </div>

            <?php if ($model_ids) : ?>
            <div class="models-grid models-grid--wide">
                <?php foreach ($model_ids as $mid) { alusonic_render_model_card($mid); } ?>
            </div>
            <p class="models-empty" hidden><?php esc_html_e('No models match your search.', 'torresan-bnb'); ?></p>
            <?php else : ?>
            <p style="text-align:center;color:var(--muted,#999)"><?php esc_html_e('No models published.', 'torresan-bnb'); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <?php $page_content = get_post_field('post_content', $pid); if (trim(wp_strip_all_tags((string) $page_content))) : ?>
    <section class="cta-band section-alt">
        <div class="container wysiwyg-content" style="text-align:center;max-width:640px;margin-left:auto;margin-right:auto">
            <?php echo apply_filters('the_content', $page_content); ?>
            <div style="margin-top:2rem"><a class="btn btn-lg" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Request a Custom Build', 'torresan-bnb'); ?></a></div>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php
get_footer();
