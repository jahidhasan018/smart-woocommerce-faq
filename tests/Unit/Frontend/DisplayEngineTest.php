<?php
/**
 * DisplayEngine unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
use WSFQ\Admin\SettingsService;
use WSFQ\Frontend\DisplayEngine;
use WSFQ\Frontend\Renderers\AccordionRenderer;
use WSFQ\Frontend\Renderers\RendererInterface;
use WSFQ\Support\Interfaces\FaqResolverInterface;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the display engine.
 */
final class DisplayEngineTest extends TestCase {

	/**
	 * Register() hooks the configured positions.
	 */
	public function test_register_hooks_positions(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'display' => array(
					'positions' => array( 'product_tab' => true ),
				),
			)
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'add_action' )->justReturn( null );
		Functions\when( 'add_filter' )->justReturn( null );

		$engine = new DisplayEngine(
			$this->resolver(),
			$this->renderer(),
			new SettingsService()
		);

		$hooked = array();
		Functions\when( 'add_filter' )->alias(
			static function ( $tag, $callback ) use ( &$hooked ) {
				$hooked[ $tag ] = $callback;
			}
		);

		$engine->register();

		// Product tab added via filter.
		$this->assertArrayHasKey( 'woocommerce_product_tabs', $hooked );
	}

	/**
	 * Register() skips positions disabled in the option.
	 */
	public function test_register_skips_disabled_positions(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'display' => array(
					'positions' => array(
						'product_tab'       => false,
						'after_add_to_cart' => false,
					),
				),
			)
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'add_action' )->justReturn( null );

		$hooked = array();
		Functions\when( 'add_filter' )->alias(
			static function ( $tag, $callback ) use ( &$hooked ) {
				$hooked[ $tag ] = $callback;
			}
		);

		$engine = new DisplayEngine(
			$this->resolver(),
			$this->renderer(),
			new SettingsService()
		);
		$engine->register();

		$this->assertArrayNotHasKey( 'woocommerce_product_tabs', $hooked );
	}

	/**
	 * Positions come from the settings service, so the admin toggles actually
	 * control the storefront.
	 */
	public function test_register_reads_positions_from_settings(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'display' => array(
					'positions' => array(
						'product_tab' => true,
						'cart'        => true,
						'checkout'    => false,
					),
				),
			)
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$filters = array();
		Functions\when( 'add_filter' )->alias(
			static function ( $tag, $cb ) use ( &$filters ) {
				$filters[ $tag ] = $cb;
			}
		);
		$actions = array();
		Functions\when( 'add_action' )->alias(
			static function ( $tag, $cb ) use ( &$actions ) {
				$actions[ $tag ] = $cb;
			}
		);

		$engine = new DisplayEngine(
			$this->resolver(),
			$this->renderer(),
			new SettingsService()
		);
		$engine->register();

		$this->assertArrayHasKey( 'woocommerce_product_tabs', $filters );
		$this->assertArrayHasKey( 'woocommerce_cart_collaterals', $actions );
		// Disabled positions must not be hooked.
		$this->assertArrayNotHasKey( 'woocommerce_after_checkout_form', $actions );
	}

	/**
	 * Render() reads the expand-all setting rather than a separate option.
	 */
	public function test_render_reads_expand_all_from_settings(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'display' => array( 'expand_all' => false ),
			)
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$captured = null;
		$renderer = \Mockery::mock( RendererInterface::class );
		$renderer->shouldReceive( 'render' )
			->once()
			->andReturnUsing(
				static function ( $ids, $args ) use ( &$captured ) {
					$captured = $args;
					return '';
				}
			);

		$engine = new DisplayEngine(
			$this->resolver(),
			$renderer,
			new SettingsService()
		);
		$engine->render( array( 1 ), array() );

		$this->assertFalse( $captured['expand_all'] );
	}

	/**
	 * Render_for_product renders the resolved FAQs.
	 */
	public function test_render_for_product(): void {
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$resolver = \Mockery::mock( FaqResolverInterface::class );
		$resolver->shouldReceive( 'resolve' )->once()->with( 42 )->andReturn( array( 10, 11 ) );

		$renderer = \Mockery::mock( RendererInterface::class );
		$renderer->shouldReceive( 'render' )->once()->with( array( 10, 11 ), \Mockery::type( 'array' ) )->andReturn( '<div>ok</div>' );

		$engine = new DisplayEngine( $resolver, $renderer, new SettingsService() );

		$this->assertSame( '<div>ok</div>', $engine->render_for_product( 42, array() ) );
	}

	/**
	 * Render_for_product returns empty when no FAQs resolve.
	 */
	public function test_render_for_product_empty(): void {
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$resolver = \Mockery::mock( FaqResolverInterface::class );
		$resolver->shouldReceive( 'resolve' )->once()->with( 42 )->andReturn( array() );

		$renderer = \Mockery::mock( RendererInterface::class );
		$renderer->shouldReceive( 'render' )->once()->with( array(), \Mockery::type( 'array' ) )->andReturn( '' );

		$engine = new DisplayEngine( $resolver, $renderer, new SettingsService() );

		$this->assertSame( '', $engine->render_for_product( 42, array() ) );
	}

	/**
	 * Enqueues the built frontend assets when the file exists.
	 */
	public function test_enqueue_assets(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$registered = array();
		Functions\when( 'wp_register_script' )->alias(
			static function ( $handle, $src, $deps, $version ) use ( &$registered ) {
				$registered['scripts'][ $handle ] = array( $src, $deps, $version );
			}
		);
		Functions\when( 'wp_register_style' )->alias(
			static function ( $handle, $src, $deps, $version ) use ( &$registered ) {
				$registered['styles'][ $handle ] = array( $src, $deps, $version );
			}
		);
		Functions\when( 'wp_enqueue_script' )->alias(
			static function ( $h ) use ( &$registered ) {
				$registered['enqueued_scripts'][] = $h;
			}
		);
		Functions\when( 'wp_enqueue_style' )->alias(
			static function ( $h ) use ( &$registered ) {
				$registered['enqueued_styles'][] = $h;
			}
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'plugin_dir_url' )->justReturn( 'http://example.test/wp-content/plugins/smart-woocommerce-faq/' );

		$engine = new DisplayEngine(
			$this->resolver(),
			$this->renderer(),
			new SettingsService()
		);
		$engine->enqueue_assets();

		$this->assertContains( 'wsfq-frontend', $registered['enqueued_scripts'] );
		$this->assertContains( 'wsfq-frontend', $registered['enqueued_styles'] );
	}

	/**
	 * Mock resolver.
	 *
	 * @return FaqResolverInterface
	 */
	private function resolver(): FaqResolverInterface {
		return \Mockery::mock( FaqResolverInterface::class );
	}

	/**
	 * Default renderer.
	 *
	 * @return RendererInterface
	 */
	private function renderer(): RendererInterface {
		return new AccordionRenderer( new SettingsService() );
	}
}
