/**
 * Carousel — auto-advance, prev/next, dots, touch swipe
 */
function initCarousel($) {
    $('.carousel').each(function () {
        var $c      = $(this);
        var $track  = $c.find('.carousel-track');
        var $dots   = $c.find('.dot');
        var total   = $c.find('.carousel-slide').length;
        var current = 0;
        var autoTimer;

        if (total < 2) {
            $c.find('.carousel-prev, .carousel-next, .carousel-dots').hide();
            return;
        }

        function goTo(idx) {
            current = ((idx % total) + total) % total;
            $track.css('transform', 'translateX(-' + (current * 100) + '%)');
            $dots.removeClass('active').eq(current).addClass('active');
        }

        function startAuto() {
            autoTimer = setInterval(function () { goTo(current + 1); }, 5000);
        }

        function stopAuto() {
            clearInterval(autoTimer);
        }

        $c.find('.carousel-prev').on('click', function () { stopAuto(); goTo(current - 1); startAuto(); });
        $c.find('.carousel-next').on('click', function () { stopAuto(); goTo(current + 1); startAuto(); });
        $dots.on('click', function () { stopAuto(); goTo($(this).index()); startAuto(); });

        $c.on('mouseenter', stopAuto).on('mouseleave', startAuto);

        // Touch swipe
        var txStart = 0;
        $c[0].addEventListener('touchstart', function (e) {
            txStart = e.touches[0].clientX;
        }, { passive: true });
        $c[0].addEventListener('touchend', function (e) {
            var diff = txStart - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                stopAuto();
                goTo(current + (diff > 0 ? 1 : -1));
                startAuto();
            }
        }, { passive: true });

        goTo(0);
        startAuto();
    });
}

module.exports = { initCarousel: initCarousel };
