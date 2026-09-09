<?php
/**
 * DisplayEngine unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
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
		Functions\when( 'get_option' )->justReturn( array( 'product_tab' => true ) );
		Functions\when( 'add_action' )->justReturn( null );
		Functions\when( 'add_filter' )->justReturn( null );

		$engine = new DisplayEngine(
			$this->resolver(),
			$this->renderer()
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
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'add_action' )->justReturn( null );

		$hooked = array();
		Functions\when( 'add_filter' )->alias(
			static function ( $tag, $callback ) use ( &$hooked ) {
				$hooked[ $tag ] = $callback;
			}
		);

		$engine = new DisplayEngine( $this->resolver(), $this->renderer() );
		$engine->register();

		$this->assertArrayNotHasKey( 'woocommerce_product_tabs', $hooked );
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

		$engine = new DisplayEngine( $resolver, $renderer );

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

		$engine = new DisplayEngine( $resolver, $renderer );

		$this->assertSame( '', $engine->render_for_product( 42, array() ) );
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
		return new AccordionRenderer();
	}
}
