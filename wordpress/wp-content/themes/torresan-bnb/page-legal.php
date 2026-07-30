<?php
/**
 * Template Name: Legal
 * Page slug: privacy-policy | cookie-policy | termini-di-servizio
 *
 * Minimal prose page for GDPR-required legal documents.
 * Content is managed via the WP editor (supports Iubenda shortcodes).
 */

get_header();
?>

<main id="main-content" class="page-template-page-legal">

    <section class="legal-hero section section--dark">
        <div class="container legal-hero__inner">
            <h1 class="legal-hero__title"><?php the_title(); ?></h1>
        </div>
    </section>

    <section class="legal-body section">
        <div class="container">
            <div class="legal-content prose">
                <?php
                while ( have_posts() ) :
                    the_post();
                    the_content();
                endwhile;
                ?>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
