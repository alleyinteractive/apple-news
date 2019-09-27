<?php
/**
 * Publish to Apple News: Admin_Apple_Post_Sync class
 *
 * @package Apple_News
 */

// Include dependencies.
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
	 *
	 * @param \Apple_Exporter\Settings $settings Optional. Settings to use. Defaults to null.
	 */
	public function __construct( $settings = null ) {
		/**
		 * Don't re-fetch settings if they've been previously obtained.
		 * However, this class may be used within themes and therefore may
		 * need to get its own settings.
		 */
		if ( ! empty( $settings ) ) {
			$this->settings = $settings;
		} else {
			$admin_settings = new Admin_Apple_Settings();
			$this->settings = $admin_settings->fetch_settings();
		}

		// Register update hook if needed.
		if ( 'yes' === $this->settings->get( 'api_autosync' )
			|| 'yes' === $this->settings->get( 'api_autosync_update' )
		) {
			// This needs to happen after meta boxes save.
			add_action( 'save_post', [ $this, 'do_publish' ], 99, 2 );
		}

		// Register delete hook if needed.
		if ( 'yes' === $this->settings->get( 'api_autosync_delete' ) ) {
			add_action( 'before_delete_post', array( $this, 'do_delete' ) );
		}
	}

	/**
	 * When a post is published, or a published post updated, trigger this function.
	 *
	 * @since 0.4.0
	 * @param int     $id   The ID of the post being updated.
	 * @param WP_Post $post The post object being updated.
	 * @access public
	 */
	public function do_publish( $id, $post ) {
		if ( 'publish' !== $post->post_status
			|| ! in_array( $post->post_type, $this->settings->post_types, true )
			|| ( ! current_user_can( apply_filters( 'apple_news_publish_capability', Apple_News::get_capability_for_post_type( 'publish_posts', $post->post_type ) ) )
				&& ! ( defined( 'DOING_CRON' ) && DOING_CRON ) )
		) {
			return;
		}

		// If the post has been marked as deleted from the API, ignore this update.
		$deleted = get_post_meta( $id, 'apple_news_api_deleted', true );
		if ( $deleted ) {
			return;
		}

		// Proceed based on the current settings for auto publish and update.
		$updated = get_post_meta( $id, 'apple_news_api_id', true );
		if ( $updated && 'yes' !== $this->settings->api_autosync_update
			|| ! $updated && 'yes' !== $this->settings->api_autosync ) {
			return;
		}

		/**
		 * Ability to override the autopublishing of posts on a per-post level.
		 *
		 * @param bool    $should_autopublish Flag if the post should autopublish.
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post Post object.
		 */
		$should_autopublish = (bool) apply_filters( 'apple_news_should_post_autopublish', true, $id, $post );

		// Bail if the filter returns false.
		if ( ! $should_autopublish ) {
			return;
		}

		// Proceed with the push.
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
	 * @param int $id The ID of the post being deleted.
	 * @access public
	 */
	public function do_delete( $id ) {
		$post = get_post( $id );
		if ( empty( $post->post_type )
			|| ! current_user_can( apply_filters( 'apple_news_delete_capability', Apple_News::get_capability_for_post_type( 'delete_posts', $post->post_type ) ) )
		) {
			return;
		}

		// If it does not have a remote API ID just ignore.
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
