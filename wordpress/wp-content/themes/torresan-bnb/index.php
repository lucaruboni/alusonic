<?php
get_header();
?>

<main class="container page-content">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class(); ?>>
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p>Nessun contenuto disponibile.</p>
    <?php endif; ?>
</main>

<?php
get_footer();
