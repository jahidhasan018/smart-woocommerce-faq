<?php
/**
 * Accordion FAQ renderer.
 *
 * Emits accessible, semantic accordion markup with `wsfq-` prefixed BEM-style
 * classes: toggle buttons with aria-expanded/aria-controls, and an optional
 * expand-all / collapse-all control. Styles come from the design system
 * (feature 05); the JS controller wires keyboard + toggle behavior.
 *
 * Hooks:
 * - `wsfq_before_render` (filter, string $output, int[] $faq_ids) — wrap/prefix.
 * - `wsfq_after_render` (filter, string $output, int[] $faq_ids) — wrap/suffix.
 * - `wsfq_faq_item_title` (filter, string $title, int $faq_id) — per-item title.
 * - `wsfq_faq_item_content` (filter, string $content, int $faq_id) — per-item content.
 * - `wsfq_accordion_expand_all` (filter, bool $enabled, array $args) — toggle the control.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Frontend\Renderers;

use WSFQ\Admin\SettingsService;

/**
 * Default accordion renderer.
 */
final class AccordionRenderer implements RendererInterface {

	/**
	 * Number of accordions rendered this request.
	 *
	 * Used to give every accordion a unique DOM id so the expand-all control
	 * (and any duplicate accordion on the page) targets the right one.
	 *
	 * @var int
	 */
	private static int $instances = 0;

	/**
	 * Settings source.
	 *
	 * @var SettingsService
	 */
	private SettingsService $settings;

	/**
	 * Constructor.
	 *
	 * @param SettingsService $settings Settings source.
	 */
	public function __construct( SettingsService $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Render FAQ posts as an accessible accordion list.
	 *
	 * @param int[] $faq_ids FAQ post IDs.
	 * @param array $args    Optional render args (expand_all bool, etc.).
	 * @return string
	 */
	public function render( array $faq_ids, array $args = array() ): string {
		if ( empty( $faq_ids ) ) {
			return '';
		}

		$items = array();
		foreach ( $faq_ids as $faq_id ) {
			$post = get_post( (int) $faq_id );
			if ( ! $post ) {
				continue;
			}

			$faq_id    = (int) $faq_id;
			$answer_id = 'wsfq-answer-' . $faq_id;

			$title = (string) $post->post_title;
			$title = apply_filters( 'wsfq_faq_item_title', $title, $faq_id );

			$content = (string) $post->post_content;
			$content = apply_filters( 'wsfq_faq_item_content', $content, $faq_id );

			$items[] = sprintf(
				'<div class="wsfq-faq wsfq-faq-%1$d">'
					. '<h3 class="wsfq-question">'
					. '<button type="button" class="wsfq-toggle" aria-expanded="false" aria-controls="%2$s" data-wsfq-toggle>'
					. '<span class="wsfq-toggle__label">%3$s</span>'
					. '</button>'
					. '</h3>'
					. '<div id="%2$s" class="wsfq-answer" hidden>%4$s</div>'
					. '</div>',
				$faq_id,
				esc_attr( $answer_id ),
				esc_html( $title ),
				wp_kses_post( $content )
			);
		}

		if ( empty( $items ) ) {
			return '';
		}

		$accordion_id = 'wsfq-accordion-' . ( ++self::$instances );

		$output = sprintf(
			'<div class="wsfq-accordion" id="%1$s" data-wsfq-accordion>%2$s</div>',
			esc_attr( $accordion_id ),
			implode( '', $items )
		);

		if ( $this->expand_all_enabled( $args ) ) {
			$output = sprintf(
				'<div class="wsfq-expand-all">'
					. '<button type="button" class="wsfq-expand-all-toggle" data-wsfq-expand-all aria-expanded="false" aria-controls="%1$s">'
					. '<span class="wsfq-expand-all-toggle__label wsfq-expand-all-toggle__label--expand">%2$s</span>'
					. '<span class="wsfq-expand-all-toggle__label wsfq-expand-all-toggle__label--collapse">%3$s</span>'
					. '</button>'
					. '</div>%4$s',
				esc_attr( $accordion_id ),
				esc_html__( 'Expand all', 'smart-woocommerce-faq' ),
				esc_html__( 'Collapse all', 'smart-woocommerce-faq' ),
				$output
			);
		}

		$output = apply_filters( 'wsfq_before_render', $output, array_map( 'intval', $faq_ids ) );
		$output = apply_filters( 'wsfq_after_render', $output, array_map( 'intval', $faq_ids ) );

		return $output;
	}

	/**
	 * Whether the expand-all control should render.
	 *
	 * The renderer is used directly by the shortcodes and the block, which do
	 * not go through the display engine, so it resolves the saved setting itself
	 * when the caller does not pass an explicit value.
	 *
	 * @param array $args Render args.
	 * @return bool
	 */
	private function expand_all_enabled( array $args ): bool {
		if ( array_key_exists( 'expand_all', $args ) ) {
			$enabled = (bool) $args['expand_all'];
		} else {
			$settings = $this->settings->get_all();
			$enabled  = isset( $settings['display']['expand_all'] )
				? (bool) $settings['display']['expand_all']
				: true;
		}

		/**
		 * Filters whether the expand-all / collapse-all control renders.
		 *
		 * @param bool  $enabled Whether to show the control.
		 * @param array $args    Render args.
		 */
		return (bool) apply_filters( 'wsfq_accordion_expand_all', $enabled, $args );
	}
}
