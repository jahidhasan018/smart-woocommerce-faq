<?php
/**
 * FAQ block registration + render.
 *
 * Registers the wsfq/faq Gutenberg block (Prebuilt mode) as a dynamic block:
 * the editor picks FAQ IDs from the library, and the server renders them via
 * the display engine's renderer. Custom (inline) mode is deferred.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Frontend\Blocks;

use WSFQ\Frontend\Renderers\RendererInterface;

/**
 * FAQ block.
 */
final class FaqBlock {

	public const NAME = 'wsfq/faq';

	/**
	 * Renderer.
	 *
	 * @var RendererInterface
	 */
	private RendererInterface $renderer;

	/**
	 * Constructor.
	 *
	 * @param RendererInterface $renderer Renderer.
	 */
	public function __construct( RendererInterface $renderer ) {
		$this->renderer = $renderer;
	}

	/**
	 * Register the block (hooked on init).
	 */
	public function register(): void {
		if ( class_exists( '\WP_Block_Type_Registry' )
			&& \WP_Block_Type_Registry::get_instance()->is_registered( self::NAME ) ) {
			return;
		}

		$block_path = WSFQ_DIR . 'blocks/faq/block.json';
		if ( ! is_readable( $block_path ) ) {
			return;
		}

		$this->register_editor_script();

		register_block_type(
			$block_path,
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Server-render the block.
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content    Saved content (unused for dynamic blocks).
	 * @param \WP_Block $block     Block instance.
	 * @return string
	 */
	public function render( array $attributes, string $content, \WP_Block $block ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$faq_ids = array();
		if ( isset( $attributes['faqIds'] ) && is_array( $attributes['faqIds'] ) ) {
			foreach ( $attributes['faqIds'] as $id ) {
				$int = absint( $id );
				if ( $int > 0 ) {
					$faq_ids[] = $int;
				}
			}
		}

		if ( empty( $faq_ids ) ) {
			return '';
		}

		return $this->renderer->render(
			$faq_ids,
			array(
				'source'     => 'block',
				'attributes' => $attributes,
			)
		);
	}

	/**
	 * Register the editor script for the block.
	 */
	private function register_editor_script(): void {
		$asset_file = WSFQ_DIR . 'assets/build/faq-block.asset.php';
		if ( ! is_readable( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		$deps  = isset( $asset['dependencies'] ) && is_array( $asset['dependencies'] ) ? $asset['dependencies'] : array();
		$ver   = isset( $asset['version'] ) ? $asset['version'] : WSFQ_VERSION;

		wp_register_script(
			'wsfq-faq-editor',
			esc_url( WSFQ_URL . 'assets/build/faq-block.js' ),
			$deps,
			$ver,
			true
		);
	}
}
