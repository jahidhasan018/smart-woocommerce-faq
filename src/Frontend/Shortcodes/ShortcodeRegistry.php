<?php
/**
 * FAQ shortcode registry.
 *
 * Registers the wsfq_faq_* shortcodes and renders through the display engine's
 * renderer. Search shortcode (wsfq_faq_search) is deferred to feature 13/14.
 *
 * Shortcodes: wsfq_faq_all, wsfq_faq_category, wsfq_faq_ids, wsfq_faq_product,
 * wsfq_faq_current, wsfq_faq_group.
 *
 * Hooks:
 * - `wsfq_shortcode_faq_ids` (filter, int[] $ids, array $atts) — filter resolved ids.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Frontend\Shortcodes;

use WSFQ\Frontend\Renderers\RendererInterface;
use WSFQ\PostTypes\FaqPostType;
use WSFQ\Support\Interfaces\FaqResolverInterface;

/**
 * Shortcode registry.
 */
final class ShortcodeRegistry {

	/**
	 * FAQ resolver.
	 *
	 * @var FaqResolverInterface
	 */
	private FaqResolverInterface $resolver;

	/**
	 * Renderer.
	 *
	 * @var RendererInterface
	 */
	private RendererInterface $renderer;

	/**
	 * Constructor.
	 *
	 * @param FaqResolverInterface $resolver FAQ resolver.
	 * @param RendererInterface    $renderer Renderer.
	 */
	public function __construct( FaqResolverInterface $resolver, RendererInterface $renderer ) {
		$this->resolver = $resolver;
		$this->renderer = $renderer;
	}

	/**
	 * Register all shortcodes.
	 */
	public function register(): void {
		add_shortcode( 'wsfq_faq_all', array( $this, 'render_all' ) );
		add_shortcode( 'wsfq_faq_category', array( $this, 'render_category' ) );
		add_shortcode( 'wsfq_faq_ids', array( $this, 'render_ids' ) );
		add_shortcode( 'wsfq_faq_product', array( $this, 'render_product' ) );
		add_shortcode( 'wsfq_faq_current', array( $this, 'render_current' ) );
		add_shortcode( 'wsfq_faq_group', array( $this, 'render_group' ) );
	}

	/**
	 * [wsfq_faq_all] — every FAQ.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_all( array $atts ): string {
		$faq_ids = $this->query_faq_ids(
			array(
				'post_type'      => FaqPostType::SLUG,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		return $this->render_ids( array_merge( $atts, array( 'ids' => implode( ',', $faq_ids ) ) ) );
	}

	/**
	 * [wsfq_faq_category id="x"] — FAQs in a category term.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_category( array $atts ): string {
		$term_id = absint( $atts['id'] ?? 0 );
		if ( ! $term_id ) {
			return '';
		}

		$faq_ids = $this->query_faq_ids(
			array(
				'post_type'      => FaqPostType::SLUG,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => 'wsfq_faq_category',
						'field'    => 'term_id',
						'terms'    => array( $term_id ),
					),
				),
			)
		);

		return $this->render_ids( array_merge( $atts, array( 'ids' => implode( ',', $faq_ids ) ) ) );
	}

	/**
	 * [wsfq_faq_ids ids="1,2,3"] — specific FAQs.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_ids( array $atts ): string {
		$ids_raw = (string) ( $atts['ids'] ?? '' );
		$ids     = array_values( array_filter( array_map( 'absint', explode( ',', $ids_raw ) ) ) );

		if ( empty( $ids ) ) {
			return '';
		}

		/**
		 * Filters the FAQ ids a shortcode will render.
		 *
		 * @param int[] $ids  FAQ post IDs.
		 * @param array $atts Shortcode attributes.
		 */
		$ids = apply_filters( 'wsfq_shortcode_faq_ids', $ids, $atts );

		return $this->renderer->render(
			$ids,
			array(
				'source' => 'shortcode',
				'atts'   => $atts,
			)
		);
	}

	/**
	 * [wsfq_faq_product id="x"] — FAQs for a specific product.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_product( array $atts ): string {
		$product_id = absint( $atts['id'] ?? 0 );
		if ( ! $product_id ) {
			return '';
		}

		$faq_ids = $this->resolver->resolve( $product_id );

		return $this->render_ids( array_merge( $atts, array( 'ids' => implode( ',', $faq_ids ) ) ) );
	}

	/**
	 * [wsfq_faq_current] — FAQs for the current product.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_current( array $atts ): string {
		$product_id = (int) get_the_ID();
		if ( ! $product_id ) {
			return '';
		}

		$faq_ids = $this->resolver->resolve( $product_id );

		return $this->render_ids( array_merge( $atts, array( 'ids' => implode( ',', $faq_ids ) ) ) );
	}

	/**
	 * [wsfq_faq_group id="x"] — FAQs in a group term.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_group( array $atts ): string {
		$term_id = absint( $atts['id'] ?? 0 );
		if ( ! $term_id ) {
			return '';
		}

		$faq_ids = $this->query_faq_ids(
			array(
				'post_type'      => FaqPostType::SLUG,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => 'wsfq_faq_group',
						'field'    => 'term_id',
						'terms'    => array( $term_id ),
					),
				),
			)
		);

		return $this->render_ids( array_merge( $atts, array( 'ids' => implode( ',', $faq_ids ) ) ) );
	}

	/**
	 * Query FAQ post IDs.
	 *
	 * @param array $query_args Query args (get_posts-compatible).
	 * @return int[]
	 */
	private function query_faq_ids( array $query_args ): array {
		$posts = get_posts( $query_args );

		$ids = array();
		foreach ( $posts as $post ) {
			$ids[] = is_object( $post ) ? (int) $post->ID : (int) $post;
		}

		return array_values( array_unique( $ids ) );
	}
}
