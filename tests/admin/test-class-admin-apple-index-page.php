<?php
/**
 * Publish to Apple News Tests: Apple_News_Admin_Apple_Index_Page_Test class
 *
 * Contains a class to test the functionality of the Admin_Apple_Index_Page class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the behavior of the Admin_Apple_Index_Page class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Admin_Apple_Index_Page_Test extends Apple_News_Testcase {

	/**
	 * Tests the behavior of resetting a stuck post.
	 */
	public function test_reset() {
		// Create post.
		$post_id = $this->factory->post->create();

		// Add metadata to simulate a stuck post.
		update_post_meta( $post_id, 'apple_news_api_pending', time() );
		update_post_meta( $post_id, 'apple_news_api_async_in_progress', time() );
		update_post_meta( $post_id, 'apple_news_api_bundle', time() );
		update_post_meta( $post_id, 'apple_news_api_json', time() );
		update_post_meta( $post_id, 'apple_news_api_errors', time() );

		// Create simulated GET data.
		$_GET['post_id'] = $post_id; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		$_GET['page']    = 'apple_news_index'; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		$_GET['action']  = 'apple_news_reset'; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected

		// Simulate the action.
		$index_page = new Admin_Apple_Index_Page( $this->settings );
		$index_page->page_router();

		// Ensure values were deleted.
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_async_in_progress', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_bundle', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_json', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_errors', true ) );
	}
}
