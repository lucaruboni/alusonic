/**
 * Alusonic — 3D tilt for layered model cards + single-model hero media.
 * Desktop: driven by the mouse. Touch: driven by the phone's orientation
 * sensor, so tilting the device itself moves the cutout.
 */
function initTilt($) {
    var coarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;

    if (coarse) {
        initDeviceTilt();
        initInViewActivation();
    }

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

// ── Mobile: give a card the ":hover" look (glow, scale, CTA) as it scrolls
// into the middle band of the screen, since there's no mouse to hover with.
function initInViewActivation() {
    var cards = document.querySelectorAll('[data-tilt]');
    if (!cards.length || typeof IntersectionObserver === 'undefined') { return; }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            entry.target.classList.toggle('is-inview', entry.isIntersecting);
        });
    }, { rootMargin: '-30% 0px -30% 0px', threshold: 0 });

    cards.forEach(function (card) { observer.observe(card); });
}

// ── Mobile: rotate the same layers using the device's gyroscope ──
// gamma = left/right tilt (~-90..90), beta = front/back tilt (~-180..180).
// We track the beta reading at the first event as the visitor's own
// "neutral" holding angle, since nobody holds a phone perfectly flat.
function initDeviceTilt() {
    if (typeof DeviceOrientationEvent === 'undefined') { return; }
    if (!document.querySelector('[data-tilt], .model-hero-media')) { return; }

    var baseBeta = null;
    var raf = null;
    var pending = null;

    function apply() {
        raf = null;
        if (!pending) { return; }
        var px = pending.gamma, py = pending.beta;
        document.querySelectorAll('[data-tilt] .mc-stage').forEach(function (stage) {
            stage.style.transform = 'rotateX(' + (py * -12) + 'deg) rotateY(' + (px * 14) + 'deg)';
        });
        document.querySelectorAll('.model-hero-media .model-hero-tilt').forEach(function (inner) {
            inner.style.transform = 'perspective(1200px) rotateX(' + (py * -10) +
                'deg) rotateY(' + (px * 10) + 'deg) scale(1.03)';
        });
    }

    function onOrientation(e) {
        if (e.gamma === null || e.beta === null) { return; }
        if (baseBeta === null) { baseBeta = e.beta; }
        // Clamp to a comfortable range and normalise to roughly -0.5..0.5,
        // matching the mouse-driven px/py used on desktop.
        var gamma = Math.max(-30, Math.min(30, e.gamma)) / 60;
        var beta  = Math.max(-30, Math.min(30, e.beta - baseBeta)) / 60;
        pending = { gamma: gamma, beta: beta };
        if (!raf) { raf = requestAnimationFrame(apply); }
    }

    function start() {
        window.addEventListener('deviceorientation', onOrientation);
    }

    // iOS 13+ needs an explicit permission grant from a user gesture — a
    // hard WebKit requirement, no way to skip or reskin that native prompt.
    // The most discreet option is to not get in the way of it: we ask on
    // the visitor's first tap on something that *doesn't* navigate away
    // (a filter, the menu, the search box…). If their first tap is a model
    // card link instead, we let it navigate normally rather than holding
    // it up for a permission dialog — tilt just won't be armed yet on this
    // page, and gets another chance to ask on the next one.
    if (typeof DeviceOrientationEvent.requestPermission === 'function') {
        requestOnFirstNonNavigatingTap(function () {
            return DeviceOrientationEvent.requestPermission();
        }, start);
    } else {
        start();
    }
}

function requestOnFirstNonNavigatingTap(requestPermission, onGranted) {
    function handle(e) {
        var link = e.target.closest ? e.target.closest('a[href]') : null;
        if (link) { return; } // let it navigate untouched, try again next page

        document.removeEventListener('click', handle, true);
        requestPermission().then(function (state) {
            if (state === 'granted') { onGranted(); }
        }).catch(function () {});
    }

    document.addEventListener('click', handle, true);
}

module.exports = { initTilt: initTilt };
