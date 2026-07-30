/**
 * Contact form — AJAX submission with nonce + status feedback
 */
function initContactForm($) {
    $('#torresan-contact-form').on('submit', function (e) {
        e.preventDefault();

        var $form   = $(this);
        var $status = $('#form-status');
        var $btn    = $form.find('[type="submit"]');

        $status.removeClass('is-success is-error').hide().text('');
        $btn.prop('disabled', true).text('Sending\u2026');

        var ajaxUrl   = window.torresanAjax ? window.torresanAjax.url   : '/wp-admin/admin-ajax.php';
        var ajaxNonce = window.torresanAjax ? window.torresanAjax.nonce : '';
        var data      = $form.serialize() + '&action=torresan_contact&nonce=' + ajaxNonce;

        $.post(ajaxUrl, data)
            .done(function (res) {
                if (res.success) {
                    $status.addClass('is-success').text(res.data.message).show();
                    $form[0].reset();
                } else {
                    $status.addClass('is-error').text(res.data.message).show();
                }
            })
            .fail(function () {
                $status.addClass('is-error').text('Network error. Please try again.').show();
            })
            .always(function () {
                $btn.prop('disabled', false).text('Send Enquiry');
            });
    });
}

module.exports = { initContactForm: initContactForm };
