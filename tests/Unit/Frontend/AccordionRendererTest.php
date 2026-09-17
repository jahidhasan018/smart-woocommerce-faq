<?php
/**
 * AccordionRenderer unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
use WSFQ\Admin\SettingsService;
use WSFQ\Frontend\Renderers\AccordionRenderer;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the accordion renderer.
 */
final class AccordionRendererTest extends TestCase {

	/**
	 * Render() emits the pre-render filter hook.
	 */
	public function test_render_fires_pre_hook(): void {
		$this->stub_item();

		$called = array();
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) use ( &$called ) {
				$called[] = $tag;
				return $value;
			}
		);

		$this->renderer()->render( array( 10 ) );

		$this->assertContains( 'wsfq_before_render', $called );
		$this->assertContains( 'wsfq_after_render', $called );
	}

	/**
	 * Render() emits one accordion item per FAQ with escaped output.
	 */
	public function test_render_outputs_items(): void {
		$this->stub_item();

		$html = $this->renderer()->render( array( 10, 11 ) );

		$this->assertStringContainsString( 'wsfq-accordion', $html );
		$this->assertStringContainsString( 'wsfq-faq-10', $html );
		$this->assertStringContainsString( 'wsfq-faq-11', $html );
		$this->assertStringContainsString( 'Shipping question', $html );
		$this->assertStringContainsString( 'Answer here', $html );

		// Accessible toggle buttons per FAQ.
		$this->assertStringContainsString( 'aria-expanded="false"', $html );
		$this->assertStringContainsString( 'aria-controls="wsfq-answer-10"', $html );
		$this->assertStringContainsString( 'aria-controls="wsfq-answer-11"', $html );
	}

	/**
	 * Render() includes the expand-all control when the option is on.
	 */
	public function test_render_includes_expand_all_control(): void {
		$this->stub_item();

		$html = $this->renderer()->render( array( 10 ), array( 'expand_all' => true ) );

		$this->assertStringContainsString( 'wsfq-expand-all', $html );
		$this->assertStringContainsString( 'data-wsfq-expand-all', $html );
	}

	/**
	 * Render() omits the expand-all control when the option is off.
	 */
	public function test_render_omits_expand_all_control(): void {
		$this->stub_item();

		$html = $this->renderer()->render( array( 10 ), array( 'expand_all' => false ) );

		$this->assertStringNotContainsString( 'wsfq-expand-all', $html );
	}

	/**
	 * The control renders both labels so the state is visible without JS, and
	 * its aria-controls points at the accordion it toggles.
	 */
	public function test_control_targets_accordion_and_has_both_labels(): void {
		$this->stub_item();

		$html = $this->renderer()->render( array( 10 ), array( 'expand_all' => true ) );

		$this->assertStringContainsString( 'wsfq-expand-all-toggle__label--expand', $html );
		$this->assertStringContainsString( 'wsfq-expand-all-toggle__label--collapse', $html );

		// The control must reference the rendered accordion's id, which is what
		// the JS uses to find its scope.
		$this->assertSame( 1, preg_match( '/<div class="wsfq-accordion" id="([^"]+)"/', $html, $m ) );
		$accordion_id = $m[1];

		$this->assertStringContainsString( 'aria-controls="' . $accordion_id . '"', $html );
	}

	/**
	 * The renderer is used directly by shortcodes and the block, so it resolves
	 * the expand-all default from settings when the caller passes nothing.
	 */
	public function test_expand_all_defaults_to_setting(): void {
		$this->stub_item();
		Functions\when( 'get_option' )->justReturn(
			array( 'display' => array( 'expand_all' => false ) )
		);

		$off = $this->renderer()->render( array( 10 ) );
		$this->assertStringNotContainsString( 'wsfq-expand-all', $off );

		Functions\when( 'get_option' )->justReturn(
			array( 'display' => array( 'expand_all' => true ) )
		);

		$on = $this->renderer()->render( array( 10 ) );
		$this->assertStringContainsString( 'wsfq-expand-all', $on );
	}

	/**
	 * An explicit argument always wins over the saved setting.
	 */
	public function test_explicit_argument_overrides_setting(): void {
		$this->stub_item();
		Functions\when( 'get_option' )->justReturn(
			array( 'display' => array( 'expand_all' => true ) )
		);

		$html = $this->renderer()->render( array( 10 ), array( 'expand_all' => false ) );

		$this->assertStringNotContainsString( 'wsfq-expand-all', $html );
	}

	/**
	 * Each accordion gets its own DOM id so a page with two accordions keeps
	 * their expand-all controls pointing at the right one.
	 */
	public function test_accordion_ids_are_unique(): void {
		$this->stub_item();

		$first  = $this->renderer()->render( array( 10 ), array( 'expand_all' => true ) );
		$second = $this->renderer()->render( array( 11 ), array( 'expand_all' => true ) );

		preg_match( '/<div class="wsfq-accordion" id="([^"]+)"/', $first, $a );
		preg_match( '/<div class="wsfq-accordion" id="([^"]+)"/', $second, $b );

		$this->assertNotSame( $a[1], $b[1] );
	}

	/**
	 * Render() returns empty string when no FAQs given.
	 */
	public function test_render_empty_when_no_faqs(): void {
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$this->assertSame( '', $this->renderer()->render( array() ) );
	}

	/**
	 * Renderer wired with a settings service.
	 *
	 * @return AccordionRenderer
	 */
	private function renderer(): AccordionRenderer {
		return new AccordionRenderer( new SettingsService() );
	}

	/**
	 * Stub the WP functions an item render touches.
	 *
	 * @return void
	 */
	private function stub_item(): void {
		Functions\when( 'get_post' )->alias(
			static function ( $id ) {
				return (object) array(
					'ID'           => $id,
					'post_title'   => 'Shipping question',
					'post_content' => 'Answer here',
				);
			}
		);
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_html__' )->justReturn( 'Expand all' );
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( 'wp_kses_post' )->returnArg();
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'apply_filters_deprecated' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
	}
}
