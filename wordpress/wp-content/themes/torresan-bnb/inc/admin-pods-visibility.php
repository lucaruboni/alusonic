<?php
if (! defined('ABSPATH')) {
    exit;
}

/*
 * Alusonic — Pods field-group visibility on the Page edit screen.
 *
 * Every custom page (Homepage, Modelli, Artisti, Contatti, …) is the same
 * `page` post type, distinguished only by which template it uses. Pods
 * field groups are registered against the whole post type, so by default
 * every page's edit screen shows every OTHER page's groups too (e.g. the
 * Artisti page showing the Homepage's hero-video field). This hides the
 * groups that don't belong to the page currently being edited, based on
 * its selected template (and front-page status for the Homepage, which
 * has no explicit template of its own).
 *
 * Pure admin-screen JS — doesn't touch the public site or the underlying
 * Pods data at all, so it's safe to add/remove without any migration.
 */

function torresan_pods_group_visibility_map(): array
{
    // group NAME (stable, ours) => which template(s) it belongs to ('' =
    // only the site's front page, which has no _wp_page_template of its
    // own). Labels are looked up live from Pods rather than hardcoded here
    // — save_group() doesn't always overwrite a group's stored label on
    // re-seed, so a label baked into this file could silently drift out of
    // sync with the one Pods actually renders the metabox with.
    $templates_by_name = [
        'home_stats'             => [''],
        'home_materials'         => [''],
        'home_models'            => [''],
        'home_instagram'         => [''],
        'home_cta'               => [''],
        'pagina_about'           => ['page-about.php'],
        'informazioni_contatto'  => ['page-contact.php'],
        'pagina_showroom'        => ['page-showrooms.php'],
        'pagina_repairs'         => ['page-repairs.php'],
        'pagina_media'           => ['page-video.php'],
        'domande_frequenti'      => ['page-faq.php'],
    ];

    if (! function_exists('pods_api')) {
        return [];
    }

    $api  = pods_api();
    $pod  = $api->load_pod(['name' => 'page']);
    if (! $pod) {
        return [];
    }
    $groups = $api->load_groups(['pod_id' => $pod['id']]);

    $map = [];
    foreach ($groups as $group) {
        if (! isset($templates_by_name[$group['name']])) {
            continue; // shared groups (e.g. hero) stay visible everywhere
        }
        $map[] = [
            'id'        => 'pods-meta-' . sanitize_title($group['label']),
            'templates' => $templates_by_name[$group['name']],
        ];
    }
    return $map;
}

function torresan_pods_group_visibility_script(string $hook): void
{
    global $post;
    if (! in_array($hook, ['post.php', 'post-new.php'], true) || ! $post || $post->post_type !== 'page') {
        return;
    }

    $current_template = (string) get_post_meta($post->ID, '_wp_page_template', true);
    $is_front_page     = $post->ID && (int) get_option('page_on_front') === (int) $post->ID;
    $current            = $is_front_page ? '' : $current_template;

    $map = torresan_pods_group_visibility_map();
    ?>
    <script>
    (function () {
        var groups = <?php echo wp_json_encode($map); ?>;

        function apply(template, isFront) {
            var current = isFront ? '' : template;
            groups.forEach(function (g) {
                var box = document.getElementById(g.id);
                if (! box) { return; }
                box.style.display = g.templates.indexOf(current) !== -1 ? '' : 'none';
            });
        }

        apply('<?php echo esc_js($current_template); ?>', <?php echo $is_front_page ? 'true' : 'false'; ?>);

        var select = document.getElementById('page_template');
        if (select) {
            select.addEventListener('change', function () {
                // The homepage has no explicit template, so this dropdown
                // can't tell us "this is now the front page" — only real
                // template changes are reflected live.
                apply(select.value, false);
            });
        }
    })();
    </script>
    <?php
}
add_action('admin_footer-post.php', function () { torresan_pods_group_visibility_script('post.php'); });
add_action('admin_footer-post-new.php', function () { torresan_pods_group_visibility_script('post-new.php'); });
