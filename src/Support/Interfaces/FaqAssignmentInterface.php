<?php
/**
 * FAQ assignment storage contract.
 *
 * Strategy interface — storage can be postmeta (default, see DECISIONS.md ADR-001)
 * or another backend later. Depends on FaqAssignments as the data shape.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Support\Interfaces;

use WSFQ\Support\FaqAssignments;

/**
 * Persists FAQ assignments.
 */
interface FaqAssignmentInterface {

	public const KEY_GLOBAL        = '_wsfq_global';
	public const KEY_PRODUCT_IDS   = '_wsfq_product_ids';
	public const KEY_CATEGORY_IDS  = '_wsfq_category_ids';
	public const KEY_TAG_IDS       = '_wsfq_tag_ids';
	public const KEY_VARIATION_IDS = '_wsfq_variation_ids';

	/**
	 * Read assignments for an FAQ.
	 *
	 * @param int $faq_id FAQ post ID.
	 */
	public function get( int $faq_id ): FaqAssignments;

	/**
	 * Persist assignments for an FAQ.
	 *
	 * @param int            $faq_id      FAQ post ID.
	 * @param FaqAssignments $assignments Assignments to store.
	 */
	public function save( int $faq_id, FaqAssignments $assignments ): void;

	/**
	 * Remove all assignment meta for an FAQ.
	 *
	 * @param int $faq_id FAQ post ID.
	 */
	public function delete( int $faq_id ): void;
}
