/**
 * Models listing — category filter (client-side, by data-type).
 */
function initModelsFilter($) {
    var $bar = $('.filter-bar');
    if (!$bar.length) return;

    $bar.on('click', '.filter-btn', function () {
        var cat = $(this).data('filter');
        $bar.find('.filter-btn').removeClass('is-active');
        $(this).addClass('is-active');

        $('.models-grid .model-card').each(function () {
            var show = (cat === 'all') || ($(this).data('type') === cat);
            $(this).css('display', show ? '' : 'none');
        });
    });
}

module.exports = { initModelsFilter: initModelsFilter };
