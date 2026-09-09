<?php
/**
 * PHPStan-only WooCommerce symbols used by this plugin.
 *
 * The full php-stubs/woocommerce-stubs file is too large for PHPStan's memory,
 * so this partial stub declares only the WooCommerce symbols the plugin code
 * references. Not loaded in production.
 *
 * @package WSFQ
 */

declare(strict_types=1);

class WC_Product {
	public function get_id(): int { return 0; }
}

class WC_Cart {
	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_cart(): array { return array(); }
}

/**
 * @return object|null
 */
function WC() {
	return null;
}

/**
 * @return bool
 */
function is_product() {
	return false;
}