<?php
/**
 * FAQ resolver contract.
 *
 * Strategy interface — resolves which FAQs apply to a product. The default
 * implementation reads postmeta assignments; alternative resolvers (cache-backed,
 * etc.) can be swapped in.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Support\Interfaces;

/**
 * Resolves applicable FAQ IDs for a product.
 */
interface FaqResolverInterface {

	/**
	 * Resolve the FAQ IDs that apply to a product.
	 *
	 * @param int        $product_id    Product or variation ID.
	 * @param int[]|null $candidate_ids Optional pre-fetched FAQ IDs.
	 * @return int[]
	 */
	public function resolve( int $product_id, ?array $candidate_ids = null ): array;
}
