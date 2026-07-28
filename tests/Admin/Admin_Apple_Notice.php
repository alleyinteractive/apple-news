<?php
/**
 * Publish to Apple News Tests: Admin_Apple_Notice class
 *
 * Contains a class to test the functionality of the Admin_Apple_Notice class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Admin;

use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Admin_Apple_Notice class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Admin_Apple_Notice extends TestCase {

	/**
	 * A fixture containing operations to be run before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->become_admin();
	}

	/**
	 * Tests the behavior of outputting info messages.
	 */
	public function test_info() {
		\Admin_Apple_Notice::info( 'This is an info message' );

		ob_start();
		\Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-warning apple-news-notice is-dismissible" data-message="This is an info message" data-nonce="some-nonce" data-type="warning"><p><strong>This is an info message</strong></p></div>' );
		$notice   = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice   = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}

	/**
	 * Tests the behavior of outputting success messages.
	 */
	public function test_success() {
		\Admin_Apple_Notice::success( 'This is a success message' );

		ob_start();
		\Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-success apple-news-notice is-dismissible" data-message="This is a success message" data-nonce="some-nonce" data-type="success"><p><strong>This is a success message</strong></p></div>' );
		$notice   = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice   = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}

	/**
	 * Tests the behavior of outputting error messages.
	 */
	public function test_error() {
		\Admin_Apple_Notice::error( 'This is an error message' );

		ob_start();
		\Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-error apple-news-notice is-dismissible" data-message="This is an error message' . esc_attr( \Apple_News::get_support_info() ) . '" data-nonce="some-nonce" data-type="error"><p><strong>This is an error message' . \Apple_News::get_support_info() . '</strong></p></div>' );
		$notice   = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice   = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}

	/**
	 * Tests the behavior of displaying a single notice.
	 */
	public function test_formatting_single() {
		\Admin_Apple_Notice::info( 'One error occurred: error 1' );

		ob_start();
		\Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-warning apple-news-notice is-dismissible" data-message="One error occurred: error 1" data-nonce="some-nonce" data-type="warning"><p><strong>One error occurred: error 1</strong></p></div>' );
		$notice   = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice   = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}

	/**
	 * Tests the behavior of multiple errors being displayed at once.
	 */
	public function test_formatting_multiple() {
		\Admin_Apple_Notice::info( 'A number of errors occurred: error 1, error 2, error 3' );

		ob_start();
		\Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-warning apple-news-notice is-dismissible" data-message="' . esc_attr( 'A number of errors occurred:<br />error 1<br />error 2<br />error 3' ) . '" data-nonce="some-nonce" data-type="warning"><p><strong>A number of errors occurred:<br />error 1<br />error 2<br />error 3</strong></p></div>' );
		$notice   = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice   = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}

	/**
	 * Tests the behavior of line breaks in notices.
	 */
	public function test_line_breaks() {
		\Admin_Apple_Notice::info( 'One message|Another message' );

		ob_start();
		\Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-warning apple-news-notice is-dismissible" data-message="' . esc_attr( 'One message<br />Another message' ) . '" data-nonce="some-nonce" data-type="warning"><p><strong>One message<br />Another message</strong></p></div>' );
		$notice   = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice   = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}
}
