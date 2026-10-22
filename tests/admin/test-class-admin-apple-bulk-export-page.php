<?php
/**
 * Publish to Apple News Tests: Admin_Apple_Bulk_Export_Page_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

use \Apple_Exporter\Settings;

/**
 * A class to test the Admin_Apple_Bulk_Export_Page class.
 */
class Admin_Apple_Bulk_Export_Page_Test extends WP_UnitTestCase {

	/**
	 * Actions to be run before each test in this class.
	 *
	 * @access public
	 */
	public function setup() {
		parent::setup();

		$this->bulk_export_page = new Admin_Apple_Bulk_Export_Page( new Settings() );
	}

	/**
	 * Actions to be run after each test in this class.
	 *
	 * @access public
	 */
	public function tearDown() {
		$_GET     = array();
		$_REQUEST = array();

		parent::tearDown();
	}

	/**
	 * Runs the AJAX push handler as the current user and returns its JSON response.
	 *
	 * @param int $post_id The ID of the post to push.
	 * @return array The decoded response.
	 */
	private function push_post( $post_id ) {
		$_GET['id'] = $post_id;
		$_REQUEST['_wpnonce'] = wp_create_nonce( Admin_Apple_Bulk_Export_Page::ACTION );

		ob_start();
		try {
			$this->bulk_export_page->ajax_push_post();
		} catch ( WPDieException $e ) {
			// The handler ends every response with wp_die().
		}

		return json_decode( ob_get_clean(), true );
	}

	/**
	 * Ensures that a user who can publish posts cannot push a post they cannot edit.
	 *
	 * @access public
	 */
	public function testPushRequiresEditPost() {
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			)
		);

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'author' ) ) );
		$response = $this->push_post( $post_id );

		$this->assertFalse( $response['success'] );
		$this->assertEquals( 'You do not have permission to publish to Apple News', $response['error'] );
	}

	/**
	 * Ensures that a user who can edit and publish the post passes the permission check.
	 *
	 * @access public
	 */
	public function testPushAllowsEditor() {
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
				'post_status' => 'draft',
			)
		);

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'editor' ) ) );
		$response = $this->push_post( $post_id );

		// A draft stops the push after the permission check, before any API request.
		$this->assertFalse( $response['success'] );
		$this->assertContains( 'is not published', $response['error'] );
	}
}
