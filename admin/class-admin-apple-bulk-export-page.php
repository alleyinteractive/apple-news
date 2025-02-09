<?php
/**
 * Publish to Apple News: Admin_Apple_Bulk_Export_Page class
 *
 * @package Apple_News
 */

// Include dependencies.
require_once __DIR__ . '/apple-actions/index/class-push.php';

/**
 * Bulk export page. Display progress on multiple articles export process.
 *
 * @since 0.6.0
 */
class Admin_Apple_Bulk_Export_Page extends Apple_News {

	/**
	 * Action used for nonces
	 *
	 * @var array
	 * @access private
	 */
	const ACTION = 'apple_news_push_post';

	/**
	 * Current plugin settings.
	 *
	 * @var array
	 * @access private
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param \Apple_Exporter\Settings $settings Settings in use during this run.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;

		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'wp_ajax_apple_news_push_post', [ $this, 'ajax_push_post' ] );
		add_action( 'wp_ajax_apple_news_delete_post', [ $this, 'ajax_delete_post' ] );
	}

	/**
	 * Registers the plugin submenu page.
	 *
	 * @access public
	 */
	public function register_page() {
		add_submenu_page(
			'options.php', // Passing options.php means it won't appear in any menu.
			__( 'Bulk Export', 'apple-news' ), // Page title.
			__( 'Bulk Export', 'apple-news' ), // Menu title.
			/**
			 * Filters the capability required to be able to access the Bulk Export page.
			 *
			 * @param string $capability The capability required to be able to perform bulk exports. Defaults to 'manage_options'.
			 */
			apply_filters( 'apple_news_bulk_export_capability', 'manage_options' ), // Capability.
			$this->plugin_slug . '_bulk_export', // Menu Slug.
			[ $this, 'build_page' ]         // Function.
		);
	}

	/**
	 * Builds the plugin submenu page.
	 *
	 * @access public
	 */
	public function build_page() {
		$post_ids = isset( $_GET['post_ids'] ) ? sanitize_text_field( wp_unslash( $_GET['post_ids'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action   = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $post_ids ) {
			wp_safe_redirect( esc_url_raw( menu_page_url( $this->plugin_slug . '_index', false ) ) ); // phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit

			if ( ! defined( 'APPLE_NEWS_UNIT_TESTS' ) || ! APPLE_NEWS_UNIT_TESTS ) {
				exit;
			}
		}

		// Allow only specific actions.
		if ( ! in_array( $action, [ 'apple_news_push_post', 'apple_news_delete_post' ], true ) ) {
			wp_die( esc_html__( 'Invalid action.', 'apple-news' ), '', [ 'response' => 400 ] );
		}

		// Populate articles array with a set of valid posts.
		$apple_posts = [];
		foreach ( explode( ',', $post_ids ) as $post_id ) {
			$post = get_post( (int) $post_id );

			if ( ! empty( $post ) ) {
				$apple_posts[] = $post;
			}
		}

		// Override text within the partial depending on the action.
		$apple_page_title = match ( $action ) {
			'apple_news_push_post' => __( 'Bulk Export Articles', 'apple-news' ),
			'apple_news_delete_post' => __( 'Bulk Delete Articles', 'apple-news' ),
		};
		$apple_page_description = match ( $action ) {
			'apple_news_push_post' => __( 'The following articles will be exported. Articles that are already published will be updated.', 'apple-news' ),
			'apple_news_delete_post' => __( 'The following articles will be deleted.', 'apple-news' ),
		};
		$apple_submit_text = match ( $action ) {
			'apple_news_push_post' => __( 'Publish All', 'apple-news' ),
			'apple_news_delete_post' => __( 'Delete All', 'apple-news' ),
		};

		require_once __DIR__ . '/partials/page-bulk-export.php';
	}

	/**
	 * Handles the ajax action to push a post to Apple News.
	 *
	 * @access public
	 */
	public function ajax_push_post() {
		// Check the nonce.
		check_ajax_referer( self::ACTION );

		// Sanitize input data.
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected

		// Ensure the post exists and that it's published.
		$post = get_post( $id );
		if ( empty( $post ) ) {
			wp_send_json_error( __( 'This post no longer exists.', 'apple-news' ) );
		}

		// Check capabilities.
		if ( ! current_user_can(
			/** This filter is documented in admin/class-admin-apple-post-sync.php */
			apply_filters( 'apple_news_publish_capability', self::get_capability_for_post_type( 'publish_posts', $post->post_type ) )
		) ) {
			wp_send_json_error( __( 'You do not have permission to publish to Apple News', 'apple-news' ) );
		}

		if ( 'publish' !== $post->post_status ) {
			wp_send_json_error(
				sprintf(
					/* translators: %s: post ID */
					__( 'Article %s is not published and cannot be pushed to Apple News.', 'apple-news' ),
					$id
				)
			);
		}

		$action = new Apple_Actions\Index\Push( $this->settings, $id );
		try {
			$errors = $action->perform();
		} catch ( \Apple_Actions\Action_Exception $e ) {
			$errors = $e->getMessage();
		}

		if ( $errors ) {
			wp_send_json_error( $errors );
		}

		wp_send_json_success();
	}

	/**
	 * Handles the ajax action to delete a post from Apple News.
	 *
	 * @access public
	 */
	public function ajax_delete_post() {
		// Check the nonce.
		check_ajax_referer( self::ACTION );

		// Sanitize input data.
		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : -1;

		$post = get_post( $id );

		if ( empty( $post ) ) {
			wp_send_json_error( __( 'This post no longer exists.', 'apple-news' ) );
		}

		/** This filter is documented in admin/class-admin-apple-post-sync.php */
		$cap = apply_filters( 'apple_news_delete_capability', self::get_capability_for_post_type( 'delete_posts', $post->post_type ) );

		// Check capabilities.
		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error( __( 'You do not have permission to delete posts from Apple News', 'apple-news' ) );
		}

		$errors = null;

		// Try to sync only if the post has a remote ID. Ref `Admin_Apple_Post_Sync::do_delete()`.
		if ( get_post_meta( $id, 'apple_news_api_id', true ) ) {
			$action = new Apple_Actions\Index\Delete( $this->settings, $id );

			try {
				$errors = $action->perform();
			} catch ( Apple_Actions\Action_Exception $e ) {
				$errors = $e->getMessage();
			}
		}

		if ( $errors ) {
			wp_send_json_error( $errors );
		}

		wp_send_json_success();
	}

	/**
	 * Registers assets used by the bulk export process.
	 *
	 * @param string $hook The context under which this function is called.
	 * @access public
	 */
	public function register_assets( $hook ) {
		if ( 'admin_page_apple_news_bulk_export' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_slug . '_bulk_export_css',
			plugin_dir_url( __FILE__ ) . '../assets/css/bulk-export.css',
			[],
			self::$version
		);
		wp_enqueue_script(
			$this->plugin_slug . '_bulk_export_js',
			plugin_dir_url( __FILE__ ) . '../assets/js/bulk-export.js',
			[ 'jquery' ],
			self::$version,
			true
		);
	}
}
