<?php
/**
 * Publish to Apple News Tests: Apple_News_Rest_Permission_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class which is used to test the permission checks on the Apple News REST endpoints.
 */
class Apple_News_Rest_Permission_Test extends Apple_News_Testcase {

	/**
	 * Provides the endpoints that act on a specific post.
	 *
	 * @return array[] Method, path template, and whether the post ID is sent in the URL.
	 */
	public static function post_endpoint_provider(): array {
		return [
			'delete'              => [ 'post', '/apple-news/v1/delete' ],
			'get-published-state' => [ 'get', '/apple-news/v1/get-published-state/%d' ],
			'publish'             => [ 'post', '/apple-news/v1/publish' ],
			'update'              => [ 'post', '/apple-news/v1/update' ],
			'user-can-publish'    => [ 'get', '/apple-news/v1/user-can-publish/%d' ],
		];
	}

	/**
	 * Provides the endpoints that do not act on a specific post.
	 *
	 * @return array[] Path.
	 */
	public static function general_endpoint_provider(): array {
		return [
			'get-settings' => [ '/apple-news/v1/get-settings' ],
			'sections'     => [ '/apple-news/v1/sections' ],
		];
	}

	/**
	 * Sends a request to an endpoint that acts on a post.
	 *
	 * @param string $method   The HTTP method, 'get' or 'post'.
	 * @param string $path     The path, with an optional %d placeholder for the post ID.
	 * @param int    $post_id  The post ID.
	 * @return \Mantle\Testing\Test_Response
	 */
	private function request_post_endpoint( string $method, string $path, int $post_id ) {
		if ( str_contains( $path, '%d' ) ) {
			return $this->{$method}( rest_url( sprintf( $path, $post_id ) ) );
		}

		return $this->{$method}( rest_url( $path ), [ 'id' => $post_id ] );
	}

	/**
	 * Unauthenticated requests must be rejected on every post endpoint.
	 *
	 * @dataProvider post_endpoint_provider
	 *
	 * @param string $method The HTTP method.
	 * @param string $path   The path.
	 */
	public function test_post_endpoints_reject_unauthenticated( string $method, string $path ): void {
		$post_id = self::factory()->post->create();

		$this->request_post_endpoint( $method, $path, $post_id )->assertStatus( 401 );
	}

	/**
	 * A user who cannot edit the post must be rejected on every post endpoint.
	 *
	 * @dataProvider post_endpoint_provider
	 *
	 * @param string $method The HTTP method.
	 * @param string $path   The path.
	 */
	public function test_post_endpoints_reject_user_who_cannot_edit_post( string $method, string $path ): void {
		$post_id = self::factory()->post->create(
			[ 'post_author' => self::factory()->user->create( [ 'role' => 'editor' ] ) ]
		);

		$this->actingAs( $this->create_user_with_role( 'contributor' ) );

		$this->request_post_endpoint( $method, $path, $post_id )->assertStatus( 403 );
	}

	/**
	 * The post endpoints must reject requests with no post ID.
	 */
	public function test_post_endpoints_reject_missing_post_id(): void {
		$this->actingAs( $this->create_user_with_role( 'editor' ) );

		$this->post( rest_url( '/apple-news/v1/publish' ) )->assertForbidden();
	}

	/**
	 * Unauthenticated requests must be rejected on the general endpoints.
	 *
	 * @dataProvider general_endpoint_provider
	 *
	 * @param string $path The path.
	 */
	public function test_general_endpoints_reject_unauthenticated( string $path ): void {
		$this->get( rest_url( $path ) )->assertStatus( 401 );
	}

	/**
	 * Users who cannot edit posts must be rejected on the general endpoints.
	 *
	 * @dataProvider general_endpoint_provider
	 *
	 * @param string $path The path.
	 */
	public function test_general_endpoints_reject_subscriber( string $path ): void {
		$this->actingAs( $this->create_user_with_role( 'subscriber' ) );

		$this->get( rest_url( $path ) )->assertStatus( 403 );
	}

	/**
	 * The permission filter can deny access that the capability check would allow.
	 */
	public function test_permission_filter_can_deny(): void {
		$this->actingAs( $this->create_user_with_role( 'editor' ) );
		add_filter( 'apple_news_rest_permission', '__return_false' );

		$this->get( rest_url( '/apple-news/v1/sections' ) )->assertStatus( 403 );
	}

	/**
	 * Only published posts can be sent to Apple News.
	 */
	public function test_publish_rejects_unpublished_post(): void {
		$this->actingAs( $this->create_user_with_role( 'editor' ) );
		$post_id = self::factory()->post->create( [ 'post_status' => 'draft' ] );

		$this->post( rest_url( '/apple-news/v1/publish' ), [ 'id' => $post_id ] )
			->assertStatus( 400 )
			->assertJsonPath( 'code', 'apple_news_post_not_published' );
	}
}
