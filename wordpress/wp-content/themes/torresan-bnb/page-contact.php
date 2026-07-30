<?php
/**
 * Template Name: Contact
 * Page slug: contact
 *
 * Full enquiry form with AJAX submission. All fields needed for BnB booking request.
 */

wp_safe_redirect(home_url('/location/'), 301);
exit;

$pid      = get_the_ID();
$hero_img = get_the_post_thumbnail_url($pid, 'hero-bg');
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: __('Get in Touch', 'torresan-bnb');
$subtitle = torresan_field('hero_subtitle', $pid);
$phone    = torresan_field('contatti_telefono', $pid);
$email    = torresan_field('contatti_email', $pid);
$hours    = torresan_field('contatti_orari', $pid);
$maps_raw = torresan_field('maps_embed', $pid);
$address  = torresan_field('address', $pid);
?>

<main>

    <!-- ── Page Identification Hero ── -->
    <section class="page-hero"<?php echo torresan_hero_style_attr(); ?>>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <h1><?php the_title(); ?></h1>
            <?php if ($subtitle) : ?>
                <p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <!-- ── Contact Info Bar ── -->
    <?php if ($phone || $email || $hours || $address) : ?>
    <section class="section-content">
        <div class="container">
            <div class="contatti-info">
                <?php if ($address) : ?>
                <div class="contatti-info-item">
                    <strong><?php esc_html_e('Address', 'torresan-bnb'); ?></strong>
                    <p>
                        <a href="https://maps.google.com/?q=<?php echo esc_attr(urlencode($address)); ?>" target="_blank" rel="noopener noreferrer nofollow">
                            <?php echo esc_html($address); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
                <?php if ($phone) : ?>
                <div class="contatti-info-item">
                    <strong><?php esc_html_e('Phone', 'torresan-bnb'); ?></strong>
                    <p><a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></p>
                </div>
                <?php endif; ?>
                <?php if ($email) : ?>
                <div class="contatti-info-item">
                    <strong><?php esc_html_e('Email', 'torresan-bnb'); ?></strong>
                    <p><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
                </div>
                <?php endif; ?>
                <?php if ($hours) : ?>
                <div class="contatti-info-item">
                    <strong><?php esc_html_e('Hours', 'torresan-bnb'); ?></strong>
                    <p><?php echo nl2br(esc_html($hours)); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── Book Online CTA ── -->
    <section class="booking-conditions-bar" style="text-align:center">
        <div class="container">
            <p style="margin:0 0 .75rem;font-size:.8rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted,#6b6356)"><?php esc_html_e('Ready to reserve?', 'torresan-bnb'); ?></p>
            <a href="<?php echo esc_url(home_url('/book/')); ?>" class="btn"><?php esc_html_e('Book Online →', 'torresan-bnb'); ?></a>
        </div>
    </section>

    <!-- ── Write to Us ── -->
    <?php if ($email) : ?>
    <section class="section-content contact-mailto-section">
        <div class="container">
            <div class="section-header">
                <p class="eyebrow"><?php esc_html_e('Write to Us', 'torresan-bnb'); ?></p>
                <h2><?php esc_html_e('Send Us an Email', 'torresan-bnb'); ?></h2>
                <p><?php esc_html_e('Click the button below — your mail client will open with our address already filled in. We reply within a few hours.', 'torresan-bnb'); ?></p>
            </div>
            <div style="text-align:center;margin-top:2rem">
                <a class="btn" href="mailto:<?php echo esc_attr($email); ?>?subject=<?php echo rawurlencode(__('Enquiry — Torre San Bartolo', 'torresan-bnb')); ?>">
                    <?php echo esc_html($email); ?>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── Map ── -->
    <div class="map-wrap" style="margin:2rem auto;max-width:700px;">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2882.7018108519846!2d12.568986475661868!3d43.73752104697389!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x132cf796025dc18f%3A0xdaea035c60e4a0f5!2sTorre%20San%20Bartolo!5e0!3m2!1sit!2sit!4v1779551573161!5m2!1sit!2sit" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

</main>

<?php get_footer(); ?>
