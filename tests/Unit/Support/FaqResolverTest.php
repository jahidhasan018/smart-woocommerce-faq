<?php
/**
 * FaqResolver unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Support;

use Brain\Monkey\Functions;
use WSFQ\Support\FaqAssignments;
use WSFQ\Support\FaqResolver;
use WSFQ\Support\PostMetaFaqAssignment;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the FAQ resolver.
 */
final class FaqResolverTest extends TestCase {

	/**
	 * Global assignments match any product.
	 */
	public function test_matches_global(): void {
		$resolver = new FaqResolver( new PostMetaFaqAssignment() );

		$this->assertTrue( $resolver->matches( FaqAssignments::from_array( array( 'global' => true ) ), 5, array(), array() ) );
	}

	/**
	 * Direct product assignment matches the product.
	 */
	public function test_matches_direct_product(): void {
		$resolver = new FaqResolver( new PostMetaFaqAssignment() );

		$this->assertTrue(
			$resolver->matches( FaqAssignments::from_array( array( 'product_ids' => array( 5, 9 ) ) ), 5, array(), array() )
		);
		$this->assertFalse(
			$resolver->matches( FaqAssignments::from_array( array( 'product_ids' => array( 6 ) ) ), 5, array(), array() )
		);
	}

	/**
	 * Category assignment matches when the product is in that category.
	 */
	public function test_matches_category(): void {
		$resolver = new FaqResolver( new PostMetaFaqAssignment() );

		$this->assertTrue(
			$resolver->matches(
				FaqAssignments::from_array( array( 'category_ids' => array( 20 ) ) ),
				5,
				array( 18, 20 ),
				array()
			)
		);
		$this->assertFalse(
			$resolver->matches(
				FaqAssignments::from_array( array( 'category_ids' => array( 30 ) ) ),
				5,
				array( 18, 20 ),
				array()
			)
		);
	}

	/**
	 * Tag assignment matches when the product has that tag.
	 */
	public function test_matches_tag(): void {
		$resolver = new FaqResolver( new PostMetaFaqAssignment() );

		$this->assertTrue(
			$resolver->matches(
				FaqAssignments::from_array( array( 'tag_ids' => array( 40 ) ) ),
				5,
				array(),
				array( 40 )
			)
		);
	}

	/**
	 * Variation assignment matches the variation ID.
	 */
	public function test_matches_variation(): void {
		$resolver = new FaqResolver( new PostMetaFaqAssignment() );

		$this->assertTrue(
			$resolver->matches(
				FaqAssignments::from_array( array( 'variation_ids' => array( 88 ) ) ),
				88,
				array(),
				array()
			)
		);
		$this->assertFalse(
			$resolver->matches(
				FaqAssignments::from_array( array( 'variation_ids' => array( 88 ) ) ),
				5,
				array(),
				array()
			)
		);
	}

	/**
	 * Resolve() returns matching FAQ IDs, excluding unassigned ones.
	 */
	public function test_resolve_returns_matching_ids(): void {
		$repository = new PostMetaFaqAssignment();

		// FAQ 10 global, FAQ 11 assigned to product 5, FAQ 12 unassigned.
		$meta_map = array(
			10 => array( '_wsfq_global' => '1' ),
			11 => array( '_wsfq_product_ids' => array( 5 ) ),
			12 => array(),
		);

		Functions\when( 'get_post_meta' )->alias(
			static function ( $post_id, $key, $single ) use ( $meta_map ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
				$data = $meta_map[ $post_id ] ?? array();
				return $data[ $key ] ?? '';
			}
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'get_the_terms' )->justReturn( array() );

		$resolver = new FaqResolver( $repository );

		$ids = $resolver->resolve( 5, array( 10, 11, 12 ) );

		$this->assertSame( array( 10, 11 ), $ids );
	}

	/**
	 * Resolve() applies the result filter hook.
	 */
	public function test_resolve_applies_result_filter(): void {
		Functions\when( 'get_post_meta' )->justReturn( '' );
		Functions\when( 'get_the_terms' )->justReturn( array() );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				if ( 'wsfq_resolve_faq_ids' === $tag ) {
					return array( 10, 11, 12 );
				}
				return $value;
			}
		);

		$resolver = new FaqResolver( new PostMetaFaqAssignment() );

		$ids = $resolver->resolve( 5, array( 10, 11, 12 ) );

		$this->assertSame( array( 10, 11, 12 ), $ids );
	}
}
