/**
 * Location page map: shows a static image until the visitor accepts the
 * Iubenda cookies required to load the Google Maps embed, then swaps in
 * the real iframe. The iframe HTML is never injected into the DOM (not even
 * hidden) before consent, so Google never receives a request beforehand.
 */
function initLocationMap($) {
    var $container = $('.location-map--hero[data-map-html]');
    if (!$container.length) {
        return;
    }

    var injected = false;

    function injectMap() {
        if (injected) {
            return;
        }
        injected = true;

        var encoded = $container.attr('data-map-html');
        var html;
        try {
            html = window.atob(encoded);
        } catch (e) {
            return;
        }

        $container.find('.location-map-fallback').remove();
        $container.append(html);
    }

    function hasConsent() {
        try {
            if (window._iub && window._iub.cs && window._iub.cs.api
                && typeof window._iub.cs.api.isConsentGiven === 'function') {
                return !!window._iub.cs.api.isConsentGiven();
            }
        } catch (e) {}
        return false;
    }

    if (hasConsent()) {
        injectMap();
        return;
    }

    // Poll for consent changes (banner interaction, preference update, etc.)
    var pollId = window.setInterval(function () {
        if (hasConsent()) {
            injectMap();
            window.clearInterval(pollId);
        }
    }, 1000);

    // Stop polling after 10 minutes to avoid an endless timer on long visits.
    window.setTimeout(function () {
        window.clearInterval(pollId);
    }, 600000);
}

module.exports = { initLocationMap: initLocationMap };
