<?php
/**
 * SettingsController unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Api;

use Brain\Monkey\Functions;
use WSFQ\Admin\SettingsService;
use WSFQ\Api\SettingsController;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the settings REST controller.
 */
final class SettingsControllerTest extends TestCase {

	/**
	 * Registers GET + POST for wsfq/v1/settings.
	 */
	public function test_register_routes(): void {
		$routes = array();

		Functions\when( 'register_rest_route' )->alias(
			static function ( $ns, $route, $args ) use ( &$routes ) {
				$routes[] = array( $ns, $route, $args );
			}
		);
		Functions\when( '__' )->returnArg();

		$controller = new SettingsController( new SettingsService() );
		$controller->register_routes();

		$this->assertCount( 1, $routes );
		$this->assertSame( 'wsfq/v1', $routes[0][0] );
		$this->assertSame( '/settings', $routes[0][1] );

		$defs    = $routes[0][2];
		$methods = array_map(
			static function ( $def ) {
				return $def['methods'];
			},
			$defs
		);
		$this->assertContains( 'GET', $methods );
		$this->assertContains( 'POST', $methods );
	}

	/**
	 * Permission callback requires the manage_options capability.
	 */
	public function test_permission_callback_checks_capability(): void {
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'register_rest_route' )->justReturn( true );

		$controller = new SettingsController( new SettingsService() );

		$this->assertTrue( $controller->permission_callback() );

		Functions\when( 'current_user_can' )->justReturn( false );
		$this->assertFalse( $controller->permission_callback() );
	}

	/**
	 * GET returns the full settings array.
	 */
	public function test_get_settings(): void {
		Functions\when( 'register_rest_route' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( false );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'rest_ensure_response' )->alias(
			static function ( $value ) {
				return $value;
			}
		);

		$request = \Mockery::mock( \WP_REST_Request::class );

		$controller = new SettingsController( new SettingsService() );
		$result     = $controller->get_settings( $request );

		$this->assertArrayHasKey( 'display', $result );
		$this->assertTrue( $result['display']['expand_all'] );
	}

	/**
	 * POST saves the payload and returns the result.
	 */
	public function test_update_settings(): void {
		Functions\when( 'register_rest_route' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( false );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'absint' )->returnArg();
		Functions\when( 'update_option' )->justReturn( true );
		Functions\when( 'do_action' )->justReturn( null );
		Functions\when( 'rest_ensure_response' )->alias(
			static function ( $value ) {
				return $value;
			}
		);

		$request = \Mockery::mock( \WP_REST_Request::class );
		$request->shouldReceive( 'get_json_params' )->once()->andReturn(
			array( 'display' => array( 'expand_all' => false ) )
		);

		$controller = new SettingsController( new SettingsService() );
		$result     = $controller->update_settings( $request );

		$this->assertFalse( $result['display']['expand_all'] );
	}
}
