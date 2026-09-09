<?php
/**
 * FAQ renderer contract.
 *
 * Strategy interface — renderers produce frontend HTML for a set of FAQ posts
 * (accordion, list, etc.). Themes/plugins can register their own via the
 * `wsfq_renderer` filter.
 *
 * Hooks:
 * - `wsfq_renderer` (filter, RendererInterface $renderer, array $args) — choose renderer.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Frontend\Renderers;

/**
 * Renders FAQ posts to HTML.
 */
interface RendererInterface {

	/**
	 * Render a set of FAQ posts to HTML.
	 *
	 * @param int[] $faq_ids FAQ post IDs.
	 * @param array $args    Optional render args.
	 * @return string
	 */
	public function render( array $faq_ids, array $args = array() ): string;
}
