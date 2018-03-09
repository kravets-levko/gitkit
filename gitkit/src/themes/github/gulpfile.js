'use strict';

const path = require('path');
const gulp = require('gulp');
const runSequence = require('run-sequence');
const clean = require('gulp-clean');
const svgSprite = require('gulp-svg-sprite');

const appAssetsPath = path.resolve(__dirname, './assets');
const nodeModulesPath = path.resolve(__dirname, './node_modules');
const targetPath = path.resolve(__dirname, 'public');

gulp.task('default', callback => {
  runSequence(
    'clean',
    ['octicons', 'favicon'],
    callback
  );
});

gulp.task('clean', () => gulp.src([
  targetPath,
], {read: false}).pipe(clean({force: true})));

gulp.task('octicons', () => {
  const icons = [
    'clippy', 'book', 'history', 'git-branch', 'tag', 'law', 'gear',
    'file', 'file-directory', 'mark-github', 'diff', 'key', 'code',
    'git-commit', 'trashcan',
  ];

  gulp.src([
    path.join(appAssetsPath, 'logo.svg'),
  ].concat(
    icons.map(icon => path.join(nodeModulesPath, 'octicons/build/svg/' + icon + '.svg'))
  )).pipe(svgSprite({
    mode: {
      symbol: {
        dest: '',
        sprite: 'octicons.svg'
      }
    },
  })).pipe(gulp.dest(targetPath))
});

gulp.task('favicon', () => gulp.src([
    path.join(appAssetsPath, 'favicon.png'),
  ]).pipe(gulp.dest(targetPath))
);
