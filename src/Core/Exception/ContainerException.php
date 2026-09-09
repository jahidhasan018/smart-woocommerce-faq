<?php
/**
 * Exceptions thrown by the container.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Core\Exception;

use Exception;

/**
 * Container resolution failure.
 */
class ContainerException extends Exception {

	/**
	 * Build a "not found" exception for an unbound identifier.
	 *
	 * @param string $id Identifier.
	 */
	public static function notFound( string $id ): self {
		// Exception messages are never echoed to the browser — escape check not applicable.
		return new self( sprintf( 'Container has no binding for "%s".', $id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
	}

	/**
	 * Build an "invalid value" exception for a non-object factory result.
	 *
	 * @param string $id Identifier.
	 */
	public static function invalid( string $id ): self {
		// Exception messages are never echoed to the browser — escape check not applicable.
		return new self( sprintf( 'Container factory for "%s" did not return an object.', $id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
	}
}
