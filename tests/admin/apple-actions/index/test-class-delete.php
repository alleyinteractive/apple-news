<?php
/**
 * Publish to Apple News tests: Apple_News_Admin_Action_Index_Delete_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the functionality of the Apple_Actions\Index\Delete class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Admin_Action_Index_Delete_Test extends Apple_News_Testcase {
	/**
	 * Tests the behavior of the automatic delete setting.
	 */
	public function test_auto_delete() {
		// Create a post, which will automatically be published.
		$this->become_admin();
		$this->add_http_response( 'POST', 'https://news-api.apple.com/channels/foo/articles', wp_json_encode( $this->fake_article_response() ) );
		$this->assertNotEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$post_id = self::factory()->post->create();
		$this->assertEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );

		// Add an HTTP response for the delete operation, then delete the article, and verify it was triggered.
		$this->add_http_response( 'DELETE', 'https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456' );
		$this->assertNotEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		wp_delete_post( $post_id, true );
		$this->assertEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );

		// Create a new article, move it to the trash, and verify that the delete operation was triggered.
		$this->add_http_response( 'POST', 'https://news-api.apple.com/channels/foo/articles', wp_json_encode( $this->fake_article_response() ) );
		$this->assertNotEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$post_id = self::factory()->post->create();
		$this->assertEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$this->add_http_response( 'DELETE', 'https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456' );
		$this->assertNotEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		wp_delete_post( $post_id );
		$this->assertEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );

		// Create a new article, move it to draft, and verify that the delete operation was triggered.
		$this->add_http_response( 'POST', 'https://news-api.apple.com/channels/foo/articles', wp_json_encode( $this->fake_article_response() ) );
		$this->assertNotEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$post = self::factory()->post->create_and_get();
		$this->assertEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$this->add_http_response( 'DELETE', 'https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456' );
		$this->assertNotEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		$post->post_status = 'draft';
		wp_update_post( $post );
		$this->assertEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );

		// Create a new article, move it to the trash, and verify that the delete operation was triggered.
		$this->add_http_response( 'POST', 'https://news-api.apple.com/channels/foo/articles', wp_json_encode( $this->fake_article_response() ) );
		$this->assertNotEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$post_id = self::factory()->post->create();
		$this->assertEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$this->add_http_response( 'DELETE', 'https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456' );
		$this->assertNotEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		wp_delete_post( $post_id );
		$this->assertEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );

		// Opt out of deleting on unpublish by filter, create a new article, move it to draft status, and verify that the delete operation was not triggered.
		add_filter( 'apple_news_should_post_delete_on_unpublish', '__return_false' );
		$this->add_http_response( 'POST', 'https://news-api.apple.com/channels/foo/articles', wp_json_encode( $this->fake_article_response() ) );
		$this->assertNotEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$post = self::factory()->post->create_and_get();
		$this->assertEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$this->add_http_response( 'DELETE', 'https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456' );
		$this->assertNotEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		$post->post_status = 'draft';
		wp_update_post( $post );
		$this->assertNotEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		array_pop( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		$this->assertEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );

		// Opt out of deleting on trash by filter, create a new article, move it to the trash, and verify that the delete operation was not triggered.
		add_filter( 'apple_news_should_post_delete_on_trash', '__return_false' );
		$this->add_http_response( 'POST', 'https://news-api.apple.com/channels/foo/articles', wp_json_encode( $this->fake_article_response() ) );
		$this->assertNotEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$post_id = self::factory()->post->create();
		$this->assertEmpty( $this->http_responses['POST']['https://news-api.apple.com/channels/foo/articles'] );
		$this->add_http_response( 'DELETE', 'https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456' );
		$this->assertNotEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		wp_delete_post( $post_id );
		$this->assertNotEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		array_pop( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
		$this->assertEmpty( $this->http_responses['DELETE']['https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456'] );
	}
}
