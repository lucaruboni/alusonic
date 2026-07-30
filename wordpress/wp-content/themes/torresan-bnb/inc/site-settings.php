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
        'footer_tagline'   => 'Bassi e chitarre in alluminio aerospaziale, lavorati a Morciano di Romagna. Un pezzo unico, un suono che non esiste altrove.',
        'footer_address'   => "Morciano di Romagna (RN)\nItalia — su appuntamento",
        'footer_phone'     => '',
        'footer_email'     => 'info@alusonic.it',
        'footer_instagram' => 'https://www.instagram.com/alusonic/',
        'footer_est'       => 'Dal 2010, fatto in Italia.',
        'footer_slogan'    => 'Play Different, Feel a New Sound Experience',
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
        __('Impostazioni Sito BnB', 'torresan-bnb'),
        __('Impostazioni Sito BnB', 'torresan-bnb'),
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
        if ($key === 'logo_image_id') {
            $clean[$key] = absint($raw) ?: '';
        } elseif ($key === 'footer_instagram') {
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
        <h1><?php esc_html_e('Impostazioni Sito BnB', 'torresan-bnb'); ?></h1>
        <p><?php esc_html_e('Impostazioni globali visibili su tutte le pagine (footer, branding).', 'torresan-bnb'); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields('torresan_site_group'); ?>

            <h2>Logo</h2>
            <table class="form-table">
                <tr>
                    <th><label><?php esc_html_e('Logo sito', 'torresan-bnb'); ?></label></th>
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
                        <button type="button" class="button" id="tsb-logo-upload"><?php esc_html_e('Scegli / Cambia logo', 'torresan-bnb'); ?></button>
                        <?php if ($logo_id) : ?>
                            <button type="button" class="button" id="tsb-logo-remove" style="margin-left:.5rem;"><?php esc_html_e('Rimuovi', 'torresan-bnb'); ?></button>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e('Se non impostato, viene mostrato il testo "TSB".', 'torresan-bnb'); ?></p>
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
            </table>

            <h2>Footer</h2>
            <table class="form-table">
                <tr>
                    <th><label for="footer_tagline">Descrizione (colonna brand)</label></th>
                    <td><textarea id="footer_tagline" name="torresan_site_settings[footer_tagline]" class="large-text" rows="3"><?php echo esc_textarea($s['footer_tagline']); ?></textarea></td>
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
                    <th><label for="footer_instagram">Instagram</label></th>
                    <td>
                        <input id="footer_instagram" name="torresan_site_settings[footer_instagram]" type="url" class="regular-text" placeholder="https://instagram.com/alusonic" value="<?php echo esc_attr($s['footer_instagram']); ?>">
                        <p class="description">URL completo del profilo Instagram.</p>
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

            <?php submit_button(__('Salva impostazioni', 'torresan-bnb')); ?>
        </form>
    </div>
    <?php
}
