/**
 * Torresan BnB — Main JS Entry Point
 * Bundled → assets/dist/main.min.js  (via Gulp + Browserify)
 *
 * Modules:
 *   modules/header.js        — scroll header, hamburger, smooth scroll
 *   modules/faq.js           — FAQ accordion
 *   modules/carousel.js      — auto-advancing image carousel
 *   modules/lightbox.js      — full-screen image overlay
 *   modules/contact-form.js  — AJAX contact form
 *   modules/location-map.js  — cookie-gated map on the Location page
 */

var initHeader      = require('./modules/header').initHeader;
var initFaq         = require('./modules/faq').initFaq;
var initCarousel    = require('./modules/carousel').initCarousel;
var initLightbox    = require('./modules/lightbox').initLightbox;
var initContactForm = require('./modules/contact-form').initContactForm;
var initLocationMap = require('./modules/location-map').initLocationMap;
var initTilt        = require('./modules/tilt').initTilt;
var initModelsFilter = require('./modules/models-filter').initModelsFilter;
var initGalleryFilter = require('./modules/gallery-filter').initGalleryFilter;
var initParallaxGallery = require('./modules/parallax-gallery').initParallaxGallery;
var initArtistModal = require('./modules/artist-modal').initArtistModal;
var initThemeToggle = require('./modules/theme-toggle').initThemeToggle;
var initPreloader   = require('./modules/preloader').initPreloader;
var initScrollCue   = require('./modules/scroll-cue').initScrollCue;
var initModal       = require('./modules/modal').initModal;
var initFloatingMenu = require('./modules/floating-menu').initFloatingMenu;
var initHeroVideo = require('./modules/hero-video').initHeroVideo;

initPreloader();
initHeroVideo();

jQuery(function ($) {
    'use strict';

    initHeader($);
    initFaq($);
    initCarousel($);
    initLightbox($);
    initContactForm($);
    initLocationMap($);
    initTilt($);
    initModelsFilter($);
    initGalleryFilter($);
    initParallaxGallery($);
    initArtistModal($);
    initThemeToggle($);
    initScrollCue($);
    initModal($);
    initFloatingMenu($);
});
