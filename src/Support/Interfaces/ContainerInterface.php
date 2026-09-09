<?php
/**
 * PSR-11-shaped container interface.
 *
 * Mirrors the PSR-11 contract (get/has) without pulling in a runtime
 * dependency — the plan mandates zero bundled runtime packages.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Support\Interfaces;

use WSFQ\Core\Exception\ContainerException;

/**
 * Service container contract.
 */
interface ContainerInterface {

	/**
	 * Whether the container can resolve the given identifier.
	 *
	 * @param string $id Identifier.
	 */
	public function has( string $id ): bool;

	/**
	 * Resolve the given identifier.
	 *
	 * @param string $id Identifier.
	 * @throws ContainerException When the identifier cannot be resolved.
	 */
	public function get( string $id ): object;
}
