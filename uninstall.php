<?php
/**
 * Uninstall handler.
 *
 * Data is preserved by default. Opt-in cleanup is governed by the "Remove all
 * data on uninstall" setting (default off) — wired properly in Phase 2. Until
 * then this is intentionally a no-op so client sites never silently lose data.
 *
 * @package WSFQ
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Data is preserved by default. Opt-in cleanup ships with Phase 2.
