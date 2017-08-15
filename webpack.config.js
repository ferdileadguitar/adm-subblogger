const webpack = require('webpack');
const ExtractTextPlugin = require("extract-text-webpack-plugin");
const path = require('path');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
const WatchLiveReloadPlugin = require('webpack-watch-livereload-plugin');

// console.log( path.resolve(__dirname, "./src/scss") );

let ROOT_PATH = path.resolve(__dirname),
	APP_PATH  = path.resolve(ROOT_PATH, 'public'),
	BUILD_PATH= path.resolve(APP_PATH, 'dist');

module.exports = {
	context: path.resolve(__dirname, 'public/'),
	entry: {
		login: ["./src/js/app.js", "./src/js/login.js"],
		content: ["./src/js/app.js", "./src/js/content.js"],
		vendor: ["jquery", "underscore", "angular", "angular-sanitize", "joii"],
	},
	output: {
		path    : BUILD_PATH,
		filename: "js/[name].js",
		chunkFilename: "js/[name].js"
	},
	module: {
		rules: [
			{
				test: /\.scss$/,
				use: ExtractTextPlugin.extract({
					fallback: "style-loader",
					use: [
						{ loader : "css-loader" }, 
						{ loader : "postcss-loader" }, 
						{ loader : "sass-loader" }, 
						{
		            		loader : 'mixin-loader',
		            		options : {
								includePath : path.resolve(__dirname, "./node_modules/compass-mixins/lib"),
		            		}
		            	}
					],
				})
			},
			{
                test: /\.(eot|ttf|woff|woff2)$/,
                loader: 'file-loader',
                options : {
                	name : '../../fonts/[name].[ext]',
                	emitFile : false
                }
            },
            { 
            	test: /\.(png|svg|jpe?g)$/, 
            	loader: 'file-loader',
            	options : {
            		context : path.resolve(__dirname, 'src'),
                	name : '../../img/[name].[ext]',
                	emitFile : false
                } 
            },
			{
				test : /\.js$/,
				exclude : /node_modules/,
			},
			{	
				test : /\.html$/,
				exclude : /node_modules/,
				use: [ 'html-loader' ]
			}
		],
	},
	plugins: [
		new ExtractTextPlugin("css/[name].css"),
		new webpack.ProvidePlugin({jQuery: "jquery",$: "jquery","window.jQuery": "jquery"}),
		new webpack.ProvidePlugin({"_": "underscore"}),
		new webpack.optimize.CommonsChunkPlugin({name: "vendor", filename: "js/vendor.js"}),
	],
	watch: true
}
// MODULE
// npm install --save-dev uglifyjs-webpack-plugin
// npm install --save-dev webpack-watch-livereload-plugin
// *npm install --save-dev html-loader
// npm install --save-dev required-loader 
// *npm install --save-dev ng-cache-loader
// npm install sass-resources-loader
// npm install compass-mixins --save
// npm install mixin-loader --save-dev
// npm install ngtemplate-loader --save-dev
// npm npm install ng-cache-loader --save-dev

// Exernal Plugin Support
// npm install handlebars --save-dev
// npm install medium-editor --save-dev
// npm install blueimp-file-upload --save-dev
// npm install jquery-sortable --save-dev
// npm install medium-editor-insert-plugin --save

// REF
// https://github.com/shakacode/sass-resources-loader/tree/master/example