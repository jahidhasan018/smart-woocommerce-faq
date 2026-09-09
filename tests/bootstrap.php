<?php
/**
 * PHPUnit bootstrap.
 *
 * Loads the plugin autoloader. When WP_TESTS_DIR is set (integration runs via
 * bin/install-wp-tests.sh), it loads the WordPress core test bootstrap so real
 * $wpdb / post-type / hook behavior is available. Otherwise (unit suite) it
 * just wires Composer autoloading — Brain\Monkey lifecycle lives in the
 * WSFQ\Tests\Unit\TestCase base class.
 *
 * @package WSFQ
 */

declare(strict_types=1);

$autoloader = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( is_readable( $autoloader ) ) {
	require_once $autoloader;
}

// Unit tests: no WP bootstrap needed. Base TestCase is required explicitly
// (tests are intentionally NOT composer-autoloaded to avoid double-loading).
if ( empty( getenv( 'WP_TESTS_DIR' ) ) ) {
	// Plugin constants are normally defined in the main plugin file, which unit
	// tests don't load (no WP). Define them here so Core classes can reference them.
	if ( ! defined( 'WSFQ_VERSION' ) ) {
		define( 'WSFQ_VERSION', '0.1.0' );
	}
	if ( ! defined( 'WSFQ_FILE' ) ) {
		define( 'WSFQ_FILE', dirname( __DIR__ ) . '/smart-woocommerce-faq.php' );
	}
	if ( ! defined( 'WSFQ_DIR' ) ) {
		define( 'WSFQ_DIR', dirname( __DIR__ ) . '/' );
	}
	if ( ! defined( 'WSFQ_URL' ) ) {
		define( 'WSFQ_URL', 'http://example.test/plugins/smart-woocommerce-faq/' );
	}
	// Minimal WP_Block stub so classes type-hinting it are unit-testable.
	if ( ! class_exists( '\WP_Block' ) ) {
		/**
		 * Test stub for WP_Block.
		 *
		 * @package WSFQ
		 */
		class WP_Block { // phpcs:ignore
			/**
			 * Block attributes.
			 *
			 * @var array
			 */
			public $attributes = array();

			/**
			 * Block context.
			 *
			 * @var array
			 */
			public $context = array();

			/**
			 * Parsed block.
			 *
			 * @var array
			 */
			public $parsed_block = array();

			/**
			 * Constructor.
			 *
			 * @param array $parsed_block Parsed block.
			 * @param array $context      Block context.
			 */
			public function __construct( $parsed_block = array(), $context = array() ) {
				$this->parsed_block = $parsed_block;
				$this->attributes   = isset( $parsed_block['attrs'] ) ? $parsed_block['attrs'] : array();
				$this->context      = $context;
			}
		}
	}
	// Minimal REST stubs so API classes type-hinting them are unit-testable.
	if ( ! class_exists( '\WP_REST_Request' ) ) {
		/**
		 * Test stub for WP_REST_Request.
		 *
		 * @package WSFQ
		 */
		class WP_REST_Request { // phpcs:ignore
			/**
			 * Get JSON body params.
			 *
			 * @return array
			 */
			public function get_json_params() {
				return array();
			}
		}
	}
	if ( ! class_exists( '\WP_REST_Server' ) ) {
		/**
		 * Test stub for WP_REST_Server.
		 *
		 * @package WSFQ
		 */
		class WP_REST_Server { // phpcs:ignore
			public const READABLE   = 'GET';
			public const CREATABLE  = 'POST';
			public const EDITABLE   = 'PUT, PATCH';
			public const DELETABLE  = 'DELETE';
			public const ALLMETHODS = 'GET, POST, PUT, PATCH, DELETE';
		}
	}
	if ( ! class_exists( '\WP_REST_Response' ) ) {
		/**
		 * Test stub for WP_REST_Response.
		 *
		 * @package WSFQ
		 */
		class WP_REST_Response { // phpcs:ignore
			/**
			 * Response data.
			 *
			 * @var mixed
			 */
			public $data;

			/**
			 * Constructor.
			 *
			 * @param mixed $data Response data.
			 */
			public function __construct( $data = null ) {
				$this->data = $data;
			}

			/**
			 * Get data.
			 *
			 * @return mixed
			 */
			public function get_data() {
				return $this->data;
			}
		}
	}
	if ( ! class_exists( '\WP_Error' ) ) {
		/**
		 * Test stub for WP_Error.
		 *
		 * @package WSFQ
		 */
		class WP_Error { // phpcs:ignore
			/**
			 * Error data.
			 *
			 * @var array
			 */
			public $errors = array();

			/**
			 * Constructor.
			 *
			 * @param string $code    Error code.
			 * @param string $message Error message.
			 */
			public function __construct( $code = '', $message = '' ) {
				$this->errors = array( $code => array( $message ) );
			}

			/**
			 * Check for errors.
			 *
			 * @return bool
			 */
			public function has_errors() {
				return ! empty( $this->errors );
			}
		}
	}
	require_once __DIR__ . '/Unit/TestCase.php';
	return;
}

// Integration tests: real WP test suite.
$wp_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! is_dir( $wp_tests_dir ) ) {
	trigger_error( 'WP_TESTS_DIR does not exist: ' . $wp_tests_dir, E_USER_ERROR ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
}

require_once $wp_tests_dir . '/includes/functions.php';

/**
 * Load the plugin under test.
 */
function _wsfq_manually_load_plugin(): void {
	require_once dirname( __DIR__ ) . '/smart-woocommerce-faq.php';
}

tests_add_filter( 'muplugins_loaded', '_wsfq_manually_load_plugin' );

require_once $wp_tests_dir . '/includes/bootstrap.php';
