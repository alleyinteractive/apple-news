<?php
/**
 * Entry point for the admin side of the WP Plugin.
 *
 * @author  Federico Ramirez
 * @since   0.6.0
 */

require_once plugin_dir_path( __FILE__ ) . 'apple-actions/index/class-push.php';
require_once plugin_dir_path( __FILE__ ) . 'apple-actions/index/class-delete.php';
require_once plugin_dir_path( __FILE__ ) . 'apple-actions/index/class-export.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-apple-news-list-table.php';

class Admin_Apple_Index_Page extends Apple_News {

	/**
	 * Current plugin settings.
	 *
	 * @var array
	 * @access private
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	function __construct( $settings ) {
		$this->settings = $settings;

		// Handle routing to various admin pages
		add_action( 'admin_menu', array( $this, 'setup_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'setup_assets' ) );
	}



	/**
	 * Sets up the admin page.
	 *
	 * @access public
	 */
	public function setup_admin_page() {
		// Set up main page. This page reads parameters and handles actions
		// accordingly.
		add_menu_page(
			__( 'Apple News', 'apple-news' ),	// Page Title
			__( 'Apple News', 'apple-news' ),	// Menu Title
			apply_filters( 'apple_news_list_capability', 'manage_options' ), // Capability
			$this->plugin_slug . '_index', 		// Menu Slug
			array( $this, 'page_router' ), 		// Function
			'dashicons-format-aside'       		// Icon
		);
	}

	/**
	 * Sets up all pages used in the plugin's admin page. Associate each route
	 * with an action. Actions are methods that end with "_action" and must
	 * perform a task and output HTML with the result.
	 *
	 * FIXME: Regarding this class doing too much, maybe split all actions into
	 * their own class.
	 *
	 * @since 0.4.0
	 * @return mixed
	 * @access public
	 */
	public function page_router() {
		$id     = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : null;
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : null;

		// Given an action and ID, map the attributes to corresponding actions.

		if ( ! $id ) {
			switch ( $action ) {
			case 'push':
				$url  = menu_page_url( $this->plugin_slug . '_bulk_export', false );
				if ( isset( $_GET['article'] ) ) {
					$ids = is_array( $_GET['article'] ) ? array_map( 'absint', $_GET['article'] ) : absint( $_GET['article'] );
					$url .= '&ids=' . implode( '.', $ids );
				}
				wp_safe_redirect( esc_url_raw( $url ) );
				exit;
			default:
				return $this->show_post_list_action();
			}
		}

		switch ( $action ) {
		case 'settings':
			return $this->settings_action( $id );
		case 'export':
			return $this->export_action( $id );
		case 'push':
			return $this->push_action( $id );
		case 'delete':
			return $this->delete_action( $id );
		default:
			wp_die( __( 'Invalid action: ', 'apple-news' ) . $action );
		}
	}

	/**
	 * Shows a success message.
	 *
	 * @param string $message
	 * @access public
	 */
	private function notice_success( $message ) {
		Admin_Apple_Notice::success( $message );
		$this->do_redirect();
	}

	/**
	 * Shows an error message.
	 *
	 * @param string $message
	 * @access public
	 */
	private function notice_error( $message ) {
		Admin_Apple_Notice::error( $message );
		$this->do_redirect();
	}

	/**
	 * Performs the redirect after an action is complete.
	 *
	 * @param string $message
	 * @access public
	 */
	private function do_redirect() {
		// Perform the redirect
		wp_safe_redirect( esc_url_raw( self::action_query_params( '', menu_page_url( $this->plugin_slug . '_index', false ) ) ) );
		exit;
	}

	/**
	 * Helps build query params for each row action.
	 *
	 * @param string $action
	 * @param string $url
	 * @return string
	 * @access public
	 * @static
	 */
	public static function action_query_params( $action, $url ) {
		// Set the keys we need to pay attention to
		$keys = array(
			'apple_news_publish_status',
			'apple_news_date_from',
			'apple_news_date_to',
			's',
			'paged',
		);

		// Start the params
		$params = array();
		if ( ! empty( $action ) ) {
			$params['action'] = $action;
		}

		// Add the other params
		foreach ( $keys as $key ) {
			if ( ! empty( $_GET[ $key ] ) ) {
				$params[ $key ] = urlencode( sanitize_text_field( $_GET[ $key ] ) );
			}
		}

		// Add to the action URL
		return add_query_arg( $params, $url );
	}

	/**
	 * Gets a setting by name which was loaded from WordPress options.
	 *
	 * @since 0.4.0
	 * @param string $name
	 * @return mixed
	 * @access private
	 */
	private function get_setting( $name ) {
		return $this->settings->get( $name );
	}

	/**
	 * Downloads the JSON file for troubleshooting purposes.
	 *
	 * @param string $json
	 * @param int $id
	 * @access private
	 */
	private function download_json( $json, $id ) {
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="article-' . absint( $id ) . '.json"' );
		ob_clean();
		flush();
		echo $json;
		exit;
	}

	/**
	 * Sets up admin assets.
	 *
	 * @param string $hook
	 * @access public
	 */
	public function setup_assets( $hook ) {
		if ( 'toplevel_page_apple_news_index' != $hook ) {
			return;
		}

		// Enable jQuery datepicker for the export table date filter
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// Add the export table script and style
		wp_enqueue_style( $this->plugin_slug . '_export_table_css', plugin_dir_url(
			__FILE__ ) .  '../assets/css/export-table.css' );
		wp_enqueue_script( $this->plugin_slug . '_export_table_js', plugin_dir_url(
			__FILE__ ) .  '../assets/js/export-table.js', array( 'jquery', 'jquery-ui-datepicker' ), $this->version, true );
	}

	/**
	 * Shows a post from the list table.
	 *
	 * @access private
	 */
	private function show_post_list_action() {
		$table = new Admin_Apple_News_List_Table( $this->settings );
		$table->prepare_items();
		include plugin_dir_path( __FILE__ ) . 'partials/page_index.php';
	}

	/**
	 * Handles all settings actions.
	 *
	 * @param int $id
	 * @access private
	 */
	private function settings_action( $id ) {
		if ( isset( $_POST['pullquote'] ) ) {
			update_post_meta( $id, 'apple_news_pullquote', sanitize_text_field( $_POST['pullquote'] ) );
		}

		if ( isset( $_POST['pullquote_position'] ) ) {
			update_post_meta( $id, 'apple_news_pullquote_position', sanitize_text_field( $_POST['pullquote_position'] ) );
			$message = __( 'Settings saved.', 'apple-news' );
		}

		$post      = get_post( $id );
		$post_meta = get_post_meta( $id );
		include plugin_dir_path( __FILE__ ) . 'partials/page_single_settings.php';
	}

	/**
	 * Handles an export action.
	 *
	 * @param int $id
	 * @access private
	 */
	private function export_action( $id ) {
		$action = new Apple_Actions\Index\Export( $this->settings, $id );
		try {
			$json = $action->perform();
			$this->download_json( $json, $id );
		} catch ( Apple_Actions\Action_Exception $e ) {
			$this->notice_error( $e->getMessage() );
		}
	}

	/**
	 * Handles a push to Apple News action.
	 *
	 * @param int $id
	 * @access private
	 */
	private function push_action( $id ) {
		// Ensure the post is published
		if ( 'publish' != get_post_status( $id ) ) {
			$this->notice_error( sprintf(
				__( 'Article %s is not published and cannot be pushed to Apple News.', 'apple-news' ),
				$id
			) );
			return;
		}

		// Push the post
		$action = new Apple_Actions\Index\Push( $this->settings, $id );
		try {
			$action->perform();

			// In async mode, success or failure will be displayed later
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
	 * @param int $id
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

}
