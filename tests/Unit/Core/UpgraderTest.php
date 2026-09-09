<?php
/**
 * Upgrader unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Core;

use Brain\Monkey\Functions;
use WSFQ\Core\Upgrader;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for versioned migrations.
 */
final class UpgraderTest extends TestCase {

	/**
	 * With no stored version and an empty migration map, db version becomes current.
	 */
	public function test_sets_db_version_to_current_when_empty(): void {
		$updated = array();

		Functions\expect( 'get_option' )
			->once()
			->with( 'wsfq_db_version', '0.0.0' )
			->andReturn( '0.0.0' );

		Functions\expect( 'update_option' )
			->once()
			->with( 'wsfq_db_version', WSFQ_VERSION )
			->andReturnUsing(
				static function ( $key, $value ) use ( &$updated ): bool {
					$updated[] = array( $key, $value );
					return true;
				}
			);

		( new Upgrader() )->maybe_upgrade();

		$this->assertSame( array( 'wsfq_db_version', WSFQ_VERSION ), $updated[0] ?? null );
	}

	/**
	 * When already current, no migration runs and option is untouched.
	 */
	public function test_noop_when_current(): void {
		$updated = array();

		Functions\expect( 'get_option' )
			->once()
			->with( 'wsfq_db_version', '0.0.0' )
			->andReturn( WSFQ_VERSION );

		Functions\expect( 'update_option' )
			->never()
			->andReturnUsing(
				static function ( $key, $value ) use ( &$updated ): bool {
					$updated[] = array( $key, $value );
					return true;
				}
			);

		( new Upgrader() )->maybe_upgrade();

		$this->assertSame( array(), $updated );
	}

	/**
	 * A migration registered for a newer version runs and bumps db version.
	 */
	public function test_runs_migrations_in_order(): void {
		$ran = array();

		Functions\expect( 'get_option' )
			->once()
			->with( 'wsfq_db_version', '0.0.0' )
			->andReturn( '0.0.0' );

		Functions\expect( 'update_option' )
			->once()
			->with( 'wsfq_db_version', WSFQ_VERSION );

		$upgrader = new Upgrader(
			array(
				'0.2.0' => static function () use ( &$ran ): void {
					$ran[] = '0.2.0';
				},
			)
		);

		$upgrader->maybe_upgrade();

		$this->assertSame( array( '0.2.0' ), $ran );
	}

	/**
	 * Migrations at or below the stored version are skipped; if stored version is
	 * already current, no option write happens either.
	 */
	public function test_skips_past_migrations(): void {
		$ran     = array();
		$updated = array();

		Functions\expect( 'get_option' )
			->once()
			->with( 'wsfq_db_version', '0.0.0' )
			->andReturn( WSFQ_VERSION );

		Functions\expect( 'update_option' )
			->never()
			->andReturnUsing(
				static function ( $key, $value ) use ( &$updated ): bool {
					$updated[] = array( $key, $value );
					return true;
				}
			);

		$upgrader = new Upgrader(
			array(
				'0.2.0' => static function () use ( &$ran ): void {
					$ran[] = '0.2.0';
				},
			)
		);

		$upgrader->maybe_upgrade();

		$this->assertSame( array(), $ran );
		$this->assertSame( array(), $updated );
	}

	/**
	 * Multiple migrations run in ascending version order.
	 */
	public function test_runs_multiple_migrations_in_order(): void {
		$ran = array();

		Functions\expect( 'get_option' )
			->once()
			->with( 'wsfq_db_version', '0.0.0' )
			->andReturn( '0.0.0' );

		Functions\expect( 'update_option' )
			->once()
			->with( 'wsfq_db_version', WSFQ_VERSION );

		$upgrader = new Upgrader(
			array(
				'0.2.0' => static function () use ( &$ran ): void {
					$ran[] = '0.2.0';
				},
				'0.3.0' => static function () use ( &$ran ): void {
					$ran[] = '0.3.0';
				},
			)
		);

		$upgrader->maybe_upgrade();

		$this->assertSame( array( '0.2.0', '0.3.0' ), $ran );
	}
}
