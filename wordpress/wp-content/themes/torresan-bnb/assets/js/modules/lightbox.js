/**
 * Lightbox — overlay, group-aware, keyboard, touch swipe, scroll lock
 */
function initLightbox($) {
    var $overlay, $img, $counter;
    var images  = [];
    var current = 0;

    function build() {
        $overlay = $([
            '<div class="lightbox-overlay" role="dialog" aria-modal="true" aria-label="Image viewer">',
            '  <button class="lightbox-prev" aria-label="Previous image">&#8592;</button>',
            '  <div class="lightbox-inner">',
            '    <button class="lightbox-close" aria-label="Close image viewer">&times;</button>',
            '    <img class="lightbox-img" src="" alt="">',
            '  </div>',
            '  <button class="lightbox-next" aria-label="Next image">&#8594;</button>',
            '  <div class="lightbox-counter"></div>',
            '</div>'
        ].join(''));

        $('body').append($overlay);
        $img     = $overlay.find('.lightbox-img');
        $counter = $overlay.find('.lightbox-counter');

        // Close on backdrop click
        $overlay.on('click', function (e) {
            if ($(e.target).is($overlay)) close();
        });

        $overlay.find('.lightbox-close').on('click', close);
        $overlay.find('.lightbox-prev').on('click', function () { navigate(-1); });
        $overlay.find('.lightbox-next').on('click', function () { navigate(1); });

        // Keyboard navigation
        $(document).on('keydown.lightbox', function (e) {
            if (!$overlay.hasClass('is-open')) return;
            if (e.key === 'Escape')     close();
            if (e.key === 'ArrowLeft')  navigate(-1);
            if (e.key === 'ArrowRight') navigate(1);
        });

        // Touch swipe on overlay
        var lbTx = 0;
        $overlay[0].addEventListener('touchstart', function (e) {
            lbTx = e.touches[0].clientX;
        }, { passive: true });
        $overlay[0].addEventListener('touchend', function (e) {
            var diff = lbTx - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) navigate(diff > 0 ? 1 : -1);
        }, { passive: true });
    }

    function open($el) {
        var $group = $el.closest('[data-lightbox-group]');

        if ($group.length) {
            images = [];
            $group.find('[data-lightbox]').each(function () {
                images.push({
                    src: $(this).data('lightbox'),
                    alt: $(this).find('img').attr('alt') || ''
                });
            });
            current = $group.find('[data-lightbox]').index($el);
        } else {
            images  = [{ src: $el.data('lightbox'), alt: $el.find('img').attr('alt') || '' }];
            current = 0;
        }

        show(current);
        $overlay.addClass('is-open');
        $('body').css('overflow', 'hidden');

        var hasMultiple = images.length > 1;
        $overlay.find('.lightbox-prev, .lightbox-next').toggle(hasMultiple);
        $counter.toggle(hasMultiple);
    }

    function openRaw(src, alt) {
        images  = [{ src: src, alt: alt || '' }];
        current = 0;
        show(0);
        $overlay.addClass('is-open');
        $('body').css('overflow', 'hidden');
        $overlay.find('.lightbox-prev, .lightbox-next, .lightbox-counter').hide();
    }

    function close() {
        $overlay.removeClass('is-open');
        $('body').css('overflow', '');
    }

    function navigate(dir) {
        current = ((current + dir) % images.length + images.length) % images.length;
        show(current);
    }

    function show(idx) {
        var d = images[idx];
        $img.css({ width: '', height: '' });
        $img.attr({ src: d.src, alt: d.alt });
        $counter.text((idx + 1) + ' / ' + images.length);

        if ($img[0].complete) { fit(); } else { $img.one('load', fit); }
    }

    // Quanto si può ingrandire una foto oltre la sua dimensione reale prima
    // che si veda sgranata. Gran parte dell'archivio sta sotto gli 800px:
    // senza limite, a schermo pieno risulterebbe sfocata; senza ingrandimento
    // affatto, resterebbe minuscola al centro dello schermo.
    var MAX_UPSCALE = 2.2;

    function fit() {
        var el = $img[0];
        if (!el || !el.naturalWidth) { return; }

        var availW = $overlay.width() - 176;  // spazio lasciato alle frecce
        var availH = $overlay.height() * 0.9;

        var scale = Math.min(
            availW / el.naturalWidth,
            availH / el.naturalHeight,
            MAX_UPSCALE
        );

        $img.css({
            width:  Math.round(el.naturalWidth  * scale) + 'px',
            height: Math.round(el.naturalHeight * scale) + 'px'
        });
    }

    $(window).on('resize', function () {
        if ($overlay && $overlay.hasClass('is-open')) { fit(); }
    });

    build();

    // [data-lightbox] elements
    $(document).on('click', '[data-lightbox]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        open($(this));
    });

    // Carousel slides (if not already wrapped in [data-lightbox])
    $(document).on('click', '.carousel-slide', function (e) {
        e.preventDefault();
        var $slide = $(this);

        if ($slide.is('[data-lightbox]') || $slide.closest('[data-lightbox-group]').length) {
            return; // already handled above
        }

        var src = $slide.find('img').attr('src');
        var alt = $slide.find('img').attr('alt') || '';
        if (src) openRaw(src, alt);
    });
}

module.exports = { initLightbox: initLightbox };
