<?php
/**
 * Schema output integration.
 *
 * Emits FAQPage JSON-LD on product pages (resolved FAQs) and standalone FAQ
 * pages. Hooks wp_head.
 *
 * Hooks:
 * - `wsfq_schema_output` (filter, bool $enabled, array $context) — toggle output.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Frontend;

use WSFQ\PostTypes\FaqPostType;
use WSFQ\Support\Interfaces\FaqResolverInterface;

/**
 * Outputs FAQ schema on the frontend.
 */
final class SchemaOutput {

	/**
	 * FAQ resolver.
	 *
	 * @var FaqResolverInterface
	 */
	private FaqResolverInterface $resolver;

	/**
	 * Schema generator.
	 *
	 * @var SchemaGenerator
	 */
	private SchemaGenerator $generator;

	/**
	 * Constructor.
	 *
	 * @param FaqResolverInterface $resolver  FAQ resolver.
	 * @param SchemaGenerator      $generator Schema generator.
	 */
	public function __construct( FaqResolverInterface $resolver, SchemaGenerator $generator ) {
		$this->resolver  = $resolver;
		$this->generator = $generator;
	}

	/**
	 * Hook schema output into wp_head.
	 */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'maybe_output' ), 20 );
	}

	/**
	 * Emit schema when relevant.
	 */
	public function maybe_output(): void {
		$faq_ids = array();

		if ( is_product() ) {
			$product_id = $this->current_product_id();
			if ( $product_id ) {
				$faq_ids = $this->resolver->resolve( $product_id );
			}
		} elseif ( is_singular( FaqPostType::SLUG ) ) {
			$faq_ids = array( (int) get_queried_object_id() );
		} else {
			return;
		}

		$faq_ids = array_values( array_unique( array_map( 'intval', $faq_ids ) ) );

		/**
		 * Filters whether schema output is emitted.
		 *
		 * @param bool  $enabled Whether to emit schema.
		 * @param array $context Context (product/page).
		 */
		$enabled = apply_filters( 'wsfq_schema_output', true, array() );
		if ( ! $enabled ) {
			return;
		}

		echo $this->generator->render( $faq_ids ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-LD script, WP-encoded.
	}

	/**
	 * Current product ID.
	 */
	private function current_product_id(): ?int {
		global $product;
		if ( $product instanceof \WC_Product ) {
			return $product->get_id();
		}
		$post_id = (int) get_the_ID();
		return $post_id > 0 ? $post_id : null;
	}
}
