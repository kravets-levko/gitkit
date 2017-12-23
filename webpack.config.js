'use strict';

const path = require('path');
const webpack = require('webpack');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

const plugins = [
  new webpack.DefinePlugin({
    'process.env': {
      NODE_ENV: JSON.stringify(process.env.NODE_ENV),
      API_URL: JSON.stringify(process.env.API_URL),
    },
  }),
  new ExtractTextPlugin({
    filename: '[name].css',
    disable: process.env.NODE_ENV === 'development',
  }),
];

if (process.env.BUNDLE_OPTIMISE !== undefined) {
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
    'app': ['./src/scripts/index.js', './src/styles/main.scss'],
  },
  output: {
    path: path.resolve(__dirname, 'public/assets'),
    filename: '[name].js',
  },
  module: {
    rules: [
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
        test: /\.html$/,
        loader: 'raw-loader',
      },
    ],
  },
  devtool: 'source-map',
  plugins: plugins,
  stats: {
    modules: false,
    chunkModules: false,
  },
};
