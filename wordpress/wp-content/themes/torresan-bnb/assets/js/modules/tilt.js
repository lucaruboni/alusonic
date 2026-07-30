/**
 * 3D tilt effect on hover — Alusonic model cards & single-model hero.
 * Elements opt in via [data-tilt] (cards) and .model-hero-media / .model-hero-tilt.
 */
function initTilt($) {
    if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) {
        return; // skip on touch devices
    }

    // Cards: element itself tilts
    $(document).on('mousemove', '[data-tilt]', function (e) {
        var r  = this.getBoundingClientRect();
        var px = (e.clientX - r.left) / r.width - 0.5;
        var py = (e.clientY - r.top) / r.height - 0.5;
        this.style.transform = 'perspective(900px) rotateX(' + (py * -8) +
            'deg) rotateY(' + (px * 8) + 'deg) scale(1.02)';
    });
    $(document).on('mouseleave', '[data-tilt]', function () {
        this.style.transform = 'perspective(900px) rotateX(0deg) rotateY(0deg) scale(1)';
    });

    // Single-model hero: a wrapper listens, the inner .model-hero-tilt transforms
    $(document).on('mousemove', '.model-hero-media', function (e) {
        var r  = this.getBoundingClientRect();
        var px = (e.clientX - r.left) / r.width - 0.5;
        var py = (e.clientY - r.top) / r.height - 0.5;
        var inner = this.querySelector('.model-hero-tilt');
        if (inner) {
            inner.style.transform = 'perspective(1200px) rotateX(' + (py * -10) +
                'deg) rotateY(' + (px * 10) + 'deg) scale(1.03)';
        }
    });
    $(document).on('mouseleave', '.model-hero-media', function () {
        var inner = this.querySelector('.model-hero-tilt');
        if (inner) {
            inner.style.transform = 'perspective(1200px) rotateX(0deg) rotateY(0deg) scale(1)';
        }
    });

    // Parallax on the homepage hero background / figure
    var $heroBg  = $('.hero-bg');
    var $heroFig = $('.hero-figure');
    if ($heroBg.length || $heroFig.length) {
        $(window).on('scroll', function () {
            var y = window.scrollY || 0;
            if ($heroBg.length)  $heroBg[0].style.transform  = 'translateY(' + (y * 0.15) + 'px)';
            if ($heroFig.length) $heroFig[0].style.transform = 'translateY(' + (y * -0.06) + 'px)';
        });
    }
}

module.exports = { initTilt: initTilt };
