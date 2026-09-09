<?php
/**
 * Integration tests for FAQ assignment + resolution.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Integration;

use WSFQ\Support\FaqAssignments;
use WSFQ\Support\FaqResolver;
use WSFQ\Support\PostMetaFaqAssignment;
use WP_UnitTestCase;

/**
 * Verifies assignment storage + resolver against real WordPress.
 */
final class AssignmentTest extends WP_UnitTestCase {

	private PostMetaFaqAssignment $repository;
	private FaqResolver $resolver;

	protected function setUp(): void {
		parent::setUp();
		$this->repository = new PostMetaFaqAssignment();
		$this->resolver  = new FaqResolver( $this->repository );
		do_action( 'init' );
	}

	/**
	 * save() then get() round-trips assignments via real postmeta.
	 */
	public function test_save_and_get_round_trip(): void {
		$faq_id = $this->factory()->post->create(
			array( 'post_type' => 'wsfq_faq' )
		);

		$assignments = FaqAssignments::from_array(
			array(
				'global'        => true,
				'product_ids'   => array( 10, 11 ),
				'category_ids'  => array( 20 ),
				'tag_ids'       => array( 30 ),
				'variation_ids' => array( 40 ),
			)
		);

		$this->repository->save( $faq_id, $assignments );

		$read = $this->repository->get( $faq_id );
		$this->assertTrue( $read->is_global() );
		$this->assertSame( array( 10, 11 ), $read->get_product_ids() );
		$this->assertSame( array( 20 ), $read->get_category_ids() );
		$this->assertSame( array( 40 ), $read->get_variation_ids() );
	}

	/**
	 * delete() clears all assignment meta.
	 */
	public function test_delete_clears_meta(): void {
		$faq_id = $this->factory()->post->create(
			array( 'post_type' => 'wsfq_faq' )
		);
		$this->repository->save( $faq_id, FaqAssignments::from_array( array( 'global' => true ) ) );

		$this->repository->delete( $faq_id );

		$read = $this->repository->get( $faq_id );
		$this->assertFalse( $read->is_global() );
		$this->assertSame( array(), $read->get_product_ids() );
	}

	/**
	 * Resolver honors global + direct assignment against a real product.
	 */
	public function test_resolver_global_and_direct(): void {
		$product_id = $this->factory()->post->create(
			array( 'post_type' => 'product' )
		);

		$global = $this->factory()->post->create( array( 'post_type' => 'wsfq_faq' ) );
		$direct = $this->factory()->post->create( array( 'post_type' => 'wsfq_faq' ) );
		$other  = $this->factory()->post->create( array( 'post_type' => 'wsfq_faq' ) );

		$this->repository->save( $global, FaqAssignments::from_array( array( 'global' => true ) ) );
		$this->repository->save( $direct, FaqAssignments::from_array( array( 'product_ids' => array( $product_id ) ) ) );
		$this->repository->save( $other, FaqAssignments::from_array( array( 'product_ids' => array( 999999 ) ) ) );

		$ids = $this->resolver->resolve( $product_id );

		$this->assertContains( $global, $ids );
		$this->assertContains( $direct, $ids );
		$this->assertNotContains( $other, $ids );
	}
}