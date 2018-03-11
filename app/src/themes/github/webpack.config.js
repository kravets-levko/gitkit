'use strict';

const path = require('path');
const webpack = require('webpack');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

const targetPath = path.resolve(__dirname, 'public');

const plugins = [
  new webpack.DefinePlugin({
    'process.env': {
      NODE_ENV: JSON.stringify(process.env.NODE_ENV),
      API_URL: JSON.stringify(process.env.API_URL),
    },
  }),
  new ExtractTextPlugin({
    filename: '[name].css',
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
    'app': ['./scripts/index.js', './styles/main.scss'],
  },
  output: {
    path: targetPath,
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