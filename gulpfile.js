'use strict';

const path = require('path');
const gulp = require('gulp');
const runSequence = require('run-sequence');
const clean = require('gulp-clean');

const nodeModulesPath = path.resolve(__dirname, './node_modules');
const publicAssetsPath = path.resolve(__dirname, './public/assets');
const viewsAssetsPath = path.resolve(__dirname, './src/views/assets');

gulp.task('default', callback => {
  runSequence(
    'clean',
    ['octicons'],
    callback
  );
});

gulp.task('clean', () => gulp.src([
  publicAssetsPath,
  viewsAssetsPath,
], {read: false}).pipe(clean()));

gulp.task('octicons', () => gulp.src([
    path.join(nodeModulesPath, 'octicons/build/svg/*'),
  ]).pipe(gulp.dest(path.join(viewsAssetsPath, 'octicons')))
);
