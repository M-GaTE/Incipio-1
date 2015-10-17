import yaml from 'gulp-yaml-validate';

// Will not lint Symfony YAML files as it should be done via Symfony YAML linter
const LINT_FILES = [
    __dirname + '/../.scrutinizer.yml',
    __dirname + '/../.styleci.yml',
];

module.exports = function(gulp) {
    return function() {
        LINT_FILES.forEach(function(file) {
            gulp.src(file)
                .pipe(yaml())
            ;
        });
    };
};
