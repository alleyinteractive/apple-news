<?php
/**
 * Publish to Apple News tests: MIME_Builder class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Push_API;

use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Apple_Push_API\MIME_Builder class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class MIME_Builder extends TestCase {

	/**
	 * Sets up the test case.
	 */
	public function setUp(): void {
		parent::setUp();

		require_once dirname( __DIR__, 2 ) . '/includes/apple-push-api/request/class-request.php';
	}

	/**
	 * Tests the behavior of adding JSON to the MIME builder.
	 */
	public function test_add_json() {
		$builder  = new \Apple_Push_API\MIME_Builder();
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
		$builder  = new \Apple_Push_API\MIME_Builder();
		$name     = 'some-name';
		$filename = 'article.json';
		$json     = '';

		$this->expectException( \Apple_Push_API\Request\Request_Exception::class );
		$this->expectExceptionMessage( 'The attachment article.json could not be included in the request because it was empty.' );

		$builder->add_json_string( $name, $filename, $json );
	}

	/**
	 * Ensure that remote bundle sources are fetched with unsafe URL rejection enabled.
	 */
	public function test_remote_sources_reject_unsafe_urls(): void {
		$captured = [];
		add_filter(
			'pre_http_request',
			function ( $preempt, $parsed_args, $url ) use ( &$captured ) {
				$captured[ $parsed_args['method'] . ' ' . $url ] = ! empty( $parsed_args['reject_unsafe_urls'] );

				return new \WP_Error( 'blocked', 'Blocked for test.' );
			},
			5,
			3
		);

		// The blocked request yields no content, which the builder reports as an exception.
		try {
			$builder = new \Apple_Push_API\MIME_Builder();
			$builder->add_content_from_file( 'http://123.45.67.89/latest/meta-data/' );
		} catch ( \Apple_Push_API\Request\Request_Exception $e ) {
			$this->assertNotEmpty( $e->getMessage() );
		}

		$this->assertNotEmpty( $captured );
		$this->assertNotContains( false, $captured, 'Every request for a bundle source must reject unsafe URLs.' );
	}

	/**
	 * Ensure that files outside the uploads directory are not read from disk.
	 */
	public function test_local_file_outside_uploads_is_not_read(): void {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$path = wp_tempnam( 'apple-news-test' );
		file_put_contents( $path, 'super-secret-contents' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents, WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_file_put_contents

		// Nothing is read from outside the uploads directory, so the builder has no content and reports it.
		try {
			$builder = new \Apple_Push_API\MIME_Builder();
			$builder->add_content_from_file( $path );
			$this->fail( 'A file outside of the uploads directory should not have been read.' );
		} catch ( \Apple_Push_API\Request\Request_Exception $e ) {
			$this->assertNotEmpty( $e->getMessage() );
		} finally {
			unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink, WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
		}
	}

	/**
	 * Ensure that files inside the uploads directory are still read from disk.
	 */
	public function test_local_file_inside_uploads_is_read(): void {
		$uploads = wp_get_upload_dir();
		wp_mkdir_p( $uploads['basedir'] );
		$path = $uploads['basedir'] . '/apple-news-test-bundle.txt';
		file_put_contents( $path, 'bundle-file-contents' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents, WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_file_put_contents

		$builder = new \Apple_Push_API\MIME_Builder();
		$result  = $builder->add_content_from_file( $path );

		unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink, WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink

		$this->assertStringContainsString( 'bundle-file-contents', $result );
	}
}
