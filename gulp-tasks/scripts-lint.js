import eslint from 'gulp-eslint';

const OPTIONS = {
    configFile: __dirname + '/../.eslintrc.yml',
};

module.exports = function(gulp, config) {
    const LINT_FILES = [
        config.src + '/scripts/**/*.js', // application scripts
        __dirname + '/*.js',             // gulps script files
        __dirname + '/../.js',           // gulp.js
    ];

    return function() {
        gulp.src(LINT_FILES)
            .pipe(eslint(OPTIONS))
            .pipe(eslint.format())
            .pipe(eslint.failAfterError())
        ;
    };
};
