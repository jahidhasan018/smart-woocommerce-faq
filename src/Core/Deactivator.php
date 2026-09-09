<?php
/**
 * Plugin deactivator.
 *
 * Runs on deactivation: flushes rewrite rules. Data is preserved — removal only
 * happens on uninstall with the opt-in toggle (default off).
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Core;

/**
 * Deactivation handler.
 */
final class Deactivator {

	/**
	 * Flush rewrite rules.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
