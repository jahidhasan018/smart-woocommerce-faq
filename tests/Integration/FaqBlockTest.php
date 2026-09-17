<?php
/**
 * Integration tests for the FAQ block.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Integration;

use WSFQ\Admin\SettingsService;
use WSFQ\Frontend\Blocks\FaqBlock;
use WSFQ\Frontend\Renderers\AccordionRenderer;
use WP_UnitTestCase;

/**
 * Verifies the FAQ block registers and renders against real WordPress.
 */
final class FaqBlockTest extends WP_UnitTestCase {

	/**
	 * The wsfq/faq block is registered.
	 */
	public function test_block_is_registered(): void {
		do_action( 'init' );

		$this->assertTrue( \WP_Block_Type_Registry::get_instance()->is_registered( 'wsfq/faq' ) );
	}

	/**
	 * The block renders FAQs via the render callback.
	 */
	public function test_block_renders_faqs(): void {
		$faq = $this->factory()->post->create(
			array(
				'post_type'    => 'wsfq_faq',
				'post_title'   => 'Block FAQ',
				'post_content' => 'Block answer',
				'post_status'  => 'publish',
			)
		);

		$block = new FaqBlock( new AccordionRenderer( new SettingsService() ) );
		$out   = $block->render(
			array( 'faqIds' => array( $faq ) ),
			'',
			new \WP_Block(
				array(
					'blockName' => 'wsfq/faq',
					'attrs'     => array( 'faqIds' => array( $faq ) ),
				)
			)
		);

		$this->assertStringContainsString( 'wsfq-accordion', $out );
		$this->assertStringContainsString( 'Block FAQ', $out );
		$this->assertStringContainsString( 'Block answer', $out );
	}
}