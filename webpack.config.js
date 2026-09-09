/**
 * wp-scripts webpack configuration.
 *
 * Points the entry at the frontend JS (which imports the SCSS) and outputs to
 * assets/build per the plan's skeleton.
 *
 * @package
 */

const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );

module.exports = {
	...defaultConfig,
	entry: {
		frontend: path.resolve( __dirname, 'assets/src/js/index.js' ),
		'faq-block': path.resolve(
			__dirname,
			'assets/src/blocks/faq/index.js'
		),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'assets/build' ),
	},
};
