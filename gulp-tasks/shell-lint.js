import spellcheck from 'gulp-spellcheck';

const LINT_FILES = __dirname + '/../scripts/**/*.sh';

module.exports = function(gulp) {
    return function() {
        gulp.src(LINT_FILES)
            .pipe(spellcheck())
        ;
    };
};
