<?php
/**
 * Hello-world integration test — proves the real-WP test pipeline works
 * (needs bin/install-wp-tests.sh to have set up WP_TESTS_DIR).
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Integration;

use WP_UnitTestCase;

/**
 * Pipeline smoke test against real WordPress.
 */
final class PipelineSmokeTest extends WP_UnitTestCase {

	/**
	 * Trivial assertion that the integration runner is alive against WP core.
	 */
	public function test_integration_pipeline_is_alive(): void {
		$this->assertTrue( true );
	}

	/**
	 * Real WP is bootstrapped: post insertion works.
	 */
	public function test_wp_can_insert_a_post(): void {
		$post_id = $this->factory()->post->create(
			array(
				'post_title' => 'WSFQ pipeline smoke',
			)
		);

		$this->assertIsInt( $post_id );
		$this->assertSame( 'WSFQ pipeline smoke', get_the_title( $post_id ) );
	}
}