/**
 * Forza l'avvio del video hero quando l'autoplay del browser lo blocca
 * (Safari/iOS in Low Power Mode, Chrome con Data Saver, ecc.): l'attributo
 * HTML autoplay da solo a volte lascia il video in pausa sul primo frame,
 * mostrando l'icona di play nativa senza mai partire.
 */
function initHeroVideo() {
    var videos = document.querySelectorAll('.hero-video');
    if (!videos.length) { return; }

    function tryPlay(video) {
        var p = video.play();
        if (p && typeof p.catch === 'function') {
            p.catch(function () {
                // Riprova al primo gesto dell'utente (richiesto da alcune policy di autoplay).
                var retry = function () {
                    video.play().catch(function () {});
                    document.removeEventListener('touchstart', retry);
                    document.removeEventListener('click', retry);
                };
                document.addEventListener('touchstart', retry, { once: true, passive: true });
                document.addEventListener('click', retry, { once: true });
            });
        }
    }

    videos.forEach(function (video) {
        if (video.readyState >= 2) {
            tryPlay(video);
        } else {
            video.addEventListener('loadeddata', function () { tryPlay(video); }, { once: true });
        }
        video.addEventListener('pause', function () {
            if (!document.hidden) { tryPlay(video); }
        });
    });
}

module.exports = { initHeroVideo: initHeroVideo };
