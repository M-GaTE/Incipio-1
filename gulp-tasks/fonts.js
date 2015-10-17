module.exports = function(gulp, config) {
    return function() {
        gulp.src(config.src + '/fonts/**/*')
            .pipe(gulp.dest(config.dist + '/fonts'))
        ;
    };
};
