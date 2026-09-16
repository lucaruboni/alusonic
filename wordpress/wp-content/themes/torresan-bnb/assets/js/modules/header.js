/**
 * Alusonic — sticky header, mobile menu toggle, language dropdown, smooth scroll.
 */
function initHeader($) {
    var $header     = $('.site-header');
    var $menuToggle = $('.menu-toggle');
    var $siteNav    = $('.site-nav');

    // Subtle shadow/opacity change once the page is scrolled a little.
    function checkScroll() {
        if ($(window).scrollTop() > 10) {
            $header.addClass('scrolled');
        } else {
            $header.removeClass('scrolled');
        }
    }
    $(window).on('scroll', checkScroll);
    checkScroll();

    // Hamburger toggle (mobile)
    $menuToggle.on('click', function () {
        var isOpen = $siteNav.hasClass('is-open');
        $(this).toggleClass('is-active');
        $(this).attr('aria-expanded', isOpen ? 'false' : 'true');
        $siteNav.toggleClass('is-open', !isOpen);
        $('body').css('overflow', isOpen ? '' : 'hidden');
    });

    // Close nav after clicking a link (but not a parent link that just opens a submenu on mobile)
    $siteNav.on('click', 'a', function (e) {
        var $li = $(this).parent();
        var coarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
        if (coarse && $li.hasClass('menu-item-has-children') && !$li.hasClass('is-open')) {
            e.preventDefault();
            $siteNav.find('.menu-item-has-children.is-open').removeClass('is-open');
            $li.addClass('is-open');
            return;
        }
        if ($siteNav.hasClass('is-open')) {
            $menuToggle.removeClass('is-active').attr('aria-expanded', 'false');
            $siteNav.removeClass('is-open');
            $('body').css('overflow', '');
        }
    });

    // Language switcher dropdown
    $('.lang-switcher--dropdown').each(function () {
        var $wrap   = $(this);
        var $toggle = $wrap.find('.lang-switcher-toggle');
        var $list   = $wrap.find('.lang-switcher-list');
        if (!$toggle.length || !$list.length) return;

        $list.prop('hidden', true);

        $toggle.on('click', function (e) {
            e.stopPropagation();
            var expanded = $toggle.attr('aria-expanded') === 'true';
            $toggle.attr('aria-expanded', expanded ? 'false' : 'true');
            $list.prop('hidden', expanded);
        });
        $(document).on('click', function () {
            $toggle.attr('aria-expanded', 'false');
            $list.prop('hidden', true);
        });
        $list.on('click', function (e) { e.stopPropagation(); });
    });

    // Smooth scroll for on-page anchor links
    $(document).on('click', 'a[href*="#"]', function (e) {
        var href = this.getAttribute('href') || '';
        var hashIndex = href.indexOf('#');
        if (hashIndex < 0) return;
        var hash = href.slice(hashIndex);
        if (hash.length < 2) return;
        // Only intercept same-page anchors
        var base = href.slice(0, hashIndex);
        if (base && base.indexOf(window.location.pathname) === -1 && base.charAt(0) !== '#') return;
        var target = $(hash);
        if (!target.length) return;
        e.preventDefault();
        $('html, body').animate({ scrollTop: target.offset().top - 90 }, 500);
    });
}

module.exports = { initHeader: initHeader };
