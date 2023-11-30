<?php
/**
 * Entry point for the admin side of the WP Plugin.
 *
 * @author  Federico Ramirez
 * @since   0.0.0
 * @package Apple_News
 */

// Include dependencies.
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-post-sync.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-index-page.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-bulk-export-page.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-notice.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-meta-boxes.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-async.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-sections.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-themes.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-preview.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-json.php';
require_once plugin_dir_path( __FILE__ ) . 'class-automation.php';

// REST Includes.
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-delete.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-get-published-state.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-get-settings.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-modify-post.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-publish.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-sections.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-update.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-user-can-publish.php';

/**
 * Entry-point class for the plugin.
 */
class Admin_Apple_News extends Apple_News {

	/**
	 * Current settings.
	 *
	 * @var Settings
	 */
	public static $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Register hooks.
		add_action( 'admin_print_styles-toplevel_page_apple_news_index', [ $this, 'plugin_styles' ] );

		/**
		 * Admin_Settings builds the settings page for the plugin. Besides setting
		 * it up, let's get the settings getter and setter object and save it into
		 * $settings.
		 */
		$admin_settings = new Admin_Apple_Settings();
		self::$settings = $admin_settings->fetch_settings();

		// Initialize notice messaging utility.
		add_action( 'admin_enqueue_scripts', 'Admin_Apple_Notice::register_assets' );
		add_action( 'admin_notices', 'Admin_Apple_Notice::show' );
		add_action( 'wp_ajax_apple_news_dismiss_notice', 'Admin_Apple_Notice::wp_ajax_dismiss_notice' );

		// Set up main page.
		new Admin_Apple_Index_Page( self::$settings );

		// Set up all sub pages.
		new Admin_Apple_Bulk_Export_Page( self::$settings );

		// Set up posts syncing if enabled in the settings.
		new Admin_Apple_Post_Sync( self::$settings );

		// Set up the publish meta box if enabled in the settings.
		new Admin_Apple_Meta_Boxes( self::$settings );

		// Set up asynchronous publishing features.
		new Admin_Apple_Async( self::$settings );

		// Add section support.
		new Admin_Apple_Sections( self::$settings );

		// Add theme support.
		new Admin_Apple_Themes();

		// Add preview support.
		new Admin_Apple_Preview();

		// Add JSON customization support.
		new Admin_Apple_JSON();

		// Add automation support.
		Apple_News\Admin\Automation::init();

		// Enhancements if the block editor is available.
		if ( apple_news_block_editor_is_active() ) {
			$post_types = self::$settings->post_types;

			// Define custom postmeta fields to register.
			$postmeta = [
				'apple_news_api_created_at'      => [
					'default' => '',
				],
				'apple_news_api_id'              => [
					'default' => '',
				],
				'apple_news_api_modified_at'     => [
					'default' => '',
				],
				'apple_news_api_revision'        => [
					'default' => '',
				],
				'apple_news_api_share_url'       => [
					'default' => '',
				],
				'apple_news_coverimage'          => [
					'default' => 0,
					'type'    => 'integer',
				],
				'apple_news_coverimage_caption'  => [
					'default' => '',
				],
				'apple_news_is_hidden'           => [
					'default' => false,
					'type'    => 'boolean',
				],
				'apple_news_is_paid'             => [
					'default' => false,
					'type'    => 'boolean',
				],
				'apple_news_is_preview'          => [
					'default' => false,
					'type'    => 'boolean',
				],
				'apple_news_is_sponsored'        => [
					'default' => false,
					'type'    => 'boolean',
				],
				'apple_news_maturity_rating'     => [
					'default' => '',
				],
				'apple_news_metadata'            => [
					'default'           => '',
					'sanitize_callback' => function ( $value ) {
						return ! empty( $value ) && is_string( $value ) ? json_decode( $value, true ) : $value;
					},
					'show_in_rest'      => [
						'prepare_callback' => 'apple_news_json_encode',
					],
				],
				'apple_news_pullquote'           => [
					'default' => '',
				],
				'apple_news_pullquote_position'  => [
					'default' => '',
				],
				'apple_news_slug'                => [
					'default' => '',
				],
				'apple_news_sections'            => [
					'default'           => '',
					'sanitize_callback' => 'apple_news_sanitize_selected_sections',
					'show_in_rest'      => [
						'prepare_callback' => 'apple_news_json_encode',
					],
				],
				'apple_news_suppress_video_url'  => [
					'default' => false,
					'type'    => 'boolean',
				],
				'apple_news_use_image_component' => [
					'default' => false,
					'type'    => 'boolean',
				],
			];

			// Loop over postmeta fields and register each.
			foreach ( $postmeta as $meta_key => $options ) {
				apple_news_register_meta_helper( 'post', $post_types, $meta_key, $options );
			}

			// Prevent Yoast Duplicate Post plugin from cloning apple_news meta.
			add_filter(
				'duplicate_post_meta_keys_filter',
				function ( $meta_keys ) {
					return is_array( $meta_keys ) ?
					array_filter(
						$meta_keys,
						function ( $key ) {
							return substr( $key, 0, 11 ) !== 'apple_news_';
						}
					)
					: $meta_keys;
				} 
			);

			add_action(
				'rest_api_init',
				function () {
					$post_types = ! empty( self::$settings->post_types ) ? self::$settings->post_types : [];

					foreach ( $post_types as $post_type ) {
						register_rest_field(
							$post_type,
							'apple_news_notices',
							[
								'get_callback' => [ 'Admin_Apple_Notice', 'get_if_allowed' ],
							]
						);
					}
				}
			);

			// Loop over registered post types and add a callback for removing protected Apple News meta.
			if ( ! empty( self::$settings->post_types ) && is_array( self::$settings->post_types ) ) {
				foreach ( self::$settings->post_types as $post_type ) {
					add_action(
						'rest_insert_' . $post_type,
						[ $this, 'action_rest_insert_post' ],
						10,
						2
					);
				}
			}
		}
	}

	/**
	 * A function to display an error message.
	 *
	 * @param string $message The message to display.
	 *
	 * @since 1.2.5
	 * @access public
	 */
	public static function show_error( $message ) {
		if ( apple_news_block_editor_is_active_for_post() ) {
			Admin_Apple_Notice::error( $message );
		} else {
			echo '<div class="apple-news-notice apple-news-notice-error" role="alert"><p>'
				. esc_html( $message )
				. '</p></div>';
		}
	}

	/**
	 * A callback function for the rest_insert_{$this->post_type} action hook.
	 *
	 * @param WP_Post         $post    Inserted or updated post object.
	 * @param WP_REST_Request $request Request object.
	 */
	public function action_rest_insert_post( $post, $request ) {
		// Try to get the meta param.
		$meta = $request->get_param( 'meta' );
		if ( empty( $meta ) || ! is_array( $meta ) ) {
			return;
		}

		// Re-construct meta, removing protected keys.
		$new_meta = [];
		foreach ( $meta as $key => $value ) {
			if ( false === strpos( $key, 'apple_news_api_' ) ) {
				$new_meta[ $key ] = $value;
			}
		}

		// Overwrite the meta property with the new value.
		$request->set_param( 'meta', $new_meta );
	}

	/**
	 * Implements certain plugin styles inline.
	 *
	 * @access public
	 */
	public function plugin_styles() {
		// Styles are tiny, for now just embed them.
		echo '<style type="text/css">';
		echo '.wp-list-table .column-sync { width: 15%; }';
		echo '.wp-list-table .column-updated_at { width: 15%; }';
		// Clipboard fix.
		echo '.row-actions.is-active { visibility: visible }';
		echo '</style>';
	}

	/**
	 * Get post status.
	 *
	 * @param int $post_id The ID of the post to look up.
	 * @return string
	 */
	public static function get_post_status( $post_id ) {
		$key   = 'apple_news_post_state_' . $post_id;
		$state = get_transient( $key );
		if ( false === $state ) {
			// Get the state from the API.
			// If this causes an error, display that message instead of the state.
			try {
				$action = new Apple_Actions\Index\Get( self::$settings, $post_id );
				$state  = $action->get_data( 'state', __( 'N/A', 'apple-news' ) );
			} catch ( \Apple_Push_API\Request\Request_Exception $e ) {
				$state = $e->getMessage();
			}

			$cache_expiration = ( 'LIVE' === $state || 'TAKEN_DOWN' === $state ) ? 3600 : 60;

			/**
			 * Filters the cache lifetime for API responses.
			 *
			 * Most responses are cached to avoid repeatedly hitting the API, which
			 * would slow down your admin dashboard. Different statuses are cached
			 * for different times since some are more likely to change quickly than
			 * others.
			 *
			 * @param int    $expiration The current cache lifetime.
			 * @param string $state      The current Apple News API status for the post.
			 */
			set_transient( $key, $state, apply_filters( 'apple_news_post_status_cache_expiration', $cache_expiration, $state ) );
		}

		return $state;
	}
}
