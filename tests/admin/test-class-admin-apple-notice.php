<?php

use \Admin_Apple_Notice as Admin_Apple_Notice;
use \Apple_News as Apple_News;

class Admin_Apple_Notice_Test extends WP_UnitTestCase {

	public function setup() {
		$this->user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->user_id );
	}

	public function testInfo() {
		Admin_Apple_Notice::info( 'This is an info message', $this->user_id );

		ob_start();
		Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-warning apple-news-notice is-dismissible" data-message="This is an info message" data-nonce="some-nonce" data-type="warning"><p><strong>This is an info message</strong></p></div>' );
		$notice = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}

	public function testSuccess() {
		Admin_Apple_Notice::success( 'This is a success message', $this->user_id );

		ob_start();
		Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-success apple-news-notice is-dismissible" data-message="This is a success message" data-nonce="some-nonce" data-type="success"><p><strong>This is a success message</strong></p></div>' );
		$notice = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}

	public function testError() {
		Admin_Apple_Notice::error( 'This is an error message', $this->user_id );

		ob_start();
		Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-error apple-news-notice is-dismissible" data-message="This is an error message' . esc_attr( Apple_News::get_support_info() ) . '" data-nonce="some-nonce" data-type="error"><p><strong>This is an error message' . Apple_News::get_support_info() . '</strong></p></div>' );
		$notice = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}

	public function testFormattingSingle() {
		Admin_Apple_Notice::info( 'One error occurred: error 1', $this->user_id );

		ob_start();
		Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-warning apple-news-notice is-dismissible" data-message="One error occurred: error 1" data-nonce="some-nonce" data-type="warning"><p><strong>One error occurred: error 1</strong></p></div>' );
		$notice = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}

	public function testFormattingMultiple() {
		Admin_Apple_Notice::info( 'A number of errors occurred: error 1, error 2, error 3', $this->user_id );

		ob_start();
		Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-warning apple-news-notice is-dismissible" data-message="' . esc_attr( 'A number of errors occurred:<br />error 1<br />error 2<br />error 3' ) . '" data-nonce="some-nonce" data-type="warning"><p><strong>A number of errors occurred:<br />error 1<br />error 2<br />error 3</strong></p></div>' );
		$notice = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}

	public function testLineBreaks() {
		Admin_Apple_Notice::info( 'One message|Another message', $this->user_id );

		ob_start();
		Admin_Apple_Notice::show();
		$notice = ob_get_contents();
		ob_end_clean();

		$expected = preg_replace( '/\s+/', '', '<div class="notice notice-warning apple-news-notice is-dismissible" data-message="' . esc_attr( 'One message<br />Another message' ) . '" data-nonce="some-nonce" data-type="warning"><p><strong>One message<br />Another message</strong></p></div>' );
		$notice = preg_replace( '/data-nonce="[^"]+"/', 'data-nonce="some-nonce"', $notice );
		$notice = preg_replace( '/\s+/', '', $notice );

		$this->assertEquals( $expected, $notice );
	}
}
