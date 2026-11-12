<?php
/**
 * Publish to Apple News Tests: Rest_Permissions class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\REST;

use Apple_News\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

use function Apple_News\REST\modify_post;

/**
 * A class which is used to test authorization on the Apple News REST endpoints.
 */
class Rest_Permissions extends TestCase {

	const API_ID = 'abcd1234-ef56-ab78-cd90-efabcdef123456';

	/**
	 * Cleans up filters added by tests.
	 */
	public function tearDown(): void {
		remove_all_filters( 'apple_news_rest_permission' );
		remove_all_filters( 'apple_news_delete_capability' );
		parent::tearDown();
	}

	/**
	 * Provides every route as [ method, path, needs post ID ].
	 *
	 * @return array[]
	 */
	public static function routes(): array {
		return [
			'publish'              => [ 'post', 'publish', true ],
			'update'               => [ 'post', 'update', true ],
			'delete'               => [ 'post', 'delete', true ],
			'get-published-state'  => [ 'get', 'get-published-state/%d', true ],
			'user-can-publish'     => [ 'get', 'user-can-publish/%d', true ],
			'sections'             => [ 'get', 'sections', false ],
			'get-settings'         => [ 'get', 'get-settings', false ],
			'is-valid-cover-media' => [ 'get', 'is-valid-cover-media?type=video_url&url=https://example.org/a.mp4', false ],
		];
	}

	/**
	 * Sends a request to a route.
	 *
	 * @param string $method  The HTTP method, 'get' or 'post'.
	 * @param string $path    The route path, with an optional %d placeholder for the post ID.
	 * @param int    $post_id The post ID.
	 */
	private function send( string $method, string $path, int $post_id ) {
		$url = rest_url( '/apple-news/v1/' . sprintf( $path, $post_id ) );

		return 'post' === $method
			? $this->post( $url, [ 'id' => $post_id ] )
			: $this->get( $url );
	}

	/**
	 * Queues API responses so pushes, updates and deletes succeed.
	 */
	private function queue_api_responses(): void {
		$url = 'https://news-api.apple.com/articles/' . self::API_ID;
		$this->add_http_response( 'DELETE', $url );
		$this->add_http_response( 'POST', 'https://news-api.apple.com/channels/foo/articles', wp_json_encode( $this->fake_article_response() ) );
		$this->add_http_response( 'GET', $url, wp_json_encode( $this->fake_article_response() ) );
		$this->add_http_response( 'POST', $url, wp_json_encode( $this->fake_article_response() ) );
	}

	/**
	 * Asserts that no queued API response was consumed.
	 */
	private function assert_no_api_calls(): void {
		$url = 'https://news-api.apple.com/articles/' . self::API_ID;
		$this->assertNotEmpty( $this->http_responses['DELETE'][ $url ] );
		$this->assertNotEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$this->assertNotEmpty( $this->http_responses['POST'][ $url ] );
	}

	/**
	 * Creates a post with a remote article ID.
	 *
	 * @param array $args Post arguments.
	 */
	private function create_article( array $args = [] ): int {
		$post_id = self::factory()->post->create( array_merge( [ 'post_status' => 'publish' ], $args ) );
		update_post_meta( $post_id, 'apple_news_api_id', self::API_ID );

		return $post_id;
	}

	/**
	 * Logged-out requests are rejected on every route.
	 *
	 * @param string $method The HTTP method.
	 * @param string $path   The route path.
	 * @param bool   $needs_id Whether the route takes a post ID.
	 */
	#[DataProvider( 'routes' )]
	public function test_logged_out_is_unauthorized( string $method, string $path, bool $needs_id ): void {
		$post_id = $this->create_article();
		$this->queue_api_responses();
		wp_set_current_user( 0 );

		$this->send( $method, $path, $post_id )->assertStatus( 401 );
		$this->assert_no_api_calls();
	}

	/**
	 * Subscribers are rejected on every route.
	 *
	 * @param string $method The HTTP method.
	 * @param string $path   The route path.
	 * @param bool   $needs_id Whether the route takes a post ID.
	 */
	#[DataProvider( 'routes' )]
	public function test_subscriber_is_forbidden( string $method, string $path, bool $needs_id ): void {
		$post_id = $this->create_article();
		$this->queue_api_responses();
		$this->actingAs( $this->create_user_with_role( 'subscriber' ) );

		$this->send( $method, $path, $post_id )->assertStatus( 403 );
		$this->assert_no_api_calls();
	}

	/**
	 * Authors cannot act on other users' posts.
	 *
	 * @param string $method  The HTTP method.
	 * @param string $path    The route path.
	 * @param bool   $needs_id Whether the route takes a post ID.
	 */
	#[DataProvider( 'routes' )]
	public function test_author_forbidden_on_other_users_post( string $method, string $path, bool $needs_id ): void {
		if ( ! $needs_id ) {
			$this->markTestSkipped( 'Route does not take a post ID.' );
		}

		$post_id = $this->create_article( [ 'post_author' => $this->create_user_with_role( 'editor' )->ID ] );
		$this->queue_api_responses();
		$this->actingAs( $this->create_user_with_role( 'author' ) );

		$this->send( $method, $path, $post_id )->assertStatus( 403 );
		$this->assert_no_api_calls();
	}

	/**
	 * Authors can publish, update and delete their own published posts.
	 *
	 * @param string $route The route.
	 */
	#[DataProvider( 'author_routes' )]
	public function test_author_allowed_on_own_post( string $route ): void {
		$author = $this->create_user_with_role( 'author' );
		$this->actingAs( $author );
		$post_id = $this->create_article( [ 'post_author' => $author->ID ] );
		$this->queue_api_responses();

		$this->post( rest_url( '/apple-news/v1/' . $route ), [ 'id' => $post_id ] )->assertOk();
	}

	/**
	 * Provides the mutating routes.
	 *
	 * @return array[]
	 */
	public static function author_routes(): array {
		return [
			'publish' => [ 'publish' ],
			'update'  => [ 'update' ],
			'delete'  => [ 'delete' ],
		];
	}

	/**
	 * Sections are no longer returned to logged-out users.
	 */
	public function test_sections_logged_out(): void {
		wp_set_current_user( 0 );
		$this->get( rest_url( '/apple-news/v1/sections' ) )->assertStatus( 401 );
	}

	/**
	 * Provides the post statuses that must not be pushed.
	 *
	 * @return array[]
	 */
	public static function unpublished_statuses(): array {
		return [
			'draft'   => [ 'draft' ],
			'pending' => [ 'pending' ],
			'private' => [ 'private' ],
			'future'  => [ 'future' ],
		];
	}

	/**
	 * Publish and update reject posts that are not published.
	 *
	 * @param string $status The post status.
	 */
	#[DataProvider( 'unpublished_statuses' )]
	public function test_publish_and_update_require_published_status( string $status ): void {
		$args = [ 'post_status' => $status ];
		if ( 'future' === $status ) {
			$args['post_date'] = gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS );
		}
		$post_id = $this->create_article( $args );
		$this->queue_api_responses();
		$this->actingAs( $this->create_user_with_role( 'editor' ) );

		foreach ( [ 'publish', 'update' ] as $route ) {
			$this->post( rest_url( '/apple-news/v1/' . $route ), [ 'id' => $post_id ] )
				->assertStatus( 400 )
				->assertJsonPath( 'code', 'apple_news_post_not_published' );
		}

		$this->assert_no_api_calls();
	}

	/**
	 * Delete is allowed for a trashed post.
	 */
	public function test_delete_allowed_for_trashed_post(): void {
		$post_id = $this->create_article();
		wp_trash_post( $post_id );
		$this->queue_api_responses();
		$this->actingAs( $this->create_user_with_role( 'editor' ) );

		$this->post( rest_url( '/apple-news/v1/delete' ), [ 'id' => $post_id ] )->assertOk();
		$this->assertEmpty( $this->http_responses['DELETE'][ 'https://news-api.apple.com/articles/' . self::API_ID ] );
	}

	/**
	 * Delete respects the delete capability filter.
	 */
	public function test_delete_respects_capability_filter(): void {
		$post_id = $this->create_article();
		$this->queue_api_responses();
		add_filter( 'apple_news_delete_capability', fn() => 'do_not_allow' );
		$this->become_admin();

		$this->post( rest_url( '/apple-news/v1/delete' ), [ 'id' => $post_id ] )->assertStatus( 401 );
		$this->assert_no_api_calls();
	}

	/**
	 * The permission filter can grant access.
	 */
	public function test_permission_filter_can_allow(): void {
		add_filter( 'apple_news_rest_permission', '__return_true' );
		$this->actingAs( $this->create_user_with_role( 'subscriber' ) );

		$this->get( rest_url( '/apple-news/v1/sections' ) )->assertOk();
	}

	/**
	 * The permission filter can deny access and receives the route.
	 */
	public function test_permission_filter_can_deny_and_receives_route(): void {
		$routes = [];
		add_filter(
			'apple_news_rest_permission',
			function ( $allowed, $route ) use ( &$routes ) {
				$routes[] = $route;
				return false;
			},
			10,
			2
		);
		$post_id = $this->create_article();
		$this->queue_api_responses();
		$this->become_admin();

		$this->post( rest_url( '/apple-news/v1/publish' ), [ 'id' => $post_id ] )->assertStatus( 403 );
		$this->assertSame( [ 'publish' ], array_values( array_unique( $routes ) ) );
		$this->assert_no_api_calls();
	}

	/**
	 * Calling modify_post() directly is self-protecting, regardless of the filter.
	 */
	public function test_modify_post_checks_edit_post_directly(): void {
		$post_id = $this->create_article( [ 'post_author' => $this->create_user_with_role( 'editor' )->ID ] );
		$this->queue_api_responses();
		add_filter( 'apple_news_rest_permission', '__return_true' );
		$this->actingAs( $this->create_user_with_role( 'author' ) );

		foreach ( [ 'publish', 'update', 'delete' ] as $operation ) {
			$this->assertWPError( modify_post( $post_id, $operation ) );
		}

		$this->assert_no_api_calls();
	}
}
