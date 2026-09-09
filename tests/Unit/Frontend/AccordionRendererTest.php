<?php
/**
 * AccordionRenderer unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
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
		Functions\when( 'get_post' )->justReturn(
			(object) array(
				'ID'           => 10,
				'post_title'   => 'Q: Shipping?',
				'post_content' => 'A: Fast.',
			)
		);
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( 'wp_kses_post' )->returnArg();
		Functions\when( 'apply_filters_deprecated' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$called = array();
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) use ( &$called ) {
				$called[] = $tag;
				return $value;
			}
		);

		( new AccordionRenderer() )->render( array( 10 ) );

		$this->assertContains( 'wsfq_before_render', $called );
		$this->assertContains( 'wsfq_after_render', $called );
	}

	/**
	 * Render() emits one accordion item per FAQ with escaped output.
	 */
	public function test_render_outputs_items(): void {
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
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( 'wp_kses_post' )->returnArg();
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

		$html = ( new AccordionRenderer() )->render( array( 10, 11 ) );

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
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( 'esc_html__' )->justReturn( 'Expand all' );
		Functions\when( 'wp_kses_post' )->returnArg();
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

		$html = ( new AccordionRenderer() )->render( array( 10 ), array( 'expand_all' => true ) );

		$this->assertStringContainsString( 'wsfq-expand-all', $html );
		$this->assertStringContainsString( 'data-wsfq-expand-all', $html );
	}

	/**
	 * Render() omits the expand-all control when the option is off.
	 */
	public function test_render_omits_expand_all_control(): void {
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
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( 'esc_html__' )->justReturn( 'Expand all' );
		Functions\when( 'wp_kses_post' )->returnArg();
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

		$html = ( new AccordionRenderer() )->render( array( 10 ), array( 'expand_all' => false ) );

		$this->assertStringNotContainsString( 'wsfq-expand-all', $html );
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
		Functions\when( 'apply_filters_deprecated' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$this->assertSame( '', ( new AccordionRenderer() )->render( array() ) );
	}
}
