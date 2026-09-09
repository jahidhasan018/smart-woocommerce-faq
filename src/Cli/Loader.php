<?php
/**
 * WP-CLI loader.
 *
 * Registers the `wsfq` command namespace with WP-CLI when running in a CLI
 * context. Commands live in WSFQ\Cli\Commands\* and delegate to the same
 * service layer as the REST/admin paths, firing the same wsfq_ hooks.
 *
 * See /docs/skills/wp-cli.md for conventions.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Cli;

/**
 * Registers WP-CLI commands.
 */
final class Loader {

	/**
	 * Register the wsfq command namespace.
	 */
	public static function register(): void {
		if ( ! class_exists( '\WP_CLI' ) ) {
			return;
		}

		\WP_CLI::add_command(
			'wsfq',
			self::class,
			array(
				'before_invoke' => array( self::class, 'check_requirements' ),
			)
		);
	}

	/**
	 * No-op root command; subcommands are registered individually as they ship.
	 * $args/$assoc_args are required by the WP-CLI __invoke signature.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative args.
	 */
	public function __invoke( array $args, array $assoc_args ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		\WP_CLI::log( 'Smart FAQ for WooCommerce — use `wp wsfq <subcommand>`. See docs/skills/wp-cli.md.' );
	}

	/**
	 * Requirements check for the wsfq command tree.
	 */
	public static function check_requirements(): void {
		if ( ! class_exists( '\WooCommerce' ) ) {
			\WP_CLI::warning( 'WooCommerce is not active. Some wsfq commands require it.' );
		}
	}
}
