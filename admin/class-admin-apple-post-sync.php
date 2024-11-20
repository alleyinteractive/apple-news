<?php
/**
 * Publish to Apple News: Admin_Apple_Post_Sync class
 *
 * @package Apple_News
 */

// Include dependencies.
require_once __DIR__ . '/apple-actions/index/class-push.php';
require_once __DIR__ . '/apple-actions/index/class-delete.php';

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
			// Fork for new behavior in WP 5.6 vs. old behavior.
			if ( function_exists( 'wp_after_insert_post' ) ) {
				add_action( 'wp_after_insert_post', [ $this, 'do_publish' ], 10, 2 );
			} else {
				add_action( 'save_post', [ $this, 'do_publish' ], 99, 2 );
			}
		}

		// Register delete hook if needed.
		if ( 'yes' === $this->settings->get( 'api_autosync_delete' ) ) {
			add_action( 'before_delete_post', [ $this, 'do_delete' ] );
		}

		// Optionally take certain actions on post status transition.
		add_action( 'transition_post_status', [ $this, 'action__transition_post_status' ], 10, 3 );
	}

	/**
	 * A callback function for the transition_post_status action hook.
	 *
	 * @param string  $new_status The new post status after the transition.
	 * @param string  $old_status The previous post status.
	 * @param WP_Post $post       The post object being transitioned.
	 */
	public function action__transition_post_status( $new_status, $old_status, $post ) {
		/**
		 * Determines whether to delete an article via the Apple News API if it is
		 * moved from publish status to the trash in WordPress.
		 *
		 * @since 2.3.3
		 *
		 * @param bool $should_delete Whether the post should be deleted via the Apple News API or not.
		 * @param int  $post_id       The ID of the post that was moved to the trash.
		 */
		$delete_on_trash = apply_filters(
			'apple_news_should_post_delete_on_trash',
			'yes' === $this->settings->api_autosync_trash,
			$post->ID
		);

		/**
		 * Determines whether to delete an article via the Apple News API if it is
		 * unpublished in WordPress (defined as moving a post from the `publish`
		 * status to any other status, including `trash`).
		 *
		 * @since 2.3.3
		 *
		 * @param bool $should_delete Whether the post should be deleted via the Apple News API or not.
		 * @param int  $post_id       The ID of the post that was unpublished.
		 */
		$delete_on_unpublish = apply_filters(
			'apple_news_should_post_delete_on_unpublish',
			'yes' === $this->settings->api_autosync_unpublish,
			$post->ID
		);

		// Determine whether to delete the article via the API.
		if ( $old_status !== $new_status
			&& 'publish' === $old_status
			&& ( $delete_on_unpublish
				|| ( 'trash' === $new_status
					&& $delete_on_trash
				)
			)
		) {
			$this->do_delete( $post->ID );
		}
	}

	/**
	 * When a post is published, or a published post updated, trigger this function.
	 *
	 * @since 0.4.0
	 *
	 * @param int     $id   The ID of the post being updated.
	 * @param WP_Post $post The post object being updated.
	 */
	public function do_publish( $id, $post ) {
		if ( 'publish' !== $post->post_status
			|| ! in_array( $post->post_type, $this->settings->post_types, true )
			|| (
				! current_user_can(
					/**
					 * Filters the publish capability required to publish posts to Apple News.
					 *
					 * @param string $capability The capability required to publish posts to Apple News. Defaults to 'publish_posts', or the equivalent for the post type.
					 */
					apply_filters( 'apple_news_publish_capability', Apple_News::get_capability_for_post_type( 'publish_posts', $post->post_type ) )
				) && ! ( defined( 'DOING_CRON' ) && DOING_CRON )
			)
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
		if ( ( $updated && 'yes' !== $this->settings->api_autosync_update )
			|| ( ! $updated && 'yes' !== $this->settings->api_autosync ) ) {
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
			|| ! current_user_can(
				/**
				 * Filters the delete capability required to delete posts from Apple News.
				 *
				 * @param string $capability The capability required to delete posts from Apple News. Defaults to 'delete_posts', or the equivalent for the post type.
				 */
				apply_filters( 'apple_news_delete_capability', Apple_News::get_capability_for_post_type( 'delete_posts', $post->post_type ) )
			)
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
