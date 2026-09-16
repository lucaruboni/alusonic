<?php
/**
 * Template Name: Video
 * Curated YouTube videos from the official Alusonic channel.
 */

get_header();

$pid      = get_the_ID();
$eyebrow  = torresan_field('hero_eyebrow', $pid) ?: __('Video', 'torresan-bnb');
$subtitle = torresan_field('hero_subtitle', $pid);

$yt      = torresan_field('media_youtube', $pid);
$videos  = alusonic_parse_pairs(torresan_field('media_videos', $pid)); // [youtube_id, title]
?>

<main>
    <section class="page-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <?php if ($eyebrow) : ?><p class="eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
            <h1><?php the_title(); ?></h1>
            <?php if ($subtitle) : ?><p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
        </div>
    </section>

    <section class="section-tight">
        <div class="container">
            <?php if ($videos) : ?>
            <div class="video-grid">
                <?php foreach ($videos as [$yt_raw, $title]) : $yt_id = alusonic_youtube_id($yt_raw); if (! $yt_id) continue; ?>
                <div class="video-item">
                    <div class="video-embed">
                        <iframe
                            src="https://www.youtube-nocookie.com/embed/<?php echo esc_attr($yt_id); ?>"
                            title="<?php echo esc_attr($title); ?>"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                    </div>
                    <?php if ($title) : ?><div class="video-title"><?php echo esc_html($title); ?></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <p style="text-align:center;color:var(--muted,#999)"><?php esc_html_e('No videos available at the moment.', 'torresan-bnb'); ?></p>
            <?php endif; ?>

            <?php if ($yt) : ?>
            <div style="text-align:center;margin-top:3rem">
                <a class="btn" href="<?php echo esc_url($yt); ?>" target="_blank" rel="noopener"><?php esc_html_e('All videos on the YouTube channel', 'torresan-bnb'); ?> ↗</a>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
