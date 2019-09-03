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
	 * The option name for section/taxonomy mappings.
	 */
	const TAXONOMY_MAPPING_KEY = 'apple_news_section_taxonomy_mappings';

	/**
	 * The option name for section/theme mappings.
	 */
	const THEME_MAPPING_KEY = 'apple_news_section_theme_mappings';

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
	 * Valid actions handled by this class and their callback functions.
	 *
	 * @var array
	 * @access private
	 */
	private $valid_actions;

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
	 * Returns an array of section data without requiring an instance of the object.
	 *
	 * @access public
	 * @return array An array of section data.
	 */
	public static function get_sections() {

		// Try to load from cache.
		$sections = get_transient( 'apple_news_sections' );
		if ( false !== $sections ) {
			return $sections;
		}

		// Try to get sections. The get_sections call sets the transient.
		$admin_settings = new Admin_Apple_Settings();
		$section_api    = new Section( $admin_settings->fetch_settings() );
		$sections       = $section_api->get_sections();
		if ( empty( $sections ) || ! is_array( $sections ) ) {
			$sections = array();
			Admin_Apple_News::show_error(
				__( 'Unable to fetch a list of sections.', 'apple-news' )
			);
		}

		return $sections;
	}

	/**
	 * Given a post ID, returns an array of section URLs based on applied taxonomy.
	 *
	 * Supports overrides for manual section selection and fallback to postmeta
	 * when no mappings are set.
	 *
	 * @param int    $post_id The ID of the post to query.
	 * @param string $format The return format to use. Can be 'url' or 'raw'.
	 *
	 * @access public
	 * @return array An array of section data according to the requested format.
	 */
	public static function get_sections_for_post( $post_id, $format = 'url' ) {

		// Try to load sections from postmeta.
		$meta_value = get_post_meta( $post_id, 'apple_news_sections', true );
		if ( is_array( $meta_value ) ) {
			return $meta_value;
		}

		// Determine if there are taxonomy mappings configured.
		$mappings = get_option( self::TAXONOMY_MAPPING_KEY );
		if ( empty( $mappings ) ) {
			return array();
		}

		// Convert sections returned from the API into the requested format.
		$sections     = array();
		$sections_raw = self::get_sections();
		foreach ( $sections_raw as $section ) {

			// Ensure we have an ID to key off of.
			if ( empty( $section->id ) ) {
				continue;
			}

			// Fork for format.
			switch ( $format ) {
				case 'raw':
					$sections[ $section->id ] = $section;
					break;
				case 'url':
					if ( ! empty( $section->links->self ) ) {
						$sections[ $section->id ] = $section->links->self;
					}
					break;
			}
		}

		// Try to get configured taxonomy.
		$taxonomy = self::get_mapping_taxonomy();
		if ( empty( $taxonomy ) || is_wp_error( $taxonomy ) ) {
			wp_die( esc_html__( 'Unable to get a valid mapping taxonomy.', 'apple-news' ) );
		}

		// Try to get terms for the post.
		$terms = get_the_terms( $post_id, $taxonomy->name );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		// Loop through the mappings to determine sections.
		$post_sections = array();
		$term_ids      = wp_list_pluck( $terms, 'term_id' );
		$mappings      = get_option( self::TAXONOMY_MAPPING_KEY );
		foreach ( $mappings as $section_id => $section_term_ids ) {
			foreach ( $section_term_ids as $section_term_id ) {
				if ( in_array( $section_term_id, $term_ids, true ) ) {
					$post_sections[] = $sections[ $section_id ];
				}
			}
		}

		// Eliminate duplicates.
		$post_sections = array_unique( $post_sections );

		// If we get here and no sections are specified, fall back to Main.
		if ( empty( $post_sections ) ) {
			$post_sections[] = reset( $sections );
		}

		return $post_sections;
	}

	/**
	 * Given a section ID, check for a custom theme mapping.
	 *
	 * @param string $section_id The Apple News section ID.
	 * @access public
	 * @return string The name of the theme, or null if not found.
	 */
	public static function get_theme_for_section( $section_id ) {

		// Try to get the theme mapping for this section ID.
		$theme_mappings = get_option( self::THEME_MAPPING_KEY );
		if ( ! isset( $theme_mappings[ $section_id ] ) ) {
			return null;
		}

		return $theme_mappings[ $section_id ];
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Initialize class variables.
		$this->page_name = $this->plugin_domain . '-sections';
		$admin_settings  = new Admin_Apple_Settings();
		$this->settings  = $admin_settings->fetch_settings();

		// Set up admin action callbacks for form submissions.
		$this->valid_actions = array(
			'apple_news_set_section_mappings' => array( $this, 'set_section_mappings' ),
			'apple_news_refresh_section_list' => array( $this, 'refresh_section_list' ),
		);

		// Set up action hooks.
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'admin_init', array( $this, 'action_router' ) );
		add_action( 'admin_menu', array( $this, 'setup_section_page' ), 99 );
		add_action(
			'wp_ajax_apple_news_section_taxonomy_autocomplete',
			array( $this, 'ajax_apple_news_section_taxonomy_autocomplete' )
		);
	}

	/**
	 * Route all possible section actions to the right place.
	 *
	 * @access public
	 */
	public function action_router() {

		// Check for a valid action.
		$action = isset( $_REQUEST['action'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) )
			: null;
		if ( ( empty( $action ) || ! array_key_exists( $action, $this->valid_actions ) ) ) {
			return;
		}

		// Check the nonce.
		check_admin_referer( 'apple_news_sections' );

		// Call the callback for the action for further processing.
		call_user_func( $this->valid_actions[ $action ] );
	}

	/**
	 * AJAX endpoint for section/taxonomy mapping autocomplete fields.
	 *
	 * @access public
	 */
	public function ajax_apple_news_section_taxonomy_autocomplete() {

		// Determine if we have anything to search for.
		if ( empty( $_GET['term'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
			echo wp_json_encode( array() );
			exit;
		}

		// Try to get the taxonomy in use.
		$taxonomy = self::get_mapping_taxonomy();
		if ( empty( $taxonomy->name ) ) {
			echo wp_json_encode( array() );
			exit;
		}

		// Try to get terms matching the criteria.
		$terms = get_terms(
			array(
				'fields'     => 'names',
				'hide_empty' => false,
				'number'     => 10,
				'search'     => sanitize_text_field( wp_unslash( $_GET['term'] ) ), // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
				'taxonomy'   => $taxonomy->name,
			)
		);

		// See if we got anything.
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			echo wp_json_encode( array() );
			exit;
		}

		// Encode results and bail.
		echo wp_json_encode( $terms );
		exit();
	}

	/**
	 * Options page setup.
	 *
	 * @access public
	 */
	public function setup_section_page() {

		// Don't add the submenu page if the settings aren't initialized.
		if ( ! self::is_initialized() ) {
			return;
		}

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
			wp_die( esc_html__( 'You do not have permissions to access this page.', 'apple-news' ) );
		}

		// Negotiate the taxonomy name.
		$taxonomy = self::get_mapping_taxonomy();
		if ( empty( $taxonomy->label ) ) {
			wp_die( esc_html__( 'You specified an invalid mapping taxonomy.', 'apple-news' ) );
		}

		// Try to get a list of sections.
		$section_api  = new Section( $this->settings );
		$sections_raw = $section_api->get_sections();
		if ( empty( $sections_raw ) || ! is_array( $sections_raw ) ) {
			wp_die( esc_html__( 'Unable to fetch a list of sections.', 'apple-news' ) );
		}

		// Convert sections returned from the API into a key/value pair of id/name.
		$sections = array();
		foreach ( $sections_raw as $section ) {
			if ( ! empty( $section->id ) && ! empty( $section->name ) ) {
				$sections[ $section->id ] = $section->name;
			}
		}

		// Get mappings from settings.
		$taxonomy_mappings = array();
		$taxonomy_settings = get_option( self::TAXONOMY_MAPPING_KEY );
		if ( ! empty( $taxonomy_settings ) && is_array( $taxonomy_settings ) ) {
			foreach ( $taxonomy_settings as $section_id => $term_ids ) {
				foreach ( $term_ids as $term_id ) {
					$term = get_term( $term_id, $taxonomy->name );
					if ( ! empty( $term->name ) ) {
						$taxonomy_mappings[ $section_id ][] = $term->name;
					}
				}
			}
		}

		$theme_mappings  = get_option( self::THEME_MAPPING_KEY );
		$theme_obj       = new Admin_Apple_Themes();
		$theme_admin_url = add_query_arg( 'page', $theme_obj->theme_page_name, admin_url( 'admin.php' ) );
		$themes          = \Apple_Exporter\Theme::get_registry();

		// Load the partial with the form.
		include plugin_dir_path( __FILE__ ) . 'partials/page-sections.php';
	}

	/**
	 * Register assets for the options page.
	 *
	 * @param string $hook The context under which this function was called.
	 * @access public
	 */
	public function register_assets( $hook ) {

		// Only fire for the hook represented by this class.
		if ( 'apple-news_page_apple-news-sections' !== $hook ) {
			return;
		}

		global $wp_scripts;

		// Enqueue styles for this page.
		$jquery_ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_style(
			'apple-news-jquery-ui-autocomplete',
			'https://ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_ui->ver . '/themes/smoothness/jquery-ui.min.css',
			array(),
			self::$version
		);
		wp_enqueue_style(
			'apple-news-sections-css',
			plugin_dir_url( __FILE__ ) . '../assets/css/sections.css',
			array(),
			self::$version
		);

		// Enqueue scripts for this page.
		wp_enqueue_script(
			'apple-news-sections-js',
			plugin_dir_url( __FILE__ ) . '../assets/js/sections.js',
			array( 'jquery', 'jquery-ui-autocomplete' ),
			self::$version,
			false
		);
	}

	/**
	 * A function that busts the cache of sections and re-fetches it
	 * from the Apple News API.
	 *
	 * @access public
	 */
	private function refresh_section_list() {
		delete_transient( 'apple_news_sections' );
	}

	/**
	 * A callback for form save on the section-taxonomy mappings form.
	 *
	 * @access private
	 */
	private function set_section_mappings() {

		// Check the nonce.
		check_admin_referer( 'apple_news_sections' );

		// Ensure we got POST data.
		if ( empty( $_POST ) || ! is_array( $_POST ) ) {
			return;
		}

		// Try to get sections.
		$admin_settings = new Admin_Apple_Settings();
		$section_api    = new Section( $admin_settings->fetch_settings() );
		$sections_raw   = $section_api->get_sections();
		if ( empty( $sections_raw ) || ! is_array( $sections_raw ) ) {
			return;
		}

		// Loop through sections and look for mappings in POST data.
		$taxonomy_mappings = array();
		$theme_mappings    = array();
		$taxonomy          = self::get_mapping_taxonomy();
		$section_ids       = wp_list_pluck( $sections_raw, 'id' );
		foreach ( $section_ids as $section_id ) {

			// Determine if there is taxonomy data for this section.
			$taxonomy_key = 'taxonomy-mapping-' . $section_id;
			if ( ! empty( $_POST[ $taxonomy_key ] ) && is_array( $_POST[ $taxonomy_key ] ) ) {
				// Loop over terms and convert to term IDs for save.
				$values = array_map(
					'sanitize_text_field',
					array_map(
						'wp_unslash',
						$_POST[ $taxonomy_key ] // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					)
				);
				foreach ( $values as $value ) {
					$term = get_term_by( 'name', $value, $taxonomy->name );
					if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
						$taxonomy_mappings[ $section_id ][] = $term->term_id;
						$taxonomy_mappings[ $section_id ]   = array_unique( $taxonomy_mappings[ $section_id ] );
					}
				}
			}

			// Determine if there is theme data for this section.
			$theme_key = 'theme-mapping-' . $section_id;
			if ( ! empty( $_POST[ $theme_key ] ) ) {
				$theme_mappings[ $section_id ] = sanitize_text_field( wp_unslash( $_POST[ $theme_key ] ) );
			}
		}

		// Save the new mappings.
		update_option( self::TAXONOMY_MAPPING_KEY, $taxonomy_mappings, false );
		update_option( self::THEME_MAPPING_KEY, $theme_mappings, false );
	}
}
