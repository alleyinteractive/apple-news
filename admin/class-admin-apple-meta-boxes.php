<?php

/**
 * This class provides a meta box to publish posts to Apple News from the edit screen.
 *
 * @since 0.9.0
 */
class Admin_Apple_Meta_Boxes extends Apple_News {

	/**
	 * Current settings.
	 *
	 * @since 0.9.0
	 * @var array
	 * @access private
	 */
	private $settings;

	/**
	 * Publish action.
	 *
	 * @since 0.9.0
	 * @var array
	 * @access private
	 */
	private $publish_action = 'apple_news_publish';

	/**
	 * Constructor.
	 */
	function __construct( $settings = null ) {
		$this->settings = $settings;

		// Register hooks if enabled
		if ( 'yes' == $settings->get( 'show_metabox' ) ) {
			// Handle a publish action on save.
			// However, if auto sync is enabled, don't bother.
			if ( 'yes' != $settings->get( 'api_autosync' ) ) {
				add_action( 'save_post', array( $this, 'do_publish' ), 10, 2 );
			}

			// Add the custom meta boxes to each post type
			$post_types = $settings->get( 'post_types' );
			if ( ! is_array( $post_types ) ) {
				$post_types = array( $post_types );
			}

			foreach ( $post_types as $post_type ) {
				add_action( 'add_meta_boxes_' . $post_type, array( $this, 'add_meta_boxes' ) );
			}

			// Register assets used by the meta box
			add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		}
	}

	/**
	 * Check for a publish action from the meta box.
	 *
	 * @since 0.9.0
	 * @param int $post_id
	 * @param WP_Post $post
	 * @access public
	 */
	public function do_publish( $post_id, $post ) {
		// Check if the values we want are present in $_REQUEST params.
		if ( empty( $_POST['apple_news_publish_action'] )
			|| empty( $_POST['apple_news_publish_nonce'] )
			|| empty( $_POST['post_ID'] ) ) {
			return;
		}

		// Check the nonce
		if ( ! wp_verify_nonce( $_POST['apple_news_publish_nonce'], $this->publish_action ) ) {
			return;
		}

		// Do the publish
		$post_sync = new Admin_Apple_Post_Sync( $this->settings );
		$post_sync->do_publish( $post_id, $post, 'manual' );
	}

	/**
	 * Add the Apple News meta boxes
	 *
	 * @since 0.9.0
	 * @param WP_Post $post
	 * @access public
	 */
	public function add_meta_boxes( $post ) {
		// Only add if this post is published
		if ( 'auto-draft' == $post->post_status ) {
			return;
		}

		// Add the publish meta box
		add_meta_box(
			'apple_news_publish',
			__( 'Apple News', 'apple-news' ),
			array( $this, 'publish_meta_box' ),
			$post->post_type,
			apply_filters( 'apple_news_publish_meta_box_context', 'side' ),
			apply_filters( 'apple_news_publish_meta_box_priority', 'high' )
		);
	}

	/**
	 * Add the Apple News publish meta box
	 *
	 * @since 0.9.0
	 * @param WP_Post $post
	 * @access public
	 */
	public function publish_meta_box( $post ) {
		// Only show the publish feature if the user is authorized and auto sync is not enabled.
		// Also check if the post has been previously published and/or deleted.
		$api_id = get_post_meta( $post->ID, 'apple_news_api_id', true );
		$deleted = get_post_meta( $post->ID, 'apple_news_api_deleted', true );
		$pending = get_post_meta( $post->ID, 'apple_news_api_pending', true );

		if ( 'yes' != $this->settings->get( 'api_autosync' )
			&& current_user_can( apply_filters( 'apple_news_publish_capability', 'manage_options' ) )
			&& empty( $api_id )
			&& empty( $deleted )
			&& empty( $pending ) ):
		?>
		<p><?php esc_html_e( 'Click the button below to publish this article to Apple News', 'apple-news' ); ?></p>
		<div id="apple-news-publish">
		<input type="hidden" id="apple-news-publish-action" name="apple_news_publish_action" value="">
		<input type="hidden" id="apple-news-publish-nonce" name="apple_news_publish_nonce" value="<?php echo esc_attr( wp_create_nonce( $this->publish_action ) ) ?>" >
		<input type="button" id="apple-news-publish-submit" name="apple_news_publish_submit" value="<?php esc_attr_e( 'Publish to Apple News', 'apple-news' ) ?>" class="button-primary" />
		</div>
		<?php
		elseif ( 'yes' == $this->settings->get( 'api_autosync' )
			&& empty( $api_id )
			&& empty( $deleted )
			&& empty( $pending ) ):
		?>
		<p><?php esc_html_e( 'This post will be automatically sent to Apple News on publish.', 'apple-news' ); ?></p>
		<?php
		elseif ( 'yes' == $this->settings->get( 'api_async' )
			&& ! empty( $pending ) ):
		?>
		<p><?php esc_html_e( 'This post is currently pending publishing to Apple News.', 'apple-news' ); ?></p>
		<?php
		endif;

		// Add data about the article if it exists
		if ( ! empty( $deleted ) ) {
			?>
			<p><b><?php esc_html_e( 'This post has been deleted from Apple News', 'apple-news' ) ?></b></p>
			<?php
		}

		if ( ! empty( $api_id ) ) {
			$share_url = get_post_meta( $post->ID, 'apple_news_api_share_url', true );
			$created_at = get_post_meta( $post->ID, 'apple_news_api_created_at', true );
			$created_at = empty( $created_at ) ? __( 'None', 'apple-news' ) : get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $created_at ) ), 'F j, h:i a' );
			$modified_at = get_post_meta( $post->ID, 'apple_news_api_modified_at', true );
			$modified_at = empty( $modified_at ) ? __( 'None', 'apple-news' ) : get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $modified_at ) ), 'F j, h:i a' );
			?>
			<p><b><?php esc_html_e( 'Apple News Publish Information', 'apple-news' ) ?></b>
			<br/><?php esc_html_e( 'ID', 'apple-news' ) ?>: <?php echo esc_html( $api_id ) ?>
			<br/><?php esc_html_e( 'Created at', 'apple-news' ) ?>: <?php echo esc_html( $created_at ) ?>
			<br/><?php esc_html_e( 'Modified at', 'apple-news' ) ?>: <?php echo esc_html( $modified_at ) ?>
			<br/><?php esc_html_e( 'Share URL', 'apple-news' ) ?>: <a href="<?php echo esc_url( $share_url ) ?>" target="_blank"><?php echo esc_html( $share_url ) ?></a>
			<br/><?php esc_html_e( 'Revision', 'apple-news' ) ?>: <?php echo esc_html( get_post_meta( $post->ID, 'apple_news_api_revision', true ) ) ?>
			<?php
		}

	}

	/**
	 * Registers assets used by meta boxes.
	 *
	 * @param string $hook
	 * @access public
	 */
	public function register_assets( $hook ) {
		if ( 'post.php' != $hook ) {
			return;
		}

		wp_enqueue_script( $this->plugin_slug . '_meta_boxes_js', plugin_dir_url(
			__FILE__ ) .  '../assets/js/meta-boxes.js', array( 'jquery' ),
			$this->version, true );

		// Localize the JS file for handling frontend actions.
		wp_localize_script( $this->plugin_slug . '_meta_boxes_js', 'apple_news_meta_boxes', array(
			'publish_action' => $this->publish_action,
		) );

	}

}
