<?php
/**
 * SchemaGenerator unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
use WSFQ\Frontend\SchemaGenerator;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the JSON-LD schema generator.
 */
final class SchemaGeneratorTest extends TestCase {

	/**
	 * Build() produces a FAQPage graph from FAQ posts.
	 */
	public function test_build_produces_faq_page(): void {
		Functions\when( 'get_post' )->alias(
			static function ( $id ) {
				$map = array(
					1 => (object) array(
						'ID'           => 1,
						'post_title'   => 'Q1',
						'post_content' => 'A1',
						'post_status'  => 'publish',
					),
					2 => (object) array(
						'ID'           => 2,
						'post_title'   => 'Q2',
						'post_content' => 'A2',
						'post_status'  => 'publish',
					),
				);
				return $map[ $id ] ?? null;
			}
		);
		Functions\when( 'wp_strip_all_tags' )->returnArg();
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$schema = ( new SchemaGenerator() )->build( array( 1, 2 ) );

		$this->assertSame( 'FAQPage', $schema['@type'] );
		$this->assertCount( 2, $schema['mainEntity'] );
		$this->assertSame( 'Q1', $schema['mainEntity'][0]['name'] );
		$this->assertSame( 'A1', $schema['mainEntity'][0]['acceptedAnswer']['text'] );
		$this->assertSame( 'Q2', $schema['mainEntity'][1]['name'] );
	}

	/**
	 * Build() returns null when no valid FAQ posts resolve.
	 */
	public function test_build_returns_null_when_empty(): void {
		Functions\when( 'get_post' )->justReturn( null );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$this->assertNull( ( new SchemaGenerator() )->build( array( 99 ) ) );
		$this->assertNull( ( new SchemaGenerator() )->build( array() ) );
	}

	/**
	 * Render() wraps the schema in a JSON-LD script tag.
	 */
	public function test_render_outputs_jsonld_script(): void {
		Functions\when( 'get_post' )->alias(
			static function ( $id ) {
				return (object) array(
					'ID'           => $id,
					'post_title'   => 'Q',
					'post_content' => 'A',
					'post_status'  => 'publish',
				);
			}
		);
		Functions\when( 'wp_strip_all_tags' )->returnArg();
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'wp_json_encode' )->alias(
			static function ( $data ) {
				return json_encode( $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			}
		);

		$html = ( new SchemaGenerator() )->render( array( 5 ) );

		$this->assertStringContainsString( '<script type="application/ld+json">', $html );
		$this->assertStringContainsString( 'FAQPage', $html );
		$this->assertStringContainsString( '</script>', $html );
	}

	/**
	 * Render() returns empty string when no schema.
	 */
	public function test_render_empty_when_no_schema(): void {
		Functions\when( 'get_post' )->justReturn( null );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$this->assertSame( '', ( new SchemaGenerator() )->render( array() ) );
	}
}
