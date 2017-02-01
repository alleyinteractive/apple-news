<?php
/**
 * Entry point for the admin side of the WP Plugin.
 *
 * @author  Federico Ramirez
 * @since   0.0.0
 */

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
	function __construct() {
		// Register hooks
		add_action( 'admin_print_styles-toplevel_page_apple_news_index', array( $this, 'plugin_styles' ) );

		// Admin_Settings builds the settings page for the plugin. Besides setting
		// it up, let's get the settings getter and setter object and save it into
		// $settings.
		$admin_settings = new Admin_Apple_Settings;
		self::$settings = $admin_settings->fetch_settings();

		// Initialize notice messaging utility
		new Admin_Apple_Notice( self::$settings );

		// Set up main page
		new Admin_Apple_Index_Page( self::$settings );

		// Set up all sub pages
		new Admin_Apple_Bulk_Export_Page( self::$settings );

		// Set up posts syncing if enabled in the settings
		new Admin_Apple_Post_Sync( self::$settings );

		// Set up the publish meta box if enabled in the settings
		new Admin_Apple_Meta_Boxes( self::$settings );

		// Set up asynchronous publishing features
		new Admin_Apple_Async( self::$settings );

		// Add section support
		new Admin_Apple_Sections( self::$settings );

		// Add theme support
		new Admin_Apple_Themes();

		// Add preview support
		new Admin_Apple_Preview();
	}

	/**
	 * Implements certain plugin styles inline.
	 */
	public function plugin_styles() {
		// Styles are tiny, for now just embed them.
		echo '<style type="text/css">';
		echo '.wp-list-table .column-sync { width: 15%; }';
		echo '.wp-list-table .column-updated_at { width: 15%; }';
		// Clipboard fix
		echo '.row-actions.is-active { visibility: visible }';
		echo '</style>';
	}

	/**
	 * Get post status.
	 *
	 * @param int $post_id
	 * @return string
	 */
	public static function get_post_status( $post_id ) {
		$key = 'apple_news_post_state_' . $post_id;
		if ( false === ( $state = get_transient( $key ) ) ) {
				// Get the state from the API.
				// If this causes an error, display that message instead of the state.
				try {
					$action = new Apple_Actions\Index\Get( self::$settings, $post_id );
					$state = $action->get_data( 'state', __( 'N/A', 'apple-news' ) );
				} catch ( \Apple_Push_API\Request\Request_Exception $e ) {
					$state = $e->getMessage();
				}

				$cache_expiration = ( 'LIVE' == $state || 'TAKEN_DOWN' == $state ) ? 3600 : 60;
				set_transient( $key, $state, apply_filters( 'apple_news_post_status_cache_expiration', $cache_expiration, $state ) );
		}

		return $state;
	}
}
