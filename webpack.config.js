const path = require('path');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');

module.exports = (env, { mode }) => ({
  /*
   * See https://webpack.js.org/configuration/devtool/ for an explanation of how
   * to configure this directive. We are using the recommended options for
   * production and development mode that produce high quality source maps.
   */
  devtool: mode === 'production'
    ? 'source-map'
    : 'eval-source-map',

  // We only have one entry point - the pluginsidebar.
  entry: {
    pluginSidebar: './assets/js/pluginsidebar/index.jsx',
    adminSettings: './assets/js/admin-settings/frontend.jsx',
  },

  // Configure loaders based on extension.
  module: {
    rules: [
      {
        exclude: /node_modules/,
        test: /.jsx?$/,
        use: [
          'babel-loader',
        ],
      },
    ],
  },

  // Configure the output filename.
  output: {
    filename: '[name].js',
    path: path.join(__dirname, 'build'),
  },

  // Configure plugins.
  plugins: [
    // This maps references to @wordpress/{package-name} to the wp object.
    new DependencyExtractionWebpackPlugin({ useDefaults: true }),
  ],

  // Tell webpack that we are using both .js and .jsx extensions.
  resolve: {
    extensions: ['.js', '.jsx'],
  },
});
