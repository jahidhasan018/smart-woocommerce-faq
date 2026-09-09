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
		$this->assertTrue( $settings['display']['positions']['product_tab'] );
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
}
