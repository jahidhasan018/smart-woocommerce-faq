<?php
/**
 * FaqCloner unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Support;

use Brain\Monkey\Functions;
use WSFQ\Support\FaqCloner;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the FAQ cloner.
 */
final class FaqClonerTest extends TestCase {

	/**
	 * Returns null when the source post is not an FAQ.
	 */
	public function test_returns_null_for_non_faq(): void {
		Functions\when( 'get_post' )->justReturn( (object) array( 'post_type' => 'product' ) );

		$this->assertNull( ( new FaqCloner() )->clone( 123 ) );
	}

	/**
	 * Returns null when the source post does not exist.
	 */
	public function test_returns_null_for_missing_post(): void {
		Functions\when( 'get_post' )->justReturn( null );

		$this->assertNull( ( new FaqCloner() )->clone( 999 ) );
	}

	/**
	 * Clones an FAQ post with copied meta + terms and fires the hook.
	 */
	public function test_clones_faq_with_meta_and_terms(): void {
		$source = (object) array(
			'post_type'    => 'wsfq_faq',
			'post_title'   => 'Shipping question',
			'post_content' => 'Answer here',
		);

		Functions\when( 'get_post' )->justReturn( $source );
		Functions\when( 'apply_filters' )->justReturn( 'Shipping question (copy)' );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'get_post_meta' )->justReturn(
			array(
				'_wsfq_product' => array( '42' ),
				'_wsfq_sort'    => array( '1' ),
			)
		);
		Functions\when( 'maybe_unserialize' )->returnArg();
		Functions\when( 'get_object_taxonomies' )->justReturn( array( 'wsfq_faq_category', 'wsfq_faq_group' ) );
		Functions\when( 'wp_get_object_terms' )->justReturn( array( 7, 8 ) );

		Functions\expect( 'wp_insert_post' )
			->once()
			->with(
				\Mockery::on(
					static function ( $args ): bool {
						return 'draft' === $args['post_status']
							&& 'Shipping question (copy)' === $args['post_title'];
					}
				)
			)
			->andReturn( 456 );

		Functions\expect( 'add_post_meta' )
			->twice();

		Functions\expect( 'wp_set_object_terms' )
			->twice()
			->with( 456, array( 7, 8 ), \Mockery::type( 'string' ) )
			->andReturn( true );

		Functions\expect( 'do_action' )
			->once()
			->with( 'wsfq_faq_cloned', 456, 123 );

		$new_id = ( new FaqCloner() )->clone( 123 );

		$this->assertSame( 456, $new_id );
	}

	/**
	 * Returns null when wp_insert_post fails.
	 */
	public function test_returns_null_when_insert_fails(): void {
		Functions\when( 'get_post' )->justReturn(
			(object) array(
				'post_type'    => 'wsfq_faq',
				'post_title'   => 'Shipping question',
				'post_content' => 'Answer here',
			)
		);
		Functions\when( 'apply_filters' )->justReturn( 'Shipping question (copy)' );
		Functions\when( 'wp_insert_post' )->justReturn( 0 );
		Functions\when( 'is_wp_error' )->justReturn( false );

		$this->assertNull( ( new FaqCloner() )->clone( 123 ) );
	}
}
