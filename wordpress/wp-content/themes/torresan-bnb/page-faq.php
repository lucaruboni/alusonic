<?php
/**
 * Template Name: FAQ
 * Page slug: faq
 *
 * LLM-optimised: semantic HTML, machine-readable structure, no JS required to read answers.
 * Adds FAQPage schema.org JSON-LD directly in the template (complements Yoast SEO).
 */

get_header();

$pid      = get_the_ID();
$hero_img = get_the_post_thumbnail_url($pid, 'hero-bg');
$hero_eyebrow  = torresan_field('hero_eyebrow', $pid);
$hero_subtitle = torresan_field('hero_subtitle', $pid);
$faq_ids  = torresan_get_faq($pid);

// Build structured FAQ data for schema + rendering
$faqs = [];
foreach ($faq_ids as $fid) {
    $faq = get_post($fid);
    if (! $faq || $faq->post_status !== 'publish') {
        continue;
    }
    $q = get_the_title($fid);
    $a = wp_strip_all_tags(apply_filters('the_content', $faq->post_content));
    if ($q && trim($a)) {
        $faqs[] = ['q' => $q, 'a' => $a, 'html' => apply_filters('the_content', $faq->post_content)];
    }
}

// Output FAQPage JSON-LD (compatible alongside Yoast — Google handles multiple JSON-LD blocks)
if (! empty($faqs)) :
    $schema_items = array_map(fn($f) => [
        '@type'          => 'Question',
        'name'           => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], $faqs);
    $schema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $schema_items,
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
endif;
?>

<main itemscope itemtype="https://schema.org/FAQPage">

    <!-- ── Page Identification Hero ── -->
    <section class="<?php echo esc_attr(torresan_hero_class()); ?>"<?php echo torresan_hero_style_attr(); ?>>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <?php if ($hero_eyebrow) : ?><p class="eyebrow"><?php echo esc_html($hero_eyebrow); ?></p><?php endif; ?>
            <h1><?php the_title(); ?></h1>
            <?php if ($hero_subtitle) : ?><p class="page-hero-subtitle"><?php echo esc_html($hero_subtitle); ?></p><?php endif; ?>
        </div>
    </section>

    <!-- ── Intro text (WP editor) ── -->
    <?php if (have_posts()) : while (have_posts()) : the_post();
        $content = get_the_content();
        if (trim($content)) : ?>
    <section class="section-content section-content--tight-bottom">
        <div class="container container-narrow wysiwyg-content">
            <?php the_content(); ?>
        </div>
    </section>
    <?php endif; endwhile; endif; ?>

    <!-- ── FAQ Accordion (machine-readable: answers in DOM, accordion is progressive enhancement) ── -->
    <?php if (! empty($faqs)) : ?>
    <section class="section-faq" aria-label="Frequently Asked Questions">
        <div class="container container-narrow">
            <h2>Frequently Asked Questions</h2>

            <?php foreach ($faqs as $f) : ?>
            <article class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                <button class="faq-trigger" type="button" aria-expanded="false">
                    <span itemprop="name"><?php echo esc_html($f['q']); ?></span>
                    <span class="faq-icon" aria-hidden="true">+</span>
                </button>
                <div class="faq-content" itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">
                    <div itemprop="text">
                        <?php echo wp_kses_post($f['html']); ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>

