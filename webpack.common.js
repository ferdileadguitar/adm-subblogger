const webpack           = require('webpack');
const path              = require('path');
const ExtractTextPlugin = require("extract-text-webpack-plugin");

let ROOT_PATH = path.resolve(__dirname),
	APP_PATH  = path.resolve(ROOT_PATH, 'public'),
	BUILD_PATH= path.resolve(APP_PATH, 'build');
	DIST_PATH = path.resolve(APP_PATH, 'dist');

module.exports = {
	context  : APP_PATH,
	entry: {
		'login'        : ["./src/js/app.js", "./src/js/login.js"],
		'content'      : ["./src/js/app.js", "./src/js/content.js"],
		'content-user' : ["./src/js/app.js", "./src/js/content-user.js"],
		'authors'      : ["./src/js/app.js", "./src/js/authors.js"],
		'channel'      : ["./src/js/app.js", "./src/js/channel.js"],

		'vendor'       : ["underscore", "angular", "angular-sanitize", "joii", "ng-tags-input"],
	},
	output: {
		path           : DIST_PATH,
		filename       : "js/[name].js",
		chunkFilename  : "js/[name].js"
	},
	module: {
		rules: [
			{
				test: /\.scss$/,
				use: ExtractTextPlugin.extract({
					fallback: "style-loader",
					use: [
						{ loader : "css-loader" }, 
						// { loader : "postcss-loader" }, 
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
                	name : '[name].[ext]',
                	emitFile : false,
                	useRelativePath : true
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
				test    : /\.js$/,
				exclude : /node_modules/,
			},
			{	
				test : /\.html$/,
				exclude : /node_modules/,
				use: [ 'html-loader' ]
			}
		],
	},
	resolve : {
		extensions: [".js"],
		alias : {
			/*Fuck with this comrs from medium editor plugins, i will fix it soon*/
			'handlebar'       : path.resolve(__dirname, './public/src/js/vendor/medium/handlebars.runtime.js'),
			'mediumEditor'    : path.resolve(__dirname, './public/src/js/vendor/medium/medium-editor.js'),
			'jquerySortable'  : path.resolve(__dirname, './public/src/js/vendor/medium/jquery-sortable.js'),

			'blueimpUpload'   						    	: path.resolve(__dirname, './public/src/js/vendor/bluimp-upload/jquery.fileupload.js'),
			'vendor/bluimp-upload/jquery.ui.widget' 		: path.resolve(__dirname, './public/src/js/vendor/bluimp-upload/jquery.ui.widget.js'),
			'vendor/medium/handlebars.runtime' 				: path.resolve(__dirname, './public/src/js/vendor/medium/handlebars.runtime.js'),
			'vendor/medium/medium-editor' 					: path.resolve(__dirname, './public/src/js/vendor/medium/medium-editor.js'),
			'vendor/medium/jquery-sortable-min' 			: path.resolve(__dirname, './public/src/js/vendor/medium/jquery-sortable.js'),
			'vendor/bluimp-upload/jquery.fileupload-ui'    	: path.resolve(__dirname, './public/src/js/vendor/bluimp-upload/jquery.fileupload-ui.js'),
			'vendor/bluimp-upload/jquery.fileupload' 	   	: path.resolve(__dirname, './public/src/js/vendor/bluimp-upload/jquery.fileupload.js'),
			'vendor/bluimp-upload/jquery.iframe-transport' 	: path.resolve(__dirname, './public/src/js/vendor/bluimp-upload/jquery.iframe.transport.js'),
		}
	},
	plugins : [
		new ExtractTextPlugin("css/[name].css"),
		new webpack.ProvidePlugin({jQuery: "jquery",$: "jquery","window.jQuery": "jquery"}),
		new webpack.ProvidePlugin({"_": "underscore"}),
		new webpack.optimize.CommonsChunkPlugin({name: "vendor", filename: "js/vendor.js"}),
	]
};