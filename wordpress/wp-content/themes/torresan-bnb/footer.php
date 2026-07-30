<?php
if (! defined('ABSPATH')) {
    exit;
}

$s = torresan_get_site_settings();
?>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-col footer-brand">
            <div class="brand-name"><?php bloginfo('name'); ?></div>
            <div class="brand-sub"><?php echo esc_html(get_bloginfo('description') ?: 'Aluminium Instruments'); ?></div>
            <?php if (! empty($s['footer_tagline'])) : ?>
                <p class="footer-tagline"><?php echo esc_html($s['footer_tagline']); ?></p>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <div class="footer-col-title"><?php esc_html_e('Naviga', 'torresan-bnb'); ?></div>
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
        </div>

        <div class="footer-col">
            <div class="footer-col-title"><?php esc_html_e('Seguici', 'torresan-bnb'); ?></div>
            <?php if (! empty($s['footer_instagram'])) : ?>
                <a class="footer-ig" href="<?php echo esc_url($s['footer_instagram']); ?>" target="_blank" rel="noopener">@<?php echo esc_html(trim(basename(rtrim($s['footer_instagram'], '/')))); ?> ↗</a>
            <?php endif; ?>
            <?php if (! empty($s['footer_email'])) : ?>
                <a class="footer-email" href="mailto:<?php echo esc_attr($s['footer_email']); ?>"><?php echo esc_html($s['footer_email']); ?></a>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <span class="footer-copy">&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. <?php echo esc_html($s['footer_est'] ?? ''); ?></span>
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
