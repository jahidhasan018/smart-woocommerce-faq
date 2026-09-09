<?php
/**
 * Settings REST controller.
 *
 * Exposes wsfq/v1/settings (GET + POST) so the admin React app, CLI, and
 * automation all go through one validated path backed by register_setting.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Api;

use WSFQ\Admin\SettingsService;

/**
 * Settings REST endpoints.
 */
final class SettingsController {

	public const NAMESPACE = 'wsfq/v1';
	public const ROUTE     = '/settings';

	/**
	 * Settings service.
	 *
	 * @var SettingsService
	 */
	private SettingsService $service;

	/**
	 * Constructor.
	 *
	 * @param SettingsService $service Settings service.
	 */
	public function __construct( SettingsService $service ) {
		$this->service = $service;
	}

	/**
	 * Register the routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'permission_callback' ),
					'args'                => array(
						'display' => array(
							'type'        => 'object',
							'description' => __( 'Display settings.', 'smart-woocommerce-faq' ),
						),
					),
				),
			)
		);
	}

	/**
	 * GET wsfq/v1/settings.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|array
	 */
	public function get_settings( \WP_REST_Request $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		$settings = $this->service->get_all();

		/**
		 * Fires after settings are read via REST.
		 *
		 * @param array $settings Settings.
		 */
		do_action( 'wsfq_settings_rest_read', $settings );

		return rest_ensure_response( $settings );
	}

	/**
	 * POST wsfq/v1/settings.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|array
	 */
	public function update_settings( \WP_REST_Request $request ) {
		$payload = $request->get_json_params();
		$payload = is_array( $payload ) ? $payload : array();

		$settings = $this->service->save( $payload );

		return rest_ensure_response( $settings );
	}

	/**
	 * Permission callback — admins only.
	 *
	 * @return bool
	 */
	public function permission_callback(): bool {
		return current_user_can( 'manage_options' );
	}
}
