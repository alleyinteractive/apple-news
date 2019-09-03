<?php
/**
 * Entry point for the admin side of the WP Plugin.
 *
 * @author  Federico Ramirez
 * @since   0.6.0
 * @package Apple_News
 */

// Include dependencies.
require_once plugin_dir_path( __FILE__ ) . 'apple-actions/index/class-get.php';
require_once plugin_dir_path( __FILE__ ) . 'apple-actions/index/class-push.php';
require_once plugin_dir_path( __FILE__ ) . 'apple-actions/index/class-delete.php';
require_once plugin_dir_path( __FILE__ ) . 'apple-actions/index/class-export.php';
require_once plugin_dir_path( __FILE__ ) . 'apple-actions/index/class-section.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-news-list-table.php';

use \Apple_Exporter\Workspace as Workspace;

/**
 * A class to manage the index page of the Apple News admin interface.
 *
 * @package Apple_News
 */
class Admin_Apple_Index_Page extends Apple_News {

	/**
	 * Current plugin settings.
	 *
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
	 *
	 * @param \Apple_Exporter\Settings $settings Settings in use during this run.
	 * @access public
	 */
	public function __construct( $settings ) {
		parent::__construct();
		$this->settings = $settings;

		// Handle routing to various admin pages.
		add_action( 'admin_init', array( $this, 'page_router' ) );
		add_action( 'admin_menu', array( $this, 'setup_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'setup_assets' ) );
	}



	/**
	 * Sets up the admin page.
	 *
	 * @access public
	 */
	public function setup_admin_page() {
		// Set up main page. This page reads parameters and handles actions.
		// accordingly.
		add_menu_page(
			__( 'Apple News', 'apple-news' ),
			__( 'Apple News', 'apple-news' ),
			apply_filters( 'apple_news_list_capability', 'manage_options' ),
			$this->plugin_slug . '_index',
			array( $this, 'admin_page' ),
			'dashicons-format-aside'
		);

		add_submenu_page(
			$this->plugin_slug . '_index',
			__( 'Articles', 'apple-news' ),
			__( 'Articles', 'apple-news' ),
			apply_filters( 'apple_news_list_capability', 'manage_options' ),
			$this->plugin_slug . '_index',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Decide which template to load for the Apple News admin page
	 *
	 * @access public
	 */
	public function admin_page() {
		$id     = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : null; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : null; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended

		switch ( $action ) {
			case self::namespace_action( 'push' ):
				$section = new Apple_Actions\Index\Section( $this->settings );
				try {
					$sections = $section->get_sections();
				} catch ( Apple_Actions\Action_Exception $e ) {
					Admin_Apple_Notice::error( $e->getMessage() );
				}

				$post             = get_post( $id );
				$post_meta        = get_post_meta( $id );
				$enable_cover_art = ( 'yes' === $this->settings->enable_cover_art );
				include plugin_dir_path( __FILE__ ) . 'partials/page-single-push.php';
				break;
			default:
				$this->show_post_list_action();
				break;
		}
	}

	/**
	 * Sets up all pages used in the plugin's admin page. Associate each route
	 * with an action. Actions are methods that end with "_action" and must
	 * perform a task and output HTML with the result.
	 *
	 * @todo Regarding this class doing too much, maybe split all actions into their own class.
	 *
	 * @since 0.4.0
	 * @access public
	 * @return mixed The result of the requested action.
	 */
	public function page_router() {
		$id      = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : null; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
		$action  = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : null; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
		$action2 = isset( $_GET['action2'] ) ? sanitize_text_field( wp_unslash( $_GET['action2'] ) ) : null; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended

		// Allow for bulk actions from top or bottom.
		if ( ( empty( $action ) || '-1' === $action ) && ! empty( $action2 ) ) {
			$action = $action2;
		}

		// Given an action and ID, map the attributes to corresponding actions.
		switch ( $action ) {
			case self::namespace_action( 'export' ):
				return $this->export_action( $id );
			case self::namespace_action( 'reset' ):
				return $this->reset_action( $id );
			case self::namespace_action( 'push' ): // phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
				if ( ! $id ) {
					$url = menu_page_url( $this->plugin_slug . '_bulk_export', false );
					if ( isset( $_GET['article'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
						$ids  = is_array( $_GET['article'] ) ? array_map( 'absint', $_GET['article'] ) : absint( $_GET['article'] ); //  phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
						$url .= '&ids=' . implode( '.', $ids );
					}
					wp_safe_redirect( esc_url_raw( $url ) );
					if ( ! defined( 'APPLE_NEWS_UNIT_TESTS' ) || ! APPLE_NEWS_UNIT_TESTS ) {
						exit;
					}
				} else {
					return $this->push_action( $id );
				}
			case self::namespace_action( 'delete' ):
				return $this->delete_action( $id );
		}
	}

	/**
	 * Shows a success message.
	 *
	 * @param string $message The message to show.
	 * @access public
	 */
	private function notice_success( $message ) {
		Admin_Apple_Notice::success( $message );
		$this->do_redirect();
	}

	/**
	 * Shows an error message.
	 *
	 * @param string $message The message to show.
	 * @access public
	 */
	private function notice_error( $message ) {
		Admin_Apple_Notice::error( $message );
		$this->do_redirect();
	}

	/**
	 * Performs the redirect after an action is complete.
	 *
	 * @access public
	 */
	private function do_redirect() {
		// Perform the redirect.
		wp_safe_redirect( esc_url_raw( self::action_query_params( '', menu_page_url( $this->plugin_slug . '_index', false ) ) ) );
		if ( ! defined( 'APPLE_NEWS_UNIT_TESTS' ) || ! APPLE_NEWS_UNIT_TESTS ) {
			exit;
		}
	}

	/**
	 * Adds a namespace to all actions
	 *
	 * @param string $action The action to be performed.
	 * @access public
	 * @return string The name of the action, namespaced.
	 */
	public static function namespace_action( $action ) {
		return 'apple_news_' . $action;
	}

	/**
	 * Helps build query params for each row action.
	 *
	 * @param string $action The action to be performed.
	 * @param string $url    The URL to be augmented with the action.
	 * @access public
	 * @return string The URL with query args added.
	 */
	public static function action_query_params( $action, $url ) {
		// Set the keys we need to pay attention to.
		$keys = array(
			'apple_news_publish_status',
			'apple_news_date_from',
			'apple_news_date_to',
			's',
			'paged',
		);

		// Start the params.
		$params = array();
		if ( ! empty( $action ) ) {
			$params['action'] = self::namespace_action( $action );
		}

		// Add the other params.
		foreach ( $keys as $key ) {
			if ( ! empty( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
				$params[ $key ] = rawurlencode( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) ); //  phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
			}
		}

		// Add to the action URL.
		return add_query_arg( $params, $url );
	}

	/**
	 * Gets a setting by name which was loaded from WordPress options.
	 *
	 * @since 0.4.0
	 * @param string $name The name of the setting to look up.
	 * @access private
	 * @return mixed The requested setting value.
	 */
	private function get_setting( $name ) {
		return $this->settings->get( $name );
	}

	/**
	 * Downloads the JSON file for troubleshooting purposes.
	 *
	 * @param string $json The JSON to be exported.
	 * @param int    $id   The ID of the article being exported.
	 * @access private
	 */
	private function download_json( $json, $id ) {
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="article-' . absint( $id ) . '.json"' );
		echo wp_json_encode( json_decode( $json ) );
		die();
	}

	/**
	 * Sets up admin assets.
	 *
	 * @param string $hook The context under which this function was called.
	 * @access public
	 */
	public function setup_assets( $hook ) {
		if ( 'toplevel_page_apple_news_index' !== $hook ) {
			return;
		}

		// Enable jQuery datepicker for the export table date filter.
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// Add the export table script and style.
		wp_enqueue_style(
			$this->plugin_slug . '_export_table_css',
			plugin_dir_url( __FILE__ ) . '../assets/css/export-table.css',
			array(),
			self::$version
		);
		wp_enqueue_script(
			$this->plugin_slug . '_export_table_js',
			plugin_dir_url( __FILE__ ) . '../assets/js/export-table.js',
			array( 'jquery', 'jquery-ui-datepicker' ),
			self::$version,
			true
		);
		wp_enqueue_script(
			$this->plugin_slug . '_single_push_js',
			plugin_dir_url( __FILE__ ) . '../assets/js/single-push.js',
			array( 'jquery' ),
			self::$version,
			true
		);

		// Localize strings.
		wp_localize_script(
			$this->plugin_slug . '_export_table_js',
			'apple_news_export_table',
			array(
				'reset_confirmation'  => __( "Are you sure you want to reset status? Please only proceed if you're certain the post is stuck or this could reset in duplicate posts in Apple News.", 'apple-news' ),
				'delete_confirmation' => __( 'Are you sure you want to delete this post from Apple News?', 'apple-news' ),
			)
		);
	}

	/**
	 * Shows the list of articles available for publishing to Apple News.
	 *
	 * @access public
	 */
	public function show_post_list_action() {
		$table = new Admin_Apple_News_List_Table( $this->settings );
		$table->prepare_items();
		include plugin_dir_path( __FILE__ ) . 'partials/page-index.php';
	}

	/**
	 * Handles an export action.
	 *
	 * @param int $id The ID of the post being exported.
	 * @access public
	 */
	public function export_action( $id ) {
		$export = new Apple_Actions\Index\Export(
			$this->settings,
			$id,
			\Admin_Apple_Sections::get_sections_for_post( $id )
		);

		try {
			$json = $export->perform();
			$this->download_json( $json, $id );
		} catch ( Apple_Actions\Action_Exception $e ) {
			$this->notice_error( $e->getMessage() );
		}
	}

	/**
	 * Handles a push to Apple News action.
	 *
	 * @param int $id The ID of the post being pushed.
	 * @access private
	 */
	private function push_action( $id ) {
		// Ensure the post is published.
		if ( 'publish' !== get_post_status( $id ) ) {
			$this->notice_error(
				sprintf(
					// translators: token is the post ID.
					__( 'Article %s is not published and cannot be pushed to Apple News.', 'apple-news' ),
					$id
				)
			);
			return;
		}

		// Save fields.
		\Admin_Apple_Meta_Boxes::save_post_meta( $id );

		$message = __( 'Settings saved.', 'apple-news' );

		// Push the post.
		$action = new Apple_Actions\Index\Push( $this->settings, $id );
		try {
			$action->perform();

			// In async mode, success or failure will be displayed later.
			if ( 'yes' !== $this->settings->get( 'api_async' ) ) {
				$this->notice_success( __( 'Your article has been pushed successfully!', 'apple-news' ) );
			} else {
				$this->notice_success( __( 'Your article will be pushed shortly.', 'apple-news' ) );
			}
		} catch ( Apple_Actions\Action_Exception $e ) {
			$this->notice_error( $e->getMessage() );
		}
	}

	/**
	 * Handles a delete from Apple News action.
	 *
	 * @param int $id The ID of the post being deleted.
	 * @access private
	 */
	private function delete_action( $id ) {
		$action = new Apple_Actions\Index\Delete( $this->settings, $id );
		try {
			$action->perform();
			$this->notice_success( __( 'Your article has been removed from apple news.', 'apple-news' ) );
		} catch ( Apple_Actions\Action_Exception $e ) {
			$this->notice_error( $e->getMessage() );
		}
	}

	/**
	 * Handles a reset action.
	 *
	 * @param int $id The ID of the post being reset.
	 * @access private
	 */
	private function reset_action( $id ) {
		// Remove the pending designation if it exists.
		delete_post_meta( $id, 'apple_news_api_pending' );

		// Remove the async in progress flag.
		delete_post_meta( $id, 'apple_news_api_async_in_progress' );

		// Manually clean the workspace.
		$workspace = new Workspace( $id );
		$workspace->clean_up();

		// This can only succeed.
		$this->notice_success( __( 'Your article status has been successfully reset!', 'apple-news' ) );
	}

}
