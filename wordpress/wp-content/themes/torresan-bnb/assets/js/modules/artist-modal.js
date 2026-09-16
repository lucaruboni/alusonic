/**
 * Artist bio modal — click/tap an artist card to open a larger photo +
 * full biography. Data comes from data-* attributes on the card itself.
 */
function initArtistModal($) {
    var $cards = $('.artist-card[data-artist-name]');
    if (! $cards.length) { return; }

    var $overlay = $([
        '<div class="artist-modal-overlay" role="dialog" aria-modal="true" aria-label="Artist bio">',
        '  <div class="artist-modal">',
        '    <button class="artist-modal-close" aria-label="Close">&times;</button>',
        '    <div class="artist-modal-photo"><img src="" alt=""></div>',
        '    <div class="artist-modal-body">',
        '      <div class="artist-modal-band"></div>',
        '      <h3 class="artist-modal-name"></h3>',
        '      <div class="artist-modal-model"></div>',
        '      <p class="artist-modal-bio"></p>',
        '    </div>',
        '  </div>',
        '</div>'
    ].join(''));
    $('body').append($overlay);

    function open($card) {
        var photo = $card.data('artist-photo');
        $overlay.find('.artist-modal-photo img').attr({ src: photo, alt: $card.data('artist-name') });
        $overlay.find('.artist-modal-band').text($card.data('artist-band') || '');
        $overlay.find('.artist-modal-name').text($card.data('artist-name') || '');
        $overlay.find('.artist-modal-model').text($card.data('artist-model') ? 'Suona: ' + $card.data('artist-model') : '');
        $overlay.find('.artist-modal-bio').text($card.data('artist-bio') || '');
        $overlay.addClass('is-open');
        $('body').css('overflow', 'hidden');
    }
    function close() {
        $overlay.removeClass('is-open');
        $('body').css('overflow', '');
    }

    $cards.attr('tabindex', '0').attr('role', 'button');
    $cards.on('click', function (e) {
        e.preventDefault();
        open($(this));
    });
    $cards.on('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open($(this)); }
    });

    $overlay.on('click', function (e) {
        if ($(e.target).is($overlay)) { close(); }
    });
    $overlay.find('.artist-modal-close').on('click', close);
    $(document).on('keydown.artist-modal', function (e) {
        if (e.key === 'Escape' && $overlay.hasClass('is-open')) { close(); }
    });
}

module.exports = { initArtistModal: initArtistModal };
