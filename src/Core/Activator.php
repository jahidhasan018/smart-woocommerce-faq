<?php
/**
 * Plugin activator.
 *
 * Runs on activation: registers the CPT + taxonomies so rewrite rules are known,
 * then flushes them once. No heavy work — activation hooks are fragile.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Core;

use WSFQ\PostTypes\FaqPostType;
use WSFQ\Taxonomies\FaqCategory;
use WSFQ\Taxonomies\FaqGroup;

/**
 * Activation handler.
 */
final class Activator {

	/**
	 * Register content types and flush rewrite rules.
	 */
	public static function activate(): void {
		FaqPostType::register();
		FaqCategory::register();
		FaqGroup::register();

		flush_rewrite_rules();
	}
}
