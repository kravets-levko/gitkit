'use strict';

const path = require('path');
const webpack = require('webpack');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

const targetPath = path.resolve(__dirname, 'public');

const plugins = [
  new webpack.DefinePlugin({
    'process.env': {
      NODE_ENV: JSON.stringify(process.env.NODE_ENV),
      API_URL: JSON.stringify(process.env.API_URL),
    },
  }),
  new CleanWebpackPlugin([
    targetPath,
  ], {
    verbose: true,
    exclude: ['.gitignore'],
  }),
  new CopyWebpackPlugin([
    {from: 'assets/favicon.png', to: 'favicon.png'}
  ]),
  new ExtractTextPlugin({
    filename: '[name].css',
  }),
  new SpriteLoaderPlugin({
    plainSprite: true,
  }),
];

if (process.env.NODE_ENV === 'production') {
  plugins.push(new UglifyJsPlugin({
    sourceMap: true,
    uglifyOptions: {
      mangle: {
        keep_classnames: true,
        keep_fnames: true,
      },
    },
  }));
}

module.exports = {
  entry: {
    'app': [
      './scripts/index.js',
      './styles/main.scss',
      './assets/logo.svg',
    ],
    'octicons': [
      'clippy', 'book', 'history', 'git-branch', 'tag', 'law', 'gear', 'file',
      'file-directory', 'mark-github', 'diff', 'key', 'code', 'git-commit',
      'trashcan', 'unfold',
    ].map(name => 'octicons/build/svg/' + name + '.svg'),
  },
  output: {
    path: targetPath,
    filename: '[name].js',
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /\/node_modules\//,
        use: 'babel-loader',
      },
      {
        test: /\.s[ac]ss$/,
        use: ExtractTextPlugin.extract({
          use: [
            'raw-loader',
            'postcss-loader',
            'sass-loader',
          ],
        }),
      },
      {
        test: /\.svg$/,
        use: [{
          loader: 'svg-sprite-loader',
          options: {
            extract: true,
            spriteFilename: '[chunkname].svg',
          },
        }, {
          loader: 'svg-fill-loader',
          options: 'fill=currentColor&selector=path',
        }] ,
      },
      {
        test: /\.vue$/,
        use: 'vue-loader',
      }
    ],
  },
  resolve: {
    extensions: ['.js', '.json', '.vue'],
    alias: {
      'vue$': 'vue/dist/vue.common.js'
    },
  },
  devtool: 'source-map',
  plugins: plugins,
  stats: {
    modules: false,
    chunkModules: false,
  },
};
