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
	 * Given a post ID, returns an array of section URLs based on applied taxonomy.
	 *
	 * Supports overrides for manual section selection and fallback to postmeta
	 * when no mappings are set.
	 *
	 * @param int $post_id The ID of the post to query.
	 *
	 * @access public
	 * @return array An array of section URLs for the post.
	 */
	public static function get_sections_for_post( $post_id ) {

		// Try to load sections from postmeta.
		$meta_value = get_post_meta( $post_id, 'apple_news_sections', true );
		if ( is_array( $meta_value ) ) {
			return $meta_value;
		}

		// Determine if there are taxonomy mappings configured.
		$mappings = get_option( Admin_Apple_Sections::TAXONOMY_MAPPING_KEY );
		if ( empty( $mappings ) ) {
			return array();
		}

		// Try to get sections.
		$admin_settings = new Admin_Apple_Settings;
		$section_api = new Section( $admin_settings->fetch_settings() );
		$sections_raw = $section_api->get_sections();
		if ( empty( $sections_raw ) || ! is_array( $sections_raw ) ) {
			wp_die( __( 'Unable to fetch a list of sections.', 'apple-news' ) );
		}

		// Convert sections returned from the API into a key/value pair of id/URL.
		$sections = array();
		foreach ( $sections_raw as $section ) {
			if ( ! empty( $section->id ) && ! empty( $section->links->self ) ) {
				$sections[ $section->id ] = $section->links->self;
			}
		}

		// Try to get configured taxonomy.
		$taxonomy = self::get_mapping_taxonomy();
		if ( empty( $taxonomy ) || is_wp_error( $taxonomy ) ) {
			wp_die( __( 'Unable to get a valid mapping taxonomy.', 'apple-news' ) );
		}

		// Try to get terms for the post.
		$terms = get_the_terms( $post_id, $taxonomy->name );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		// Loop through the mappings to determine sections.
		$post_sections = array();
		$term_ids = wp_list_pluck( $terms, 'term_id' );
		$mappings = get_option( self::TAXONOMY_MAPPING_KEY );
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
	 * Constructor.
	 */
	function __construct() {

		// Initialize class variables.
		$this->page_name = $this->plugin_domain . '-sections';
		$admin_settings = new Admin_Apple_Settings;
		$this->settings = $admin_settings->fetch_settings();

		// Set up admin action callbacks for form submissions.
		$this->valid_actions = array(
			'apple_news_set_section_taxonomy_mappings' => array( $this, 'set_section_taxonomy_mappings' ),
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
		$action	= isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : null;
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
	 * @return array An array of values matching the query.
	 */
	public function ajax_apple_news_section_taxonomy_autocomplete() {

		// Determine if we have anything to search for.
		if ( empty( $_GET['term'] ) ) {
			echo json_encode( array() );
			exit;
		}

		// Try to get the taxonomy in use.
		$taxonomy = self::get_mapping_taxonomy();
		if ( empty( $taxonomy->name ) ) {
			echo json_encode( array() );
			exit;
		}

		// Try to get terms matching the criteria.
		$terms = get_terms(
			array(
				'fields' => 'names',
				'hide_empty' => false,
				'number' => 10,
				'search' => sanitize_text_field( $_GET['term'] ),
				'taxonomy' => $taxonomy->name,
			)
		);

		// See if we got anything.
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			echo json_encode( array() );
			exit;
		}

		// Encode results and bail.
		echo json_encode( $terms );
		exit();
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
		$sections = array();
		foreach ( $sections_raw as $section ) {
			if ( ! empty( $section->id ) && ! empty( $section->name ) ) {
				$sections[ $section->id ] = $section->name;
			}
		}

		// Get mappings from settings.
		$mappings = array();
		$settings = get_option( self::TAXONOMY_MAPPING_KEY );
		if ( ! empty( $settings ) && is_array( $settings ) ) {
			foreach ( $settings as $section_id => $term_ids ) {
				foreach ( $term_ids as $term_id ) {
					$term = get_term( $term_id, $taxonomy->name );
					if ( ! empty( $term->name ) ) {
						$mappings[ $section_id ][] = $term->name;
					}
				}
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

		// Only fire for the hook represented by this class.
		if ( 'apple-news_page_apple-news-sections' !== $hook ) {
			return;
		}

		global $wp_scripts;

		// Enqueue styles for this page.
		$jquery_ui = $wp_scripts->query('jquery-ui-core');
		wp_enqueue_style(
			'apple-news-jquery-ui-autocomplete',
			'//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_ui->ver . '/themes/smoothness/jquery-ui.min.css'
		);
		wp_enqueue_style(
			'apple-news-sections-css',
			plugin_dir_url( __FILE__ ) . '../assets/css/sections.css'
		);

		// Enqueue scripts for this page.
		wp_enqueue_script(
			'apple-news-sections-js',
			plugin_dir_url( __FILE__ ) . '../assets/js/sections.js',
			array( 'jquery', 'jquery-ui-autocomplete' )
		);
	}

	/**
	 * A callback for form save on the section-taxonomy mappings form.
	 *
	 * @access private
	 */
	private function set_section_taxonomy_mappings() {

		// Ensure we got POST data.
		if ( empty( $_POST ) || ! is_array( $_POST ) ) {
			return;
		}

		// Try to get sections.
		$admin_settings = new Admin_Apple_Settings;
		$section_api = new Section( $admin_settings->fetch_settings() );
		$sections_raw = $section_api->get_sections();
		if ( empty( $sections_raw ) || ! is_array( $sections_raw ) ) {
			return;
		}

		// Loop through sections and look for mappings in POST data.
		$mappings = array();
		$taxonomy = self::get_mapping_taxonomy();
		$section_ids = wp_list_pluck( $sections_raw, 'id' );
		foreach ( $section_ids as $section_id ) {

			// Determine if there is data for this section.
			$key = 'taxonomy-mapping-' . $section_id;
			if ( empty( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
				continue;
			}

			// Loop over terms and convert to term IDs for save.
			$values = array_map( 'sanitize_text_field', $_POST[ $key ] );
			foreach ( $values as $value ) {
				if ( function_exists( 'wpcom_vip_get_term_by' ) ) {
					$term = wpcom_vip_get_term_by( 'name', $value, $taxonomy->name );
				} else {
					$term = get_term_by( 'name', $value, $taxonomy->name );
				}
				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$mappings[ $section_id ][] = $term->term_id;
					$mappings[ $section_id ] = array_unique( $mappings[ $section_id ] );
				}
			}
		}

		// Save the new mappings.
		update_option( self::TAXONOMY_MAPPING_KEY, $mappings, false );
	}
}
