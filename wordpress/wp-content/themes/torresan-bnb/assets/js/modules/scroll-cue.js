/**
 * Homepage hero — clicking the animated scroll cue smooth-scrolls to the
 * section right after the hero, instead of jumping to an anchor.
 */
function initScrollCue($) {
    $(document).on('click', '.hero-scroll-cue', function (e) {
        e.preventDefault();
        var hero = document.querySelector('.hero');
        var next = hero && hero.nextElementSibling;
        var top = next ? next.getBoundingClientRect().top + window.scrollY : window.innerHeight;
        $('html, body').animate({ scrollTop: top }, 600);
    });
}

module.exports = { initScrollCue: initScrollCue };
