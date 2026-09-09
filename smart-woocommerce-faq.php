<?php
/**
 * Plugin Name:       Smart FAQ for WooCommerce
 * Plugin URI:        https://github.com/jahidhasan018/smart-woocommerce-faq
 * Description:       Central FAQ library with product/category/tag/variation assignment, display engine, Gutenberg blocks, AI generation (BYOK), search, and analytics — all developer-friendly and hooked.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Jahid Hasan
 * Author URI:        https://github.com/jahidhasan018
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smart-woocommerce-faq
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to:      9.9
 *
 * @package WSFQ
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WSFQ_VERSION', '0.1.0' );
define( 'WSFQ_FILE', __FILE__ );
define( 'WSFQ_DIR', plugin_dir_path( __FILE__ ) );
define( 'WSFQ_URL', plugin_dir_url( __FILE__ ) );

require_once WSFQ_DIR . 'vendor/autoload.php';

/**
 * Boot the plugin.
 */
function wsfq_boot(): void {
	\WSFQ\Core\Plugin::instance();

	if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( \WSFQ\Cli\Loader::class ) ) {
		\WSFQ\Cli\Loader::register();
	}
}

add_action( 'plugins_loaded', 'wsfq_boot' );
