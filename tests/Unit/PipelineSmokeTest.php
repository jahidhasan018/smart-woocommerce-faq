<?php
/**
 * Hello-world unit test — proves the unit test pipeline works end-to-end.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit;

/**
 * Pipeline smoke test.
 */
final class PipelineSmokeTest extends TestCase {

	/**
	 * Trivial assertion that the unit runner is alive.
	 */
	public function test_unit_pipeline_is_alive(): void {
		$this->assertTrue( true );
	}

	/**
	 * Brain\Monkey is wired: a WP function can be expected and called.
	 */
	public function test_brain_monkey_can_mock_wp_function(): void {
		\Brain\Monkey\Functions\when( 'wsfq_mock_me' )
			->justReturn( 'mocked' );

		$this->assertSame( 'mocked', \wsfq_mock_me() );
	}
}
