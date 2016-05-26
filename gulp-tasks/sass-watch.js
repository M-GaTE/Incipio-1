module.exports = function(gulp, config) {
    return function() {
        gulp.watch(config.src + '/scss/**/*.scss', ['css'])
            .on('change', function(event) {
                console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
            })
        ;
    };
};
