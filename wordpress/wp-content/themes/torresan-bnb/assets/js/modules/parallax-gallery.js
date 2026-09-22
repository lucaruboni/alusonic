/**
 * Alusonic — parallax gallery: scroll (vertical) + mouse (horizontal) parallax
 * on `.parallax-item img`. Fullscreen viewing is handled separately by
 * modules/lightbox.js via [data-lightbox] / [data-lightbox-group] on the
 * same markup.
 */
function initParallaxGallery($) {
    var coarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
    var $items = $('.parallax-item');
    if (! $items.length) { return; }

    if (coarse) { return; }

    /* ── Vertical parallax on scroll ── */
    var mx = 0;

    function applyScroll() {
        var vh = window.innerHeight || document.documentElement.clientHeight;
        $items.each(function (i) {
            var r = this.getBoundingClientRect();
            var center = r.top + r.height / 2 - vh / 2;
            var depth = (i % 3 === 0) ? 0.10 : (i % 3 === 1 ? -0.07 : 0.14);
            var ty = center * -depth;
            var img = this.querySelector('.parallax-item-media');
            if (img) {
                img.style.transform = 'translate3d(' + (mx * 10) + 'px,' + (ty * 0.4) + 'px,0) scale(1.03)';
            }
        });
    }

    /* ── Horizontal parallax on mouse move (per gallery section, the page
       can have one per model group) ── */
    $('.parallax-gallery').each(function () {
        $(this).on('mousemove', function (e) {
            var r = this.getBoundingClientRect();
            mx = (e.clientX - r.left) / r.width - 0.5;
            applyScroll();
        });
        $(this).on('mouseleave', function () { mx = 0; applyScroll(); });
    });

    var ticking = false;
    $(window).on('scroll resize', function () {
        if (! ticking) {
            window.requestAnimationFrame(function () { applyScroll(); ticking = false; });
            ticking = true;
        }
    });

    applyScroll();
}

module.exports = { initParallaxGallery: initParallaxGallery };
