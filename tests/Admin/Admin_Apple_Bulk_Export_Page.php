<?php
/**
 * Publish to Apple News Tests: Admin_Apple_Bulk_Export_Page class
 *
 * Contains a class to test the functionality of the Admin_Apple_Bulk_Export_Page class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Admin;

use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Admin_Apple_Bulk_Export_Page class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Admin_Apple_Bulk_Export_Page extends TestCase {

	/**
	 * The number of requests made to the Apple News API during a test.
	 *
	 * @var int
	 */
	private int $api_requests = 0;

	/**
	 * Counts outgoing requests to the Apple News API.
	 *
	 * @param false|array|\WP_Error $preempt A preemptive return value of an HTTP request.
	 * @param array                 $args    HTTP request arguments.
	 * @param string                $url     The request URL.
	 *
	 * @return false|array|\WP_Error The preempt value, unmodified.
	 */
	public function count_api_requests( $preempt, $args, $url ) {
		if ( str_contains( $url, 'news-api.apple.com' ) ) {
			++$this->api_requests;
		}

		return $preempt;
	}

	/**
	 * A data provider of user roles that may act on any post.
	 *
	 * @return array[] Role names.
	 */
	public static function data_privileged_roles(): array {
		return [
			'editor'        => [ 'editor' ],
			'administrator' => [ 'administrator' ],
		];
	}

	/**
	 * Tests that an invalid nonce is rejected for both actions.
	 */
	public function test_invalid_nonce_rejected() {
		$post_id = $this->create_post_for( self::factory()->user->create( [ 'role' => 'author' ] ) );
		$this->become_user( 'author' );
		$_GET['id']           = $post_id; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		$_REQUEST['_wpnonce'] = 'invalid'; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected

		$this->assertFalse( $this->run_handler( 'ajax_push_post' )['success'] ?? false );
		$this->assertFalse( $this->run_handler( 'ajax_delete_post' )['success'] ?? false );
		$this->assertSame( 0, $this->api_requests );
	}

	/**
	 * Tests that an author cannot delete another user's post from Apple News.
	 */
	public function test_author_cannot_delete_others_post() {
		$owner_id = self::factory()->user->create( [ 'role' => 'author' ] );
		$post_id  = $this->create_post_for( $owner_id, true );
		$this->become_user( 'author' );

		$result = $this->request( 'ajax_delete_post', $post_id );

		$this->assertFalse( $result['success'] );
		$this->assertSame( 0, $this->api_requests );
		$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_id', true ) );
	}

	/**
	 * Tests that an author cannot push another user's post to Apple News.
	 */
	public function test_author_cannot_push_others_post() {
		$owner_id = self::factory()->user->create( [ 'role' => 'author' ] );
		$post_id  = $this->create_post_for( $owner_id );
		$this->become_user( 'author' );
		$this->add_push_response();

		$result = $this->request( 'ajax_push_post', $post_id );

		$this->assertFalse( $result['success'] );
		$this->assertSame( 0, $this->api_requests );
	}

	/**
	 * Tests that an author can delete their own post from Apple News.
	 */
	public function test_author_can_delete_own_post() {
		$user_id = $this->become_user( 'author' );
		$post_id = $this->create_post_for( $user_id, true );
		$this->add_delete_response();

		$result = $this->request( 'ajax_delete_post', $post_id );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $this->api_requests );
	}

	/**
	 * Tests that an author can push their own post to Apple News.
	 */
	public function test_author_can_push_own_post() {
		$user_id = $this->become_user( 'author' );
		$post_id = $this->create_post_for( $user_id );
		$this->add_push_response();

		$result = $this->request( 'ajax_push_post', $post_id );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $this->api_requests );
	}

	/**
	 * Tests that privileged users can delete any post from Apple News.
	 *
	 * @param string $role The role of the acting user.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_privileged_roles' )]
	public function test_privileged_user_can_delete_any_post( string $role ) {
		$post_id = $this->create_post_for( self::factory()->user->create( [ 'role' => 'author' ] ), true );
		$this->become_user( $role );
		$this->add_delete_response();

		$result = $this->request( 'ajax_delete_post', $post_id );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $this->api_requests );
	}

	/**
	 * Tests that privileged users can push any post to Apple News.
	 *
	 * @param string $role The role of the acting user.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'data_privileged_roles' )]
	public function test_privileged_user_can_push_any_post( string $role ) {
		$post_id = $this->create_post_for( self::factory()->user->create( [ 'role' => 'author' ] ) );
		$this->become_user( $role );
		$this->add_push_response();

		$result = $this->request( 'ajax_push_post', $post_id );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $this->api_requests );
	}

	/**
	 * Resets request state and hooks after each test.
	 */
	public function tearDown(): void {
		$_GET     = []; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		$_REQUEST = [];
		remove_filter( 'pre_http_request', [ $this, 'count_api_requests' ], 5 );
		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', [ $this, 'get_die_handler' ] );
		parent::tearDown();
	}

	/**
	 * Sets up request counting before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->api_requests = 0;
		add_filter( 'pre_http_request', [ $this, 'count_api_requests' ], 5, 3 );
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', [ $this, 'get_die_handler' ] );
	}

	/**
	 * Replaces the ajax die handler with one that throws instead of exiting.
	 *
	 * @return callable The die handler.
	 */
	public function get_die_handler() {
		return function () {
			throw new \RuntimeException( 'wp_die' );
		};
	}

	/**
	 * Queues a fake response for a delete request.
	 */
	private function add_delete_response() {
		$this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'][] = [
			'body'     => '',
			'cookies'  => [],
			'filename' => null,
			'headers'  => [],
			'response' => [
				'code'    => 204,
				'message' => 'No Content',
			],
		];
	}

	/**
	 * Queues a fake response for a push request.
	 */
	private function add_push_response() {
		$this->add_http_response(
			'POST',
			'https://news-api.apple.com/channels/foo/articles',
			wp_json_encode( $this->fake_article_response() ),
			[],
			[
				'code'    => 201,
				'message' => 'Created',
			]
		);
	}

	/**
	 * Creates a published post for a user, optionally already synced to Apple News.
	 *
	 * @param int  $author_id The post author.
	 * @param bool $synced    Optional. Whether the post is already synced. Defaults to false.
	 *
	 * @return int The post ID.
	 */
	private function create_post_for( int $author_id, bool $synced = false ): int {
		$post_id = self::factory()->post->create(
			[
				'post_author' => $author_id,
				'post_status' => 'publish',
			]
		);
		if ( $synced ) {
			update_post_meta( $post_id, 'apple_news_api_id', 'abcd1234-ef56-ab78-cd90-efabcdef123456' );
		}

		// Discard any requests triggered by creating the post.
		$this->api_requests   = 0;
		$this->http_responses = [];

		return $post_id;
	}

	/**
	 * Creates a user with a role and sets them as the current user.
	 *
	 * @param string $role The role.
	 *
	 * @return int The user ID.
	 */
	private function become_user( string $role ): int {
		$user_id = self::factory()->user->create( [ 'role' => $role ] );
		wp_set_current_user( $user_id );

		return $user_id;
	}

	/**
	 * Performs an ajax request with a valid nonce.
	 *
	 * @param string $method  The handler method to call.
	 * @param int    $post_id The post ID.
	 *
	 * @return array The decoded JSON response.
	 */
	private function request( string $method, int $post_id ): array {
		$_GET['id']           = $post_id; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		$_REQUEST['_wpnonce'] = wp_create_nonce( \Admin_Apple_Bulk_Export_Page::ACTION );

		return $this->run_handler( $method );
	}

	/**
	 * Runs an ajax handler, capturing its JSON output.
	 *
	 * @param string $method The handler method to call.
	 *
	 * @return array The decoded JSON response, or an unsuccessful response if the request died.
	 */
	private function run_handler( string $method ): array {
		$page = new \Admin_Apple_Bulk_Export_Page( $this->settings );

		ob_start();
		try {
			$page->$method();
		} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// wp_send_json_* and wp_die() terminate by throwing in the test harness.
		}
		$output = ob_get_clean();

		$decoded = json_decode( (string) $output, true );

		return is_array( $decoded ) ? $decoded : [ 'success' => false ];
	}
}
