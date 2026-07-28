<?php
/**
 * Publish to Apple News tests: Credentials class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Push_API;

use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Apple_Push_API\Credentials class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Credentials extends TestCase {

	/**
	 * Tests the behavior of the getters on the Credentials class.
	 */
	public function test_gets_values() {
		$credentials = new \Apple_Push_API\Credentials( 'foo', 'bar' );
		$this->assertEquals( 'foo', $credentials->key() );
		$this->assertEquals( 'bar', $credentials->secret() );
	}
}
