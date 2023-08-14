const path = require('path');

var config = {};

var configCustomer = Object.assign({}, config, {
	name: "configCustomer",
	entry: [
		'script-loader!./node_modules/jquery/dist/jquery.js',
		'script-loader!./node_modules/bootstrap/dist/js/bootstrap.bundle.js',
		'script-loader!./node_modules/bootbox/bootbox.js',
		'script-loader!./node_modules/jquery.cookie/jquery.cookie.js',
		'script-loader!./node_modules/cleave.js/dist/cleave.js'
	],
	output: {
		filename: 'vendor.min.js',
		path: path.resolve(__dirname, 'www/theme/dreamdinners/scripts/customer')
	},
});

var configAdmin = Object.assign({}, config, {
	name: "configCustomer",
	entry: [
		'script-loader!./node_modules/jquery/dist/jquery.js',
		'script-loader!./node_modules/bootstrap/dist/js/bootstrap.bundle.js',
		'script-loader!./node_modules/bootbox/bootbox.js',
		'script-loader!./node_modules/jquery.cookie/jquery.cookie.js',
		'script-loader!./node_modules/scrolltofixed/jquery-scrolltofixed.js',
		'script-loader!./node_modules/cleave.js/dist/cleave.js',
		'script-loader!./src/www/theme/dreamdinners/js/customer/vendor/jquery.ba-dotimeout.js',
		'script-loader!./node_modules/jquery-ui/dist/jquery-ui.js'
	],
	output: {
		filename: 'vendor.min.js',
		path: path.resolve(__dirname, 'www/theme/dreamdinners/scripts/admin')
	},
});

module.exports = [configCustomer, configAdmin];