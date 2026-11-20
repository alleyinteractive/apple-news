<?php
/**
 * Publish to Apple News Tests: Rest_Sync_Meta class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\REST;

use Apple_News\Tests\TestCase;

/**
 * A class which is used to test the protection of Apple News sync meta in the REST API.
 */
class Rest_Sync_Meta extends TestCase {

	/**
	 * The sync meta keys that should only ever be written by the plugin.
	 *
	 * @var string[]
	 */
	private const SYNC_KEYS = [
		'apple_news_api_created_at',
		'apple_news_api_id',
		'apple_news_api_modified_at',
		'apple_news_api_revision',
		'apple_news_api_share_url',
	];

	/**
	 * Ensure that no user, including an administrator, can edit sync meta through meta capability checks.
	 */
	public function test_sync_meta_is_not_user_editable(): void {
		$post_id = self::factory()->post->create();

		foreach ( [ 'contributor', 'author', 'editor', 'administrator' ] as $role ) {
			$this->actingAs( $this->create_user_with_role( $role ) );

			foreach ( self::SYNC_KEYS as $key ) {
				$this->assertFalse(
					current_user_can( 'edit_post_meta', $post_id, $key ),
					"A {$role} should not be able to edit {$key}."
				);
			}
		}
	}

	/**
	 * Ensure that a contributor cannot point a draft at another article's Apple News ID.
	 */
	public function test_contributor_cannot_set_api_id_via_rest(): void {
		$user = $this->create_user_with_role( 'contributor' );
		$this->actingAs( $user );

		$post_id = self::factory()->post->create(
			[
				'post_author' => $user->ID,
				'post_status' => 'draft',
			]
		);

		$request = new \WP_REST_Request( 'POST', '/wp/v2/posts/' . $post_id );
		$request->set_body_params(
			[
				'meta' => [
					'apple_news_api_id' => 'victim-article-id',
				],
			]
		);
		rest_do_request( $request );

		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_id', true ) );
	}

	/**
	 * Ensure that sync meta is not exposed to unauthenticated users.
	 */
	public function test_sync_meta_hidden_from_public_rest_responses(): void {
		$post_id = self::factory()->post->create( [ 'post_status' => 'publish' ] );
		add_post_meta( $post_id, 'apple_news_api_id', 'def456' );
		add_post_meta( $post_id, 'apple_news_api_share_url', 'mno456' );

		wp_set_current_user( 0 );

		$response = rest_do_request( new \WP_REST_Request( 'GET', '/wp/v2/posts/' . $post_id ) );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertIsArray( $data['meta'] );
		foreach ( self::SYNC_KEYS as $key ) {
			$this->assertArrayNotHasKey( $key, $data['meta'] );
		}
	}

	/**
	 * Ensure that sync meta is still available to users who can edit the post.
	 */
	public function test_sync_meta_visible_to_editors(): void {
		$post_id = self::factory()->post->create( [ 'post_status' => 'publish' ] );
		add_post_meta( $post_id, 'apple_news_api_id', 'def456' );

		$this->actingAs( $this->create_user_with_role( 'editor' ) );

		$request = new \WP_REST_Request( 'GET', '/wp/v2/posts/' . $post_id );
		$request->set_param( 'context', 'edit' );
		$data = rest_do_request( $request )->get_data();

		$this->assertSame( 'def456', $data['meta']['apple_news_api_id'] );
	}
}
