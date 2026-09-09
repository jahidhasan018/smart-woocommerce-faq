<?php
/**
 * Versioned migration runner.
 *
 * Compares the stored `wsfq_db_version` option against WSFQ_VERSION and runs any
 * registered migrations that haven't been applied yet, in ascending version order.
 * Exists from day one so the pattern is established before real data-model changes
 * arrive (see plan Phase 9).
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Core;

/**
 * Applies versioned migrations.
 */
final class Upgrader {

	/**
	 * Registered migrations: target version => callable.
	 *
	 * @var array<string, callable>
	 */
	private array $migrations;

	/**
	 * Constructor.
	 *
	 * @param array<string, callable>|null $migrations Migration map (test seam; defaults to none).
	 */
	public function __construct( ?array $migrations = null ) {
		$this->migrations = $migrations ?? array();
	}

	/**
	 * Run any pending migrations, then sync the stored version.
	 */
	public function maybe_upgrade(): void {
		$current = (string) get_option( 'wsfq_db_version', '0.0.0' );

		// Already current — nothing to do.
		if ( version_compare( $current, WSFQ_VERSION, '>=' ) ) {
			return;
		}

		// Run every registered migration that's newer than the stored version.
		ksort( $this->migrations );
		foreach ( $this->migrations as $version => $migration ) {
			if ( version_compare( $current, (string) $version, '<' ) ) {
				$migration();
			}
		}

		update_option( 'wsfq_db_version', WSFQ_VERSION );
	}
}
