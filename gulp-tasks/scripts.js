import babelify from 'babelify';
import browserify from 'browserify';
import buffer from 'vinyl-buffer';
import source from 'vinyl-source-stream';
import sourcemaps from 'gulp-sourcemaps';
import uglify from 'gulp-uglify';
import watchify from 'watchify';
import _ from 'lodash';

const OPTIONS = {
    debug: false,
    transform: [babelify],
};

function logError(err) {
    console.error(err);
    this.emit('end');
}

function buildDev(gulp, config) {
    return function() {
        let _bundle;
        if (true === config.watch) {
            _bundle = watchify(browserify(config.browserify));
            _bundle.on('update', buildDev);
        } else {
            _bundle = browserify(config.browserify);
        }

        _bundle
            .bundle()
            .on('error', logError)
            .pipe(source('app.js'))
            .pipe(buffer())
            .pipe(sourcemaps.init({ loadMaps: true }))
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest(config.dist))
        ;
    };
}

function buildProd(gulp, config) {
    return function() {
        browserify(config.browserify)
            .bundle()
            .on('error', logError)
            .pipe(source('app.js'))
            .pipe(buffer())
            .pipe(uglify())
            .on('error', logError)
            .pipe(gulp.dest(config.dist))
        ;
    };
}

module.exports = function(gulp, config) {
    let factory = buildProd;

    OPTIONS.entries = config.src + '/scripts/app.js';
    config.browserify = _.assign(watchify.args, OPTIONS);

    if ('prod' !== process.env.NODE_ENV) {
        config.browserify.debug = true;
        factory = buildDev;
    }

    return factory(gulp, config);
};
