<?php
/**
 * Integration tests for the FAQ schema.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Integration;

use WSFQ\Frontend\SchemaGenerator;
use WSFQ\Frontend\SchemaOutput;
use WSFQ\Support\FaqAssignments;
use WSFQ\Support\FaqResolver;
use WSFQ\Support\PostMetaFaqAssignment;
use WP_UnitTestCase;

/**
 * Verifies FAQPage schema against real WordPress.
 */
final class SchemaTest extends WP_UnitTestCase {

	/**
	 * Schema renders for a FAQ with a real post.
	 */
	public function test_schema_builds_from_real_post(): void {
		$faq = $this->factory()->post->create(
			array(
				'post_type'    => 'wsfq_faq',
				'post_title'   => 'Schema Q',
				'post_content' => 'Schema A',
				'post_status'  => 'publish',
			)
		);

		$generator = new SchemaGenerator();
		$schema    = $generator->build( array( $faq ) );

		$this->assertIsArray( $schema );
		$this->assertSame( 'FAQPage', $schema['@type'] );
		$this->assertSame( 'Schema Q', $schema['mainEntity'][0]['name'] );
		$this->assertSame( 'Schema A', $schema['mainEntity'][0]['acceptedAnswer']['text'] );
	}

	/**
	 * A product with a global FAQ emits schema via the output hook.
	 */
	public function test_product_page_emits_schema(): void {
		if ( ! function_exists( 'wc_get_product' ) ) {
			$this->markTestSkipped( 'WooCommerce not loaded in test environment.' );
		}

		$product_id = $this->factory()->post->create( array( 'post_type' => 'product' ) );
		$faq        = $this->factory()->post->create(
			array(
				'post_type'    => 'wsfq_faq',
				'post_title'   => 'Prod Q',
				'post_content' => 'Prod A',
				'post_status'  => 'publish',
			)
		);
		( new PostMetaFaqAssignment() )->save( $faq, FaqAssignments::from_array( array( 'global' => true ) ) );

		$output = new SchemaOutput(
			new FaqResolver( new PostMetaFaqAssignment() ),
			new SchemaGenerator()
		);

		// Simulate a product page context.
		global $product, $post;
		$post    = get_post( $product_id );
		$product = wc_get_product( $product_id );

		ob_start();
		$output->maybe_output();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'application/ld+json', $html );
		$this->assertStringContainsString( 'FAQPage', $html );
		$this->assertStringContainsString( 'Prod Q', $html );
	}
}