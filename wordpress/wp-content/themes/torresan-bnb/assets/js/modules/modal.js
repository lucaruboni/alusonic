/**
 * Generic modal open/close — [data-modal-open="id"] shows #id, which must
 * carry [data-modal]; closes on the [data-modal-close] button, backdrop
 * click, or Escape. Used by the header's "Richiedi Info" contact modal.
 */
function initModal($) {
    var $modals = $('[data-modal]');
    if (! $modals.length) { return; }

    function open($modal) {
        $modal.addClass('is-open');
        $('body').css('overflow', 'hidden');
    }
    function close($modal) {
        $modal.removeClass('is-open');
        $('body').css('overflow', '');
    }

    $(document).on('click', '[data-modal-open]', function (e) {
        e.preventDefault();
        var id = $(this).data('modal-open');
        var $modal = $('#' + id);
        if ($modal.length) { open($modal); }
    });

    $(document).on('click', '[data-modal-close]', function () {
        close($(this).closest('[data-modal]'));
    });

    $modals.on('click', function (e) {
        if (e.target === this) { close($(this)); }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') { $modals.filter('.is-open').each(function () { close($(this)); }); }
    });
}

module.exports = { initModal: initModal };
