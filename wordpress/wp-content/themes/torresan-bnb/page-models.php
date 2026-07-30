<?php
/**
 * Template Name: Modelli (catalogo)
 * Lists every instrument model with a category filter.
 */

get_header();

$pid      = get_the_ID();
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: __('Catalogo', 'torresan-bnb');
$subtitle = torresan_field('hero_subtitle', $pid);

$model_ids = alusonic_model_ids();

// Build the set of category filters actually present.
$cats = [
    'basso'    => __('Bassi', 'torresan-bnb'),
    'chitarra' => __('Chitarre', 'torresan-bnb'),
    'cabinet'  => __('Cabinet', 'torresan-bnb'),
];
$present = [];
foreach ($model_ids as $mid) { $present[alusonic_model_type($mid)] = true; }
?>

<main>
    <section class="page-hero">
        <div class="page-hero-content">
            <?php if ($eyebrow) : ?><p class="eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
            <h1><?php the_title(); ?></h1>
            <?php if ($subtitle) : ?><p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
        </div>
    </section>

    <section class="section-tight">
        <div class="container">
            <?php if (count(array_intersect_key($cats, $present)) > 1) : ?>
            <div class="filter-bar">
                <button class="filter-btn is-active" data-filter="all"><?php esc_html_e('Tutti', 'torresan-bnb'); ?></button>
                <?php foreach ($cats as $key => $label) : if (empty($present[$key])) continue; ?>
                    <button class="filter-btn" data-filter="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($model_ids) : ?>
            <div class="models-grid">
                <?php foreach ($model_ids as $mid) { alusonic_render_model_card($mid); } ?>
            </div>
            <?php else : ?>
            <p style="text-align:center;color:var(--muted,#999)"><?php esc_html_e('Nessun modello pubblicato.', 'torresan-bnb'); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <?php $page_content = get_post_field('post_content', $pid); if (trim(wp_strip_all_tags((string) $page_content))) : ?>
    <section class="cta-band section-alt">
        <div class="container wysiwyg-content" style="text-align:center;max-width:640px;margin-left:auto;margin-right:auto">
            <?php echo apply_filters('the_content', $page_content); ?>
            <div style="margin-top:2rem"><a class="btn btn-lg" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Richiedi un Custom', 'torresan-bnb'); ?></a></div>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php
get_footer();
