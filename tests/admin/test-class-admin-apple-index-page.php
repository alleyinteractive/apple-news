<?php

use \Apple_Exporter\Settings as Settings;

class Admin_Apple_Index_Page_Test extends WP_UnitTestCase {

	public function setup() {
		parent::setup();

		$this->settings = new Settings();
	}

	public function tearDown() {
		$_GET     = array();
		$_REQUEST = array();

		parent::tearDown();
	}

	/**
	 * Creates a post with metadata that simulates a stuck post.
	 *
	 * @return int The post ID.
	 */
	private function create_stuck_post() {
		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'apple_news_api_pending', time() );
		update_post_meta( $post_id, 'apple_news_api_async_in_progress', time() );
		update_post_meta( $post_id, 'apple_news_api_bundle', time() );
		update_post_meta( $post_id, 'apple_news_api_json', time() );
		update_post_meta( $post_id, 'apple_news_api_errors', time() );

		return $post_id;
	}

	/**
	 * Sets simulated request data for a reset action.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $nonce   The nonce to send, if any.
	 */
	private function set_reset_request( $post_id, $nonce = '' ) {
		$_GET['post_id'] = $post_id;
		$_GET['page'] = 'apple_news_index';
		$_GET['action'] = 'apple_news_reset';

		if ( $nonce ) {
			$_GET['_wpnonce'] = $nonce;
			$_REQUEST['_wpnonce'] = $nonce;
		}
	}

	public function testReset() {
		$post_id = $this->create_stuck_post();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->set_reset_request( $post_id, wp_create_nonce( Admin_Apple_Index_Page::get_nonce_action( 'apple_news_reset', $post_id ) ) );

		// Simulate the action
		$index_page = new Admin_Apple_Index_Page( $this->settings );
		$index_page->page_router();

		// Ensure values were deleted
		$this->assertEquals( false, get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		$this->assertEquals( false, get_post_meta( $post_id, 'apple_news_api_async_in_progress', true ) );
		$this->assertEquals( false, get_post_meta( $post_id, 'apple_news_api_bundle', true ) );
		$this->assertEquals( false, get_post_meta( $post_id, 'apple_news_api_json', true ) );
		$this->assertEquals( false, get_post_meta( $post_id, 'apple_news_api_errors', true ) );
	}

	public function testResetRequiresLogin() {
		$post_id = $this->create_stuck_post();

		wp_set_current_user( 0 );
		$this->set_reset_request( $post_id );

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		try {
			$index_page->page_router();
			$this->fail( 'Expected the router to stop a logged-out request.' );
		} catch ( WPDieException $e ) {
			$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		}
	}

	public function testResetRequiresCapability() {
		$post_id = $this->create_stuck_post();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );
		$this->set_reset_request( $post_id, wp_create_nonce( Admin_Apple_Index_Page::get_nonce_action( 'apple_news_reset', $post_id ) ) );

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		try {
			$index_page->page_router();
			$this->fail( 'Expected the router to stop a user without the list capability.' );
		} catch ( WPDieException $e ) {
			$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		}
	}

	public function testResetRequiresNonce() {
		$post_id = $this->create_stuck_post();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->set_reset_request( $post_id );

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		try {
			$index_page->page_router();
			$this->fail( 'Expected the router to stop a request without a nonce.' );
		} catch ( WPDieException $e ) {
			$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		}
	}

	public function testUnrelatedActionIsIgnored() {
		wp_set_current_user( 0 );
		$_GET['action'] = 'heartbeat';

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		$this->assertNull( $index_page->page_router() );
	}
}

