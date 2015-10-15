import csscss from 'gulp-csscss';

module.exports = function(gulp, config) {
    return function() {
        gulp.src(config.src + '/app.css')
            .pipe(csscss())
        ;
    };
};
