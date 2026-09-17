<?php
/**
 * Admin settings page.
 *
 * Registers the wsfq-smart-faq admin page and mounts the React settings app.
 * The React app (assets/src/admin) talks to wsfq/v1/settings.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Admin;

/**
 * Admin page shell.
 */
final class SettingsPage {

	public const SLUG = 'wsfq-smart-faq';

	/**
	 * Register the admin menu page.
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'Smart FAQ', 'smart-woocommerce-faq' ),
			__( 'Smart FAQ', 'smart-woocommerce-faq' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render' ),
			'dashicons-editor-help'
		);
	}

	/**
	 * Enqueue the admin settings app assets.
	 */
	public function enqueue_assets(): void {
		$asset_file = WSFQ_DIR . 'assets/build/admin.asset.php';
		if ( ! is_readable( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		$deps  = isset( $asset['dependencies'] ) && is_array( $asset['dependencies'] ) ? $asset['dependencies'] : array();
		$ver   = isset( $asset['version'] ) ? $asset['version'] : WSFQ_VERSION;

		wp_enqueue_script(
			'wsfq-admin',
			esc_url( WSFQ_URL . 'assets/build/admin.js' ),
			$deps,
			$ver,
			true
		);
		wp_enqueue_style(
			'wsfq-admin',
			esc_url( WSFQ_URL . 'assets/build/style-admin.css' ),
			array(),
			$ver
		);
	}

	/**
	 * Render the settings page shell.
	 */
	public function render(): void {
		echo '<div class="wrap wsfq-settings-wrap">';
		echo '<h1>' . esc_html__( 'Smart FAQ', 'smart-woocommerce-faq' ) . '</h1>';
		echo '<p class="wsfq-settings__intro">' . esc_html__( 'Manage your FAQ library and control where the accordion appears across your WooCommerce store.', 'smart-woocommerce-faq' ) . '</p>';
		echo '<div id="wsfq-settings-root" data-wsfq-settings-page="' . esc_attr( self::SLUG ) . '"></div>';
		echo '</div>';
	}
}
