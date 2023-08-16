const path = require('path');

var config = {};

var configCustomer = Object.assign({}, config, {
	name: "configCustomer",
	mode: "production",
	entry: [
		'script-loader!jquery',
		'script-loader!bootstrap/dist/js/bootstrap.bundle.js',
		'script-loader!bootbox',
		'script-loader!jquery.cookie',
		'script-loader!cleave.js/dist/cleave.js',
		'script-loader!scrolltofixed'
	],
	output: {
		filename: 'vendor.min.js',
		path: path.resolve(__dirname, 'www/theme/dreamdinners/scripts/customer')
	},
});

var configAdmin = Object.assign({}, config, {
	name: "configAdmin",
	mode: "production",
	entry: [
		'script-loader!jquery',
		'script-loader!bootstrap/dist/js/bootstrap.bundle.js',
		'script-loader!bootbox',
		'script-loader!jquery.cookie',
		'script-loader!cleave.js/dist/cleave.js',
		'script-loader!scrolltofixed',
		'script-loader!./src/www/theme/dreamdinners/js/vendor/jquery.ba-dotimeout.js'
	],
	output: {
		filename: 'vendor.min.js',
		path: path.resolve(__dirname, 'www/theme/dreamdinners/scripts/admin')
	},
});

module.exports = [configCustomer, configAdmin];