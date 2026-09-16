<?php

if (! defined('ABSPATH')) {
    exit;
}

/* ═══════════════════════════════════════════
 *  Global Site Settings (Footer, Branding)
 * ═══════════════════════════════════════════ */

function torresan_site_settings_defaults(): array
{
    return [
        'logo_image_id'    => '',
        'mega_menu_video_id' => '',
        'footer_tagline'    => 'Bassi e chitarre in alluminio aerospaziale, lavorati a Morciano di Romagna. Un pezzo unico, un suono che non esiste altrove.',
        'footer_legal_name' => 'Alusonic Audio Technology',
        'footer_address'    => "Via Serrata, 4a — 47833 Morciano di Romagna (RN)\nItalia",
        'footer_phone'      => '+39 366 2089806',
        'footer_email'      => 'info@alusonic.com',
        'footer_vat'        => 'P.IVA 03865350403',
        'footer_instagram'  => 'https://www.instagram.com/alusonic/',
        'footer_facebook'   => 'https://www.facebook.com/alusonicaluminiuminstruments',
        'footer_youtube'    => 'https://www.youtube.com/user/Alusonic',
        'footer_est'        => 'Tutti i diritti riservati.',
        'footer_credit'     => 'Made by LP',
        'footer_slogan'     => 'Play Different, Feel a New Sound Experience',
    ];
}

function torresan_get_site_settings(): array
{
    $saved = get_option('torresan_site_settings', []);

    if (! is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, torresan_site_settings_defaults());
}

function torresan_site_settings_menu(): void
{
    add_theme_page(
        __('Site Settings', 'torresan-bnb'),
        __('Site Settings', 'torresan-bnb'),
        'manage_options',
        'torresan-bnb-settings',
        'torresan_site_settings_page'
    );
}
add_action('admin_menu', 'torresan_site_settings_menu');

function torresan_site_settings_init(): void
{
    register_setting(
        'torresan_site_group',
        'torresan_site_settings',
        [
            'type'              => 'array',
            'sanitize_callback' => 'torresan_sanitize_site_settings',
            'default'           => torresan_site_settings_defaults(),
        ]
    );
}
add_action('admin_init', 'torresan_site_settings_init');

function torresan_sanitize_site_settings($input): array
{
    $defaults = torresan_site_settings_defaults();
    $input    = is_array($input) ? $input : [];
    $clean    = [];

    foreach ($defaults as $key => $value) {
        $raw = $input[$key] ?? $value;
        if (in_array($key, ['logo_image_id', 'mega_menu_video_id'], true)) {
            $clean[$key] = absint($raw) ?: '';
        } elseif (in_array($key, ['footer_instagram', 'footer_facebook', 'footer_youtube'], true)) {
            $clean[$key] = esc_url_raw(trim((string) $raw));
        } elseif (in_array($key, ['footer_address', 'footer_tagline'], true)) {
            $clean[$key] = sanitize_textarea_field((string) $raw);
        } else {
            $clean[$key] = sanitize_text_field((string) $raw);
        }
    }

    return $clean;
}

function torresan_site_settings_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $s = torresan_get_site_settings();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Site Settings', 'torresan-bnb'); ?></h1>
        <p><?php esc_html_e('Global settings shown on every page (footer, branding).', 'torresan-bnb'); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields('torresan_site_group'); ?>

            <h2>Logo</h2>
            <table class="form-table">
                <tr>
                    <th><label><?php esc_html_e('Site logo', 'torresan-bnb'); ?></label></th>
                    <td>
                        <?php
                        $logo_id  = absint($s['logo_image_id'] ?? 0);
                        $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
                        ?>
                        <div id="tsb-logo-wrap">
                            <?php if ($logo_url) : ?>
                                <img id="tsb-logo-preview" src="<?php echo esc_url($logo_url); ?>" style="max-height:80px;display:block;margin-bottom:.5rem;">
                            <?php else : ?>
                                <img id="tsb-logo-preview" src="" style="max-height:80px;display:none;margin-bottom:.5rem;">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" id="tsb-logo-id" name="torresan_site_settings[logo_image_id]" value="<?php echo esc_attr($logo_id ?: ''); ?>">
                        <button type="button" class="button" id="tsb-logo-upload"><?php esc_html_e('Choose / Change logo', 'torresan-bnb'); ?></button>
                        <?php if ($logo_id) : ?>
                            <button type="button" class="button" id="tsb-logo-remove" style="margin-left:.5rem;"><?php esc_html_e('Remove', 'torresan-bnb'); ?></button>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e('If not set, the site name is shown instead.', 'torresan-bnb'); ?></p>
                        <script>
                        jQuery(function($){
                            var frame;
                            $('#tsb-logo-upload').on('click', function(e){
                                e.preventDefault();
                                if (frame) { frame.open(); return; }
                                frame = wp.media({ title: 'Seleziona logo', button: { text: 'Usa questo logo' }, multiple: false });
                                frame.on('select', function(){
                                    var att = frame.state().get('selection').first().toJSON();
                                    $('#tsb-logo-id').val(att.id);
                                    $('#tsb-logo-preview').attr('src', att.url).show();
                                    $('#tsb-logo-remove').show();
                                });
                                frame.open();
                            });
                            $('#tsb-logo-remove').on('click', function(e){
                                e.preventDefault();
                                $('#tsb-logo-id').val('');
                                $('#tsb-logo-preview').attr('src','').hide();
                                $(this).hide();
                            });
                        });
                        </script>
                    </td>
                </tr>
                <tr>
                    <th><label>Video menu a schermo intero</label></th>
                    <td>
                        <?php
                        $mmv_id  = absint($s['mega_menu_video_id'] ?? 0);
                        $mmv_url = $mmv_id ? wp_get_attachment_url($mmv_id) : '';
                        ?>
                        <p id="tsb-mmv-preview"><?php echo $mmv_url ? esc_html(basename($mmv_url)) : __('No videos selected.', 'torresan-bnb'); ?></p>
                        <input type="hidden" id="tsb-mmv-id" name="torresan_site_settings[mega_menu_video_id]" value="<?php echo esc_attr($mmv_id ?: ''); ?>">
                        <button type="button" class="button" id="tsb-mmv-upload"><?php esc_html_e('Choose / Change video', 'torresan-bnb'); ?></button>
                        <?php if ($mmv_id) : ?>
                            <button type="button" class="button" id="tsb-mmv-remove" style="margin-left:.5rem;"><?php esc_html_e('Remove', 'torresan-bnb'); ?></button>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e('Video shown on the left of the full-screen menu (the floating hamburger icon that appears on scroll).', 'torresan-bnb'); ?></p>
                        <script>
                        jQuery(function($){
                            var frame;
                            $('#tsb-mmv-upload').on('click', function(e){
                                e.preventDefault();
                                if (frame) { frame.open(); return; }
                                frame = wp.media({ title: 'Seleziona video', library: { type: 'video' }, button: { text: 'Usa questo video' }, multiple: false });
                                frame.on('select', function(){
                                    var att = frame.state().get('selection').first().toJSON();
                                    $('#tsb-mmv-id').val(att.id);
                                    $('#tsb-mmv-preview').text(att.filename || att.url);
                                    $('#tsb-mmv-remove').show();
                                });
                                frame.open();
                            });
                            $('#tsb-mmv-remove').on('click', function(e){
                                e.preventDefault();
                                $('#tsb-mmv-id').val('');
                                $('#tsb-mmv-preview').text('Nessun video selezionato.');
                                $(this).hide();
                            });
                        });
                        </script>
                    </td>
                </tr>
            </table>

            <h2>Footer</h2>
            <table class="form-table">
                <tr>
                    <th><label for="footer_tagline">Descrizione (colonna brand)</label></th>
                    <td><textarea id="footer_tagline" name="torresan_site_settings[footer_tagline]" class="large-text" rows="3"><?php echo esc_textarea($s['footer_tagline']); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="footer_legal_name">Ragione sociale</label></th>
                    <td>
                        <input id="footer_legal_name" name="torresan_site_settings[footer_legal_name]" type="text" class="regular-text" value="<?php echo esc_attr($s['footer_legal_name']); ?>">
                        <p class="description">Nome legale dell'azienda, mostrato nella riga copyright in fondo al footer.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="footer_address">Showroom / Indirizzo</label></th>
                    <td><textarea id="footer_address" name="torresan_site_settings[footer_address]" class="large-text" rows="2"><?php echo esc_textarea($s['footer_address']); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="footer_email">Email</label></th>
                    <td><input id="footer_email" name="torresan_site_settings[footer_email]" type="email" class="regular-text" value="<?php echo esc_attr($s['footer_email']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="footer_phone">Telefono</label></th>
                    <td><input id="footer_phone" name="torresan_site_settings[footer_phone]" type="text" class="regular-text" value="<?php echo esc_attr($s['footer_phone']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="footer_vat">Partita IVA</label></th>
                    <td><input id="footer_vat" name="torresan_site_settings[footer_vat]" type="text" class="regular-text" value="<?php echo esc_attr($s['footer_vat']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="footer_credit">Credito realizzazione</label></th>
                    <td>
                        <input id="footer_credit" name="torresan_site_settings[footer_credit]" type="text" class="regular-text" value="<?php echo esc_attr($s['footer_credit']); ?>">
                        <p class="description">Es. "Made by LP" — mostrato in fondo al footer. Lascia vuoto per nasconderlo.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="footer_instagram">Instagram</label></th>
                    <td>
                        <input id="footer_instagram" name="torresan_site_settings[footer_instagram]" type="url" class="regular-text" placeholder="https://instagram.com/alusonic" value="<?php echo esc_attr($s['footer_instagram']); ?>">
                        <p class="description">URL completo del profilo Instagram.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="footer_facebook">Facebook</label></th>
                    <td>
                        <input id="footer_facebook" name="torresan_site_settings[footer_facebook]" type="url" class="regular-text" placeholder="https://facebook.com/alusonic" value="<?php echo esc_attr($s['footer_facebook']); ?>">
                        <p class="description">URL completo della pagina Facebook. Lascia vuoto per nascondere l'icona.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="footer_youtube">YouTube</label></th>
                    <td>
                        <input id="footer_youtube" name="torresan_site_settings[footer_youtube]" type="url" class="regular-text" placeholder="https://youtube.com/user/Alusonic" value="<?php echo esc_attr($s['footer_youtube']); ?>">
                        <p class="description">URL completo del canale YouTube. Lascia vuoto per nascondere l'icona.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="footer_est">Riga copyright (dopo l'anno)</label></th>
                    <td><input id="footer_est" name="torresan_site_settings[footer_est]" type="text" class="large-text" value="<?php echo esc_attr($s['footer_est']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="footer_slogan">Slogan (corsivo, in fondo)</label></th>
                    <td><input id="footer_slogan" name="torresan_site_settings[footer_slogan]" type="text" class="large-text" value="<?php echo esc_attr($s['footer_slogan']); ?>"></td>
                </tr>
            </table>

            <?php submit_button(__('Save settings', 'torresan-bnb')); ?>
        </form>
    </div>
    <?php
}

/**
 * Instagram/Facebook/YouTube icon links, built from the global site
 * settings — shared between the header contact modal and the footer.
 * Skips any channel left empty in settings.
 */
function torresan_social_icons(string $extra_class = ''): void
{
    $class = trim('social-icons ' . $extra_class);
    $s = torresan_get_site_settings();

    $channels = [
        'instagram' => [
            'url'  => $s['footer_instagram'] ?? '',
            'path' => 'M12 2.2c2.7 0 3 0 4.1.06 1.1.05 1.8.22 2.2.37.5.2 1 .5 1.4 1 .4.4.7.9 1 1.4.16.4.33 1.1.37 2.2.06 1.2.06 1.5.06 4.1s0 3-.06 4.1c-.05 1.1-.22 1.8-.37 2.2-.2.5-.5 1-1 1.4-.4.4-.9.7-1.4 1-.4.16-1.1.33-2.2.37-1.2.06-1.5.06-4.1.06s-3 0-4.1-.06c-1.1-.05-1.8-.22-2.2-.37-.5-.2-1-.5-1.4-1-.4-.4-.7-.9-1-1.4-.16-.4-.33-1.1-.37-2.2C2.2 15 2.2 14.7 2.2 12s0-3 .06-4.1c.05-1.1.22-1.8.37-2.2.2-.5.5-1 1-1.4.4-.4.9-.7 1.4-1 .4-.16 1.1-.33 2.2-.37C8.4 2.86 8.7 2.86 12 2.86v-.66Zm0 3.55a6.25 6.25 0 1 0 0 12.5 6.25 6.25 0 0 0 0-12.5Zm0 10.3a4.05 4.05 0 1 1 0-8.1 4.05 4.05 0 0 1 0 8.1Zm6.5-10.5a1.46 1.46 0 1 1-2.92 0 1.46 1.46 0 0 1 2.92 0Z',
        ],
        'facebook' => [
            'url'  => $s['footer_facebook'] ?? '',
            'path' => 'M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12Z',
        ],
        'youtube' => [
            'url'  => $s['footer_youtube'] ?? '',
            'path' => 'M23.5 7.2s-.23-1.64-.94-2.36c-.9-.94-1.9-.95-2.37-1C16.86 3.6 12 3.6 12 3.6h-.01s-4.85 0-8.18.24c-.47.05-1.47.06-2.37 1C.72 5.56.5 7.2.5 7.2S.27 9.12.27 11.04v1.8c0 1.92.23 3.84.23 3.84s.23 1.64.93 2.36c.9.95 2.08.92 2.6 1.02 1.9.18 8 .24 8 .24s4.86 0 8.19-.25c.47-.06 1.47-.06 2.37-1.01.71-.72.94-2.36.94-2.36s.23-1.92.23-3.84v-1.8c0-1.92-.23-3.84-.23-3.84ZM9.55 14.9V8.6l6.02 3.16-6.02 3.15Z',
        ],
    ];
    ?>
    <div class="<?php echo esc_attr($class); ?>">
        <?php foreach ($channels as $key => $c) :
            if (empty($c['url'])) { continue; }
        ?>
        <a href="<?php echo esc_url($c['url']); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr(ucfirst($key)); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?php echo esc_attr($c['path']); ?>"></path></svg>
        </a>
        <?php endforeach; ?>
    </div>
    <?php
}
