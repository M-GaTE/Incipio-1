import scsslint from 'gulp-scss-lint';

module.exports = function(gulp, config) {
    return function() {
        gulp.src(config.src + '/scss/**/*.scss')
            .pipe(scsslint())
        ;
    };
};
