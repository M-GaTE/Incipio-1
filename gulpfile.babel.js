'use strict';

var options = {
    options: {
        'env=prod': 'Assets build and processing are production ready'
    }
};

var gulp = require('gulp-help')(require('gulp'), options);
var argv = require('yargs').argv;

var config = {
    src: 'src/FrontBundle/Resources/assets',
    dist: 'web/assets'
};

// Sets environment to production if "--env=prod" ou "--production" option
// is passed as a parameter. Otherwise default set to dev.
if ('prod' === argv.env || 'production' === argv.env) {
    process.env.NODE_ENV = 'prod';
} else {
    process.env.NODE_ENV = 'dev';
}

gulp.task('lint:yaml', 'Lint YAML files', require('./gulp-tasks/yaml-lint')(gulp, config));

gulp.task('css', 'Build the CSS assets', require('./gulp-tasks/sass')(gulp, config));
gulp.task('check:css', 'Check CSS duplications', require('./gulp-tasks/css-check')(gulp, config));
gulp.task('lint:css', 'Lint SASS files', require('./gulp-tasks/sass-lint')(gulp, config));
gulp.task('watch:css', 'Watch CSS files to build on change', require('./gulp-tasks/sass-watch')(gulp, config));

gulp.task('fonts', 'Publish the fonts assets', require('./gulp-tasks/fonts')(gulp, config));
gulp.task('watch:fonts', 'Watch fonts files to publish on change', require('./gulp-tasks/fonts-watch')(gulp, config));

gulp.task('img', 'Process the images', require('./gulp-tasks/images')(gulp, config));
gulp.task('watch:img', 'Watch images to process on change', require('./gulp-tasks/images-watch')(gulp, config));

gulp.task('js', 'Build the JavaScript assets', require('./gulp-tasks/scripts')(gulp, config));
gulp.task('lint:js', 'Lint JavaScript files', require('./gulp-tasks/scripts-lint')(gulp, config));
gulp.task('watch:js', 'Watch JavaScript files to build on change', require('./gulp-tasks/scripts-watch')(gulp, config));

gulp.task('build', 'Build and publish all assets', ['css', 'fonts', 'img', 'js']);
gulp.task('watch', 'Watch all assets for build on change', ['watch:css', 'watch:fonts', 'watch:img', 'watch:js']);

gulp.task('start', 'Build/publish all assets and watch files for any change', ['build', 'watch']);

gulp.task('default', ['help']);
