module.exports = {
	options: {
		map: {
			inline:     false, // save all source maps as separate files...
			annotation: 'css/' // ...to the specified directory
		},

		processors: [
			require( 'autoprefixer' )( {
				browsers: [
					'last 2 versions',
					'> 5%',
					'ie 9'
				]
			} ), // add vendor prefixes
			require( 'cssnano' )() // minify the result
		]
	},
	dist:    {
		src:  'css/digest.css',
		dest: 'css/digest.min.css'
	}
};
