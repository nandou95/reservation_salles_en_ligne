'use strict'

let gulp = require('gulp')
let requireDir = require('require-dir')

gulp.paths = {
    dist: 'dist',
};

var paths = gulp.paths;

// include files in gulp tasks directory

requireDir('gulp-tasks')

gulp.task('default', gulp.series('serve'));
