<?php

use \Apple_Exporter\Settings as Settings;

class Admin_Apple_Index_Page_Test extends WP_UnitTestCase {

	public function setup() {
		parent::setup();

		$this->settings = new Settings();
	}

	public function tearDown() {
		$_GET     = array();
		$_REQUEST = array();

		parent::tearDown();
	}

	/**
	 * Creates a post with metadata that simulates a stuck post.
	 *
	 * @param array $args Optional. Arguments for the post factory.
	 * @return int The post ID.
	 */
	private function create_stuck_post( $args = array() ) {
		$post_id = $this->factory->post->create( $args );

		update_post_meta( $post_id, 'apple_news_api_pending', time() );
		update_post_meta( $post_id, 'apple_news_api_async_in_progress', time() );
		update_post_meta( $post_id, 'apple_news_api_bundle', time() );
		update_post_meta( $post_id, 'apple_news_api_json', time() );
		update_post_meta( $post_id, 'apple_news_api_errors', time() );

		return $post_id;
	}

	/**
	 * Sets simulated request data for a reset action.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $nonce   The nonce to send, if any.
	 */
	private function set_reset_request( $post_id, $nonce = '' ) {
		$_GET['post_id'] = $post_id;
		$_GET['page'] = 'apple_news_index';
		$_GET['action'] = 'apple_news_reset';

		if ( $nonce ) {
			$_GET['_wpnonce'] = $nonce;
			$_REQUEST['_wpnonce'] = $nonce;
		}
	}

	public function testReset() {
		$post_id = $this->create_stuck_post();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->set_reset_request( $post_id, wp_create_nonce( Admin_Apple_Index_Page::get_nonce_action( 'apple_news_reset', $post_id ) ) );

		// Simulate the action
		$index_page = new Admin_Apple_Index_Page( $this->settings );
		$index_page->page_router();

		// Ensure values were deleted
		$this->assertEquals( false, get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		$this->assertEquals( false, get_post_meta( $post_id, 'apple_news_api_async_in_progress', true ) );
		$this->assertEquals( false, get_post_meta( $post_id, 'apple_news_api_bundle', true ) );
		$this->assertEquals( false, get_post_meta( $post_id, 'apple_news_api_json', true ) );
		$this->assertEquals( false, get_post_meta( $post_id, 'apple_news_api_errors', true ) );
	}

	public function testResetRequiresLogin() {
		$post_id = $this->create_stuck_post();

		wp_set_current_user( 0 );
		$this->set_reset_request( $post_id );

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		try {
			$index_page->page_router();
			$this->fail( 'Expected the router to stop a logged-out request.' );
		} catch ( WPDieException $e ) {
			$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		}
	}

	public function testResetRequiresCapability() {
		$post_id = $this->create_stuck_post();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );
		$this->set_reset_request( $post_id, wp_create_nonce( Admin_Apple_Index_Page::get_nonce_action( 'apple_news_reset', $post_id ) ) );

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		try {
			$index_page->page_router();
			$this->fail( 'Expected the router to stop a user without the list capability.' );
		} catch ( WPDieException $e ) {
			$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		}
	}

	public function testResetRequiresNonce() {
		$post_id = $this->create_stuck_post();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->set_reset_request( $post_id );

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		try {
			$index_page->page_router();
			$this->fail( 'Expected the router to stop a request without a nonce.' );
		} catch ( WPDieException $e ) {
			$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		}
	}

	public function testResetRequiresEditPost() {
		$post_id = $this->create_stuck_post(
			array(
				'post_author' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			)
		);

		// Give authors access to the list so that only the per-post check can stop them.
		add_filter( 'apple_news_list_capability', function () {
			return 'edit_posts';
		} );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'author' ) ) );
		$this->set_reset_request( $post_id, wp_create_nonce( Admin_Apple_Index_Page::get_nonce_action( 'apple_news_reset', $post_id ) ) );

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		try {
			$index_page->page_router();
			$this->fail( 'Expected the router to stop a user who cannot edit the post.' );
		} catch ( WPDieException $e ) {
			$this->assertNotEmpty( get_post_meta( $post_id, 'apple_news_api_pending', true ) );
		}
	}

	public function testPushRequiresPublishCapability() {
		$user_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_status' => 'draft',
			)
		);

		add_filter( 'apple_news_list_capability', function () {
			return 'edit_posts';
		} );

		wp_set_current_user( $user_id );
		$nonce = wp_create_nonce( Admin_Apple_Index_Page::get_nonce_action( 'apple_news_push', $post_id ) );
		$_GET['post_id'] = $post_id;
		$_GET['page'] = 'apple_news_index';
		$_GET['action'] = 'apple_news_push';
		$_GET['_wpnonce'] = $nonce;
		$_REQUEST['_wpnonce'] = $nonce;

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		try {
			$index_page->page_router();
			$this->fail( 'Expected the router to stop a user without the publish capability.' );
		} catch ( WPDieException $e ) {
			$this->assertContains( 'You do not have permission', $e->getMessage() );
		}
	}

	public function testBulkActionRequiresNonce() {
		$post_id = $this->factory->post->create();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$_GET['page'] = 'apple_news_index';
		$_GET['action'] = 'apple_news_push';
		$_GET['article'] = array( $post_id );

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		try {
			$index_page->page_router();
			$this->fail( 'Expected the router to stop a bulk action without a nonce.' );
		} catch ( WPDieException $e ) {
			$this->assertNotContains( 'You do not have permission', $e->getMessage() );
		}
	}

	public function testRouterRunsOnlyWhenIndexPageLoads() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		$this->assertFalse( has_action( 'admin_init', array( $index_page, 'page_router' ) ) );

		$index_page->setup_admin_page();
		$this->assertNotFalse( has_action( 'load-toplevel_page_apple_news_index', array( $index_page, 'page_router' ) ) );
	}

	public function testUnrelatedActionIsIgnored() {
		wp_set_current_user( 0 );
		$_GET['action'] = 'heartbeat';

		$index_page = new Admin_Apple_Index_Page( $this->settings );
		$this->assertNull( $index_page->page_router() );
	}
}

