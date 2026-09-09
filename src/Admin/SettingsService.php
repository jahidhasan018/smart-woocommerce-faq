<?php
/**
 * Settings service.
 *
 * Reads and persists plugin settings (wsfq_settings option) with merge-on-save
 * semantics. Backed by the WP Settings API / register_setting and exposed to
 * the admin React app + CLI via the wsfq/v1/settings REST route.
 *
 * Hooks:
 * - `wsfq_settings_loaded` (filter, array $settings) — before returning.
 * - `wsfq_settings_saved` (action, array $settings) — after persist.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Admin;

/**
 * Settings storage.
 */
final class SettingsService {

	public const OPTION_KEY = 'wsfq_settings';

	/**
	 * Default settings.
	 *
	 * @var array<string, array>
	 */
	public const DEFAULTS = array(
		'display' => array(
			'expand_all' => true,
			'positions'  => array(
				'product_tab'           => true,
				'after_add_to_cart'     => true,
				'after_product_meta'    => true,
				'after_product_summary' => true,
				'after_single_product'  => true,
				'shop_archive'          => true,
				'cart'                  => true,
				'checkout'              => true,
			),
		),
	);

	/**
	 * Register the setting with the Settings API.
	 */
	public function register_setting(): void {
		register_setting(
			'wsfq_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::DEFAULTS,
			)
		);
	}

	/**
	 * Read all settings (merged with defaults).
	 *
	 * @return array
	 */
	public function get_all(): array {
		$stored = get_option( self::OPTION_KEY, array() );
		$stored = is_array( $stored ) ? $stored : array();

		$settings = array_replace_recursive( self::DEFAULTS, $stored );

		/**
		 * Filters the loaded settings.
		 *
		 * @param array $settings Settings.
		 */
		$settings = apply_filters( 'wsfq_settings_loaded', $settings );

		return $settings;
	}

	/**
	 * Persist settings (partial updates merge into stored + defaults).
	 *
	 * @param array $update Partial settings to merge.
	 * @return array The full saved settings.
	 */
	public function save( array $update ): array {
		$current = $this->get_all();
		$merged  = array_replace_recursive( $current, $this->sanitize( $update ) );

		update_option( self::OPTION_KEY, $merged );

		/**
		 * Fires after settings are saved.
		 *
		 * @param array $merged Saved settings.
		 */
		do_action( 'wsfq_settings_saved', $merged );

		return $merged;
	}

	/**
	 * Sanitize a settings payload.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize( array $input ): array {
		$out = array();

		if ( isset( $input['display'] ) && is_array( $input['display'] ) ) {
			$out['display'] = array();

			if ( isset( $input['display']['expand_all'] ) ) {
				$out['display']['expand_all'] = (bool) $input['display']['expand_all'];
			}

			if ( isset( $input['display']['positions'] ) && is_array( $input['display']['positions'] ) ) {
				$out['display']['positions'] = array();
				foreach ( $input['display']['positions'] as $position => $enabled ) {
					$out['display']['positions'][ sanitize_text_field( (string) $position ) ] = (bool) $enabled;
				}
			}
		}

		return $out;
	}
}
