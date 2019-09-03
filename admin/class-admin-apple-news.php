<?php
/**
 * Entry point for the admin side of the WP Plugin.
 *
 * @author  Federico Ramirez
 * @since   0.0.0
 * @package Apple_News
 */

global $post;
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
// REST Includes.
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-clear-notifications.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-delete.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/REST/apple-news-get-notifications.php';
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
		add_action( 'admin_print_styles-toplevel_page_apple_news_index', array( $this, 'plugin_styles' ) );
		add_action( 'init', array( $this, 'action_init' ) );

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

		// Enhancements if the block editor is available.
		if ( apple_news_block_editor_is_active() ) {
			$post_types = self::$settings->post_types;

			// Define custom postmeta fields to register.
			$postmeta = [
				'apple_news_api_created_at'     => [],
				'apple_news_api_id'             => [],
				'apple_news_api_modified_at'    => [],
				'apple_news_api_revision'       => [],
				'apple_news_api_share_url'      => [],
				'apple_news_coverart'           => [
					'sanitize_callback' => 'apple_news_sanitize_coverart_data',
					'show_in_rest'      => [
						'prepare_callback' => 'apple_news_json_encode',
					],
				],
				'apple_news_is_hidden'          => [
					'type' => 'boolean',
				],
				'apple_news_is_paid'            => [
					'type' => 'boolean',
				],
				'apple_news_is_preview'         => [
					'type' => 'boolean',
				],
				'apple_news_is_sponsored'       => [
					'type' => 'boolean',
				],
				'apple_news_maturity_rating'    => [],
				'apple_news_pullquote'          => [],
				'apple_news_pullquote_position' => [],
				'apple_news_sections'           => [
					'sanitize_callback' => 'apple_news_sanitize_selected_sections',
					'show_in_rest'      => [
						'prepare_callback' => 'apple_news_json_encode',
					],
				],
			];

			// Loop over postmeta fields and register each.
			foreach ( $postmeta as $meta_key => $options ) {
				apple_news_register_meta_helper( 'post', $post_types, $meta_key, $options );
			}
		}
	}

	/**
	 * Returns an array of custom image sizes, indexed by key, with metadata.
	 *
	 * @access public
	 * @return array The array of custom image sizes.
	 */
	public static function get_image_sizes() {
		return array(
			'apple_news_ca_landscape_12_9' => array(
				'height'      => 1374,
				'label'       => __( 'iPad Pro (12.9 in): 1832 x 1374 px', 'apple-news' ),
				'orientation' => 'landscape',
				'type'        => 'coverArt',
				'width'       => 1832,
			),
			'apple_news_ca_landscape_9_7'  => array(
				'height'      => 1032,
				'label'       => __( 'iPad (7.9/9.7 in): 1376 x 1032 px', 'apple-news' ),
				'orientation' => 'landscape',
				'type'        => 'coverArt',
				'width'       => 1376,
			),
			'apple_news_ca_landscape_5_5'  => array(
				'height'      => 783,
				'label'       => __( 'iPhone (5.5 in): 1044 x 783 px', 'apple-news' ),
				'orientation' => 'landscape',
				'type'        => 'coverArt',
				'width'       => 1044,
			),
			'apple_news_ca_landscape_4_7'  => array(
				'height'      => 474,
				'label'       => __( 'iPhone (4.7 in): 632 x 474 px', 'apple-news' ),
				'orientation' => 'landscape',
				'type'        => 'coverArt',
				'width'       => 632,
			),
			'apple_news_ca_landscape_4_0'  => array(
				'height'      => 402,
				'label'       => __( 'iPhone (4 in): 536 x 402 px', 'apple-news' ),
				'orientation' => 'landscape',
				'type'        => 'coverArt',
				'width'       => 536,
			),
			'apple_news_ca_portrait_12_9'  => array(
				'height'      => 1496,
				'label'       => __( 'iPad Pro (12.9 in): 1122 x 1496 px', 'apple-news' ),
				'orientation' => 'portrait',
				'type'        => 'coverArt',
				'width'       => 1122,
			),
			'apple_news_ca_portrait_9_7'   => array(
				'height'      => 1120,
				'label'       => __( 'iPad (7.9/9.7 in): 840 x 1120 px', 'apple-news' ),
				'orientation' => 'portrait',
				'type'        => 'coverArt',
				'width'       => 840,
			),
			'apple_news_ca_portrait_5_5'   => array(
				'height'      => 916,
				'label'       => __( 'iPhone (5.5 in): 687 x 916 px', 'apple-news' ),
				'orientation' => 'portrait',
				'type'        => 'coverArt',
				'width'       => 687,
			),
			'apple_news_ca_portrait_4_7'   => array(
				'height'      => 552,
				'label'       => __( 'iPhone (4.7 in): 414 x 552 px', 'apple-news' ),
				'orientation' => 'portrait',
				'type'        => 'coverArt',
				'width'       => 414,
			),
			'apple_news_ca_portrait_4_0'   => array(
				'height'      => 472,
				'label'       => __( 'iPhone (4 in): 354 x 472 px', 'apple-news' ),
				'orientation' => 'portrait',
				'type'        => 'coverArt',
				'width'       => 354,
			),
			'apple_news_ca_square_12_9'    => array(
				'height'      => 1472,
				'label'       => __( 'iPad Pro (12.9 in): 1472 x 1472 px', 'apple-news' ),
				'orientation' => 'square',
				'type'        => 'coverArt',
				'width'       => 1472,
			),
			'apple_news_ca_square_9_7'     => array(
				'height'      => 1104,
				'label'       => __( 'iPad (7.9/9.7 in): 1104 x 1104 px', 'apple-news' ),
				'orientation' => 'square',
				'type'        => 'coverArt',
				'width'       => 1104,
			),
			'apple_news_ca_square_5_5'     => array(
				'height'      => 912,
				'label'       => __( 'iPhone (5.5 in): 912 x 912 px', 'apple-news' ),
				'orientation' => 'square',
				'type'        => 'coverArt',
				'width'       => 912,
			),
			'apple_news_ca_square_4_7'     => array(
				'height'      => 550,
				'label'       => __( 'iPhone (4.7 in): 550 x 550 px', 'apple-news' ),
				'orientation' => 'square',
				'type'        => 'coverArt',
				'width'       => 550,
			),
			'apple_news_ca_square_4_0'     => array(
				'height'      => 470,
				'label'       => __( 'iPhone (4 in): 470 x 470 px', 'apple-news' ),
				'orientation' => 'square',
				'type'        => 'coverArt',
				'width'       => 470,
			),
		);
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
	 * Actions to be run on the `init` action hook.
	 *
	 * @access public
	 */
	public function action_init() {

		// Register custom image crops.
		if ( 'yes' === self::$settings->enable_cover_art ) {
			$image_sizes = self::get_image_sizes();
			foreach ( $image_sizes as $name => $data ) {
				add_image_size( $name, $data['width'], $data['height'], true );
			}
		}
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
			set_transient( $key, $state, apply_filters( 'apple_news_post_status_cache_expiration', $cache_expiration, $state ) );
		}

		return $state;
	}
}
