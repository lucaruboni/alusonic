<?php
/**
 * Template part: renders a single "sezione" content block.
 *
 * Expects global $sezione_post to be set (WP_Post object of type "sezione").
 * Called by torresan_render_sezioni() helper.
 */

if (! defined('ABSPATH') || empty($sezione_post)) {
    return;
}

$sid     = $sezione_post->ID;
$title   = get_the_title($sid);
$content = apply_filters('the_content', $sezione_post->post_content);
$tipo    = torresan_field('sezione_tipo', $sid) ?: 'solo_testo';
$eyebrow = torresan_field('sezione_eyebrow', $sid);
$layout  = torresan_field('sezione_layout', $sid) ?: 'immagine_destra';
$sfondo  = torresan_field('sezione_sfondo', $sid) ?: 'chiaro';
$img_url = torresan_image_url('sezione_immagine', 'section-card', $sid);
$gallery = torresan_gallery_ids('sezione_galleria', $sid);
$cta_txt = torresan_field('sezione_cta_testo', $sid);
$cta_url = torresan_field('sezione_cta_url', $sid);

$bg_class = 'sezione-bg-' . esc_attr($sfondo);
?>

<?php if ($tipo === 'testo_e_immagine') : ?>
<section class="section-content sezione-block <?php echo $bg_class; ?>">
    <div class="container">
        <div class="content-header-split">
            <div class="content-header-left">
                <?php if ($eyebrow) : ?>
                    <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
                <?php endif; ?>
                <?php if ($title) : ?>
                    <h2><?php echo esc_html($title); ?></h2>
                <?php endif; ?>
                <?php if ($cta_txt && $cta_url) : ?>
                    <a class="section-link" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_txt); ?></a>
                <?php endif; ?>
            </div>
            <?php if (trim($sezione_post->post_content)) : ?>
            <div class="content-header-right">
                <div class="wysiwyg-content"><?php echo $content; ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        $imgs = [];
        if ($gallery) {
            foreach ($gallery as $gid) {
                $u = wp_get_attachment_image_url($gid, 'section-card');
                if ($u) $imgs[] = ['url' => $u, 'alt' => get_post_meta($gid, '_wp_attachment_image_alt', true)];
            }
        }
        if (! $imgs && $img_url) {
            $imgs[] = ['url' => $img_url, 'alt' => $title];
        }
        ?>
        <?php if ($imgs) : ?>
        <div class="content-images-row">
            <?php foreach ($imgs as $i => $im) : ?>
            <div class="grid-img<?php echo $i === 0 ? ' grid-img--large' : ' grid-img--small'; ?>">
                <img src="<?php echo esc_url($im['url']); ?>" alt="<?php echo esc_attr($im['alt']); ?>" loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php elseif ($tipo === 'solo_testo') : ?>
<section class="section-content sezione-block <?php echo $bg_class; ?>">
    <div class="container">
        <div class="wysiwyg-content" style="text-align:center">
            <?php if ($eyebrow) : ?>
                <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>
            <?php if ($title) : ?>
                <h2><?php echo esc_html($title); ?></h2>
            <?php endif; ?>
            <?php if (trim($sezione_post->post_content)) : ?>
                <?php echo $content; ?>
            <?php endif; ?>
            <?php if ($cta_txt && $cta_url) : ?>
                <a class="btn" href="<?php echo esc_url($cta_url); ?>" style="margin-top:1.5rem"><?php echo esc_html($cta_txt); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php elseif ($tipo === 'galleria_foto') : ?>
<section class="section-content sezione-block <?php echo $bg_class; ?>">
    <div class="container">
        <?php if ($eyebrow || $title) : ?>
        <div class="section-header" style="text-align:center;margin-bottom:2.5rem">
            <?php if ($eyebrow) : ?>
                <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>
            <?php if ($title) : ?>
                <h2><?php echo esc_html($title); ?></h2>
            <?php endif; ?>
            <?php if (trim($sezione_post->post_content)) : ?>
                <div class="wysiwyg-content"><?php echo $content; ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($gallery) : ?>
        <div class="photo-gallery">
            <?php foreach ($gallery as $img_id) :
                $g_url = wp_get_attachment_image_url($img_id, 'section-card');
                if (! $g_url) continue;
            ?>
                <div class="gallery-item">
                    <img src="<?php echo esc_url($g_url); ?>" alt="<?php echo esc_attr(get_post_meta($img_id, '_wp_attachment_image_alt', true)); ?>" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php elseif ($tipo === 'citazione') : ?>
<section class="section-content sezione-block sezione-quote <?php echo $bg_class; ?>">
    <div class="container container-narrow" style="text-align:center">
        <?php if ($eyebrow) : ?>
            <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>
        <?php if (trim($sezione_post->post_content)) : ?>
            <blockquote class="sezione-blockquote">
                <?php echo $content; ?>
            </blockquote>
        <?php endif; ?>
        <?php if ($title) : ?>
            <cite class="sezione-cite"><?php echo esc_html($title); ?></cite>
        <?php endif; ?>
    </div>
</section>

<?php elseif ($tipo === 'banner_cta') : ?>
<section class="section-content sezione-block sezione-banner" <?php if ($img_url) : ?>style="background-image:url('<?php echo esc_url($img_url); ?>')"<?php endif; ?>>
    <div class="sezione-banner-overlay"></div>
    <div class="container sezione-banner-content">
        <?php if ($eyebrow) : ?>
            <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>
        <?php if ($title) : ?>
            <h2><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        <?php if (trim($sezione_post->post_content)) : ?>
            <div class="wysiwyg-content"><?php echo $content; ?></div>
        <?php endif; ?>
        <?php if ($cta_txt && $cta_url) : ?>
            <a class="btn btn-light" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_txt); ?></a>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
