const glob = require('glob');
const path = require('path');

const isProd = process.env.NODE_ENV === 'production';

module.exports = {
  mode: isProd ? 'production' : 'development',
  target: 'web',

  entry: () =>
    glob.sync('./src/**/*.js').reduce((obj, el) => {
      obj[el] = el;
      return obj;
    }, {}),
  output: {
    filename: (pathData) => {
      const { name } = pathData.chunk;
      return name.replace('./src/', '');
    },
    path: path.resolve(__dirname, '../plugin/src/Resources/js/scripts'),
  },

  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'babel-loader',
          },
        ],
      },
      {
        test: /\.svg$/,
        loader: 'svg-inline-loader',
      },
      {
        test: /\.(css|styl)$/,
        use: [
          { loader: 'style-loader' },
          { loader: 'css-loader' },
          { loader: 'postcss-loader' },
          { loader: 'stylus-loader' },
        ],
      },
    ],
  },

  devtool: isProd ? false : 'eval-source-map',
  resolve: {
    extensions: ['.tsx', '.ts', '.js'],
    alias: {
      '@xf/scripts': path.resolve(__dirname, 'src/'),
      '@xf/styles': path.resolve(__dirname, '../styles/src/'),
    },
  },
};
