<?php
/**
 * Publish to Apple News tests: Apple_News_Admin_Action_Index_Get_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the functionality of the Apple_Actions\Index\Get class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Admin_Action_Index_Get_Test extends Apple_News_Testcase {

	/**
	 * Tests the behavior of the get action without an API ID assigned to the post.
	 */
	public function test_get_action_without_api_id(): void {
		$post_id = self::factory()->post->create();
		$action  = new Apple_Actions\Index\Get( $this->settings, $post_id );

		$this->assertNull( $action->perform() );
	}

	/**
	 * Test the behavior of the get action with an API ID assigned to the post.
	 */
	public function test_get_action(): void {
		$api_id  = 'def456';
		$post_id = self::factory()->post
			->with_meta( [ 'apple_news_api_id' => $api_id ] )
			->create();

		$response = $this->fake_article_response( [ 'id' => $api_id ] );

		// Fake the API response for the GET request.
		$this->add_http_response(
			verb: 'GET',
			url: 'https://news-api.apple.com/articles/' . $api_id,
			body: wp_json_encode( $response ),
			response: [
				'code'    => 200,
				'message' => 'OK',
			]
		);

		$action = new Apple_Actions\Index\Get( $this->settings, $post_id );
		$data   = $action->perform();

		$this->assertNotEmpty( $data );
		$this->assertSame( $api_id, $data->data->id );
		$this->assertSame( $response['data']['createdAt'], $data->data->createdAt );
		$this->assertSame( $response['data']['modifiedAt'], $data->data->modifiedAt );
		$this->assertSame( $response['data']['shareUrl'], $data->data->shareUrl );
		$this->assertSame( $response['data']['revision'], $data->data->revision );
		$this->assertSame( $response['data']['type'], $data->data->type );
	}

	/**
	 * Test the behavior of the get action with a deleted Apple News article assigned to the post.
	 */
	public function test_get_deleted_article(): void {
		$api_id  = 'def456';
		$post_id = self::factory()->post->create();
		$action  = new Apple_Actions\Index\Get( $this->settings, $post_id );

		$this->assertNull( $action->perform() );

		add_post_meta( $post_id, 'apple_news_api_id', $api_id );

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

		$action = new Apple_Actions\Index\Get( $this->settings, $post_id );

		$this->assertNull( $action->perform() );
		$this->assertEmpty( get_post_meta( $post_id, 'apple_news_api_id', true ) );
	}
}
