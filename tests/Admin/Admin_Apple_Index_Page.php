<?php
/**
 * Publish to Apple News Tests: Admin_Apple_Index_Page class
 *
 * Contains a class to test the functionality of the Admin_Apple_Index_Page class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Admin;

use Apple_News\Tests\TestCase;
use Mantle\Testing\Exceptions\WP_Die_Exception;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A class to test the behavior of the Admin_Apple_Index_Page class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Admin_Apple_Index_Page extends TestCase {

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
		$index_page = new \Admin_Apple_Index_Page( $this->settings );
		$index_page->page_router();

		// Ensure values were deleted.
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_async_in_progress', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_bundle', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_json', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_errors', true ) );
	}

	/**
	 * Resets the simulated request data after each test.
	 */
	public function tearDown(): void {
		$_GET = []; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		remove_all_filters( 'apple_news_list_capability' );
		remove_all_filters( 'map_meta_cap' );
		parent::tearDown();
	}

	/**
	 * Provides the single-post actions.
	 *
	 * @return array[]
	 */
	public static function single_actions(): array {
		return [
			'debug'  => [ 'debug' ],
			'export' => [ 'export' ],
			'push'   => [ 'push' ],
			'delete' => [ 'delete' ],
			'reset'  => [ 'reset' ],
		];
	}

	/**
	 * Builds the router for a request and returns the nonce-less request state.
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
	 * Creates a published post with a stuck state and a remote article ID.
	 *
	 * @param int $author_id Optional. The post author.
	 * @return int The post ID.
	 */
	private function create_article( int $author_id = 0 ): int {
		$args = [ 'post_status' => 'publish' ];
		if ( $author_id ) {
			$args['post_author'] = $author_id;
		}

		$this->add_http_response( 'POST', 'https://news-api.apple.com/channels/foo/articles', wp_json_encode( $this->fake_article_response() ) );
		$post_id = self::factory()->post->create( $args );

		update_post_meta( $post_id, 'apple_news_api_id', 'abcd1234-ef56-ab78-cd90-efabcdef123456' );
		update_post_meta( $post_id, 'apple_news_api_pending', time() );
		update_post_meta( $post_id, 'apple_news_api_async_in_progress', time() );

		return $post_id;
	}

	/**
	 * Runs the router and asserts that it died with a 403.
	 */
	private function assert_denied(): void {
		$index_page = new \Admin_Apple_Index_Page( $this->settings );
		$died       = false;

		ob_start();
		try {
			$index_page->page_router();
		} catch ( WP_Die_Exception $e ) {
			$died = true;
		}
		$output = ob_get_clean();

		$this->assertTrue( $died, 'Expected wp_die to be triggered.' );
		$this->assertStringNotContainsString( 'Apple News API Push', $output );
		$this->assertStringNotContainsString( '"version"', $output );
	}

	/**
	 * Asserts that no side effects from the actions occurred.
	 *
	 * @param int $post_id The post ID.
	 */
	private function assert_untouched( int $post_id ): void {
		$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_async_in_progress', true ) );
		$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_id', true ) );
		$this->assertEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] ?? [] );
		$this->assertEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] ?? [] );
	}

	/**
	 * Queues responses so a pushed or deleted article succeeds.
	 */
	private function queue_api_responses(): void {
		$this->add_http_response( 'DELETE', 'https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456' );
		$this->add_http_response( 'POST', 'https://news-api.apple.com/channels/foo/articles', wp_json_encode( $this->fake_article_response() ) );
		$this->add_http_response( 'GET', 'https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456', wp_json_encode( $this->fake_article_response() ) );
		$this->add_http_response( 'POST', 'https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456', wp_json_encode( $this->fake_article_response() ) );
	}

	/**
	 * Asserts the action ran for an authorized user.
	 *
	 * @param string $action  The action name.
	 * @param int    $post_id The post ID.
	 */
	private function assert_performed( string $action, int $post_id ): void {
		$this->queue_api_responses();
		$index_page = new \Admin_Apple_Index_Page( $this->settings );

		ob_start();
		$index_page->page_router();
		$output = ob_get_clean();

		switch ( $action ) {
			case 'debug':
				$this->assertStringContainsString( 'Apple News API Push', $output );
				break;
			case 'export':
				$this->assertStringContainsString( '"version"', $output );
				break;
			case 'reset':
				$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
				$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_async_in_progress', true ) );
				break;
			case 'delete':
				$this->assertEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
				break;
			case 'push':
				$this->assertEmpty( $this->http_responses['POST']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
				break;
		}
	}

	/**
	 * Logged-out requests are denied.
	 *
	 * @param string $action The action name.
	 */
	#[DataProvider( 'single_actions' )]
	public function test_logged_out_denied( string $action ) {
		$post_id = $this->create_article();
		wp_set_current_user( 0 );
		$this->set_request( $action, $post_id, wp_create_nonce( "apple_news_{$action}_{$post_id}" ) );
		$this->queue_api_responses();
		$this->assert_denied();
		$this->assert_untouched_after_queue( $post_id );
	}

	/**
	 * Users without the list capability are denied.
	 *
	 * @param string $action The action name.
	 */
	#[DataProvider( 'single_actions' )]
	public function test_lacking_list_capability_denied( string $action ) {
		$author  = $this->create_user_with_role( 'author' );
		$post_id = $this->create_article( $author->ID );
		wp_set_current_user( $author->ID );
		$this->set_request( $action, $post_id, wp_create_nonce( "apple_news_{$action}_{$post_id}" ) );
		$this->queue_api_responses();
		$this->assert_denied();
		$this->assert_untouched_after_queue( $post_id );
	}

	/**
	 * Users with the list capability but not edit_post on the target are denied.
	 *
	 * @param string $action The action name.
	 */
	#[DataProvider( 'single_actions' )]
	public function test_lacking_edit_post_denied( string $action ) {
		add_filter( 'apple_news_list_capability', fn() => 'read' );
		$other   = $this->create_user_with_role( 'subscriber' );
		$post_id = $this->create_article();
		wp_set_current_user( $other->ID );
		$this->assertFalse( current_user_can( 'edit_post', $post_id ) );
		$this->set_request( $action, $post_id, wp_create_nonce( "apple_news_{$action}_{$post_id}" ) );
		$this->queue_api_responses();
		$this->assert_denied();
		$this->assert_untouched_after_queue( $post_id );
	}

	/**
	 * Authorized users with a missing nonce are denied.
	 *
	 * @param string $action The action name.
	 */
	#[DataProvider( 'single_actions' )]
	public function test_missing_nonce_denied( string $action ) {
		$this->become_admin();
		$post_id = $this->create_article();
		$this->set_request( $action, $post_id );
		$this->queue_api_responses();
		$this->assert_denied();
		$this->assert_untouched_after_queue( $post_id );
	}

	/**
	 * Authorized users with a nonce for another post are denied.
	 *
	 * @param string $action The action name.
	 */
	#[DataProvider( 'single_actions' )]
	public function test_wrong_post_nonce_denied( string $action ) {
		$this->become_admin();
		$post_id  = $this->create_article();
		$other_id = $post_id + 1000;
		$this->set_request( $action, $post_id, wp_create_nonce( "apple_news_{$action}_{$other_id}" ) );
		$this->queue_api_responses();
		$this->assert_denied();
		$this->assert_untouched_after_queue( $post_id );
	}

	/**
	 * A nonexistent post is never acted on.
	 *
	 * @param string $action The action name.
	 */
	#[DataProvider( 'single_actions' )]
	public function test_nonexistent_post_denied( string $action ) {
		$this->become_admin();
		$post_id = 9999999;
		$this->set_request( $action, $post_id, wp_create_nonce( "apple_news_{$action}_{$post_id}" ) );
		$this->queue_api_responses();
		$this->assert_denied();
	}

	/**
	 * Authorized users with a valid nonce can perform the action.
	 *
	 * @param string $action The action name.
	 */
	#[DataProvider( 'single_actions' )]
	public function test_authorized_performed( string $action ) {
		$this->become_admin();
		$post_id = $this->create_article();
		$this->set_request( $action, $post_id, wp_create_nonce( "apple_news_{$action}_{$post_id}" ) );
		$this->assert_performed( $action, $post_id );
	}

	/**
	 * Pushing requires the publish capability on the post type.
	 */
	public function test_push_requires_publish_capability() {
		add_filter( 'apple_news_list_capability', fn() => 'read' );
		add_filter( 'apple_news_publish_capability', fn() => 'manage_network_options' );
		$author  = $this->create_user_with_role( 'author' );
		$post_id = $this->create_article( $author->ID );
		wp_set_current_user( $author->ID );
		$this->assertTrue( current_user_can( 'edit_post', $post_id ) );
		$this->set_request( 'push', $post_id, wp_create_nonce( "apple_news_push_{$post_id}" ) );
		$this->queue_api_responses();
		$this->assert_denied();
		$this->assert_untouched_after_queue( $post_id );
		remove_all_filters( 'apple_news_publish_capability' );
	}

	/**
	 * Deleting requires the delete capability on the post type.
	 */
	public function test_delete_requires_delete_capability() {
		add_filter( 'apple_news_list_capability', fn() => 'read' );
		add_filter( 'apple_news_delete_capability', fn() => 'manage_network_options' );
		$editor  = $this->create_user_with_role( 'editor' );
		$post_id = $this->create_article();
		wp_set_current_user( $editor->ID );
		$this->assertTrue( current_user_can( 'edit_post', $post_id ) );
		$this->set_request( 'delete', $post_id, wp_create_nonce( "apple_news_delete_{$post_id}" ) );
		$this->queue_api_responses();
		$this->assert_denied();
		$this->assert_untouched_after_queue( $post_id );
		remove_all_filters( 'apple_news_delete_capability' );
	}

	/**
	 * Asserts nothing was consumed from the queued API responses.
	 *
	 * @param int $post_id The post ID.
	 */
	private function assert_untouched_after_queue( int $post_id ): void {
		$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_async_in_progress', true ) );
		$this->assertNotEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		$this->assertNotEmpty( $this->http_responses['POST']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
	}

	/**
	 * Bulk actions require the bulk-articles nonce.
	 *
	 * @param string $action The action name.
	 */
	#[DataProvider( 'bulk_actions' )]
	public function test_bulk_requires_nonce( string $action ) {
		$this->become_admin();
		$post_id = $this->create_article();
		$this->set_request( $action, null );
		$_GET['article'] = [ $post_id ];
		$this->assert_denied();

		// A valid nonce redirects to the bulk export page.
		$_GET['_wpnonce'] = wp_create_nonce( 'bulk-articles' );
		$_REQUEST         = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$redirected       = '';
		add_filter(
			'wp_redirect',
			function ( $location ) use ( &$redirected ) {
				$redirected = $location;
				return false;
			}
		);
		( new \Admin_Apple_Index_Page( $this->settings ) )->page_router();
		$this->assertStringContainsString( 'apple_news_' . $action . '_post', $redirected );
	}

	/**
	 * Bulk actions require the list capability.
	 *
	 * @param string $action The action name.
	 */
	#[DataProvider( 'bulk_actions' )]
	public function test_bulk_requires_list_capability( string $action ) {
		$author = $this->create_user_with_role( 'author' );
		wp_set_current_user( $author->ID );
		$this->set_request( $action, null, wp_create_nonce( 'bulk-articles' ) );
		$_GET['article'] = [ 1 ];
		$this->assert_denied();
	}

	/**
	 * Provides the bulk actions.
	 *
	 * @return array[]
	 */
	public static function bulk_actions(): array {
		return [
			'push'   => [ 'push' ],
			'delete' => [ 'delete' ],
		];
	}

	/**
	 * The router runs on the page load hook rather than admin_init.
	 */
	public function test_router_hook() {
		$this->become_admin();
		$index_page = new \Admin_Apple_Index_Page( $this->settings );
		$this->assertFalse( has_action( 'admin_init', [ $index_page, 'page_router' ] ) );

		$index_page->setup_admin_page();
		$this->assertNotFalse( has_action( 'load-toplevel_page_apple_news_index', [ $index_page, 'page_router' ] ) );
	}

	/**
	 * Row action links carry valid nonces.
	 */
	public function test_row_action_nonces() {
		$this->become_admin();
		require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
		require_once ABSPATH . 'wp-admin/includes/screen.php';
		require_once ABSPATH . 'wp-admin/includes/template.php';
		\set_current_screen( 'toplevel_page_apple_news_index' );
		get_current_screen()->parent_base = 'apple_news_index';

		$post_id = $this->create_article();
		$table   = new \Admin_Apple_News_List_Table( $this->settings );
		$html    = $table->column_title( get_post( $post_id ) );

		foreach ( [ 'export', 'debug', 'reset', 'delete' ] as $action ) {
			$this->assertMatchesRegularExpression(
				'/action=apple_news_' . $action . '[^\'"]*_wpnonce=([a-f0-9]+)|_wpnonce=([a-f0-9]+)[^\'"]*action=apple_news_' . $action . '/',
				html_entity_decode( $html ),
				"Missing nonce for {$action}"
			);
			preg_match_all( "/href='([^']*apple_news_{$action}[^']*)'/", $html, $m );
			parse_str( wp_parse_url( html_entity_decode( $m[1][0] ), PHP_URL_QUERY ), $query );
			$this->assertSame( 1, wp_verify_nonce( $query['_wpnonce'], "apple_news_{$action}_{$post_id}" ) );
		}
	}
}
