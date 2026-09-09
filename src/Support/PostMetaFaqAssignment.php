<?php
/**
 * Postmeta-backed FAQ assignment repository.
 *
 * Stores FaqAssignments as postmeta on the FAQ post. Uses set_post_meta per key
 * so storage matches WP's object-cache-aware conventions (DECISIONS.md ADR-001).
 *
 * Hooks:
 * - `wsfq_faq_assignments_loaded` (filter, array $data, int $faq_id) — before save.
 * - `wsfq_faq_assignments_saved` (action, int $faq_id, FaqAssignments $assignments).
 * - `wsfq_faq_assignments_deleted` (action, int $faq_id).
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Support;

use WSFQ\Support\Interfaces\FaqAssignmentInterface;

/**
 * Postmeta-backed assignment repository.
 */
final class PostMetaFaqAssignment implements FaqAssignmentInterface {

	/**
	 * Read assignments for an FAQ.
	 *
	 * @param int $faq_id FAQ post ID.
	 */
	public function get( int $faq_id ): FaqAssignments {
		$data = array(
			'global'        => (bool) get_post_meta( $faq_id, self::KEY_GLOBAL, true ),
			'product_ids'   => (array) get_post_meta( $faq_id, self::KEY_PRODUCT_IDS, true ),
			'category_ids'  => (array) get_post_meta( $faq_id, self::KEY_CATEGORY_IDS, true ),
			'tag_ids'       => (array) get_post_meta( $faq_id, self::KEY_TAG_IDS, true ),
			'variation_ids' => (array) get_post_meta( $faq_id, self::KEY_VARIATION_IDS, true ),
		);

		/**
		 * Filters raw assignment data as it is read.
		 *
		 * @param array $data   Raw assignment data.
		 * @param int   $faq_id FAQ post ID.
		 */
		$data = apply_filters( 'wsfq_faq_assignments_loaded', $data, $faq_id );

		return FaqAssignments::from_array( $data );
	}

	/**
	 * Persist assignments for an FAQ.
	 *
	 * @param int            $faq_id      FAQ post ID.
	 * @param FaqAssignments $assignments Assignments to store.
	 */
	public function save( int $faq_id, FaqAssignments $assignments ): void {
		update_post_meta( $faq_id, self::KEY_GLOBAL, $assignments->is_global() ? 1 : 0 );
		update_post_meta( $faq_id, self::KEY_PRODUCT_IDS, $assignments->get_product_ids() );
		update_post_meta( $faq_id, self::KEY_CATEGORY_IDS, $assignments->get_category_ids() );
		update_post_meta( $faq_id, self::KEY_TAG_IDS, $assignments->get_tag_ids() );
		update_post_meta( $faq_id, self::KEY_VARIATION_IDS, $assignments->get_variation_ids() );

		/**
		 * Fires after FAQ assignments are saved.
		 *
		 * @param int            $faq_id      FAQ post ID.
		 * @param FaqAssignments $assignments Stored assignments.
		 */
		do_action( 'wsfq_faq_assignments_saved', $faq_id, $assignments );
	}

	/**
	 * Remove all assignment meta for an FAQ.
	 *
	 * @param int $faq_id FAQ post ID.
	 */
	public function delete( int $faq_id ): void {
		delete_post_meta( $faq_id, self::KEY_GLOBAL );
		delete_post_meta( $faq_id, self::KEY_PRODUCT_IDS );
		delete_post_meta( $faq_id, self::KEY_CATEGORY_IDS );
		delete_post_meta( $faq_id, self::KEY_TAG_IDS );
		delete_post_meta( $faq_id, self::KEY_VARIATION_IDS );

		/**
		 * Fires after FAQ assignments are deleted.
		 *
		 * @param int $faq_id FAQ post ID.
		 */
		do_action( 'wsfq_faq_assignments_deleted', $faq_id );
	}
}
