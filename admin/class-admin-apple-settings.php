<?php
/**
 * Publish to Apple News: Admin_Apple_Settings class
 *
 * @package Apple_News
 */

// Include dependencies.
require_once plugin_dir_path( __FILE__ ) . '../includes/apple-exporter/class-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/class-admin-apple-settings-section.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/class-admin-apple-settings-section-api.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/class-admin-apple-settings-section-advanced.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/class-admin-apple-settings-section-post-types.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/class-admin-apple-settings-section-developer-tools.php';

use Apple_Exporter\Settings as Settings;

/**
 * This class is in charge of creating a WordPress page to manage the
 * Exporter's settings class.
 */
class Admin_Apple_Settings extends Apple_News {

	/**
	 * Keeps track of whether functionality has been initialized or not.
	 *
	 * @access private
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * Associative array of fields and types. If not present, defaults to string.
	 * Possible types are: integer, color, boolean, string and options.
	 * If options, use an array instead of a string.
	 *
	 * @since 0.4.0
	 * @var array
	 * @access private
	 */
	private $field_types;

	/**
	 * Optionally define more elaborated labels for each setting and store them
	 * here.
	 *
	 * @since 0.6.0
	 * @var array
	 * @access private
	 */
	private $field_labels;

	/**
	 * Only load settings once. Cache results for easy and efficient usage.
	 *
	 * @var Settings
	 * @access private
	 */
	private $loaded_settings;

	/**
	 * Available settings sections.
	 *
	 * @var array
	 * @access private
	 */
	private $sections;

	/**
	 * Settings page name.
	 *
	 * @var string
	 * @access private
	 */
	private $page_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->loaded_settings = null;
		$this->sections        = array();
		$this->page_name       = $this->plugin_domain . '-options';

		if ( ! self::$initialized ) {
			add_action( 'admin_init', array( $this, 'register_sections' ), 5 );
			add_action( 'admin_menu', array( $this, 'setup_options_page' ), 99 );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
			self::$initialized = true;
		}
	}

	/**
	 * Add settings sections.
	 *
	 * @access private
	 */
	private function add_sections() {
		$this->add_section( new Admin_Apple_Settings_Section_API( $this->page_name ) );
		$this->add_section( new Admin_Apple_Settings_Section_Post_Types( $this->page_name ) );
		$this->add_section( new Admin_Apple_Settings_Section_Advanced( $this->page_name ) );
		$this->add_section( new Admin_Apple_Settings_Section_Developer_Tools( $this->page_name ) );
	}

	/**
	 * Add a settings section.
	 *
	 * @param Admin_Apple_Settings_Section $section The section to add.
	 * @access private
	 */
	private function add_section( $section ) {
		$this->sections[] = $section;
	}

	/**
	 * Load exporter settings and register them.
	 *
	 * @since 0.4.0
	 * @access public
	 */
	public function register_sections() {
		$this->add_sections();
		$this->sections = apply_filters( 'apple_news_settings_sections', $this->sections );
	}

	/**
	 * Options page setup.
	 *
	 * @access public
	 */
	public function setup_options_page() {
		add_submenu_page(
			'apple_news_index',
			__( 'Apple News Options', 'apple-news' ),
			__( 'Settings', 'apple-news' ),
			apply_filters( 'apple_news_settings_capability', 'manage_options' ),
			$this->page_name,
			array( $this, 'page_options_render' )
		);
	}

	/**
	 * Options page render.
	 *
	 * @access public
	 */
	public function page_options_render() {
		if ( ! current_user_can( apply_filters( 'apple_news_settings_capability', 'manage_options' ) ) ) {
			wp_die( esc_html__( 'You do not have permissions to access this page.', 'apple-news' ) );
		}

		$sections = $this->sections;
		include plugin_dir_path( __FILE__ ) . 'partials/page-options.php';
	}

	/**
	 * Register assets for the options page.
	 *
	 * @param string $hook The context under which this function is called.
	 * @access public
	 */
	public function register_assets( $hook ) {
		if ( 'apple-news_page_apple-news-options' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'apple-news-select2-css',
			plugin_dir_url( __FILE__ ) . '../assets/css/select2.min.css',
			array(),
			self::$version
		);

		wp_enqueue_script(
			'apple-news-select2-js',
			plugin_dir_url( __FILE__ ) . '../assets/js/select2.full.min.js',
			array( 'jquery' ),
			self::$version,
			false
		);

		wp_enqueue_script(
			'apple-news-settings',
			plugin_dir_url( __FILE__ ) . '../assets/js/settings.js',
			array( 'jquery' ),
			self::$version,
			true
		);
	}

	/**
	 * Creates a new Settings object and loads it with WordPress' saved settings.
	 *
	 * Merges saved settings from WordPress with default settings in the object.
	 *
	 * @access public
	 * @return Settings A Settings object containing merged settings.
	 */
	public function fetch_settings() {

		// If settings are not already loaded, load them.
		if ( is_null( $this->loaded_settings ) ) {

			// Initialize.
			$settings    = new Settings();
			$wp_settings = get_option( self::$option_name );

			// Merge settings in the option with defaults.
			foreach ( $settings->all() as $key => $value ) {
				$wp_value       = ( empty( $wp_settings[ $key ] ) )
					? $value
					: $wp_settings[ $key ];
				$settings->$key = $wp_value;
			}

			// Store in local object storage.
			$this->loaded_settings = $settings;
		}

		/**
		 * Allows for filtering of the merged settings before returning.
		 *
		 * @since 0.4.0
		 *
		 * @param Settings $settings The settings to be filtered.
		 */
		return apply_filters( 'apple_news_loaded_settings', $this->loaded_settings );
	}

	/**
	 * Replaces the current settings.
	 *
	 * @param array Settings $settings New settings to replace the old.
	 * @access public
	 */
	public function save_settings( $settings ) {
		update_option( self::$option_name, $settings );
		$this->loaded_settings = $settings;
	}
}
