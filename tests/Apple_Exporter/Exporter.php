<?php
/**
 * Publish to Apple News Tests: Exporter class
 *
 * Contains a class to test the functionality of the Apple_Exporter\Exporter class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter;

use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Apple_Exporter\Exporter class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Exporter extends TestCase {

	/**
	 * Tests the functionality of the prepare_for_encoding function to ensure
	 * that unwanted characters are stripped.
	 */
	public function test_prepare_for_encoding() {
		// Test UTF-8 characters with accents common in French.
		$test_content = 'Pondant à Noël — aÀâÂèÈéÉêÊëËîÎïÏôÔùÙûÛüÜÿŸçÇœŒ€æÆ';
		\Apple_Exporter\Exporter::prepare_for_encoding( $test_content );
		$this->assertEquals( 'Pondant à Noël — aÀâÂèÈéÉêÊëËîÎïÏôÔùÙûÛüÜÿŸçÇœŒ€æÆ', $test_content );

		// Test Unicode whitespace character removal.
		$test_content = json_decode( '"\u0020"' )
			. json_decode( '"\u00a0"' )
			. json_decode( '"\u2000"' )
			. json_decode( '"\u2001"' )
			. json_decode( '"\u2002"' )
			. json_decode( '"\u2003"' )
			. json_decode( '"\u2004"' )
			. json_decode( '"\u2005"' )
			. json_decode( '"\u2006"' )
			. json_decode( '"\u2007"' )
			. json_decode( '"\u2008"' )
			. json_decode( '"\u2009"' )
			. json_decode( '"\u200a"' )
			. json_decode( '"\u202f"' )
			. json_decode( '"\u205f"' )
			. json_decode( '"\u3000"' );
		\Apple_Exporter\Exporter::prepare_for_encoding( $test_content );
		$this->assertEquals(
			str_repeat( ' ', strlen( $test_content ) ),
			$test_content
		);
	}
}
