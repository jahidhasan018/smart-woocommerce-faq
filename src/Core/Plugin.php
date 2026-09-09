<?php
/**
 * Plugin singleton bootstrap.
 *
 * Wires the service container, registers lifecycle hooks, and runs versioned
 * migrations on plugins_loaded. This is the only place that knows the whole
 * service graph — nothing else instantiates services ad-hoc.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Core;

use WSFQ\PostTypes\FaqPostType;
use WSFQ\Taxonomies\FaqCategory;
use WSFQ\Taxonomies\FaqGroup;

/**
 * Plugin bootstrap.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Service container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Get the singleton (and boot it once).
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — singleton.
	 */
	private function __construct() {
		$this->container = new Container();
		$this->wire();
		$this->register_hooks();
	}

	/**
	 * Access the container.
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Bind services into the container.
	 */
	private function wire(): void {
		$this->container->singleton(
			Upgrader::class,
			static fn(): Upgrader => new Upgrader()
		);

		$this->container->singleton(
			\WSFQ\Support\Interfaces\FaqAssignmentInterface::class,
			static fn(): \WSFQ\Support\PostMetaFaqAssignment => new \WSFQ\Support\PostMetaFaqAssignment()
		);

		$this->container->singleton(
			\WSFQ\Support\FaqResolver::class,
			static fn( Container $c ): \WSFQ\Support\FaqResolver => new \WSFQ\Support\FaqResolver(
				$c->get( \WSFQ\Support\Interfaces\FaqAssignmentInterface::class )
			)
		);

		$this->container->singleton(
			\WSFQ\Support\Interfaces\FaqResolverInterface::class,
			static fn( Container $c ): \WSFQ\Support\FaqResolver => $c->get( \WSFQ\Support\FaqResolver::class )
		);

		$this->container->singleton(
			\WSFQ\Frontend\DisplayEngine::class,
			static fn( Container $c ): \WSFQ\Frontend\DisplayEngine => new \WSFQ\Frontend\DisplayEngine(
				$c->get( \WSFQ\Support\Interfaces\FaqResolverInterface::class ),
				new \WSFQ\Frontend\Renderers\AccordionRenderer()
			)
		);

		$this->container->singleton(
			\WSFQ\Frontend\Shortcodes\ShortcodeRegistry::class,
			static fn( Container $c ): \WSFQ\Frontend\Shortcodes\ShortcodeRegistry => new \WSFQ\Frontend\Shortcodes\ShortcodeRegistry(
				$c->get( \WSFQ\Support\Interfaces\FaqResolverInterface::class ),
				new \WSFQ\Frontend\Renderers\AccordionRenderer()
			)
		);
	}

	/**
	 * Register lifecycle and content-type hooks at top level.
	 */
	private function register_hooks(): void {
		register_activation_hook( WSFQ_FILE, array( Activator::class, 'activate' ) );
		register_deactivation_hook( WSFQ_FILE, array( Deactivator::class, 'deactivate' ) );

		add_action( 'init', array( FaqPostType::class, 'register' ) );
		add_action( 'init', array( FaqCategory::class, 'register' ) );
		add_action( 'init', array( FaqGroup::class, 'register' ) );

		// Shortcodes.
		add_action(
			'init',
			static function (): void {
				$registry = self::$instance->container()->get( \WSFQ\Frontend\Shortcodes\ShortcodeRegistry::class );
				$registry->register();
			},
			20
		);

		// Frontend display engine (woocommerce hooks).
		add_action(
			'wp',
			static function (): void {
				$engine = self::$instance->container()->get( \WSFQ\Frontend\DisplayEngine::class );
				$engine->register();
				$engine->enqueue_assets();
			},
			10
		);

		// Run versioned migrations on plugins_loaded (this hook fires when the
		// plugin boots here).
		$this->container->get( Upgrader::class )->maybe_upgrade();
	}
}
