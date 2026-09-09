<?php
/**
 * FaqAssignments value object tests.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit\Support;

use WSFQ\Support\FaqAssignments;
use WSFQ\Tests\Unit\TestCase;

/**
 * Tests for the assignment value object.
 */
final class FaqAssignmentsTest extends TestCase {

	/**
	 * Defaults are empty / non-global.
	 */
	public function test_defaults(): void {
		$assignments = new FaqAssignments();

		$this->assertFalse( $assignments->is_global() );
		$this->assertSame( array(), $assignments->get_product_ids() );
		$this->assertSame( array(), $assignments->get_category_ids() );
		$this->assertSame( array(), $assignments->get_tag_ids() );
		$this->assertSame( array(), $assignments->get_variation_ids() );
	}

	/**
	 * FromArray populates all fields (normalizing to int arrays).
	 */
	public function test_from_array(): void {
		$assignments = FaqAssignments::from_array(
			array(
				'global'        => true,
				'product_ids'   => array( 1, 2, '3' ),
				'category_ids'  => array( 4, 5 ),
				'tag_ids'       => array( 6 ),
				'variation_ids' => array( 7, 8 ),
			)
		);

		$this->assertTrue( $assignments->is_global() );
		$this->assertSame( array( 1, 2, 3 ), $assignments->get_product_ids() );
		$this->assertSame( array( 4, 5 ), $assignments->get_category_ids() );
		$this->assertSame( array( 6 ), $assignments->get_tag_ids() );
		$this->assertSame( array( 7, 8 ), $assignments->get_variation_ids() );
	}

	/**
	 * ToArray round-trips.
	 */
	public function test_to_array_round_trip(): void {
		$original = FaqAssignments::from_array(
			array(
				'global'       => true,
				'product_ids'  => array( 1 ),
				'category_ids' => array( 2 ),
			)
		);

		$this->assertEquals( $original, FaqAssignments::from_array( $original->to_array() ) );
	}

	/**
	 * WithGlobal / with_product_ids return modified copies.
	 */
	public function test_with_returns_copies(): void {
		$base = new FaqAssignments();
		$mod  = $base->with_global( true )->with_product_ids( array( 10 ) );

		$this->assertFalse( $base->is_global() );
		$this->assertTrue( $mod->is_global() );
		$this->assertSame( array( 10 ), $mod->get_product_ids() );
	}
}
