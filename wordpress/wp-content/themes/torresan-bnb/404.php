<?php
/**
 * 404 Not Found
 *
 * WordPress renders this template whenever no content matches the requested URL.
 * Maintains the full site header/footer and uses the standard .page-hero pattern
 * so the error page is visually indistinguishable from any other page.
 *
 * HTTP 404 and no-cache headers are set explicitly so CDNs / proxies never cache
 * a "not found" response as a valid page.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

status_header( 404 );
nocache_headers();
get_header();
?>

<main id="main-content">

    <section class="page-hero page-hero--404">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <p class="eyebrow">Errore 404</p>
            <h1><?php esc_html_e( 'Page not found', 'torresan-bnb' ); ?></h1>
            <p class="hero-subtitle">
                <?php esc_html_e(
                    'The page you are looking for does not exist or has been moved.', 'torresan-bnb'
                ); ?>
            </p>
            <div class="hero-404-actions">
                <a class="btn btn-light" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    ← <?php esc_html_e( 'Back to Home', 'torresan-bnb' ); ?>
                </a>
                <a class="btn" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
                    <?php esc_html_e( 'Get in touch', 'torresan-bnb' ); ?>
                </a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
