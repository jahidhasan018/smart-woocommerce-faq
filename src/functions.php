<?php
/**
 * Global helper functions.
 *
 * @package WSFQ
 */

declare(strict_types=1);

/**
 * Clone an FAQ post (including meta + taxonomy assignments).
 *
 * Convenience wrapper around WSFQ\Support\FaqCloner for use in templates/theme
 * code. Returns the new post ID or null on failure.
 *
 * @param int $post_id Source FAQ post ID.
 * @return int|null
 */
function wsfq_clone_faq( int $post_id ): ?int {
	$cloner = new \WSFQ\Support\FaqCloner();
	return $cloner->clone( $post_id );
}
