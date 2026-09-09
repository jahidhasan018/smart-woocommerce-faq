<?php
/**
 * Plugin bootstrap unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Core;

use Brain\Monkey\Functions;
use WSFQ\Core\Plugin;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the Plugin singleton bootstrap.
 */
final class PluginTest extends TestCase {

	/**
	 * Reset the singleton before each test so hook registration can be asserted.
	 */
	protected function setUp(): void {
		parent::setUp();
		$reflection = new \ReflectionClass( Plugin::class );
		$prop       = $reflection->getProperty( 'instance' );
		$prop->setValue( null, null );
	}

	/**
	 * Instantiates the plugin and wires the container.
	 */
	public function test_instance_wires_container(): void {
		$this->mock_core();

		$plugin = Plugin::instance();

		$this->assertInstanceOf( Plugin::class, $plugin );
		$this->assertTrue( $plugin->container()->has( \WSFQ\Core\Upgrader::class ) );
	}

	/**
	 * Asserts instance() is a singleton.
	 */
	public function test_instance_is_singleton(): void {
		$this->mock_core();

		$this->assertSame( Plugin::instance(), Plugin::instance() );
	}

	/**
	 * Activation + deactivation hooks are registered.
	 */
	public function test_registers_lifecycle_hooks(): void {
		// Lifecycle hooks asserted via expect — so do NOT pre-stub them.
		$this->mock_core( false );

		$activated   = array();
		$deactivated = array();

		Functions\expect( 'register_activation_hook' )
			->once()
			->with( \WSFQ_FILE, array( \WSFQ\Core\Activator::class, 'activate' ) )
			->andReturnUsing(
				static function ( $file, $callback ) use ( &$activated ): void {
					$activated[] = array( $file, $callback );
				}
			);

		Functions\expect( 'register_deactivation_hook' )
			->once()
			->with( \WSFQ_FILE, array( \WSFQ\Core\Deactivator::class, 'deactivate' ) )
			->andReturnUsing(
				static function ( $file, $callback ) use ( &$deactivated ): void {
					$deactivated[] = array( $file, $callback );
				}
			);

		Plugin::instance();

		$this->assertCount( 1, $activated );
		$this->assertSame( \WSFQ\Core\Activator::class, $activated[0][1][0] );
		$this->assertCount( 1, $deactivated );
		$this->assertSame( \WSFQ\Core\Deactivator::class, $deactivated[0][1][0] );
	}

	/**
	 * CPT + taxonomy registrars are hooked on init.
	 */
	public function test_registers_init_hooks(): void {
		// Lifecycle hooks need to exist here; add_action is captured, not stubbed.
		$this->mock_core( true, false );

		$registered = array();

		Functions\when( 'add_action' )->alias(
			static function ( $hook, $callback ) use ( &$registered ): void {
				$registered[ $hook ][] = $callback;
			}
		);

		Plugin::instance();

		$this->assertSame(
			array( \WSFQ\PostTypes\FaqPostType::class, 'register' ),
			$registered['init'][0] ?? null
		);
		$this->assertSame(
			array( \WSFQ\Taxonomies\FaqCategory::class, 'register' ),
			$registered['init'][1] ?? null
		);
		$this->assertSame(
			array( \WSFQ\Taxonomies\FaqGroup::class, 'register' ),
			$registered['init'][2] ?? null
		);
	}

	/**
	 * Mock the core WP functions Plugin touches.
	 *
	 * @param bool $stub_lifecycle When false, lifecycle hooks are left unstubbed so
	 *                             tests can assert them via Functions\expect().
	 * @param bool $stub_init      When false, add_action is left unstubbed so tests
	 *                             can assert it via Actions\expectAdded().
	 */
	private function mock_core( bool $stub_lifecycle = true, bool $stub_init = true ): void {
		if ( $stub_lifecycle ) {
			Functions\when( 'register_activation_hook' )->justReturn( null );
			Functions\when( 'register_deactivation_hook' )->justReturn( null );
			Functions\when( 'flush_rewrite_rules' )->justReturn( null );
		}
		if ( $stub_init ) {
			Functions\when( 'add_action' )->justReturn( null );
		}
		Functions\when( 'get_option' )->justReturn( '0.0.0' );
		Functions\when( 'update_option' )->justReturn( true );
	}
}
