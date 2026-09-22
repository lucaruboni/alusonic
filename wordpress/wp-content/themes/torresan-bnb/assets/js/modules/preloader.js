/**
 * Full-screen splash shown on first load so the hero video (and other
 * above-the-fold assets) have time to fetch before they're revealed.
 * Shows a simulated progress percentage (real load progress isn't
 * reliably measurable) that eases toward ~92% while waiting and jumps to
 * 100% on window 'load'. Stays visible at least MIN_MS regardless of how
 * fast loading actually finishes, so the logo doesn't just flash by.
 */
function initPreloader() {
    var el = document.getElementById('site-preloader');
    if (!el) { return; }

    var fill  = el.querySelector('.site-preloader-fill');
    var pctEl = el.querySelector('.site-preloader-pct');
    var MIN_MS = 1500;
    var start = Date.now();
    var pct = 0;
    var finished = false;
    var hidden = false;

    function render() {
        if (fill)  { fill.style.width = pct + '%'; }
        if (pctEl) { pctEl.textContent = Math.round(pct) + '%'; }
    }

    var tick = setInterval(function () {
        var remaining = 92 - pct;
        pct = Math.min(92, pct + Math.max(0.4, remaining * 0.06));
        render();
    }, 90);

    function finish() {
        if (finished) { return; }
        finished = true;
        clearInterval(tick);
        pct = 100;
        render();

        var wait = Math.max(250, MIN_MS - (Date.now() - start));
        setTimeout(function () {
            if (hidden) { return; }
            hidden = true;
            el.classList.add('is-hidden');
        }, wait);
    }

    if (document.readyState === 'complete') {
        finish();
    } else {
        window.addEventListener('load', finish);
    }
    setTimeout(finish, 6000);
}

module.exports = { initPreloader: initPreloader };
