const webpack   = require('webpack');
const merge     = require('webpack-merge');
const common    = require('./webpack.common.js');
const UglifyJs  = require('uglifyjs-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const path      = require('path');

module.exports = merge(common, {
	plugins : [
		// new UglifyJs(),
	]
});