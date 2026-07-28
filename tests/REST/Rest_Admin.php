<?php
/**
 * Publish to Apple News Tests: Rest_Admin class
 *
 * Contains a class which is used to test REST requests in the admin.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\REST;

use Apple_News\Tests\TestCase;

/**
 * A class which is used to test REST requests in the admin.
 */
class Rest_Admin extends TestCase {

	/**
	 * Make a REST request to reset API postmeta entries.
	 *
	 * This can sometimes happen when Gutenberg sends updated post
	 * data to the admin, but we don't want to overwrite the API data
	 * stored in postmeta.
	 */
	public function test_rest_overwrite_api_data(): void {
		$this->actingAs( $this->create_user_with_role( 'editor' ) );
		$this->assertAuthenticated();

		// Create a test post and give it sample data for the API postmeta.
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'apple_news_api_created_at', 'abc123' );
		add_post_meta( $post_id, 'apple_news_api_id', 'def456666' );
		add_post_meta( $post_id, 'apple_news_api_modified_at', 'ghi789' );
		add_post_meta( $post_id, 'apple_news_api_revision', 'jkl123' );
		add_post_meta( $post_id, 'apple_news_api_share_url', 'mno456' );

		// Update the post via REST request and attempt to reset the API postmeta.
		$endpoint = '/wp/v2/posts/' . $post_id;
		$payload  = [
			'content' => '<!-- wp:paragraph -->\n<p>Testing.</p>\n<!-- /wp:paragraph -->',
			'id'      => $post_id,
			'meta'    => [
				'apple_news_api_created_at'  => '',
				'apple_news_api_id'          => '',
				'apple_news_api_modified_at' => '',
				'apple_news_api_revision'    => '',
				'apple_news_api_share_url'   => '',
			],
		];
		$request  = new \WP_REST_Request( 'POST', $endpoint );
		$request->set_body_params( $payload );
		rest_do_request( $request );

		// Ensure that the API postmeta was _not_ reset by the REST request.
		$this->assertEquals( 'abc123', get_post_meta( $post_id, 'apple_news_api_created_at', true ) );
		$this->assertEquals( 'def456666', get_post_meta( $post_id, 'apple_news_api_id', true ) );
		$this->assertEquals( 'ghi789', get_post_meta( $post_id, 'apple_news_api_modified_at', true ) );
		$this->assertEquals( 'jkl123', get_post_meta( $post_id, 'apple_news_api_revision', true ) );
		$this->assertEquals( 'mno456', get_post_meta( $post_id, 'apple_news_api_share_url', true ) );
	}
}
