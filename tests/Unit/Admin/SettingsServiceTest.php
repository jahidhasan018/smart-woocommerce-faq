<?php
/**
 * SettingsService unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use WSFQ\Admin\SettingsService;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the settings service.
 */
final class SettingsServiceTest extends TestCase {

	/**
	 * Get_all() returns defaults when nothing is stored.
	 */
	public function test_get_all_returns_defaults(): void {
		Functions\when( 'get_option' )->justReturn( false );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$settings = ( new SettingsService() )->get_all();

		$this->assertSame( SettingsService::DEFAULTS, $settings );
		$this->assertTrue( $settings['display']['expand_all'] );
		$this->assertTrue( $settings['display']['positions']['after_product_summary'] );
		$this->assertFalse( $settings['display']['positions']['product_tab'] );
	}

	/**
	 * Get_all() returns the stored option when present.
	 */
	public function test_get_all_returns_stored(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'display' => array(
					'expand_all' => false,
					'positions'  => array( 'product_tab' => true ),
				),
			)
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$settings = ( new SettingsService() )->get_all();

		$this->assertFalse( $settings['display']['expand_all'] );
		$this->assertTrue( $settings['display']['positions']['product_tab'] );
	}

	/**
	 * Save() merges partial updates and persists via update_option.
	 */
	public function test_save_merges_and_persists(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'display' => array(
					'expand_all' => true,
					'positions'  => array(
						'product_tab' => true,
						'cart'        => true,
					),
				),
			)
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'absint' )->returnArg();

		$saved = array();
		Functions\expect( 'update_option' )
			->once()
			->with( 'wsfq_settings', \Mockery::type( 'array' ) )
			->andReturnUsing(
				static function ( $key, $value ) use ( &$saved ) {
					$saved[] = $value;
					return true;
				}
			);

		Functions\expect( 'do_action' )
			->once()
			->with( 'wsfq_settings_saved', \Mockery::type( 'array' ) );

		$settings = ( new SettingsService() )->save(
			array(
				'display' => array(
					'expand_all' => false,
				),
			)
		);

		$this->assertFalse( $saved[0]['display']['expand_all'] );
		$this->assertTrue( $saved[0]['display']['positions']['cart'] );
	}

	/**
	 * Defaults enable exactly one product-page position.
	 *
	 * The product-page positions are mutually exclusive: the accordion renders
	 * in one place on a product page, so more than one on is an invalid state.
	 */
	public function test_defaults_enable_single_product_position(): void {
		Functions\when( 'get_option' )->justReturn( false );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$settings  = ( new SettingsService() )->get_all();
		$positions = $settings['display']['positions'];
		$enabled   = array_keys( array_filter( $positions ) );
		$product   = array_intersect( $enabled, SettingsService::PRODUCT_POSITIONS );

		$this->assertCount( 1, $product, 'Exactly one product-page position may be on.' );
	}

	/**
	 * Page-level positions stay independent of each other.
	 */
	public function test_defaults_enable_all_page_positions(): void {
		Functions\when( 'get_option' )->justReturn( false );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$positions = ( new SettingsService() )->get_all()['display']['positions'];

		$this->assertTrue( $positions['shop_archive'] );
		$this->assertTrue( $positions['cart'] );
		$this->assertTrue( $positions['checkout'] );
	}

	/**
	 * Stored data with several product positions on is normalised on read, so a
	 * pre-existing install cannot render the accordion in five places.
	 */
	public function test_get_all_normalises_stored_multi_position_state(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'display' => array(
					'positions' => array(
						'product_tab'           => true,
						'after_add_to_cart'     => true,
						'after_product_meta'    => true,
						'after_product_summary' => true,
						'after_single_product'  => true,
					),
				),
			)
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$positions = ( new SettingsService() )->get_all()['display']['positions'];
		$enabled   = array_keys( array_filter( $positions ) );
		$product   = array_intersect( $enabled, SettingsService::PRODUCT_POSITIONS );

		$this->assertCount( 1, $product, 'Stored multi-position state must collapse to one.' );
	}

	/**
	 * Save() enforces the single-product-position rule, so REST and CLI writes
	 * cannot create an invalid state.
	 */
	public function test_save_enforces_single_product_position(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'display' => array(
					'positions' => array( 'after_product_summary' => true ),
				),
			)
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'sanitize_text_field' )->returnArg();

		$saved = array();
		Functions\when( 'update_option' )->alias(
			static function ( $key, $value ) use ( &$saved ) {
				$saved[] = $value;
				return true;
			}
		);
		Functions\when( 'do_action' )->justReturn( null );

		( new SettingsService() )->save(
			array(
				'display' => array(
					'positions' => array(
						'product_tab'       => true,
						'after_add_to_cart' => true,
					),
				),
			)
		);

		$positions = $saved[0]['display']['positions'];
		$enabled   = array_keys( array_filter( $positions ) );
		$product   = array_intersect( $enabled, SettingsService::PRODUCT_POSITIONS );

		$this->assertCount( 1, $product, 'Save() must keep exactly one product position on.' );
	}

	/**
	 * An explicit product position in the payload wins over the stored one, so
	 * changing the dropdown is not silently ignored.
	 */
	public function test_save_honours_newly_selected_product_position(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'display' => array(
					'positions' => array( 'after_product_summary' => true ),
				),
			)
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'sanitize_text_field' )->returnArg();

		$saved = array();
		Functions\when( 'update_option' )->alias(
			static function ( $key, $value ) use ( &$saved ) {
				$saved[] = $value;
				return true;
			}
		);
		Functions\when( 'do_action' )->justReturn( null );

		( new SettingsService() )->save(
			array(
				'display' => array(
					'positions' => array( 'product_tab' => true ),
				),
			)
		);

		$positions = $saved[0]['display']['positions'];

		$this->assertTrue( $positions['product_tab'] );
		$this->assertFalse( $positions['after_product_summary'] );
	}

	/**
	 * Choosing "no product position" is a real choice: it must persist rather
	 * than falling back to the default.
	 */
	public function test_save_allows_disabling_product_position(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'display' => array(
					'positions' => array( 'after_product_summary' => true ),
				),
			)
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'sanitize_text_field' )->returnArg();

		$saved = array();
		Functions\when( 'update_option' )->alias(
			static function ( $key, $value ) use ( &$saved ) {
				$saved[] = $value;
				return true;
			}
		);
		Functions\when( 'do_action' )->justReturn( null );

		( new SettingsService() )->save(
			array(
				'display' => array(
					'positions' => array( 'after_product_summary' => false ),
				),
			)
		);

		$positions = $saved[0]['display']['positions'];
		$enabled   = array_filter(
			array_intersect_key( $positions, array_flip( SettingsService::PRODUCT_POSITIONS ) )
		);

		$this->assertSame( array(), $enabled, 'No product position should stay on.' );
		$this->assertFalse( $positions['after_product_summary'] );
	}
}
