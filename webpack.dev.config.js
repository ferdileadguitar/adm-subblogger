const webpack = require('webpack');
const merge   = require('webpack-merge');
const common  = require('./webpack.common.js');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const path    = require('path');

module.exports = merge(common, {
	// devtool   : 'eval',
	// devServer : {
	//     contentBase: path.join(__dirname, './public/build'),
	//     publicPath : 'build',
	//     open: false,
	//     hot: true,
	//     compress:true,
	//     inline: true,
	//     port: 8080,
	//     host: 'localhost',
	//     watchOptions :  { poll: false },
	//     proxy: { 
	//     	'*': {
	//     		target : 'http://localhost:3000' 	
	//     	}
	//     },
	// 	headers: {
	// 		'Access-Control-Allow-Origin'      : '*',
	// 		'Access-Allow-Control-Methods'     : 'PUT, DELETE, POST, GET, PATCH, OPTIONS',
	// 		'Access-Control-Allow-Headers'     : 'X-Requested-With, content-type, Authorization',
	// 		'Access-Control-Allow-Credentials' : 'true'		
	// 	},
	// 	allowedHosts: [
	// 		'http://localhost:3000', 
	// 		'http://localhost:4200', 
	// 		'http://localhost:8000'
	// 	],
	// 	historyApiFallback: false
	// watch  : true,
	plugins : [
		// new webpack.HotModuleReplacementPlugin(),
		new BrowserSyncPlugin({
			port : 3000,
			host : 'localhost',
			proxy: 'http://localhost:3000/'
		})
	]
	// },
});
