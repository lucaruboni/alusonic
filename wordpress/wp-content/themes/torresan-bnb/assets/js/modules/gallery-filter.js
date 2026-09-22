/**
 * Galleria — stessa UX di filtro della pagina Modelli, ma applicata a intere
 * sezioni per-modello (.gallery-model-group) invece che a singole card.
 */
function initGalleryFilter($) {
    var $groups = $('.gallery-model-group');
    if (!$groups.length) return;

    var $panel  = $('#gallery-filters');
    var $toggle = $panel.length ? $('.filters-toggle[aria-controls="gallery-filters"]') : $();
    var $count  = $toggle.find('.filters-toggle-count');
    var $reset  = $panel.find('.filters-reset');
    var $search = $panel.closest('.models-toolbar').find('.models-search-input');
    var $empty  = $('.models-empty');

    var state = { family: 'all', type: 'all' };

    function activeCount() {
        var n = 0;
        if (state.family !== 'all') n++;
        if (state.type !== 'all') n++;
        return n;
    }

    function apply() {
        var q = ($search.val() || '').toLowerCase().trim();
        var visible = 0;

        $groups.each(function () {
            var $g = $(this);
            var okFamily = state.family === 'all' || String($g.data('family')) === state.family;
            var okType   = state.type === 'all' || String($g.data('type')) === state.type;
            var okText   = !q || String($g.data('name') || '').indexOf(q) !== -1;

            var show = okFamily && okType && okText;
            $g.css('display', show ? '' : 'none');
            if (show) visible++;
        });

        $empty.prop('hidden', visible !== 0);

        var n = activeCount();
        $count.prop('hidden', n === 0).text(n);
        $reset.prop('hidden', n === 0 && !q);
    }

    $panel.on('click', '.filter-bar[data-filter-group] .filter-btn', function () {
        var $bar = $(this).closest('.filter-bar');
        var group = $bar.data('filter-group');

        state[group] = $(this).data('filter');
        $bar.find('.filter-btn').removeClass('is-active');
        $(this).addClass('is-active');
        apply();
    });

    $reset.on('click', function () {
        state = { family: 'all', type: 'all' };
        $panel.find('.filter-bar[data-filter-group]').each(function () {
            var $bar = $(this);
            $bar.find('.filter-btn').removeClass('is-active')
                .filter('[data-filter="all"]').addClass('is-active');
        });
        $search.val('');
        apply();
    });

    $toggle.on('click', function () {
        var open = $panel.hasClass('is-open');
        $panel.toggleClass('is-open', !open);
        $(this).attr('aria-expanded', open ? 'false' : 'true');
    });

    $search.on('input', apply);

    apply();
}

module.exports = { initGalleryFilter: initGalleryFilter };
