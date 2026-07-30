/**
 * Alusonic — 3D tilt for layered model cards + hero parallax (scroll + mouse).
 */
function initTilt($) {
    var coarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;

    if (!coarse) {
        // ── Model cards: rotate the .mc-stage inside [data-tilt] ──
        $(document).on('mousemove', '[data-tilt]', function (e) {
            var stage = this.querySelector('.mc-stage');
            if (!stage) return;
            var r  = this.getBoundingClientRect();
            var px = (e.clientX - r.left) / r.width - 0.5;
            var py = (e.clientY - r.top) / r.height - 0.5;
            stage.style.transform = 'rotateX(' + (py * -12) + 'deg) rotateY(' + (px * 14) + 'deg)';
        });
        $(document).on('mouseleave', '[data-tilt]', function () {
            var stage = this.querySelector('.mc-stage');
            if (stage) { stage.style.transform = 'rotateX(0deg) rotateY(0deg)'; }
        });

        // ── Single-model hero: inner .model-hero-tilt transforms ──
        $(document).on('mousemove', '.model-hero-media', function (e) {
            var inner = this.querySelector('.model-hero-tilt');
            if (!inner) return;
            var r  = this.getBoundingClientRect();
            var px = (e.clientX - r.left) / r.width - 0.5;
            var py = (e.clientY - r.top) / r.height - 0.5;
            inner.style.transform = 'perspective(1200px) rotateX(' + (py * -10) +
                'deg) rotateY(' + (px * 10) + 'deg) scale(1.03)';
        });
        $(document).on('mouseleave', '.model-hero-media', function () {
            var inner = this.querySelector('.model-hero-tilt');
            if (inner) { inner.style.transform = 'perspective(1200px) rotateX(0deg) rotateY(0deg) scale(1)'; }
        });
    }

    // ── Hero parallax: scroll (vertical) + mouse (both axes) ──
    var $hero    = $('.hero');
    var $heroBg  = $('.hero-bg');
    var $heroFig = $('.hero-figure');

    if ($hero.length && ($heroBg.length || $heroFig.length)) {
        var scrollY = 0, mx = 0, my = 0;

        function apply() {
            if ($heroBg.length) {
                $heroBg[0].style.transform =
                    'translate3d(' + (mx * 22) + 'px,' + (scrollY * 0.15 + my * 16) + 'px,0)';
            }
            if ($heroFig.length) {
                $heroFig[0].style.transform =
                    'translate3d(' + (mx * -34) + 'px,' + (scrollY * -0.06 + my * -22) + 'px,0)';
            }
        }

        $(window).on('scroll', function () { scrollY = window.scrollY || 0; apply(); });

        if (!coarse) {
            $hero.on('mousemove', function (e) {
                var r = this.getBoundingClientRect();
                mx = (e.clientX - r.left) / r.width - 0.5;
                my = (e.clientY - r.top) / r.height - 0.5;
                apply();
            });
            $hero.on('mouseleave', function () { mx = 0; my = 0; apply(); });
        }

        apply();
    }
}

module.exports = { initTilt: initTilt };
