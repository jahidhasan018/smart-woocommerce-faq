<?php
/**
 * Base TestCase for unit tests — wires Brain\Monkey so WP core functions can be
 * mocked without bootstrapping WordPress.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Tests\Unit;

use Brain\Monkey;

/**
 * Unit test base.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase {

	/**
	 * Set up Brain\Monkey before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Tear down Brain\Monkey after each test.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
