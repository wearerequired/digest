module.exports = {
	options: {
		stripBanners: true,
		banner:       '/*! <%= package.title %> - v<%= package.version %>\n' +
		              ' * <%= package.homepage %>\n' +
		              ' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
		              ' * Licensed GPLv2+' +
		              ' */\n',
		separator:    ';\n'
	},
	dist:    {
		src:  [
			'js/src/digest.js'
		],
		dest: 'js/digest.js'
	}
};
