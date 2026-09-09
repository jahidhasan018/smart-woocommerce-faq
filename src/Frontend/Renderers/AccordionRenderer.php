<?php
/**
 * Accordion FAQ renderer.
 *
 * Emits semantic, accessible accordion markup with `wsfq-` prefixed BEM-style
 * classes. Styling + aria state toggles are refined in feature 05 (Design
 * System); this ships the structural HTML + hooks.
 *
 * Hooks:
 * - `wsfq_before_render` (filter, string $output, int[] $faq_ids) — wrap/prefix.
 * - `wsfq_after_render` (filter, string $output, int[] $faq_ids) — wrap/suffix.
 * - `wsfq_faq_item_title` (filter, string $title, int $faq_id) — per-item title.
 * - `wsfq_faq_item_content` (filter, string $content, int $faq_id) — per-item content.
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
	 * Render FAQ posts as an accordion list.
	 *
	 * @param int[] $faq_ids FAQ post IDs.
	 * @param array $args    Optional render args.
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

			$title = (string) $post->post_title;
			$title = apply_filters( 'wsfq_faq_item_title', $title, (int) $faq_id );

			$content = (string) $post->post_content;
			$content = apply_filters( 'wsfq_faq_item_content', $content, (int) $faq_id );

			$items[] = sprintf(
				'<div class="wsfq-faq wsfq-faq-%d"><h3 class="wsfq-question">%s</h3><div class="wsfq-answer">%s</div></div>',
				(int) $faq_id,
				esc_html( $title ),
				wp_kses_post( $content )
			);
		}

		if ( empty( $items ) ) {
			return '';
		}

		$output = sprintf(
			'<div class="wsfq-accordion" data-wsfq-accordion>%s</div>',
			implode( '', $items )
		);

		$output = apply_filters( 'wsfq_before_render', $output, array_map( 'intval', $faq_ids ) );
		$output = apply_filters( 'wsfq_after_render', $output, array_map( 'intval', $faq_ids ) );

		return $output;
	}
}
