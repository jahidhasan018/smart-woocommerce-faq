<?php
/**
 * ShortcodeRegistry unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
use WSFQ\Admin\SettingsService;
use WSFQ\Frontend\Renderers\AccordionRenderer;
use WSFQ\Frontend\Renderers\RendererInterface;
use WSFQ\Frontend\Shortcodes\ShortcodeRegistry;
use WSFQ\Support\Interfaces\FaqResolverInterface;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the shortcode registry.
 */
final class ShortcodeRegistryTest extends TestCase {

	/**
	 * Registers all six shortcodes.
	 */
	public function test_register_registers_shortcodes(): void {
		$registered = array();
		Functions\when( 'add_shortcode' )->alias(
			static function ( $tag, $callback ) use ( &$registered ) {
				$registered[ $tag ] = $callback;
			}
		);

		$registry = new ShortcodeRegistry( $this->resolver(), $this->renderer() );
		$registry->register();

		$this->assertArrayHasKey( 'wsfq_faq_all', $registered );
		$this->assertArrayHasKey( 'wsfq_faq_category', $registered );
		$this->assertArrayHasKey( 'wsfq_faq_ids', $registered );
		$this->assertArrayHasKey( 'wsfq_faq_product', $registered );
		$this->assertArrayHasKey( 'wsfq_faq_current', $registered );
		$this->assertArrayHasKey( 'wsfq_faq_group', $registered );
	}

	/**
	 * Wsfq_faq_all renders all published FAQs.
	 */
	public function test_faq_all_renders_all(): void {
		$renderer = \Mockery::mock( RendererInterface::class );
		$renderer->shouldReceive( 'render' )
			->once()
			->with( array( 1, 2, 3 ), \Mockery::type( 'array' ) )
			->andReturn( '<div>faqs</div>' );

		Functions\when( 'add_shortcode' )->justReturn( null );
		Functions\when( 'get_posts' )->justReturn( array( (object) array( 'ID' => 1 ), (object) array( 'ID' => 2 ), (object) array( 'ID' => 3 ) ) );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$registry = new ShortcodeRegistry( $this->resolver(), $renderer );
		$output   = $registry->render_all( array() );

		$this->assertSame( '<div>faqs</div>', $output );
	}

	/**
	 * Wsfq_faq_ids renders the given FAQ ids.
	 */
	public function test_faq_ids_renders_given_ids(): void {
		$renderer = \Mockery::mock( RendererInterface::class );
		$renderer->shouldReceive( 'render' )
			->once()
			->with( array( 5, 9 ), \Mockery::type( 'array' ) )
			->andReturn( '<div>ok</div>' );

		Functions\when( 'add_shortcode' )->justReturn( null );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$registry = new ShortcodeRegistry( $this->resolver(), $renderer );

		$this->assertSame( '<div>ok</div>', $registry->render_ids( array( 'ids' => '5,9' ) ) );
	}

	/**
	 * Wsfq_faq_product renders FAQs for a specific product.
	 */
	public function test_faq_product_renders_product_faqs(): void {
		$resolver = \Mockery::mock( FaqResolverInterface::class );
		$resolver->shouldReceive( 'resolve' )->once()->with( 77 )->andReturn( array( 1, 2 ) );

		$renderer = \Mockery::mock( RendererInterface::class );
		$renderer->shouldReceive( 'render' )
			->once()
			->with( array( 1, 2 ), \Mockery::type( 'array' ) )
			->andReturn( '<div>ok</div>' );

		Functions\when( 'add_shortcode' )->justReturn( null );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$registry = new ShortcodeRegistry( $resolver, $renderer );

		$this->assertSame( '<div>ok</div>', $registry->render_product( array( 'id' => '77' ) ) );
	}

	/**
	 * Wsfq_faq_current renders for the current product.
	 */
	public function test_faq_current_renders_current_product(): void {
		$resolver = \Mockery::mock( FaqResolverInterface::class );
		$resolver->shouldReceive( 'resolve' )->once()->with( 42 )->andReturn( array( 10 ) );

		$renderer = \Mockery::mock( RendererInterface::class );
		$renderer->shouldReceive( 'render' )
			->once()
			->with( array( 10 ), \Mockery::type( 'array' ) )
			->andReturn( '<div>ok</div>' );

		Functions\when( 'add_shortcode' )->justReturn( null );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'get_the_ID' )->justReturn( 42 );

		$registry = new ShortcodeRegistry( $resolver, $renderer );

		$this->assertSame( '<div>ok</div>', $registry->render_current( array() ) );
	}

	/**
	 * Wsfq_faq_current returns empty when no product context.
	 */
	public function test_faq_current_empty_without_product(): void {
		Functions\when( 'add_shortcode' )->justReturn( null );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'get_the_ID' )->justReturn( 0 );

		$registry = new ShortcodeRegistry( $this->resolver(), $this->renderer() );

		$this->assertSame( '', $registry->render_current( array() ) );
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
