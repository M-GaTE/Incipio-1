module.exports = function(gulp, config) {
    return function() {
        config.watch = true;
        gulp.watch(config.src + '/scripts/**/*.js', ['js']);
    };
};
