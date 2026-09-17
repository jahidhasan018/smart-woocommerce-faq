<?php
/**
 * Integration tests for shortcodes.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Integration;

use WSFQ\Admin\SettingsService;
use WSFQ\Frontend\Renderers\AccordionRenderer;
use WSFQ\Frontend\Shortcodes\ShortcodeRegistry;
use WSFQ\Support\FaqAssignments;
use WSFQ\Support\FaqResolver;
use WSFQ\Support\PostMetaFaqAssignment;
use WP_UnitTestCase;

/**
 * Verifies shortcodes against real WordPress.
 */
final class ShortcodeTest extends WP_UnitTestCase {

	private ShortcodeRegistry $registry;

	protected function setUp(): void {
		parent::setUp();
		$repository     = new PostMetaFaqAssignment();
		$this->registry = new ShortcodeRegistry(
			new FaqResolver( $repository ),
			new AccordionRenderer( new SettingsService() )
		);
		do_action( 'init' );
	}

	/**
	 * [wsfq_faq_all] renders all published FAQs.
	 */
	public function test_faq_all_renders_all(): void {
		$faq1 = $this->factory()->post->create(
			array(
				'post_type'    => 'wsfq_faq',
				'post_title'   => 'First',
				'post_content' => 'Answer 1',
				'post_status'  => 'publish',
			)
		);
		$this->factory()->post->create(
			array(
				'post_type'    => 'wsfq_faq',
				'post_title'   => 'Second',
				'post_content' => 'Answer 2',
				'post_status'  => 'publish',
			)
		);

		$html = $this->registry->render_all( array() );

		$this->assertStringContainsString( 'wsfq-faq-' . $faq1, $html );
		$this->assertStringContainsString( 'First', $html );
		$this->assertStringContainsString( 'Answer 2', $html );
	}

	/**
	 * [wsfq_faq_ids ids="x,y"] renders only the given FAQs.
	 */
	public function test_faq_ids_renders_given_only(): void {
		$faq1 = $this->factory()->post->create( array( 'post_type' => 'wsfq_faq', 'post_title' => 'Keep', 'post_status' => 'publish' ) );
		$faq2 = $this->factory()->post->create( array( 'post_type' => 'wsfq_faq', 'post_title' => 'Drop', 'post_status' => 'publish' ) );

		$html = $this->registry->render_ids( array( 'ids' => (string) $faq1 ) );

		$this->assertStringContainsString( 'Keep', $html );
		$this->assertStringNotContainsString( 'Drop', $html );
	}

	/**
	 * [wsfq_faq_category id="x"] renders FAQs in a category term.
	 */
	public function test_faq_category_renders_term_faqs(): void {
		$term_id = $this->factory()->term->create(
			array( 'taxonomy' => 'wsfq_faq_category', 'name' => 'Shipping' )
		);

		$faq_in = $this->factory()->post->create( array( 'post_type' => 'wsfq_faq', 'post_title' => 'In category', 'post_status' => 'publish' ) );
		$faq_out = $this->factory()->post->create( array( 'post_type' => 'wsfq_faq', 'post_title' => 'Outside', 'post_status' => 'publish' ) );

		wp_set_object_terms( $faq_in, array( $term_id ), 'wsfq_faq_category' );

		$html = $this->registry->render_category( array( 'id' => (string) $term_id ) );

		$this->assertStringContainsString( 'In category', $html );
		$this->assertStringNotContainsString( 'Outside', $html );
	}

	/**
	 * [wsfq_faq_product id="x"] renders FAQs assigned to a product.
	 */
	public function test_faq_product_renders_assigned(): void {
		$product_id = $this->factory()->post->create( array( 'post_type' => 'product' ) );
		$faq = $this->factory()->post->create( array( 'post_type' => 'wsfq_faq', 'post_title' => 'Product FAQ', 'post_status' => 'publish' ) );

		( new PostMetaFaqAssignment() )->save(
			$faq,
			FaqAssignments::from_array( array( 'product_ids' => array( $product_id ) ) )
		);

		$html = $this->registry->render_product( array( 'id' => (string) $product_id ) );

		$this->assertStringContainsString( 'Product FAQ', $html );
	}
}