<?php
/**
 * FAQ cloner.
 *
 * Copies an FAQ post plus its postmeta and taxonomy assignments. Fires a
 * documented hook so cloning can be extended or suppressed.
 *
 * Hooks:
 * - `wsfq_faq_cloned` (action, int $new_id, int $source_id) — after a clone.
 * - `wsfq_clone_faq_title` (filter, string $title, int $source_id) — customize the cloned title.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Support;

use WSFQ\PostTypes\FaqPostType;

/**
 * Duplicates FAQ posts.
 */
final class FaqCloner {

	/**
	 * Clone an FAQ post with its meta and taxonomy assignments.
	 *
	 * @param int $post_id Source FAQ post ID.
	 * @return int|null New post ID, or null on failure / non-FAQ.
	 */
	public function clone( int $post_id ): ?int {
		$source = get_post( $post_id );
		if ( ! $source || FaqPostType::SLUG !== $source->post_type ) {
			return null;
		}

		$title = apply_filters( 'wsfq_clone_faq_title', $source->post_title . ' (copy)', $post_id );

		$new_id = wp_insert_post(
			array(
				'post_type'    => FaqPostType::SLUG,
				'post_title'   => $title,
				'post_content' => $source->post_content,
				'post_status'  => 'draft',
			)
		);

		if ( ! $new_id ) {
			return null;
		}

		$new_id = (int) $new_id;

		// Copy postmeta (assignment meta included).
		foreach ( get_post_meta( $post_id ) as $key => $values ) {
			foreach ( $values as $value ) {
				add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
			}
		}

		// Copy taxonomy assignments.
		$taxonomies = get_object_taxonomies( FaqPostType::SLUG );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( $terms && ! is_wp_error( $terms ) ) {
				wp_set_object_terms( $new_id, $terms, $taxonomy );
			}
		}

		/**
		 * Fires after an FAQ has been cloned.
		 *
		 * @param int $new_id    New (cloned) post ID.
		 * @param int $post_id   Source post ID.
		 */
		do_action( 'wsfq_faq_cloned', $new_id, $post_id );

		return $new_id;
	}
}
