/**
 * Catalogo modelli — filtri combinabili + ricerca testuale.
 *
 * Tre criteri indipendenti che si sommano:
 *   linea      Django, Django GTS/H, The Doom, J-Special, chitarre
 *   tipo       basso, chitarra
 *   signature  solo i modelli sviluppati con un artista
 *
 * "Signature" è volutamente separato dalla linea: non è una famiglia ma una
 * caratteristica trasversale (il Supreme Carbon è un Django ed è il signature
 * di Cass Lewis), quindi deve potersi combinare con le altre.
 *
 * Su telefono i gruppi stanno in un pannello richiudibile, con il numero di
 * filtri attivi mostrato sul pulsante.
 */
function initModelsFilter($) {
    var $grid = $('.models-grid');
    if (!$grid.length) return;

    var $panel     = $('.models-filters');
    var $toggle    = $('.filters-toggle');
    var $count     = $('.filters-toggle-count');
    var $reset     = $('.filters-reset');
    var $search    = $('.models-search-input');
    var $empty     = $('.models-empty');
    var $signature = $('[data-filter-signature]');

    var state = { family: 'all', type: 'all', signature: false };

    function activeCount() {
        var n = 0;
        if (state.family !== 'all') n++;
        if (state.type !== 'all') n++;
        if (state.signature) n++;
        return n;
    }

    function apply() {
        var q = ($search.val() || '').toLowerCase().trim();
        var visible = 0;

        $grid.find('.model-card').each(function () {
            var $card = $(this);
            var okFamily = state.family === 'all' || String($card.data('family')) === state.family;
            var okType   = state.type === 'all' || String($card.data('type')) === state.type;
            var okSign   = !state.signature || String($card.data('signature')) === '1';
            var okText   = !q || String($card.data('name') || '').indexOf(q) !== -1;

            var show = okFamily && okType && okSign && okText;
            $card.css('display', show ? '' : 'none');
            if (show) visible++;
        });

        $empty.prop('hidden', visible !== 0);

        var n = activeCount();
        $count.prop('hidden', n === 0).text(n);
        $reset.prop('hidden', n === 0 && !q);
    }

    // Gruppi a selezione singola (linea, tipo)
    $('.filter-bar[data-filter-group]').on('click', '.filter-btn', function () {
        var $bar = $(this).closest('.filter-bar');
        var group = $bar.data('filter-group');

        state[group] = $(this).data('filter');
        $bar.find('.filter-btn').removeClass('is-active');
        $(this).addClass('is-active');
        apply();
    });

    // Interruttore signature
    $signature.on('click', function () {
        state.signature = !state.signature;
        $(this).toggleClass('is-active', state.signature)
               .attr('aria-pressed', state.signature ? 'true' : 'false');
        apply();
    });

    $reset.on('click', function () {
        state = { family: 'all', type: 'all', signature: false };
        $('.filter-bar[data-filter-group]').each(function () {
            var $bar = $(this);
            $bar.find('.filter-btn').removeClass('is-active')
                .filter('[data-filter="all"]').addClass('is-active');
        });
        $signature.removeClass('is-active').attr('aria-pressed', 'false');
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

module.exports = { initModelsFilter: initModelsFilter };
