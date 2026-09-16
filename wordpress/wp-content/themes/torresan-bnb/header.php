<?php
if (! defined('ABSPATH')) {
    exit;
}
$_alu_settings   = torresan_get_site_settings();
$_alu_logo_id    = absint($_alu_settings['logo_image_id'] ?? 0);
$_alu_mmv_id     = absint($_alu_settings['mega_menu_video_id'] ?? 0);
$_alu_mmv_url    = $_alu_mmv_id ? wp_get_attachment_url($_alu_mmv_id) : '';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script>
    // Applied before first paint to avoid a flash of the wrong theme.
    // Priority: saved user choice > OS/browser preference > dark (default).
    (function () {
        try {
            var saved = localStorage.getItem('alu-theme');
            var theme = saved || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
            document.documentElement.setAttribute('data-theme', theme);
        } catch (e) {}
    })();
    </script>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php // Solo in homepage: altrove è un ritardo gratuito su una pagina che non ha
      // il video hero da precaricare. initPreloader() esce da solo se manca. ?>
<?php if (is_front_page()) : ?>
<div id="site-preloader" class="site-preloader" aria-hidden="true">
    <?php if ($_alu_logo_id) : ?>
        <?php echo wp_get_attachment_image($_alu_logo_id, 'medium', false, ['class' => 'site-preloader-logo', 'alt' => '']); ?>
    <?php endif; ?>
    <div class="site-preloader-bar"><div class="site-preloader-fill"></div></div>
    <div class="site-preloader-pct">0%</div>
</div>
<?php endif; ?>

<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php if ($_alu_logo_id) : ?>
                <?php echo wp_get_attachment_image($_alu_logo_id, 'medium', false, ['class' => 'brand-logo', 'alt' => esc_attr(get_bloginfo('name'))]); ?>
            <?php else : ?>
                <span class="brand-text">
                    <span class="brand-name"><?php bloginfo('name'); ?></span>
                    <span class="brand-sub"><?php echo esc_html(get_bloginfo('description') ?: 'Aluminium Instruments'); ?></span>
                </span>
            <?php endif; ?>
        </a>

        <nav class="site-nav" aria-label="<?php esc_attr_e('Main navigation', 'torresan-bnb'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'fallback_cb'    => false,
                'container'      => false,
                'menu_class'     => 'menu-list',
                'depth'          => 2,
            ]);
            ?>
        </nav>

        <?php // Strumenti sempre presenti: su desktop in fila accanto alla nav,
              // su telefono restano solo Instagram e l'hamburger. ?>
        <div class="header-tools">
            <?php torresan_language_switcher('lang-switcher--nav'); ?>

            <?php if (! empty($_alu_settings['footer_instagram'])) : ?>
                <a class="header-ig" href="<?php echo esc_url($_alu_settings['footer_instagram']); ?>" target="_blank" rel="noopener" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.7"/>
                        <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.7"/>
                        <circle cx="17.2" cy="6.8" r="1.2" fill="currentColor"/>
                    </svg>
                </a>
            <?php endif; ?>

            <button class="theme-toggle" type="button" aria-label="<?php esc_attr_e('Toggle light/dark theme', 'torresan-bnb'); ?>">
                <svg class="theme-toggle-icon theme-toggle-icon--sun" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="4.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 2.5v2.5M12 19v2.5M4.2 4.2l1.8 1.8M18 18l1.8 1.8M2.5 12H5M19 12h2.5M4.2 19.8L6 18M18 6l1.8-1.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                <svg class="theme-toggle-icon theme-toggle-icon--moon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </button>

            <button class="header-cta" type="button" data-modal-open="contact-modal"><?php esc_html_e('Request Info', 'torresan-bnb'); ?></button>

            <?php // Apre lo stesso mega menu dell'hamburger flottante: il vecchio
                  // pannello .site-nav scorrevole non si apriva su telefono. ?>
            <button class="menu-toggle" type="button" data-mega-menu-toggle aria-label="<?php esc_attr_e('Open menu', 'torresan-bnb'); ?>" aria-expanded="false">
                <span class="wrapper-menu">
                    <span class="line-menu half start"></span>
                    <span class="line-menu"></span>
                    <span class="line-menu half end"></span>
                </span>
            </button>
        </div>
    </div>
</header>

<div id="contact-modal" class="contact-modal-overlay" data-modal>
    <div class="contact-modal">
        <button class="contact-modal-close" type="button" data-modal-close aria-label="<?php esc_attr_e('Close', 'torresan-bnb'); ?>">&times;</button>
        <p class="eyebrow"><?php esc_html_e('Let’s talk about your next instrument', 'torresan-bnb'); ?></p>
        <h3><?php esc_html_e('How can we help?', 'torresan-bnb'); ?></h3>
        <p><?php esc_html_e('Request a quote, book a showroom trial, or write to us directly about a custom configuration.', 'torresan-bnb'); ?></p>
        <a class="btn btn-lg" href="<?php echo torresan_booking_url(); ?>"><?php esc_html_e('Go to Contact', 'torresan-bnb'); ?></a>
        <?php torresan_social_icons('contact-modal-social'); ?>
    </div>
</div>

<!-- Floating logo — mirrors the hamburger on the opposite corner, same
     scroll-in behaviour. Desktop/tablet only: on phones the two corners are
     too close together and it would crowd the hamburger. -->
<?php if ($_alu_logo_id) : ?>
<a class="floating-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Back to Home', 'torresan-bnb'); ?>">
    <?php echo wp_get_attachment_image($_alu_logo_id, 'medium', false, ['class' => 'floating-logo-img', 'alt' => esc_attr(get_bloginfo('name'))]); ?>
</a>
<?php endif; ?>

<!-- Floating hamburger — fades in once the sticky header has scrolled past view -->
<button class="floating-menu-toggle" type="button" data-mega-menu-toggle aria-label="<?php esc_attr_e('Open menu', 'torresan-bnb'); ?>" aria-expanded="false">
    <span class="wrapper-menu">
        <span class="line-menu half start"></span>
        <span class="line-menu"></span>
        <span class="line-menu half end"></span>
    </span>
</button>

<div class="mega-menu" data-mega-menu>
    <div class="mega-menu-inner">
        <div class="mega-menu-media">
            <?php if ($_alu_mmv_url) : ?>
            <video autoplay muted loop playsinline preload="none">
                <source src="<?php echo esc_url($_alu_mmv_url); ?>" type="video/mp4">
            </video>
            <?php endif; ?>
            <?php if ($_alu_logo_id) : ?>
                <?php echo wp_get_attachment_image($_alu_logo_id, 'medium', false, ['class' => 'mega-menu-logo', 'alt' => esc_attr(get_bloginfo('name'))]); ?>
            <?php endif; ?>
        </div>
        <div class="mega-menu-panel">
            <nav aria-label="<?php esc_attr_e('Main menu', 'torresan-bnb'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'fallback_cb'    => false,
                    'container'      => false,
                    'menu_class'     => 'mega-menu-list',
                    'depth'          => 1,
                ]);
                ?>
            </nav>
            <?php // Su telefono l'header non ha il selettore: qui è l'unico punto
                  // da cui si può cambiare lingua. ?>
            <?php torresan_language_switcher('lang-switcher--mega'); ?>
            <?php torresan_social_icons('mega-menu-social'); ?>
        </div>
    </div>
</div>
