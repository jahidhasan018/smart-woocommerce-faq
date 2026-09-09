<?php
/**
 * FAQ display engine.
 *
 * Registers WooCommerce display positions and renders resolved FAQs for the
 * current product. Position enablement reads the `wsfq_display_positions`
 * option (default: all on); the settings UI (feature 08) manages it later.
 *
 * Positions: product_tab, after_add_to_cart, after_product_meta,
 * after_product_summary, after_single_product, shop_archive, cart, checkout.
 *
 * Hooks:
 * - `wsfq_display_positions` (filter, array $positions) — full position list.
 * - `wsfq_display_position_enabled` (filter, bool $enabled, string $position).
 * - `wsfq_renderer` (filter, RendererInterface $renderer, array $args).
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Frontend;

use WSFQ\Frontend\Renderers\AccordionRenderer;
use WSFQ\Frontend\Renderers\RendererInterface;
use WSFQ\Support\Interfaces\FaqResolverInterface;

/**
 * Display engine.
 */
final class DisplayEngine {

	/**
	 * Resolves FAQs for a product.
	 *
	 * @var FaqResolverInterface
	 */
	private FaqResolverInterface $resolver;

	/**
	 * Default renderer.
	 *
	 * @var RendererInterface
	 */
	private RendererInterface $renderer;

	/**
	 * Position slug => default enabled.
	 *
	 * @var array<string, bool>
	 */
	private const POSITIONS = array(
		'product_tab'           => true,
		'after_add_to_cart'     => true,
		'after_product_meta'    => true,
		'after_product_summary' => true,
		'after_single_product'  => true,
		'shop_archive'          => true,
		'cart'                  => true,
		'checkout'              => true,
	);

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
	 * Register the enabled display positions.
	 */
	public function register(): void {
		$enabled = $this->enabled_positions();

		if ( isset( $enabled['product_tab'] ) ) {
			add_filter( 'woocommerce_product_tabs', array( $this, 'add_product_tab' ) );
		}

		if ( isset( $enabled['after_add_to_cart'] ) ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'render_after_add_to_cart' ), 31 );
		}

		if ( isset( $enabled['after_product_meta'] ) ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'render_after_product_meta' ), 41 );
		}

		if ( isset( $enabled['after_product_summary'] ) ) {
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'render_after_product_summary' ), 20 );
		}

		if ( isset( $enabled['after_single_product'] ) ) {
			add_action( 'woocommerce_after_single_product', array( $this, 'render_after_single_product' ), 10 );
		}

		if ( isset( $enabled['shop_archive'] ) ) {
			add_action( 'woocommerce_archive_description', array( $this, 'render_shop_archive' ), 10 );
		}

		if ( isset( $enabled['cart'] ) ) {
			add_action( 'woocommerce_cart_collaterals', array( $this, 'render_cart' ), 10 );
		}

		if ( isset( $enabled['checkout'] ) ) {
			add_action( 'woocommerce_after_checkout_form', array( $this, 'render_checkout' ), 10 );
		}
	}

	/**
	 * Add the FAQ tab to product tabs.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public function add_product_tab( array $tabs ): array {
		$product_id = $this->current_product_id();
		if ( ! $product_id ) {
			return $tabs;
		}

		$tabs['wsfq_faq_tab'] = array(
			'title'    => apply_filters( 'wsfq_tab_title', __( 'FAQs', 'smart-woocommerce-faq' ) ),
			'priority' => 50,
			'callback' => array( $this, 'render_product_tab' ),
		);

		return $tabs;
	}

	/**
	 * Enqueue the built frontend assets (JS + CSS).
	 */
	public function enqueue_assets(): void {
		$asset_file = WSFQ_DIR . 'assets/build/frontend.asset.php';
		if ( ! is_readable( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		$deps  = isset( $asset['dependencies'] ) && is_array( $asset['dependencies'] ) ? $asset['dependencies'] : array();
		$ver   = isset( $asset['version'] ) ? $asset['version'] : WSFQ_VERSION;

		wp_register_script(
			'wsfq-frontend',
			esc_url( WSFQ_URL . 'assets/build/frontend.js' ),
			$deps,
			$ver,
			true
		);
		wp_register_style(
			'wsfq-frontend',
			esc_url( WSFQ_URL . 'assets/build/frontend.css' ),
			array(),
			$ver
		);

		wp_enqueue_script( 'wsfq-frontend' );
		wp_enqueue_style( 'wsfq-frontend' );
	}

	/**
	 * Render the product tab content.
	 */
	public function render_product_tab(): void {
		$product_id = $this->current_product_id();
		if ( $product_id ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render() escapes internally.
			echo $this->render_for_product( $product_id, array( 'position' => 'product_tab' ) );
		}
	}

	/**
	 * Render after add-to-cart button.
	 */
	public function render_after_add_to_cart(): void {
		$product_id = $this->current_product_id();
		if ( $product_id ) {
			echo $this->render_for_product( $product_id, array( 'position' => 'after_add_to_cart' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render after product meta.
	 */
	public function render_after_product_meta(): void {
		$product_id = $this->current_product_id();
		if ( $product_id ) {
			echo $this->render_for_product( $product_id, array( 'position' => 'after_product_meta' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render after product summary.
	 */
	public function render_after_product_summary(): void {
		$product_id = $this->current_product_id();
		if ( $product_id ) {
			echo $this->render_for_product( $product_id, array( 'position' => 'after_product_summary' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render after the single product.
	 */
	public function render_after_single_product(): void {
		$product_id = $this->current_product_id();
		if ( $product_id ) {
			echo $this->render_for_product( $product_id, array( 'position' => 'after_single_product' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render on shop/archive pages.
	 */
	public function render_shop_archive(): void {
		$product_id = $this->current_product_id();
		if ( $product_id ) {
			echo $this->render_for_product( $product_id, array( 'position' => 'shop_archive' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render on the cart page.
	 */
	public function render_cart(): void {
		$cart = WC()->cart;
		if ( ! $cart ) {
			return;
		}

		$faq_ids = array();
		foreach ( $cart->get_cart() as $cart_item ) {
			$faq_ids = array_merge( $faq_ids, $this->resolver->resolve( (int) $cart_item['product_id'] ) );
		}

		$faq_ids = array_values( array_unique( array_map( 'intval', $faq_ids ) ) );

		if ( $faq_ids ) {
			echo $this->render( $faq_ids, array( 'position' => 'cart' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render on the checkout page.
	 */
	public function render_checkout(): void {
		$cart = WC()->cart;
		if ( ! $cart ) {
			return;
		}

		$faq_ids = array();
		foreach ( $cart->get_cart() as $cart_item ) {
			$faq_ids = array_merge( $faq_ids, $this->resolver->resolve( (int) $cart_item['product_id'] ) );
		}

		$faq_ids = array_values( array_unique( array_map( 'intval', $faq_ids ) ) );

		if ( $faq_ids ) {
			echo $this->render( $faq_ids, array( 'position' => 'checkout' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render resolved FAQs for a product.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $args       Render args.
	 * @return string
	 */
	public function render_for_product( int $product_id, array $args = array() ): string {
		$faq_ids = $this->resolver->resolve( $product_id );
		return $this->render( $faq_ids, $args );
	}

	/**
	 * Render via the active renderer.
	 *
	 * @param int[] $faq_ids FAQ IDs.
	 * @param array $args    Render args.
	 * @return string
	 */
	public function render( array $faq_ids, array $args = array() ): string {
		// Expand-all control defaults to on, overridable per call or via filter.
		if ( ! array_key_exists( 'expand_all', $args ) ) {
			$args['expand_all'] = (bool) get_option( 'wsfq_expand_all', true );
		}

		$renderer = apply_filters( 'wsfq_renderer', $this->renderer, $args );
		if ( ! $renderer instanceof RendererInterface ) {
			$renderer = $this->renderer;
		}
		return $renderer->render( $faq_ids, $args );
	}

	/**
	 * Enabled positions per the option (all on by default).
	 *
	 * @return array<string, bool>
	 */
	private function enabled_positions(): array {
		$positions = get_option( 'wsfq_display_positions', self::POSITIONS );

		/**
		 * Filters which display positions are enabled.
		 *
		 * @param array<string, bool> $positions Position slug => enabled.
		 */
		$positions = apply_filters( 'wsfq_display_positions', $positions );

		return array_filter( $positions, 'boolval' );
	}

	/**
	 * Current product ID from the global product / cart context.
	 */
	private function current_product_id(): ?int {
		$product_id = (int) get_the_ID();

		global $product;
		if ( $product instanceof \WC_Product ) {
			$product_id = $product->get_id();
		}

		return $product_id > 0 ? $product_id : null;
	}
}
