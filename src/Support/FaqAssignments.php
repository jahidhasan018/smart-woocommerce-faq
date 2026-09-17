<?php
/**
 * FAQ assignment value object.
 *
 * Immutable snapshot of where a FAQ is assigned: global, direct products,
 * product categories, product tags, and variations. Backed by postmeta on the
 * FAQ post (see openspec/DECISIONS.md ADR-001).
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Support;

/**
 * FAQ assignment value object.
 */
final class FaqAssignments {

	/**
	 * Applies to all products.
	 *
	 * @var bool
	 */
	private bool $global = false;

	/**
	 * Directly assigned product IDs.
	 *
	 * @var int[]
	 */
	private array $product_ids = array();

	/**
	 * Product category term IDs.
	 *
	 * @var int[]
	 */
	private array $category_ids = array();

	/**
	 * Product tag term IDs.
	 *
	 * @var int[]
	 */
	private array $tag_ids = array();

	/**
	 * Variation IDs.
	 *
	 * @var int[]
	 */
	private array $variation_ids = array();

	/**
	 * Create from a raw array (postmeta shape).
	 *
	 * @param array $data Raw assignment data.
	 */
	public static function from_array( array $data ): self {
		$assignments = new self();

		$assignments->global        = ! empty( $data['global'] );
		$assignments->product_ids   = self::ints( $data['product_ids'] ?? array() );
		$assignments->category_ids  = self::ints( $data['category_ids'] ?? array() );
		$assignments->tag_ids       = self::ints( $data['tag_ids'] ?? array() );
		$assignments->variation_ids = self::ints( $data['variation_ids'] ?? array() );

		return $assignments;
	}

	/**
	 * Export to an array suitable for postmeta.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'global'        => $this->global,
			'product_ids'   => $this->product_ids,
			'category_ids'  => $this->category_ids,
			'tag_ids'       => $this->tag_ids,
			'variation_ids' => $this->variation_ids,
		);
	}

	/**
	 * Whether this applies to all products.
	 */
	public function is_global(): bool {
		return $this->global;
	}

	/**
	 * Directly assigned product IDs.
	 *
	 * @return int[]
	 */
	public function get_product_ids(): array {
		return $this->product_ids;
	}

	/**
	 * Product category term IDs.
	 *
	 * @return int[]
	 */
	public function get_category_ids(): array {
		return $this->category_ids;
	}

	/**
	 * Product tag term IDs.
	 *
	 * @return int[]
	 */
	public function get_tag_ids(): array {
		return $this->tag_ids;
	}

	/**
	 * Variation IDs.
	 *
	 * @return int[]
	 */
	public function get_variation_ids(): array {
		return $this->variation_ids;
	}

	/**
	 * Return a copy with the global flag set.
	 *
	 * @param bool $is_global Global flag.
	 */
	public function with_global( bool $is_global ): self {
		$clone         = clone $this;
		$clone->global = $is_global;
		return $clone;
	}

	/**
	 * Return a copy with product IDs set.
	 *
	 * @param int[] $ids Product IDs.
	 */
	public function with_product_ids( array $ids ): self {
		$clone              = clone $this;
		$clone->product_ids = self::ints( $ids );
		return $clone;
	}

	/**
	 * Normalize an array to positive ints.
	 *
	 * @param array $values Raw values.
	 * @return int[]
	 */
	private static function ints( array $values ): array {
		$ints = array();
		foreach ( $values as $value ) {
			$int = absint( $value );
			if ( $int > 0 ) {
				$ints[] = $int;
			}
		}
		return array_values( array_unique( $ints ) );
	}
}
