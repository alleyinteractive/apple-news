<?php
/**
 * Publish to Apple News Tests: Apple_News_Rest_Post_Published_State_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class which is used to test the REST endpoint for getting the published state of a post.
 */
class Apple_News_Rest_Post_Published_State_Test extends Apple_News_Testcase {

	/**
	 * Test the REST endpoint for getting the published state of a post when the plugin is not initialized.
	 */
	public function test_get_post_published_state_with_invalid_config(): void {
		delete_option( Apple_News::$option_name );

		$this->assertFalse( Apple_News::is_initialized() );

		$post_id = self::factory()->post->create();

		$this->get( rest_url( '/apple-news/v1/get-published-state/' . $post_id ) )
			->assertStatus( 400 )
			->assertJsonPath( 'message', 'You must enter your API information on the settings page before using Publish to Apple News.' );
	}

	/**
	 * Test the REST endpoint for getting the published state of a post when not authenticated.
	 */
	public function test_get_post_published_state_unauthenticated(): void {
		$api_id = 'def456';

		// Create a test post and give it sample data for the API postmeta.
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'apple_news_api_created_at', 'abc123' );
		add_post_meta( $post_id, 'apple_news_api_id', $api_id );
		add_post_meta( $post_id, 'apple_news_api_modified_at', 'ghi789' );
		add_post_meta( $post_id, 'apple_news_api_revision', 'jkl123' );
		add_post_meta( $post_id, 'apple_news_api_share_url', 'mno456' );

		$this->get( rest_url( '/apple-news/v1/get-published-state/' . $post_id ) )
			->assertOk()
			->assertJsonPathMissing( 'publishState' );

		// Ensure that the API postmeta was _not_ reset by the REST request.
		$this->assertEquals( 'abc123', get_post_meta( $post_id, 'apple_news_api_created_at', true ) );
		$this->assertEquals( $api_id, get_post_meta( $post_id, 'apple_news_api_id', true ) );
		$this->assertEquals( 'ghi789', get_post_meta( $post_id, 'apple_news_api_modified_at', true ) );
		$this->assertEquals( 'jkl123', get_post_meta( $post_id, 'apple_news_api_revision', true ) );
		$this->assertEquals( 'mno456', get_post_meta( $post_id, 'apple_news_api_share_url', true ) );
	}

	/**
	 * Test the REST endpoint for getting the published state of a post when authenticated.
	 */
	public function test_get_post_published_state_of_an_invalid_id_when_authenticated(): void {
		$api_id = 'def456';

		// Create a test post and give it sample data for the API postmeta.
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'apple_news_api_created_at', 'abc123' );
		add_post_meta( $post_id, 'apple_news_api_id', $api_id );
		add_post_meta( $post_id, 'apple_news_api_modified_at', 'ghi789' );
		add_post_meta( $post_id, 'apple_news_api_revision', 'jkl123' );
		add_post_meta( $post_id, 'apple_news_api_share_url', 'mno456' );

		$this->actingAs( $this->create_user_with_role( 'editor' ) );
		$this->assertAuthenticated();

		// Fake the API response for the GET request.
		$this->add_http_response(
			verb: 'GET',
			url: 'https://news-api.apple.com/articles/' . $api_id,
			body: wp_json_encode(
				[
					'errors' => [
						[
							'code'    => 'NOT_FOUND',
							'keyPath' => [ 'articleId' ],
							'value'   => $api_id,
						],
					],
				]
			),
			response: [
				'code'    => 404,
				'message' => 'Not Found',
			]
		);

		$this->get( rest_url( '/apple-news/v1/get-published-state/' . $post_id ) )
			->assertOk()
			->assertJsonPath( 'publishState', 'N/A' );

		// Ensure that the API postmeta _was_ reset.
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_created_at', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_id', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_modified_at', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_revision', true ) );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_share_url', true ) );
	}
}
