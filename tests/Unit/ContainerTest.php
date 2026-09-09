<?php
/**
 * Container unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit;

use WSFQ\Core\Container;
use WSFQ\Core\Exception\ContainerException;

/**
 * Tests for the service container.
 */
final class ContainerTest extends TestCase {

	/**
	 * A bound factory returns the expected instance.
	 */
	public function test_get_returns_bound_instance(): void {
		$container = new Container();
		$container->bind( 'greeting', static fn() => new \stdClass() );

		$this->assertInstanceOf( \stdClass::class, $container->get( 'greeting' ) );
	}

	/**
	 * Tests that has() reflects bindings.
	 */
	public function test_has_reflects_bindings(): void {
		$container = new Container();
		$this->assertFalse( $container->has( 'missing' ) );

		$container->bind( 'present', static fn() => new \stdClass() );
		$this->assertTrue( $container->has( 'present' ) );
	}

	/**
	 * Singleton returns the same instance on repeated get().
	 */
	public function test_singleton_returns_same_instance(): void {
		$container = new Container();
		$container->singleton( 'shared', static fn() => new \stdClass() );

		$this->assertSame( $container->get( 'shared' ), $container->get( 'shared' ) );
	}

	/**
	 * Non-singleton returns fresh instances per get().
	 */
	public function test_bind_returns_fresh_instances(): void {
		$container = new Container();
		$container->bind( 'fresh', static fn() => new \stdClass() );

		$this->assertNotSame( $container->get( 'fresh' ), $container->get( 'fresh' ) );
	}

	/**
	 * Getting an unbound identifier throws ContainerException.
	 */
	public function test_get_unbound_throws(): void {
		$container = new Container();

		$this->expectException( ContainerException::class );
		$container->get( 'nope' );
	}

	/**
	 * Factory returning a non-object throws ContainerException.
	 */
	public function test_factory_returning_non_object_throws(): void {
		$container = new Container();
		$container->bind( 'scalar', static fn() => 'not-an-object' );

		$this->expectException( ContainerException::class );
		$container->get( 'scalar' );
	}

	/**
	 * Singleton factory receives the container instance too.
	 */
	public function test_singleton_factory_receives_container(): void {
		$container = new Container();
		$container->bind( 'inner', static fn() => new \stdClass() );
		$container->singleton(
			'outer',
			static function ( $c ) {
				$outer        = new \stdClass();
				$outer->inner = $c->get( 'inner' );
				return $outer;
			}
		);

		$this->assertInstanceOf( \stdClass::class, $container->get( 'outer' )->inner );
		$this->assertSame( $container->get( 'outer' ), $container->get( 'outer' ) );
	}

	/**
	 * Factory receives the container instance for nested resolution.
	 */
	public function test_factory_receives_container(): void {
		$container = new Container();
		$container->bind( 'inner', static fn() => new \stdClass() );
		$container->bind(
			'outer',
			static function ( $c ) {
				$outer        = new \stdClass();
				$outer->inner = $c->get( 'inner' );
				return $outer;
			}
		);

		$this->assertInstanceOf( \stdClass::class, $container->get( 'outer' )->inner );
	}
}
