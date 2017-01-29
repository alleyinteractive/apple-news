<?php
/**
 * Publish to Apple News Admin Screens: Admin_Apple_Sections class
 *
 * Contains a class which is used to manage the Sections admin settings page.
 *
 * @package Apple_News
 * @since 1.2.2
 */

use \Apple_Actions\Index\Section;
use \Apple_Exporter\Settings;

/**
 * This class is in charge of handling the management of Apple News sections.
 *
 * @since 1.2.2
 */
class Admin_Apple_Sections extends Apple_News {

	/**
	 * Section management page name.
	 *
	 * @var string
	 * @access private
	 */
	private $page_name;

	/**
	 * Contains settings loaded from WordPress and merged with defaults.
	 *
	 * @var Settings
	 * @access private
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	function __construct() {

		// Initialize class variables.
		$this->page_name = $this->plugin_domain . '-sections';
		$admin_settings = new Admin_Apple_Settings;
		$this->settings = $admin_settings->fetch_settings();

		// Set up action hooks.
		add_action( 'admin_menu', array( $this, 'setup_section_page' ), 99 );
	}

	/**
	 * Returns a taxonomy object representing the taxonomy to be mapped to sections.
	 *
	 * @access public
	 * @return WP_Taxonomy|false A WP_Taxonomy object on success; false on failure.
	 */
	public static function get_mapping_taxonomy() {

		/**
		 * Allows for modification of the taxonomy used for section mapping.
		 *
		 * @since 1.2.2
		 *
		 * @param string $taxonomy The taxonomy slug to be filtered.
		 */
		$taxonomy = apply_filters( 'apple_news_section_taxonomy', 'category' );

		return get_taxonomy( $taxonomy );
	}

	/**
	 * Options page setup.
	 *
	 * @access public
	 */
	public function setup_section_page() {
		add_submenu_page(
			'apple_news_index',
			__( 'Apple News Sections', 'apple-news' ),
			__( 'Sections', 'apple-news' ),
			apply_filters( 'apple_news_settings_capability', 'manage_options' ),
			$this->page_name,
			array( $this, 'page_sections_render' )
		);
	}

	/**
	 * Options page render.
	 *
	 * @access public
	 */
	public function page_sections_render() {

		// Don't allow access to this page if the user does not have permission.
		if ( ! current_user_can( apply_filters( 'apple_news_settings_capability', 'manage_options' ) ) ) {
			wp_die( __( 'You do not have permissions to access this page.', 'apple-news' ) );
		}

		// Negotiate the taxonomy name.
		$taxonomy = self::get_mapping_taxonomy();
		if ( empty( $taxonomy->label ) ) {
			wp_die( __( 'You specified an invalid mapping taxonomy.', 'apple-news' ) );
		}

		// Try to get a list of sections.
		$section_api = new Section( $this->settings );
		$sections_raw = $section_api->get_sections();
		if ( empty( $sections_raw ) || ! is_array( $sections_raw ) ) {
			wp_die( __( 'Unable to fetch a list of sections.', 'apple-news' ) );
		}

		// Convert sections returned from the API into a key/value pair of id/name.
		$sections = [];
		foreach ( $sections_raw as $section ) {
			if ( ! empty( $section->id ) && ! empty( $section->name ) ) {
				$sections[ $section->id ] = $section->name;
			}
		}

		// Load the partial with the form.
		include plugin_dir_path( __FILE__ ) . 'partials/page_sections.php';
	}

	/**
	 * Register assets for the options page.
	 *
	 * @param string $hook
	 * @access public
	 */
	public function register_assets( $hook ) {
		if ( 'apple-news_page_apple-news-sections' != $hook ) {
			return;
		}

		wp_enqueue_style( 'apple-news-sections-css', plugin_dir_url( __FILE__ ) .
			'../assets/css/sections.css', array() );

		wp_enqueue_script( 'apple-news-sections-js', plugin_dir_url( __FILE__ ) .
			'../assets/js/sections.js', array( 'jquery' )
		);

		wp_localize_script( 'apple-news-sections-js', 'appleNewsSections', array(
			'key1' => __( 'Message 1', 'apple-news' ),
		) );
	}
}
