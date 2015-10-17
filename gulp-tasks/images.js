import image from 'gulp-image';

function buildDev(gulp, config) {
    return function() {
        gulp.src(config.src + '/img/**/*')
            .pipe(gulp.dest(config.dist + '/img'))
        ;
    };
}

function buildProd(gulp, config) {
    return function() {
        gulp.src(config.src + '/img/**/*')
            .pipe(image())
            .pipe(gulp.dest(config.dist + '/img'))
        ;
    };
}

module.exports = function(gulp, config) {
    let factory = ('prod' === process.env.NODE_ENV) ? buildProd : buildDev;

    return factory(gulp, config);
};
