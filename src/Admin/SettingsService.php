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
	 * Product-page positions.
	 *
	 * These are mutually exclusive: the accordion renders in exactly one place
	 * on a product page, so only one of them may be enabled at a time. The order
	 * here is the fallback precedence when more than one is on.
	 *
	 * @var string[]
	 */
	public const PRODUCT_POSITIONS = array(
		'after_product_summary',
		'after_add_to_cart',
		'after_product_meta',
		'product_tab',
		'after_single_product',
	);

	/**
	 * Default settings.
	 *
	 * @var array<string, array>
	 */
	public const DEFAULTS = array(
		'display' => array(
			'expand_all' => true,
			'positions'  => array(
				// Product page: exactly one enabled.
				'after_product_summary' => true,
				'after_add_to_cart'     => false,
				'after_product_meta'    => false,
				'product_tab'           => false,
				'after_single_product'  => false,
				// Independent surfaces, each rendered on its own page.
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

		if ( isset( $settings['display']['positions'] ) && is_array( $settings['display']['positions'] ) ) {
			// A stored choice outranks a default, so upgrading the defaults never
			// overrides a position the site owner already picked.
			$stored_positions = isset( $stored['display']['positions'] ) && is_array( $stored['display']['positions'] )
				? $stored['display']['positions']
				: array();

			$settings['display']['positions'] = $this->single_product_position(
				$settings['display']['positions'],
				$stored_positions
			);
		}

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
		$clean   = $this->sanitize( $update );
		$merged  = array_replace_recursive( $current, $clean );

		if ( isset( $merged['display']['positions'] ) && is_array( $merged['display']['positions'] ) ) {
			$preferred = isset( $clean['display']['positions'] ) && is_array( $clean['display']['positions'] )
				? $clean['display']['positions']
				: array();

			$merged['display']['positions'] = $this->single_product_position(
				$merged['display']['positions'],
				$preferred
			);
		}

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
	 * Collapse the product-page positions so at most one stays enabled.
	 *
	 * Positions explicitly set by the caller win, so an update coming from the
	 * settings UI (or REST / CLI) changes the selection rather than being
	 * silently overridden by whatever was stored.
	 *
	 * @param array<string, bool> $positions All positions.
	 * @param array<string, bool> $preferred Positions explicitly set by the caller.
	 * @return array<string, bool>
	 */
	private function single_product_position( array $positions, array $preferred = array() ): array {
		$chosen = '';

		foreach ( array( $preferred, $positions ) as $source ) {
			foreach ( self::PRODUCT_POSITIONS as $slug ) {
				if ( ! empty( $source[ $slug ] ) ) {
					$chosen = $slug;
					break 2;
				}
			}
		}

		foreach ( self::PRODUCT_POSITIONS as $slug ) {
			$positions[ $slug ] = ( $slug === $chosen );
		}

		return $positions;
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
