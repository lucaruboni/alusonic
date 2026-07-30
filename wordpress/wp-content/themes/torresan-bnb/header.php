<?php
if (! defined('ABSPATH')) {
    exit;
}
$_alu_logo_id = absint(torresan_get_site_settings()['logo_image_id'] ?? 0);
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php if ($_alu_logo_id) : ?>
                <?php echo wp_get_attachment_image($_alu_logo_id, 'medium', false, ['class' => 'brand-logo', 'alt' => esc_attr(get_bloginfo('name'))]); ?>
            <?php endif; ?>
            <span class="brand-text">
                <span class="brand-name"><?php bloginfo('name'); ?></span>
                <span class="brand-sub"><?php echo esc_html(get_bloginfo('description') ?: 'Aluminium Instruments'); ?></span>
            </span>
        </a>

        <button class="menu-toggle" aria-label="<?php esc_attr_e('Open menu', 'torresan-bnb'); ?>" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="site-nav" aria-label="<?php esc_attr_e('Main navigation', 'torresan-bnb'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'fallback_cb'    => false,
                'container'      => false,
                'menu_class'     => 'menu-list',
                'depth'          => 1,
            ]);
            ?>
            <?php torresan_language_switcher('lang-switcher--nav'); ?>
            <a class="header-cta" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Richiedi Info', 'torresan-bnb'); ?></a>
        </nav>
    </div>
</header>
