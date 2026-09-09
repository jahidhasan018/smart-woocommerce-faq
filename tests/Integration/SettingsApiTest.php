<?php
/**
 * Integration tests for the settings REST API.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Integration;

use WSFQ\Admin\SettingsService;
use WSFQ\Api\SettingsController;
use WP_UnitTestCase;

/**
 * Verifies the settings route against real WordPress.
 */
final class SettingsApiTest extends WP_UnitTestCase {

	/**
	 * Settings persist via the service and read back.
	 */
	public function test_save_and_get_round_trip(): void {
		$service = new SettingsService();

		$service->save(
			array(
				'display' => array(
					'expand_all' => false,
					'positions'  => array( 'product_tab' => false ),
				),
			)
		);

		$settings = $service->get_all();

		$this->assertFalse( $settings['display']['expand_all'] );
		$this->assertFalse( $settings['display']['positions']['product_tab'] );
		// Untouched positions keep defaults.
		$this->assertTrue( $settings['display']['positions']['cart'] );
	}

	/**
	 * The route is registered in the REST server.
	 */
	public function test_route_is_registered(): void {
		$server = rest_get_server();
		$routes = $server->get_routes( 'wsfq/v1' );

		$this->assertArrayHasKey( '/wsfq/v1/settings', $routes );
	}

	/**
	 * GET returns settings with the admin capability.
	 */
	public function test_get_returns_settings(): void {
		wp_set_current_user( 1 ); // admin.
		( new SettingsService() )->save( array( 'display' => array( 'expand_all' => false ) ) );

		$request  = new \WP_REST_Request( 'GET', '/wsfq/v1/settings' );
		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertFalse( $data['display']['expand_all'] );
	}

	/**
	 * POST updates settings.
	 */
	public function test_post_updates_settings(): void {
		wp_set_current_user( 1 ); // admin.

		$request = new \WP_REST_Request( 'POST', '/wsfq/v1/settings' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( array( 'display' => array( 'expand_all' => true ) ) ) );

		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( ( new SettingsService() )->get_all()['display']['expand_all'] );
	}

	/**
	 * Non-admin cannot access the route.
	 */
	public function test_route_requires_admin(): void {
		wp_set_current_user( 0 ); // logged out.

		$request  = new \WP_REST_Request( 'GET', '/wsfq/v1/settings' );
		$response = rest_do_request( $request );

		$this->assertSame( 401, $response->get_status() );
	}
}