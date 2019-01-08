<?php

use \Apple_Exporter\Settings as Settings;

class Admin_Apple_Index_Page_Test extends WP_UnitTestCase {

	public function setup() {
		parent::setup();

		$this->settings = new Settings();
	}

	public function testReset() {
		// Create post
		$post_id = $this->factory->post->create();

		// Add metadata to simulate a stuck post
		update_post_meta( $post_id, 'apple_news_api_pending', time() );
		update_post_meta( $post_id, 'apple_news_api_async_in_progress', time() );
		update_post_meta( $post_id, 'apple_news_api_bundle', time() );
		update_post_meta( $post_id, 'apple_news_api_json', time() );
		update_post_meta( $post_id, 'apple_news_api_errors', time() );

		// Create simulated GET data
		$_GET['post_id'] = $post_id; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		$_GET['page'] = 'apple_news_index'; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		$_GET['action'] = 'apple_news_reset'; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected

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
}

