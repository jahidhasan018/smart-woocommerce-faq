<?php
/**
 * PostMetaFaqAssignment unit tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Support;

use Brain\Monkey\Functions;
use WSFQ\Support\FaqAssignments;
use WSFQ\Support\PostMetaFaqAssignment;
use WSFQ\Support\Interfaces\FaqAssignmentInterface;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the postmeta assignment repository.
 */
final class PostMetaFaqAssignmentTest extends TestCase {

	/**
	 * Get() assembles FaqAssignments from postmeta.
	 */
	public function test_get_reads_postmeta(): void {
		Functions\when( 'get_post_meta' )->alias(
			static function ( $post_id, $key, $single ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
				$map = array(
					FaqAssignmentInterface::KEY_GLOBAL  => '1',
					FaqAssignmentInterface::KEY_PRODUCT_IDS => array( 10, 11 ),
					FaqAssignmentInterface::KEY_CATEGORY_IDS => array( 20 ),
					FaqAssignmentInterface::KEY_TAG_IDS => array(),
					FaqAssignmentInterface::KEY_VARIATION_IDS => array( 30 ),
				);
				return $map[ $key ] ?? array();
			}
		);
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				return $value;
			}
		);

		$assignments = ( new PostMetaFaqAssignment() )->get( 5 );

		$this->assertTrue( $assignments->is_global() );
		$this->assertSame( array( 10, 11 ), $assignments->get_product_ids() );
		$this->assertSame( array( 20 ), $assignments->get_category_ids() );
		$this->assertSame( array( 30 ), $assignments->get_variation_ids() );
	}

	/**
	 * Save() writes each meta key and fires the hook.
	 */
	public function test_save_writes_meta_and_fires_hook(): void {
		$assignments = FaqAssignments::from_array(
			array(
				'global'        => true,
				'product_ids'   => array( 1 ),
				'category_ids'  => array( 2 ),
				'tag_ids'       => array( 3 ),
				'variation_ids' => array( 4 ),
			)
		);

		Functions\expect( 'update_post_meta' )
			->times( 5 );

		Functions\expect( 'do_action' )
			->once()
			->with( 'wsfq_faq_assignments_saved', 5, $assignments );

		( new PostMetaFaqAssignment() )->save( 5, $assignments );

		$this->assertTrue( true );
	}

	/**
	 * Delete() removes all meta keys and fires the hook.
	 */
	public function test_delete_removes_meta_and_fires_hook(): void {
		Functions\expect( 'delete_post_meta' )
			->times( 5 );

		Functions\expect( 'do_action' )
			->once()
			->with( 'wsfq_faq_assignments_deleted', 5 );

		( new PostMetaFaqAssignment() )->delete( 5 );

		$this->assertTrue( true );
	}
}
