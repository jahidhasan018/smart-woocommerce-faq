<?php
/**
 * Lightweight PSR-11-shaped service container.
 *
 * Classes receive dependencies via constructor injection resolved through this
 * container — never `new SomeConcreteClass()` buried inside another class.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Core;

use WSFQ\Core\Exception\ContainerException;
use WSFQ\Support\Interfaces\ContainerInterface;

/**
 * Minimal container.
 */
final class Container implements ContainerInterface {

	/**
	 * Registered factories, keyed by identifier.
	 *
	 * @var array<string, callable>
	 */
	private array $factories = array();

	/**
	 * Bind a factory for a given identifier.
	 *
	 * @param string   $id      Identifier.
	 * @param callable $factory Factory returning the instance.
	 */
	public function bind( string $id, callable $factory ): void {
		$this->factories[ $id ] = $factory;
	}

	/**
	 * Bind a factory whose result is shared (singleton).
	 *
	 * @param string   $id      Identifier.
	 * @param callable $factory Factory returning the instance.
	 */
	public function singleton( string $id, callable $factory ): void {
		$this->bind(
			$id,
			static function () use ( $factory ) {
				static $instance = null;
				if ( null === $instance ) {
					$instance = $factory();
				}
				return $instance;
			}
		);
	}

	/**
	 * Whether the container can resolve the given identifier.
	 *
	 * @param string $id Identifier.
	 */
	public function has( string $id ): bool {
		return isset( $this->factories[ $id ] );
	}

	/**
	 * Resolve the given identifier.
	 *
	 * @param string $id Identifier.
	 * @throws ContainerException When the identifier is not bound.
	 */
	public function get( string $id ): object {
		if ( ! $this->has( $id ) ) {
			throw ContainerException::notFound( $id );
		}

		$instance = $this->factories[ $id ]( $this );

		if ( ! is_object( $instance ) ) {
			throw ContainerException::invalid( $id );
		}

		return $instance;
	}
}
