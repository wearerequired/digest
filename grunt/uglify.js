module.exports = {
	all: {
		files:   {
			'js/digest.min.js': [ 'js/digest.js' ]
		},
		options: {
			banner:    '/*! <%= package.title %> - v<%= package.version %>\n' +
			           ' * <%= package.homepage %>\n' +
			           ' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
			           ' * Licensed GPLv2+' +
			           ' */\n',
			sourceMap: true,
			mangle:    {
				except: [ 'jQuery' ]
			}
		}
	}
};
