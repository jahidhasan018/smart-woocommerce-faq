<?php
/**
 * SettingsPage unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use WSFQ\Admin\SettingsPage;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the admin settings page.
 */
final class SettingsPageTest extends TestCase {

	/**
	 * Registers the admin menu page.
	 */
	public function test_register_menu(): void {
		$menus = array();

		Functions\when( 'add_menu_page' )->alias(
			static function ( $title, $menu, $cap, $slug, $cb ) use ( &$menus ) {
				$menus[] = array( $title, $menu, $cap, $slug, $cb );
			}
		);
		Functions\when( '__' )->returnArg();

		$page = new SettingsPage();
		$page->register_menu();

		$this->assertCount( 1, $menus );
		$this->assertSame( 'wsfq-smart-faq', $menus[0][3] );
	}

	/**
	 * Renders the root element for the React app.
	 */
	public function test_render_outputs_root(): void {
		Functions\when( 'esc_html__' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( 'esc_url' )->returnArg();

		$page = new SettingsPage();

		ob_start();
		$page->render();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'wsfq-settings-root', $html );
		$this->assertStringContainsString( 'wsfq-smart-faq', $html );
	}
}
