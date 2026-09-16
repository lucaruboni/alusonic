<?php
if (! defined('ABSPATH')) {
    exit;
}

$s = torresan_get_site_settings();
$footer_logo_id = absint($s['logo_image_id'] ?? 0);
?>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-col footer-brand">
            <?php if ($footer_logo_id) : ?>
                <a class="footer-logo-link" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php echo wp_get_attachment_image($footer_logo_id, 'medium', false, ['class' => 'footer-logo', 'alt' => esc_attr(get_bloginfo('name'))]); ?>
                </a>
            <?php else : ?>
                <a class="footer-logo-link" href="<?php echo esc_url(home_url('/')); ?>">
                    <div class="brand-name"><?php bloginfo('name'); ?></div>
                    <div class="brand-sub"><?php echo esc_html(get_bloginfo('description') ?: 'Aluminium Instruments'); ?></div>
                </a>
            <?php endif; ?>
            <?php if (! empty($s['footer_tagline'])) : ?>
                <p class="footer-tagline"><?php echo esc_html($s['footer_tagline']); ?></p>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <div class="footer-col-title"><?php esc_html_e('Navigate', 'torresan-bnb'); ?></div>
            <?php
            wp_nav_menu([
                'theme_location' => 'footer',
                'fallback_cb'    => false,
                'container'      => false,
                'menu_class'     => 'footer-menu-list',
                'depth'          => 1,
            ]);
            ?>
        </div>

        <div class="footer-col">
            <div class="footer-col-title"><?php esc_html_e('Showroom', 'torresan-bnb'); ?></div>
            <?php if (! empty($s['footer_address'])) : ?>
                <p class="footer-address"><?php echo nl2br(esc_html($s['footer_address'])); ?></p>
            <?php endif; ?>
            <?php if (! empty($s['footer_phone'])) : ?>
                <a class="footer-phone" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $s['footer_phone'])); ?>"><?php echo esc_html($s['footer_phone']); ?></a>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <div class="footer-col-title"><?php esc_html_e('Follow us', 'torresan-bnb'); ?></div>
            <?php if (! empty($s['footer_instagram'])) : ?>
                <a class="footer-ig" href="<?php echo esc_url($s['footer_instagram']); ?>" target="_blank" rel="noopener">@<?php echo esc_html(trim(basename(rtrim($s['footer_instagram'], '/')))); ?> ↗</a>
            <?php endif; ?>
            <?php if (! empty($s['footer_email'])) : ?>
                <a class="footer-email" href="mailto:<?php echo esc_attr($s['footer_email']); ?>"><?php echo esc_html($s['footer_email']); ?></a>
            <?php endif; ?>
            <?php torresan_social_icons('footer-social'); ?>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <span class="footer-copy">
                <?php if (! empty($s['footer_vat'])) : ?><?php echo esc_html($s['footer_vat']); ?> — <?php endif; ?>
                &copy; <?php echo esc_html(date('Y')); ?> <?php echo esc_html($s['footer_legal_name'] ?: get_bloginfo('name')); ?>. <?php echo esc_html($s['footer_est'] ?? ''); ?>
                <?php if (! empty($s['footer_credit'])) : ?> <?php echo esc_html($s['footer_credit']); ?><?php endif; ?>
            </span>
            <?php if (! empty($s['footer_slogan'])) : ?>
                <span class="footer-slogan">“<?php echo esc_html($s['footer_slogan']); ?>”</span>
            <?php endif; ?>
            <?php torresan_language_switcher('lang-switcher--footer'); ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
