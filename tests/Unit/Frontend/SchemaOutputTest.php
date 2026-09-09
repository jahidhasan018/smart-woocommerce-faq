<?php
/**
 * SchemaOutput unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
use WSFQ\Frontend\SchemaGenerator;
use WSFQ\Frontend\SchemaOutput;
use WSFQ\Support\Interfaces\FaqResolverInterface;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the schema output hook.
 */
final class SchemaOutputTest extends TestCase {

	/**
	 * On a product page, schema renders for the resolved FAQs.
	 */
	public function test_product_page_emits_schema(): void {
		$resolver = \Mockery::mock( FaqResolverInterface::class );
		$resolver->shouldReceive( 'resolve' )->once()->with( 10 )->andReturn( array( 1, 2 ) );

		Functions\when( 'is_product' )->justReturn( true );
		Functions\when( 'is_singular' )->justReturn( true );
		Functions\when( 'get_queried_object_id' )->justReturn( 10 );
		Functions\when( 'get_the_ID' )->justReturn( 10 );
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
		Functions\when( 'wp_json_encode' )->alias(
			static function ( $data ) {
				return json_encode( $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			}
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);
		Functions\when( 'esc_html' )->returnArg();

		$output = new SchemaOutput( $resolver, new SchemaGenerator() );

		ob_start();
		$output->maybe_output();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'application/ld+json', $html );
		$this->assertStringContainsString( 'FAQPage', $html );
	}

	/**
	 * On a non-product page without FAQ context, nothing is emitted.
	 */
	public function test_non_product_page_emits_nothing(): void {
		$resolver = \Mockery::mock( FaqResolverInterface::class );
		$resolver->shouldReceive( 'resolve' )->never();

		Functions\when( 'is_product' )->justReturn( false );
		Functions\when( 'is_singular' )->justReturn( false );
		Functions\when( 'get_queried_object_id' )->justReturn( 0 );

		$output = new SchemaOutput( $resolver, new SchemaGenerator() );

		ob_start();
		$output->maybe_output();
		$html = ob_get_clean();

		$this->assertSame( '', $html );
	}
}
