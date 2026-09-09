<?php
/**
 * Integration tests for the content-type skeleton.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Integration;

use WP_UnitTestCase;

/**
 * Verifies the CPT + taxonomies register against real WordPress.
 */
final class ContentTypeTest extends WP_UnitTestCase {

	/**
	 * The FAQ post type is registered.
	 */
	public function test_faq_post_type_is_registered(): void {
		do_action( 'init' );

		$this->assertTrue( post_type_exists( 'wsfq_faq' ) );
	}

	/**
	 * FAQ category + group taxonomies are registered and tied to the CPT.
	 */
	public function test_faq_taxonomies_are_registered(): void {
		do_action( 'init' );

		$this->assertTrue( taxonomy_exists( 'wsfq_faq_category' ) );
		$this->assertTrue( taxonomy_exists( 'wsfq_faq_group' ) );

		$tax = get_taxonomy( 'wsfq_faq_category' );
		$this->assertContains( 'wsfq_faq', $tax->object_type );
	}

	/**
	 * A real FAQ post can be created and cloned end-to-end.
	 */
	public function test_faq_can_be_cloned(): void {
		do_action( 'init' );

		$source_id = $this->factory()->post->create(
			array(
				'post_type'  => 'wsfq_faq',
				'post_title' => 'Integration FAQ',
			)
		);

		wp_set_object_terms( $source_id, array( 'shipping' ), 'wsfq_faq_category' );

		$cloned_id = wsfq_clone_faq( $source_id );

		$this->assertIsInt( $cloned_id );
		$this->assertSame( 'draft', get_post_status( $cloned_id ) );
		$this->assertSame(
			'Integration FAQ (copy)',
			get_the_title( $cloned_id )
		);
		$this->assertSame(
			array( 'shipping' ),
			wp_get_object_terms( $cloned_id, 'wsfq_faq_category', array( 'fields' => 'slugs' ) )
		);
	}
}