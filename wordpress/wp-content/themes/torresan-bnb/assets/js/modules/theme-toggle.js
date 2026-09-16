/**
 * Light/dark theme toggle. The actual theme is already applied before first
 * paint by the inline script in header.php (reads localStorage, falls back
 * to the OS/browser prefers-color-scheme) — this module just wires up the
 * button to flip and persist the choice, and keeps following the OS setting
 * live if the user never made an explicit choice of their own.
 */
function initThemeToggle($) {
    var $btn = $('.theme-toggle');
    if (! $btn.length) { return; }

    var root = document.documentElement;
    var mql = window.matchMedia ? window.matchMedia('(prefers-color-scheme: light)') : null;

    function apply(theme) {
        root.setAttribute('data-theme', theme);
    }

    $btn.on('click', function () {
        var current = root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        var next = current === 'light' ? 'dark' : 'light';
        apply(next);
        try { localStorage.setItem('alu-theme', next); } catch (e) {}
    });

    // If the visitor never explicitly chose a theme, keep following the OS setting live.
    if (mql) {
        mql.addEventListener('change', function (e) {
            var hasSaved = false;
            try { hasSaved = !!localStorage.getItem('alu-theme'); } catch (err) {}
            if (! hasSaved) { apply(e.matches ? 'light' : 'dark'); }
        });
    }
}

module.exports = { initThemeToggle: initThemeToggle };
