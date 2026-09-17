<?php
/**
 * Integration tests for the display engine.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Integration;

use WSFQ\Admin\SettingsService;
use WSFQ\Frontend\DisplayEngine;
use WSFQ\Frontend\Renderers\AccordionRenderer;
use WSFQ\Support\FaqAssignments;
use WSFQ\Support\FaqResolver;
use WSFQ\Support\PostMetaFaqAssignment;
use WP_UnitTestCase;

/**
 * Verifies the display engine against real WordPress + WooCommerce.
 */
final class DisplayEngineTest extends WP_UnitTestCase {

	private PostMetaFaqAssignment $repository;
	private DisplayEngine $engine;

	protected function setUp(): void {
		parent::setUp();
		$this->repository = new PostMetaFaqAssignment();
		$this->engine    = new DisplayEngine(
			new FaqResolver( $this->repository ),
			new AccordionRenderer( new SettingsService() ),
			new SettingsService()
		);
		do_action( 'init' );
	}

	/**
	 * The product tab is added when the product has FAQs.
	 */
	public function test_product_tab_added(): void {
		if ( ! function_exists( 'wc_get_product' ) ) {
			$this->markTestSkipped( 'WooCommerce not loaded in test environment.' );
		}

		$product_id = $this->factory()->post->create(
			array( 'post_type' => 'product' )
		);
		$faq_id = $this->factory()->post->create(
			array(
				'post_type'    => 'wsfq_faq',
				'post_title'   => 'Test FAQ',
				'post_content' => 'Test answer',
			)
		);
		$this->repository->save( $faq_id, FaqAssignments::from_array( array( 'product_ids' => array( $product_id ) ) ) );

		// Simulate a product page context.
		global $post, $product;
		$post    = get_post( $product_id );
		$product = wc_get_product( $product_id );

		$tabs = $this->engine->add_product_tab( array() );

		$this->assertArrayHasKey( 'wsfq_faq_tab', $tabs );
		$this->assertSame( 'FAQs', $tabs['wsfq_faq_tab']['title'] );
	}

	/**
	 * render_for_product returns the FAQ HTML for an assigned product.
	 */
	public function test_render_for_product_outputs_faqs(): void {
		$product_id = $this->factory()->post->create(
			array( 'post_type' => 'product' )
		);
		$faq_id = $this->factory()->post->create(
			array(
				'post_type'    => 'wsfq_faq',
				'post_title'   => 'Shipping?',
				'post_content' => 'Fast shipping.',
			)
		);
		$this->repository->save( $faq_id, FaqAssignments::from_array( array( 'global' => true ) ) );

		$html = $this->engine->render_for_product( $product_id );

		$this->assertStringContainsString( 'wsfq-accordion', $html );
		$this->assertStringContainsString( 'Shipping?', $html );
		$this->assertStringContainsString( 'Fast shipping.', $html );
	}

	/**
	 * A product with no FAQs renders empty output.
	 */
	public function test_render_for_product_empty(): void {
		$product_id = $this->factory()->post->create(
			array( 'post_type' => 'product' )
		);

		$this->assertSame( '', $this->engine->render_for_product( $product_id ) );
	}
}