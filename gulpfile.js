'use strict';

const gulp        = require('gulp');
const sass        = require('gulp-sass')(require('sass'));
const postcss     = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano     = require('cssnano');
const terser      = require('gulp-terser');
const rename      = require('gulp-rename');
const sourcemaps  = require('gulp-sourcemaps');
const browserify  = require('browserify');
const source      = require('vinyl-source-stream');
const buffer      = require('vinyl-buffer');

// ── Paths ────────────────────────────────────
const THEME   = 'wordpress/wp-content/themes/torresan-bnb';
const SRC     = `${THEME}/assets`;
const DIST    = `${THEME}/assets/dist`;

const paths = {
    scss: {
        src:   `${SRC}/scss/main.scss`,
        watch: `${SRC}/scss/**/*.scss`,
        dest:  DIST,
    },
    js: {
        src:   `${SRC}/js/main.js`,
        watch: `${SRC}/js/**/*.js`,
        dest:  DIST,
    },
};

// ── CSS task ─────────────────────────────────
function css() {
    return gulp.src(paths.scss.src)
        .pipe(sourcemaps.init())
        .pipe(sass({ outputStyle: 'expanded', silenceDeprecations: ['legacy-js-api', 'import'] }).on('error', sass.logError))
        .pipe(postcss([autoprefixer(), cssnano()]))
        .pipe(rename({ basename: 'main', suffix: '.min' }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.scss.dest));
}

// ── CSS dev (unminified) ──────────────────────
function cssDev() {
    return gulp.src(paths.scss.src)
        .pipe(sourcemaps.init())
        .pipe(sass({ outputStyle: 'expanded', silenceDeprecations: ['legacy-js-api', 'import'] }).on('error', sass.logError))
        .pipe(postcss([autoprefixer()]))
        .pipe(rename({ basename: 'main', suffix: '.min' }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.scss.dest));
}

// ── JS task ──────────────────────────────────
function js() {
    return browserify({ entries: paths.js.src, debug: true })
        .bundle()
        .pipe(source('main.min.js'))
        .pipe(buffer())
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(terser())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.js.dest));
}

// ── JS dev (unminified) ───────────────────────
function jsDev() {
    return browserify({ entries: paths.js.src, debug: true })
        .bundle()
        .pipe(source('main.min.js'))
        .pipe(buffer())
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.js.dest));
}

// ── Watch ─────────────────────────────────────
function watch() {
    gulp.watch(paths.scss.watch, cssDev);
    gulp.watch(paths.js.watch, jsDev);
}

// ── Exports ───────────────────────────────────
const build = gulp.parallel(css, js);
const dev   = gulp.series(gulp.parallel(cssDev, jsDev), watch);

exports.css   = css;
exports.js    = js;
exports.build = build;
exports.watch = watch;
exports.dev   = dev;
exports.default = build;
