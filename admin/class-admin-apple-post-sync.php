<?php

require_once plugin_dir_path( __FILE__ ) . 'apple-actions/index/class-push.php';
require_once plugin_dir_path( __FILE__ ) . 'apple-actions/index/class-delete.php';

/**
 * This class is in charge of syncing posts creation, updates and deletions
 * with Apple's News API.
 *
 * @since 0.4.0
 */
class Admin_Apple_Post_Sync {

	/**
	 * Current settings.
	 *
	 * @var array
	 * @access private
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	function __construct( $settings = null ) {
		// Don't re-fetch settings if they've been previously obtained.
		// However, this class may be used within themes and therefore may
		// need to get it's own settings.
		if ( ! empty( $settings ) ) {
			$this->settings = $settings;
		} else {
			$admin_settings = new Admin_Apple_Settings;
			$this->settings = $admin_settings->fetch_settings();
		}

		// Register update hooks if needed
		if ( 'yes' == $settings->get( 'api_autosync' ) || 'yes' == $settings->get( 'api_autosync_update' ) ) {
			add_action( 'save_post', array( $this, 'do_publish' ), 10, 2 );
			add_action( 'before_delete_post', array( $this, 'do_delete' ) );
		}
	}

	/**
	 * When a post is published, or a published post updated, trigger this
	 * function.
	 *
	 * @since 0.4.0
	 * @param int $id
	 * @param WP_Post $post
	 * @access public
	 */
	public function do_publish( $id, $post ) {
		if ( 'publish' != $post->post_status
			|| ! in_array( $post->post_type, $this->settings->get( 'post_types' ) )
			|| ! current_user_can( apply_filters( 'apple_news_publish_capability', 'manage_options' ) ) ) {
			return;
		}

		// If the post has been marked as deleted from the API, ignore this update.
		$deleted = get_post_meta( $id, 'apple_news_api_deleted', true );
		if ( $deleted	) {
			return;
		}

		// Proceed based on the current settings for auto publish and update.
		$updated = get_post_meta( $id, 'apple_news_api_id', true );
		if ( $updated && 'yes' != $this->settings->get( 'api_autosync_update' )
			|| ! $updated && 'yes' != $this->settings->get( 'api_autosync' ) ) {
			return;
		}

		// Proceed with the push
		$action = new Apple_Actions\Index\Push( $this->settings, $id );
		try {
			$action->perform();
		} catch ( Apple_Actions\Action_Exception $e ) {
			Admin_Apple_Notice::error( $e->getMessage() );
		}
	}

	/**
	 * When a post is deleted, remove it from Apple News.
	 *
	 * @since 0.4.0
	 * @param int $id
	 * @access public
	 */
	public function do_delete( $id ) {
		if ( ! current_user_can( apply_filters( 'apple_news_delete_capability', 'manage_options' ) ) ) {
			return;
		}

		// If it does not have a remote API ID just ignore
		if ( ! get_post_meta( $id, 'apple_news_api_id', true ) ) {
			return;
		}

		$action = new Apple_Actions\Index\Delete( $this->settings, $id );
		try {
			$action->perform();
		} catch ( Apple_Actions\Action_Exception $e ) {
			Admin_Apple_Notice::error( $e->getMessage() );
		}
	}
}
