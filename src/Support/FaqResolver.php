<?php
/**
 * FAQ resolver.
 *
 * Resolves which FAQ posts apply to a given product/variation, honoring global,
 * direct-product, category, tag, and variation assignments.
 *
 * Hooks:
 * - `wsfq_resolve_faq_ids` (filter, int[] $ids, int $product_id) — before return.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Support;

use WSFQ\Support\Interfaces\FaqAssignmentInterface;
use WSFQ\Support\Interfaces\FaqResolverInterface;
use WSFQ\PostTypes\FaqPostType;

/**
 * Resolves applicable FAQs for a product.
 */
final class FaqResolver implements FaqResolverInterface {

	/**
	 * Assignment storage.
	 *
	 * @var FaqAssignmentInterface
	 */
	private FaqAssignmentInterface $repository;

	/**
	 * Constructor.
	 *
	 * @param FaqAssignmentInterface $repository Assignment storage.
	 */
	public function __construct( FaqAssignmentInterface $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Resolve the FAQ IDs that apply to a product.
	 *
	 * @param int        $product_id   Product or variation ID.
	 * @param int[]|null $candidate_ids Optional pre-fetched FAQ IDs (test seam / perf).
	 * @return int[]
	 */
	public function resolve( int $product_id, ?array $candidate_ids = null ): array {
		$candidates = $candidate_ids ?? $this->all_faq_ids();

		$category_ids = $this->term_ids( $product_id, 'product_cat' );
		$tag_ids      = $this->term_ids( $product_id, 'product_tag' );

		$matched = array();
		foreach ( $candidates as $faq_id ) {
			$assignments = $this->repository->get( (int) $faq_id );
			if ( $this->matches( $assignments, $product_id, $category_ids, $tag_ids ) ) {
				$matched[] = (int) $faq_id;
			}
		}

		/**
		 * Filters the resolved FAQ IDs for a product.
		 *
		 * @param int[] $matched    Resolved FAQ IDs.
		 * @param int   $product_id Product ID.
		 */
		$matched = apply_filters( 'wsfq_resolve_faq_ids', $matched, $product_id );

		return array_values( array_unique( $matched ) );
	}

	/**
	 * Pure match check: does an assignment apply to this product?
	 *
	 * @param FaqAssignments $assignments Assignments to test.
	 * @param int            $product_id  Product or variation ID.
	 * @param int[]          $category_ids Product category term IDs.
	 * @param int[]          $tag_ids      Product tag term IDs.
	 */
	public function matches(
		FaqAssignments $assignments,
		int $product_id,
		array $category_ids,
		array $tag_ids
	): bool {
		if ( $assignments->is_global() ) {
			return true;
		}

		if ( in_array( $product_id, $assignments->get_product_ids(), true ) ) {
			return true;
		}

		if ( in_array( $product_id, $assignments->get_variation_ids(), true ) ) {
			return true;
		}

		if ( array_intersect( $assignments->get_category_ids(), $category_ids ) ) {
			return true;
		}

		if ( array_intersect( $assignments->get_tag_ids(), $tag_ids ) ) {
			return true;
		}

		return false;
	}

	/**
	 * All published FAQ post IDs.
	 *
	 * @return int[]
	 */
	private function all_faq_ids(): array {
		$query = new \WP_Query(
			array(
				'post_type'      => FaqPostType::SLUG,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		return array_map( 'intval', $query->posts );
	}

	/**
	 * Term IDs for a given object + taxonomy.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $taxonomy  Taxonomy.
	 * @return int[]
	 */
	private function term_ids( int $object_id, string $taxonomy ): array {
		$terms = get_the_terms( $object_id, $taxonomy );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return array();
		}
		return array_map( static fn( $term ): int => (int) $term->term_id, $terms );
	}
}
