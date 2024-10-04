<?php
/**
 * Publish to Apple News Tests: Apple_News_Admin_Action_Index_Push_Test class
 *
 * Contains a class to test the functionality of the Apple_Actions\Index\Push class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Actions\Action_Exception;

/**
 * A class used to test the functionality of the Apple_Actions\Index\Push class.
 */
class Apple_News_Admin_Action_Index_Push_Test extends Apple_News_Testcase {

	/**
	 * Returns an array of arrays representing function arguments to the
	 * test_metadata function.
	 */
	public function data_metadata() {
		return [
			[ 'apple_news_is_hidden', 'true', 'isHidden', true ],
			[ 'apple_news_is_hidden', 'false', 'isHidden', false ],
			[ 'apple_news_is_hidden', '1', 'isHidden', true ],
			[ 'apple_news_is_hidden', '0', 'isHidden', false ],
			[ 'apple_news_is_hidden', null, 'isHidden', null ],
			[ 'apple_news_is_paid', 'true', 'isPaid', true ],
			[ 'apple_news_is_paid', 'false', 'isPaid', false ],
			[ 'apple_news_is_paid', '1', 'isPaid', true ],
			[ 'apple_news_is_paid', '0', 'isPaid', false ],
			[ 'apple_news_is_paid', null, 'isPaid', null ],
			[ 'apple_news_is_preview', 'true', 'isPreview', true ],
			[ 'apple_news_is_preview', 'false', 'isPreview', false ],
			[ 'apple_news_is_preview', '1', 'isPreview', true ],
			[ 'apple_news_is_preview', '0', 'isPreview', false ],
			[ 'apple_news_is_preview', null, 'isPreview', null ],
			[ 'apple_news_is_sponsored', 'true', 'isSponsored', true ],
			[ 'apple_news_is_sponsored', 'false', 'isSponsored', false ],
			[ 'apple_news_is_sponsored', '1', 'isSponsored', true ],
			[ 'apple_news_is_sponsored', '0', 'isSponsored', false ],
			[ 'apple_news_is_sponsored', null, 'isSponsored', null ],
		];
	}

	/**
	 * A filter on the_content that tests the behavior of the
	 * apple_news_is_exporting function.
	 *
	 * @param string $content The content to be filtered.
	 *
	 * @return string The filtered content.
	 */
	public function filter_the_content( $content ) {
		return apple_news_is_exporting() ? '<p>EXPORTING</p>' : $content;
	}

	/**
	 * Tests the behavior of the component errors setting (none, warn, fail).
	 */
	public function test_component_errors() {

		// Set up a post with an invalid element (div).
		$this->become_admin();
		$user_id   = wp_get_current_user()->ID;
		$post_id_1 = self::factory()->post->create(
			[
				'post_author'  => $user_id,
				'post_content' => '<div>Test Content</div>',
			]
		);

		// Test the default behavior, which is no warning or error.
		$this->get_request_for_post( $post_id_1 );
		$notices = get_user_meta( $user_id, 'apple_news_notice', true );
		$this->assertEquals( 2, count( $notices ) );
		$this->assertEquals( 'error', $notices[0]['type'] );
		$this->assertEquals( 'There has been an error with the Apple News API: There has been an error with your request: ', $notices[0]['message'] );
		$this->assertEquals( 'success', $notices[1]['type'] );
		$this->assertEquals( 'abcd1234-ef56-ab78-cd90-efabcdef123456', get_post_meta( $post_id_1, 'apple_news_api_id', true ) );

		// Test the behavior of component warnings.
		$this->settings->component_alerts = 'warn';
		$post_id_2                        = self::factory()->post->create(
			[
				'post_author'  => $user_id,
				'post_content' => '<div>Test Content</div>',
			]
		);
		$this->get_request_for_post( $post_id_2 );
		$notices = get_user_meta( $user_id, 'apple_news_notice', true );
		$this->assertEquals( 4, count( $notices ) );
		$this->assertEquals( 'error', $notices[2]['type'] );
		$this->assertEquals( 'The following components are unsupported by Apple News and were removed: div', $notices[2]['message'] );
		$this->assertEquals( 'success', $notices[3]['type'] );
		$this->assertEquals( 'abcd1234-ef56-ab78-cd90-efabcdef123456', get_post_meta( $post_id_1, 'apple_news_api_id', true ) );

		// Test the behavior of component errors.
		$this->settings->component_alerts = 'fail';
		$post_id_3                        = self::factory()->post->create(
			[
				'post_author'  => $user_id,
				'post_content' => '<div>Test Content</div>',
			]
		);
		$exception                        = false;
		try {
			$this->get_request_for_post( $post_id_3 );
		} catch ( Action_Exception $e ) {
			$exception = $e;
		}
		$this->assertEquals( 'The following components are unsupported by Apple News and prevented publishing: div', $exception->getMessage() );
		$this->assertEquals( null, get_post_meta( $post_id_3, 'apple_news_api_id', true ) );

		// Clean up after ourselves.
		$this->settings->component_alerts = 'none';
	}

	/**
	 * Ensures that postmeta will be properly set after creating an article via
	 * the API.
	 */
	public function test_create() {
		$post_id = self::factory()->post->create();
		$this->get_request_for_post( $post_id );

		// Values in the assertions here are added in the get_request_for_post function call above.
		$this->assertEquals( 'abcd1234-ef56-ab78-cd90-efabcdef123456', get_post_meta( $post_id, 'apple_news_api_id', true ) );
		$this->assertEquals( '2020-01-02T03:04:05Z', get_post_meta( $post_id, 'apple_news_api_created_at', true ) );
		$this->assertEquals( '2020-01-02T03:04:05Z', get_post_meta( $post_id, 'apple_news_api_modified_at', true ) );
		$this->assertEquals( 'https://apple.news/ABCDEFGHIJKLMNOPQRSTUVW', get_post_meta( $post_id, 'apple_news_api_share_url', true ) );
		$this->assertEquals( null, get_post_meta( $post_id, 'apple_news_api_deleted', true ) );
	}

	/**
	 * Ensure the section is added to the metadata sent with the request.
	 */
	public function test_create_with_sections() {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'apple_news_sections', [ 'https://news-api.apple.com/sections/123' ] );
		$request  = $this->get_request_for_post( $post_id );
		$metadata = $this->get_metadata_from_request( $request );
		$this->assertEquals( [ 'https://news-api.apple.com/sections/123' ], $metadata['data']['links']['sections'] );
	}

	/**
	 * Ensures that custom metadata is properly set.
	 */
	public function test_custom_metadata() {
		$post_id  = self::factory()->post->create();
		$metadata = [
			[
				'key'   => 'isBoolean',
				'type'  => 'boolean',
				'value' => true,
			],
			[
				'key'   => 'isNumber',
				'type'  => 'number',
				'value' => 3,
			],
			[
				'key'   => 'isString',
				'type'  => 'string',
				'value' => 'Test String Value',
			],
			[
				'key'   => 'isArray',
				'type'  => 'array',
				'value' => '["a", "b", "c"]',
			],
		];
		add_post_meta( $post_id, 'apple_news_metadata', $metadata );
		$request  = $this->get_request_for_post( $post_id );
		$metadata = $this->get_metadata_from_request( $request );

		// Ensure metadata was properly compiled into the request.
		$this->assertEquals( true, $metadata['data']['isBoolean'] );
		$this->assertEquals( 3, $metadata['data']['isNumber'] );
		$this->assertEquals( 'Test String Value', $metadata['data']['isString'] );
		$this->assertEquals( [ 'a', 'b', 'c' ], $metadata['data']['isArray'] );
	}

	/**
	 * Ensures that the apple_news_is_exporting function works properly during a
	 * push request.
	 */
	public function test_exporting_flag() {
		$post_id = self::factory()->post->create();
		add_filter( 'the_content', [ $this, 'filter_the_content' ] );
		$request = $this->get_request_for_post( $post_id );
		remove_filter( 'the_content', [ $this, 'filter_the_content' ] );
		$this->assertTrue( false !== strpos( $request['body'], '<p>EXPORTING<\/p>' ) );
	}

	/**
	 * Ensures that maturity rating is properly set in the request.
	 */
	public function test_maturity_rating() {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'apple_news_maturity_rating', 'MATURE' );
		$request  = $this->get_request_for_post( $post_id );
		$metadata = $this->get_metadata_from_request( $request );
		$this->assertEquals( 'MATURE', $metadata['data']['maturityRating'] );
	}

	/**
	 * Ensures that named metadata is properly set.
	 *
	 * @dataProvider data_metadata
	 *
	 * @param string      $meta_key   The meta key to set (e.g., apple_news_is_hidden).
	 * @param string|null $meta_value The meta value to set, or null if the meta key should not be set at all.
	 * @param string      $property   The metadata property to check.
	 * @param string|null $expected   The expected value for the property, or null if it is expected to not be set.
	 */
	public function test_metadata( $meta_key, $meta_value, $property, $expected ) {
		$post_id = self::factory()->post->create();
		if ( ! is_null( $meta_value ) ) {
			add_post_meta( $post_id, $meta_key, $meta_value );
		}
		$request  = $this->get_request_for_post( $post_id );
		$metadata = $this->get_metadata_from_request( $request );
		if ( ! is_null( $expected ) ) {
			$this->assertEquals( $expected, $metadata['data'][ $property ], sprintf( 'Expected property %s to be %s given meta key %s and meta value %s', $property, $expected, $meta_key, $meta_value ) );
		} else {
			$this->assertArrayNotHasKey( $property, $metadata['data'] ?? [], sprintf( 'Expected property %s to not exist, but it does', $property ) );
		}
	}

	/**
	 * Tests skipping publish of a post by filters or by taxonomy term.
	 */
	public function test_skip() {
		$post_id = self::factory()->post->create();

		// Test the apple_news_skip_push filter.
		add_filter( 'apple_news_skip_push', '__return_true' );
		$exception = false;
		try {
			$this->get_request_for_post( $post_id );
		} catch ( Action_Exception $e ) {
			$exception = $e;
		}
		$this->assertEquals( sprintf( 'Skipped push of article %d due to the apple_news_skip_push filter.', $post_id ), $exception->getMessage() );
		remove_filter( 'apple_news_skip_push', '__return_true' );

		// Test the new filter for skipping by term ID.
		$term_id = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		wp_set_object_terms( $post_id, $term_id, 'category' );
		$skip_filter = function () use ( $term_id ) {
			return [ $term_id ];
		};
		add_filter( 'apple_news_skip_push_term_ids', $skip_filter );
		$exception = false;
		try {
			$this->get_request_for_post( $post_id );
		} catch ( Action_Exception $e ) {
			$exception = $e;
		}
		$this->assertEquals( sprintf( 'Skipped push of article %d due to the presence of a skip push taxonomy term.', $post_id ), $exception->getMessage() );
		remove_filter( 'apple_news_skip_push_term_ids', $skip_filter );

		// Test skip by setting the option for skipping by term ID.
		$this->settings->api_autosync_skip = wp_json_encode( [ $term_id ] );
		$exception                         = false;
		try {
			$this->get_request_for_post( $post_id );
		} catch ( Action_Exception $e ) {
			$exception = $e;
		}
		$this->assertEquals( sprintf( 'Skipped push of article %d due to the presence of a skip push taxonomy term.', $post_id ), $exception->getMessage() );
		$this->settings->api_autosync_skip = '';
	}

	/**
	 * Tests the update workflow to ensure that posts are only updated when
	 * changes have been made.
	 */
	public function test_update() {
		// Create a post and fake sending it to the API.
		$post = self::factory()->post->create_and_get();
		$this->get_request_for_post( $post->ID );

		// Ensure that the fake response from the API was saved to postmeta.
		$this->assertEquals( 'abcd1234-ef56-ab78-cd90-efabcdef123456', get_post_meta( $post->ID, 'apple_news_api_id', true ) );
		$this->assertEquals( '2020-01-02T03:04:05Z', get_post_meta( $post->ID, 'apple_news_api_created_at', true ) );
		$this->assertEquals( '2020-01-02T03:04:05Z', get_post_meta( $post->ID, 'apple_news_api_modified_at', true ) );
		$this->assertEquals( 'https://apple.news/ABCDEFGHIJKLMNOPQRSTUVW', get_post_meta( $post->ID, 'apple_news_api_share_url', true ) );
		$this->assertEquals( null, get_post_meta( $post->ID, 'apple_news_api_deleted', true ) );

		// Try to sync the post again, and verify that it bails out before attempting the sync.
		try {
			$this->get_request_for_post( $post->ID );
		} catch ( Action_Exception $e ) {
			$regexp = '/There has been an error with the Apple News API|Skipped push of article ' . preg_quote( $post->ID, '/' ) . ' to Apple News because it is already in sync\./';
			$this->assertMatchesRegularExpression( $regexp, $e->getMessage() );
		}

		// Update the post by changing the title and ensure that the update is sent to Apple.
		$post->post_title = 'Test New Title';
		wp_update_post( $post );
		$request = $this->get_request_for_update( $post->ID );
		$body    = $this->get_body_from_request( $request );
		$this->assertEquals( 'Test New Title', $body['title'] );
	}

	/**
	 * Test that the action is able to handle a deleted article.
	 */
	public function test_update_with_deleted_article(): void {
		$article_id = self::factory()->post->create();
		$api_id     = 'efabcdef123456';

		add_post_meta( $article_id, 'apple_news_api_id', $api_id );

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

		$action = new Apple_Actions\Index\Push( $this->settings, $article_id );

		try {
			$action->perform();
		} catch ( Action_Exception $e ) {
			$this->assertSame( 'The article seems to be deleted in Apple News. Reindexing the article in Apple News.', $e->getMessage() );
		}

		$this->assertEmpty( get_post_meta( $article_id, 'apple_news_api_id', true ) );
	}
}
