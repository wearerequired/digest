module.exports = {
	all: {
		files  : {
			'js/wp-digest.min.js': ['js/wp-digest.js']
		},
		options: {
			banner   : '/*! <%= package.title %> - v<%= package.version %>\n' +
			' * <%= package.homepage %>\n' +
			' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
			' * Licensed GPLv2+' +
			' */\n',
			sourceMap: true,
			mangle   : {
				except: ['jQuery']
			}
		}
	}
}
