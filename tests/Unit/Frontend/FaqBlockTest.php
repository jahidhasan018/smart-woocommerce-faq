<?php
/**
 * FaqBlock unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
use WSFQ\Frontend\Blocks\FaqBlock;
use WSFQ\Frontend\Renderers\AccordionRenderer;
use WSFQ\Frontend\Renderers\RendererInterface;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the FAQ block.
 */
final class FaqBlockTest extends TestCase {

	/**
	 * Registers the block with a render callback.
	 */
	public function test_register_registers_block(): void {
		// The real blocks/faq/block.json exists in the repo, so is_readable() returns true.
		Functions\when( 'wp_register_script' )->justReturn( true );
		Functions\when( 'esc_url' )->returnArg();

		$registered = array();
		Functions\when( 'register_block_type' )->alias(
			static function ( $path, $args ) use ( &$registered ) {
				$registered['path'] = $path;
				$registered['args'] = $args;
			}
		);

		$block = new FaqBlock( new AccordionRenderer() );
		$block->register();

		$this->assertStringContainsString( 'blocks/faq/block.json', $registered['path'] );
		$this->assertArrayHasKey( 'render_callback', $registered['args'] );
	}

	/**
	 * Renders via the renderer with the FAQ ids.
	 */
	public function test_render_outputs_faqs(): void {
		$renderer = \Mockery::mock( RendererInterface::class );
		$renderer->shouldReceive( 'render' )
			->once()
			->with( array( 1, 2, 3 ), \Mockery::type( 'array' ) )
			->andReturn( '<div>ok</div>' );

		$block = new FaqBlock( $renderer );
		$out   = $block->render(
			array( 'faqIds' => array( 1, 2, 3 ) ),
			'',
			new \WP_Block( array(), array() )
		);

		$this->assertSame( '<div>ok</div>', $out );
	}

	/**
	 * Sanitizes ids and ignores invalid values.
	 */
	public function test_render_sanitizes_ids(): void {
		$renderer = \Mockery::mock( RendererInterface::class );
		$renderer->shouldReceive( 'render' )
			->once()
			->with( array( 1, 5, 2 ), \Mockery::type( 'array' ) )
			->andReturn( '<div>ok</div>' );

		$block = new FaqBlock( $renderer );
		$out   = $block->render(
			array( 'faqIds' => array( 1, 'abc', -5, 2, 0 ) ),
			'',
			new \WP_Block( array(), array() )
		);

		$this->assertSame( '<div>ok</div>', $out );
	}

	/**
	 * Returns empty when no ids given.
	 */
	public function test_render_empty_without_ids(): void {
		$block = new FaqBlock( new AccordionRenderer() );

		$this->assertSame(
			'',
			$block->render( array(), '', new \WP_Block( array(), array() ) )
		);
	}
}
