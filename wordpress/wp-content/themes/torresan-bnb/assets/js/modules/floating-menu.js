/**
 * Floating hamburger (top-right) that fades in once the page has been
 * scrolled past the hero, giving quick access to a full-screen mega menu
 * (reel video on the left, nav + social links on the right) without having
 * to scroll back up to the sticky header.
 */
function initFloatingMenu($) {
    var $toggle = $('.floating-menu-toggle');
    var $menu   = $('[data-mega-menu]');
    var $logo   = $('.floating-logo');
    if (! $toggle.length || ! $menu.length) { return; }

    var SHOW_AT = 400;
    var visible = false;

    function checkScroll() {
        var shouldShow = window.scrollY > SHOW_AT;
        if (shouldShow !== visible) {
            visible = shouldShow;
            $toggle.toggleClass('is-visible', visible);
            // The logo fades in alongside the hamburger so the two corners
            // appear together.
            $logo.toggleClass('is-visible', visible);
        }
    }
    $(window).on('scroll', checkScroll);
    checkScroll();

    function openMenu() {
        $menu.addClass('is-open');
        // The hamburger itself morphs into the close mark, so it has to stay
        // visible while the menu is open even if the page is scrolled back up.
        $toggle.addClass('is-open').attr('aria-expanded', 'true');
        // The mega menu shows its own logo, so hide the floating one.
        $logo.addClass('is-hidden');
        $('body').css('overflow', 'hidden');
        var video = $menu.find('video')[0];
        if (video && video.paused) { video.play().catch(function () {}); }
    }
    function closeMenu() {
        $menu.removeClass('is-open');
        $toggle.removeClass('is-open').attr('aria-expanded', 'false');
        $logo.removeClass('is-hidden');
        $('body').css('overflow', '');
    }

    $(document).on('click', '[data-mega-menu-toggle]', function () {
        if ($menu.hasClass('is-open')) { closeMenu(); } else { openMenu(); }
    });
    $(document).on('click', '[data-mega-menu-open]', openMenu);
    $(document).on('click', '[data-mega-menu-close]', closeMenu);
    $menu.on('click', 'a', closeMenu);
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $menu.hasClass('is-open')) { closeMenu(); }
    });
}

module.exports = { initFloatingMenu: initFloatingMenu };
