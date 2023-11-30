<?php
/**
 * Publish to Apple News tests: Apple_News_MIME_Builder_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Push_API\MIME_Builder;

/**
 * A class to test the behavior of the Apple_Push_API\MIME_Builder class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_MIME_Builder_Test extends Apple_News_Testcase {

	/**
	 * Sets up the test case.
	 */
	public function setUp(): void {
		parent::setUp();

		apple_news_require_file( dirname( __DIR__, 2 ) . '/includes/apple-push-api/request/class-request.php' );
	}

	/**
	 * Tests the behavior of adding JSON to the MIME builder.
	 */
	public function test_add_json() {
		$builder  = new MIME_Builder();
		$eol      = "\r\n";
		$name     = 'some-name';
		$filename = 'article.json';
		$json     = '{"hello": "world"}';
		$size     = strlen( $json );

		$expected = '--' . $builder->boundary() . $eol .
			'Content-Type: application/json' . $eol .
			"Content-Disposition: form-data; name=$name; filename=$filename; size=$size" . $eol .
			$eol . $json . $eol;

		$this->assertEquals(
			$expected,
			$builder->add_json_string( $name, $filename, $json )
		);
	}

	/**
	 * Tests the behavior of adding invalid JSON to the MIME builder.
	 */
	public function test_invalid_json() {
		$builder  = new MIME_Builder();
		$name     = 'some-name';
		$filename = 'article.json';
		$json     = '';

		$this->expectException( \Apple_Push_API\Request\Request_Exception::class );
		$this->expectExceptionMessage( 'The attachment article.json could not be included in the request because it was empty.' );

		$builder->add_json_string( $name, $filename, $json );
	}
}
