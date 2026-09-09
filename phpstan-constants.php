<?php
/**
 * PHPStan-only constant declarations.
 *
 * The real values are defined in the plugin main file at runtime. This file lets
 * PHPStan resolve WSFQ_* constants without booting WordPress. Not loaded in prod.
 *
 * @package WSFQ
 */

declare(strict_types=1);

define( 'WSFQ_VERSION', '0.1.0' );
define( 'WSFQ_FILE', '/path/to/smart-woocommerce-faq.php' );
define( 'WSFQ_DIR', '/path/to/smart-woocommerce-faq/' );
define( 'WSFQ_URL', 'https://example.test/wp-content/plugins/smart-woocommerce-faq/' );
