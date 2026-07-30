<?php
/**
 * Template Name: Contatti
 * Page slug: contact
 */

get_header();

$pid       = get_the_ID();
$eyebrow   = torresan_field('hero_eyebrow', $pid) ?: __('Contatti', 'torresan-bnb');
$subtitle  = torresan_field('hero_subtitle', $pid);

$email     = torresan_field('contatti_email', $pid);
$phone     = torresan_field('contatti_telefono', $pid);
$showroom  = torresan_field('contatti_showroom', $pid);
$ig        = torresan_field('contatti_instagram', $pid);
$photo     = torresan_image_url('contatti_image', 'hero-bg', $pid);

/* Interest options: published models + generic entries */
$models = alusonic_model_ids();
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
        <div class="container contact-grid">

            <form id="torresan-contact-form" class="contact-form" novalidate>
                <div class="form-row">
                    <input type="text" name="contact_name" placeholder="<?php esc_attr_e('Nome e cognome', 'torresan-bnb'); ?>" required>
                    <input type="email" name="contact_email" placeholder="<?php esc_attr_e('Email', 'torresan-bnb'); ?>" required>
                </div>
                <select name="contact_interest">
                    <option value=""><?php esc_html_e('Interesse…', 'torresan-bnb'); ?></option>
                    <?php foreach ($models as $mid) : ?>
                        <option value="<?php echo esc_attr(get_the_title($mid)); ?>"><?php echo esc_html(get_the_title($mid)); ?></option>
                    <?php endforeach; ?>
                    <option value="<?php esc_attr_e('Modello Custom', 'torresan-bnb'); ?>"><?php esc_html_e('Modello Custom', 'torresan-bnb'); ?></option>
                    <option value="<?php esc_attr_e('Altro', 'torresan-bnb'); ?>"><?php esc_html_e('Altro', 'torresan-bnb'); ?></option>
                </select>
                <textarea name="contact_message" rows="6" placeholder="<?php esc_attr_e('Raccontaci cosa cerchi: modello, configurazione, tempistiche…', 'torresan-bnb'); ?>" required></textarea>
                <button type="submit" class="btn"><?php esc_html_e('Invia Richiesta', 'torresan-bnb'); ?></button>
                <div id="form-status" class="form-feedback" role="status" aria-live="polite"></div>
            </form>

            <div class="contact-info">
                <?php if ($photo) : ?>
                <div class="contact-photo"><img src="<?php echo esc_url($photo); ?>" alt="" loading="lazy"></div>
                <?php endif; ?>
                <?php if ($showroom) : ?>
                <div class="contact-block">
                    <div class="contact-block-title"><?php esc_html_e('Showroom', 'torresan-bnb'); ?></div>
                    <p><?php echo nl2br(esc_html($showroom)); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($email) : ?>
                <div class="contact-block">
                    <div class="contact-block-title"><?php esc_html_e('Contatto diretto', 'torresan-bnb'); ?></div>
                    <p><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
                    <?php if ($phone) : ?><p><a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></p><?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if ($ig) : ?>
                <div class="contact-block">
                    <div class="contact-block-title"><?php esc_html_e('Social', 'torresan-bnb'); ?></div>
                    <p><a class="is-accent" href="<?php echo esc_url($ig); ?>" target="_blank" rel="noopener">@<?php echo esc_html(trim(basename(rtrim($ig, '/')))); ?> ↗</a></p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </section>
</main>

<?php get_footer(); ?>
