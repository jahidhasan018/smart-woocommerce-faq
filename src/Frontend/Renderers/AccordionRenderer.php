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

/**
 * Default accordion renderer.
 */
final class AccordionRenderer implements RendererInterface {

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
				'<div class="wsfq-faq wsfq-faq-%d">'
					. '<h3 class="wsfq-question">'
					. '<button type="button" class="wsfq-toggle" aria-expanded="false" aria-controls="%s" data-wsfq-toggle>%s</button>'
					. '</h3>'
					. '<div id="%s" class="wsfq-answer" hidden>%s</div>'
					. '</div>',
				$faq_id,
				esc_attr( $answer_id ),
				esc_html( $title ),
				esc_attr( $answer_id ),
				wp_kses_post( $content )
			);
		}

		if ( empty( $items ) ) {
			return '';
		}

		$output = sprintf( '<div class="wsfq-accordion" data-wsfq-accordion>%s</div>', implode( '', $items ) );

		if ( $this->expand_all_enabled( $args ) ) {
			$control = sprintf(
				'<div class="wsfq-expand-all"><button type="button" class="wsfq-expand-all-toggle" data-wsfq-expand-all aria-expanded="false">%s</button></div>',
				esc_html__( 'Expand all', 'smart-woocommerce-faq' )
			);
			$output  = $control . $output;
		}

		$output = apply_filters( 'wsfq_before_render', $output, array_map( 'intval', $faq_ids ) );
		$output = apply_filters( 'wsfq_after_render', $output, array_map( 'intval', $faq_ids ) );

		return $output;
	}

	/**
	 * Whether the expand-all control should render.
	 *
	 * @param array $args Render args.
	 * @return bool
	 */
	private function expand_all_enabled( array $args ): bool {
		$enabled = ! empty( $args['expand_all'] );

		/**
		 * Filters whether the expand-all / collapse-all control renders.
		 *
		 * @param bool  $enabled Whether to show the control.
		 * @param array $args    Render args.
		 */
		return (bool) apply_filters( 'wsfq_accordion_expand_all', $enabled, $args );
	}
}
