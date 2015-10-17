import minifyCss from 'gulp-minify-css';
import plumber from 'gulp-plumber';
import sass from 'gulp-sass';
import sourcemaps from 'gulp-sourcemaps';

// https://github.com/sass/node-sass#options
const OPTIONS = {
    prod: {
        outputStyle: 'compressed',
    },
    dev: {
        outputStyle: 'expanded',
    },
};

function buildDev(gulp, config) {
    return function() {
        gulp.src(config.src + '/scss/app.scss')
            .pipe(plumber())
            .pipe(sourcemaps.init())
                .pipe(sass(OPTIONS.dev))
                .pipe(sass().on('error', sass.logError))
            .pipe(sourcemaps.write())
            .pipe(gulp.dest(config.dist))
        ;
    };
}

function buildProd(gulp, config) {
    return function() {
        gulp.src(config.src + '/scss/app.scss')
            .pipe(plumber())
            .pipe(sass(OPTIONS.prod))
            .pipe(sass().on('error', sass.logError))
            .pipe(minifyCss())
            .pipe(gulp.dest(config.dist))
        ;
    };
}

module.exports = function(gulp, config) {
    let factory = ('prod' === process.env.NODE_ENV) ? buildProd : buildDev;

    return factory(gulp, config);
};
