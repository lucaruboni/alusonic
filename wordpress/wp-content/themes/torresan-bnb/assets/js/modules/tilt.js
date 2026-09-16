/**
 * Alusonic — 3D tilt for layered model cards + single-model hero media.
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
}

module.exports = { initTilt: initTilt };
