const path = require( 'path' );
const TerserPlugin = require( 'terser-webpack-plugin' );

const externals = {};

const isProduction = process.env.NODE_ENV === 'production';

const baseConfig = {
	mode: isProduction ? 'production' : 'development',

	devtool: isProduction ? undefined : 'inline-source-map',

	stats: 'errors-only',

	// https://webpack.js.org/configuration/optimization/#optimization-runtimechunk
	optimization: {
		runtimeChunk: false,
		minimizer: [
			new TerserPlugin( {
				parallel: true,
				extractComments: false,
				terserOptions: {
					output: {
						comments: false,
					},
					compress: {
						passes: 2,
					},
				},
			} ),
		],
	},

	// https://webpack.js.org/configuration/entry-context/#context
	context: path.resolve( __dirname, 'js/src' ),

	// https://webpack.js.org/configuration/externals/
	externals,
};

module.exports = [
	{
		...baseConfig,

		// https://webpack.js.org/configuration/entry-context/#entry
		entry: {
			digest: './digest.js',
		},

		// https://webpack.js.org/configuration/output/
		output: {
			path: path.resolve( __dirname, 'js' ),
			filename: '[name].js',
		},

		// https://github.com/babel/babel-loader#usage
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: 'babel-loader',
				},
			],
		},
	},
];
