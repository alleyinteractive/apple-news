<?php
/**
 * Publish to Apple News Tests: Apple_News_Admin_Apple_Index_Page_Test class
 *
 * Contains a class to test the functionality of the Admin_Apple_Index_Page class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Mantle\Testing\Exceptions\WP_Die_Exception;

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
		$this->become_admin();
		$this->set_request( 'reset', $post_id, wp_create_nonce( "apple_news_reset_{$post_id}" ) );

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

	/**
	 * Ensures that a single-post action is rejected when the nonce is missing.
	 */
	public function test_reset_denied_without_nonce() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, 'apple_news_api_pending', time() );

		$this->become_admin();
		$this->set_request( 'reset', $post_id );

		$this->assertRouterDies();
		$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
	}

	/**
	 * Ensures that a nonce created for one post cannot be used for another.
	 */
	public function test_reset_denied_with_nonce_for_another_post() {
		$post_id       = $this->factory->post->create();
		$other_post_id = $this->factory->post->create();
		update_post_meta( $post_id, 'apple_news_api_pending', time() );

		$this->become_admin();
		$this->set_request( 'reset', $post_id, wp_create_nonce( "apple_news_reset_{$other_post_id}" ) );

		$this->assertRouterDies();
		$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
	}

	/**
	 * Ensures that a user who cannot view the list page cannot run an action, even with a valid nonce.
	 */
	public function test_reset_denied_without_list_capability() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, 'apple_news_api_pending', time() );

		wp_set_current_user( $this->create_user_with_role( 'subscriber' )->ID );
		$this->set_request( 'reset', $post_id, wp_create_nonce( "apple_news_reset_{$post_id}" ) );

		$this->assertRouterDies();
		$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
	}

	/**
	 * Ensures that a user who can view the list page but cannot edit the post cannot run an action.
	 */
	public function test_reset_denied_without_edit_post_capability() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, 'apple_news_api_pending', time() );

		wp_set_current_user( $this->create_user_with_role( 'contributor' )->ID );
		add_filter( 'apple_news_list_capability', fn() => 'read' );
		$this->set_request( 'reset', $post_id, wp_create_nonce( "apple_news_reset_{$post_id}" ) );

		$this->assertRouterDies();
		$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
	}

	/**
	 * Ensures that requests that do not map to a known action are ignored.
	 */
	public function test_unknown_action_is_ignored() {
		$post_id = $this->factory->post->create();
		$this->become_admin();
		$this->set_request( 'unknown', $post_id );

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		$index_page->page_router();

		$this->assertTrue( true );
	}

	/**
	 * Ensures that the row action links on the list table carry a nonce for the action and post.
	 */
	public function test_row_action_urls_include_nonce() {
		$post_id = $this->factory->post->create();
		$this->become_admin();

		// The URL builder uses no table state, so skip the constructor, which requires an admin screen.
		$list_table = ( new ReflectionClass( Admin_Apple_News_List_Table::class ) )->newInstanceWithoutConstructor();
		$method     = new ReflectionMethod( $list_table, 'get_action_url' );
		$url        = $method->invoke( $list_table, 'push', $post_id, admin_url( 'admin.php?page=apple_news_index' ) );

		parse_str( (string) wp_parse_url( html_entity_decode( $url ), PHP_URL_QUERY ), $query );
		$this->assertSame( 'apple_news_push', $query['action'] );
		$this->assertNotFalse( wp_verify_nonce( $query['_wpnonce'], "apple_news_push_{$post_id}" ) );
		$this->assertFalse( wp_verify_nonce( $query['_wpnonce'], 'apple_news_push_' . ( $post_id + 1 ) ) );
	}

	/**
	 * Sets up the request state for the page router.
	 *
	 * @param string   $action  The action name, without the namespace.
	 * @param int|null $post_id The post ID, if any.
	 * @param string   $nonce   Optional. The nonce to send.
	 */
	private function set_request( string $action, ?int $post_id, string $nonce = '' ): void {
		$_GET = [
			'page'   => 'apple_news_index',
			'action' => 'apple_news_' . $action,
		];

		if ( $post_id ) {
			$_GET['post_id'] = $post_id;
		}

		if ( $nonce ) {
			$_GET['_wpnonce'] = $nonce;
		}

		$_REQUEST = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Runs the page router and asserts that the request ends with wp_die.
	 */
	private function assertRouterDies(): void {
		$died = false;
		ob_start();
		try {
			$index_page = new Admin_Apple_Index_Page( $this->settings );
			$index_page->page_router();
		} catch ( WP_Die_Exception $e ) {
			$died = true;
		}
		ob_end_clean();

		$this->assertTrue( $died, 'Expected wp_die to be triggered.' );
	}
}
