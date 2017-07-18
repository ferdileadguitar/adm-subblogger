const webpack = require('webpack');
const ExtractTextPlugin = require("extract-text-webpack-plugin");

module.exports = {
	entry: {
		login: ["./src/js/app.js", "./src/js/login.js"],
		content: ["./src/js/app.js", "./src/js/content.js"],
		vendor: ["jquery", "underscore", "angular", "angular-sanitize", "joii"],
	},
	output: {
		filename: "./dist/js/[name].js",
		chunkFilename: "./dist/js/[id].js"
	},
	module: {
		rules: [
			{
				test: /\.scss$/,
				use: ExtractTextPlugin.extract({
					fallback: "style-loader",
					use: ["css-loader", "postcss-loader", "sass-loader"]
				})
			},
			{
                test: /\.(eot|ttf|woff|woff2)$/,
                loader: 'file-loader?name=fonts/[name].[ext]&emitFile=false'
            },
            { 
            	test: /\.(png|svg|jpe?g)$/, 
            	loader: 'file-loader?name=img/[name].[ext]&emitFile=false' 
            }
		],
	},
	plugins: [
		new ExtractTextPlugin("./dist/css/[name].css"),
		new webpack.ProvidePlugin({jQuery: "jquery",$: "jquery","window.jQuery": "jquery"}),
		new webpack.ProvidePlugin({"_": "underscore"}),
		new webpack.optimize.CommonsChunkPlugin({name: "vendor", filename: "./dist/js/vendor.js"}),
	],
	watch: true
}