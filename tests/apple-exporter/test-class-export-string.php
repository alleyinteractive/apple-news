<?php
/**
 * Publish to Apple News Tests: Apple_News_Export_String_Test class
 *
 * Contains a class to test the functionality of the Apple_Exporter\Theme class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Export_String;

/**
 * A class to test the behavior of the Apple_Exporter\Export_String class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Export_String_Test extends Apple_News_Testcase {

	/**
	 * Tests the behavior of the exporter with just a title and body content.
	 */
	public function test_minimal_content() {
		$content = new Export_String();
		$this->assertEquals( '<p>Example content</p>', $content->get_json_for_html( '<p>Example content</p>' ) );
	}
}
