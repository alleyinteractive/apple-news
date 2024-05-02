<?php
/**
 * Publish to Apple News Tests: Apple_News_Admin_Apple_Meta_Boxes_Test class
 *
 * Contains a class to test the functionality of the Admin_Apple_Meta_Boxes class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the behavior of the Admin_Apple_Meta_Boxes class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Admin_Apple_Meta_Boxes_Test extends Apple_News_Testcase {

	/**
	 * Tests the behavior of a save with auto-sync enabled.
	 */
	public function test_save_no_auto_sync() {
		// Set API settings to not auto sync and to enable the meta box.
		$this->settings->set( 'api_autosync', 'no' );
		$this->settings->set( 'show_metabox', 'yes' );

		// Create post.
		$post_id = $this->factory->post->create();

		// Create post data.
		/* phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
		$_POST['post_ID']                       = $post_id;
		$_POST['apple_news_sections']           = [ 'https://news-api.apple.com/sections/1234567890' ];
		$_POST['apple_news_is_hidden']          = '';
		$_POST['apple_news_is_paid']            = '';
		$_POST['apple_news_is_preview']         = '';
		$_POST['apple_news_is_sponsored']       = '';
		$_POST['apple_news_maturity_rating']    = 'MATURE';
		$_POST['apple_news_pullquote']          = 'test pullquote';
		$_POST['apple_news_pullquote_position'] = 'middle';
		$_POST['apple_news_nonce']              = wp_create_nonce( 'apple_news_publish' );
		$_POST['apple_news_publish_action']     = 'apple_news_publish';
		$_REQUEST['post_ID']                    = $_POST['post_ID'];
		$_REQUEST['apple_news_nonce']           = $_POST['apple_news_nonce'];
		/* phpcs:enable */

		// Create the meta box class and simulate a save.
		$meta_box = new Admin_Apple_Meta_Boxes( $this->settings );
		if ( 'yes' === $this->settings->get( 'show_metabox' ) ) {
			$meta_box->do_publish( $post_id, get_post( $post_id ) );
		}

		// Check the meta values.
		$this->assertEquals( [ 'https://news-api.apple.com/sections/1234567890' ], get_post_meta( $post_id, 'apple_news_sections', true ) );
		$this->assertEquals( '', get_post_meta( $post_id, 'apple_news_is_hidden', true ) );
		$this->assertEquals( '', get_post_meta( $post_id, 'apple_news_is_paid', true ) );
		$this->assertEquals( '', get_post_meta( $post_id, 'apple_news_is_preview', true ) );
		$this->assertEquals( '', get_post_meta( $post_id, 'apple_news_is_sponsored', true ) );
		$this->assertEquals( 'MATURE', get_post_meta( $post_id, 'apple_news_maturity_rating', true ) );
		$this->assertEquals( 'test pullquote', get_post_meta( $post_id, 'apple_news_pullquote', true ) );
		$this->assertEquals( 'middle', get_post_meta( $post_id, 'apple_news_pullquote_position', true ) );
	}

	/**
	 * Tests the behavior of saving a post with auto-sync enabled.
	 */
	public function test_save_with_auto_sync() {
		// Set API settings to not auto sync and to enable the meta box.
		$this->settings->set( 'api_autosync', 'yes' );
		$this->settings->set( 'show_metabox', 'yes' );

		// Create post.
		$post_id = $this->factory->post->create();

		// Create post data.
		/* phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
		$_POST['post_ID']                       = $post_id;
		$_POST['apple_news_sections']           = [ 'https://news-api.apple.com/sections/1234567890' ];
		$_POST['apple_news_is_hidden']          = '';
		$_POST['apple_news_is_paid']            = '';
		$_POST['apple_news_is_preview']         = '';
		$_POST['apple_news_is_sponsored']       = '';
		$_POST['apple_news_maturity_rating']    = 'MATURE';
		$_POST['apple_news_pullquote']          = 'test pullquote';
		$_POST['apple_news_pullquote_position'] = 'middle';
		$_POST['apple_news_nonce']              = wp_create_nonce( 'apple_news_publish' );
		$_POST['apple_news_publish_action']     = 'apple_news_publish';
		$_REQUEST['post_ID']                    = $_POST['post_ID'];
		$_REQUEST['apple_news_nonce']           = $_POST['apple_news_nonce'];
		/* phpcs:enable */

		// Create the meta box class and simulate a save.
		$meta_box = new Admin_Apple_Meta_Boxes( $this->settings );
		if ( 'yes' === $this->settings->get( 'show_metabox' ) ) {
			$meta_box->do_publish( $post_id, get_post( $post_id ) );
		}

		// Check the meta values.
		$this->assertEquals( [ 'https://news-api.apple.com/sections/1234567890' ], get_post_meta( $post_id, 'apple_news_sections', true ) );
		$this->assertEquals( '', get_post_meta( $post_id, 'apple_news_is_hidden', true ) );
		$this->assertEquals( '', get_post_meta( $post_id, 'apple_news_is_paid', true ) );
		$this->assertEquals( '', get_post_meta( $post_id, 'apple_news_is_preview', true ) );
		$this->assertEquals( '', get_post_meta( $post_id, 'apple_news_is_sponsored', true ) );
		$this->assertEquals( 'MATURE', get_post_meta( $post_id, 'apple_news_maturity_rating', true ) );
		$this->assertEquals( 'test pullquote', get_post_meta( $post_id, 'apple_news_pullquote', true ) );
		$this->assertEquals( 'middle', get_post_meta( $post_id, 'apple_news_pullquote_position', true ) );
	}
}
