<?php
/**
 * Publish to Apple News Includes: Apple_News class
 *
 * Contains a class which is used to manage the Publish to Apple News plugin.
 *
 * @package Apple_News
 * @since 0.2.0
 */

/**
 * Base plugin class with core plugin information and shared functionality
 * between frontend and backend plugin classes.
 *
 * @author Federico Ramirez
 * @since 0.2.0
 */
class Apple_News {

	/**
	 * Link to support for the plugin on github.
	 *
	 * @var string
	 * @access public
	 */
	public static $github_support_url = 'https://github.com/alleyinteractive/apple-news/issues';

	/**
	 * Option name for settings.
	 *
	 * @var string
	 * @access public
	 */
	public static $option_name = 'apple_news_settings';

	/**
	 * Plugin version.
	 *
	 * @var string
	 * @access public
	 */
	public static $version = '1.2.7';

	/**
	 * Link to support for the plugin on WordPress.org.
	 *
	 * @var string
	 * @access public
	 */
	public static $wordpress_org_support_url = 'https://wordpress.org/support/plugin/publish-to-apple-news';

	/**
	 * Plugin domain.
	 *
	 * @var string
	 * @access protected
	 */
	protected $plugin_domain = 'apple-news';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 * @access protected
	 */
	protected $plugin_slug = 'apple_news';

	/**
	 * An array of contexts where assets should be enqueued.
	 *
	 * @var array
	 * @access private
	 */
	private $_contexts = array(
		'post.php',
		'post-new.php',
		'toplevel_page_apple_news_index',
	);

	/**
	 * Maturity ratings.
	 *
	 * @var string
	 * @access public
	 */
	public static $maturity_ratings = array( 'KIDS', 'MATURE', 'GENERAL' );

	/**
	 * Extracts the filename for bundling an asset.
	 *
	 * This functionality is used in a number of classes that do not have a common
	 * ancestor.
	 *
	 * @access public
	 * @return string The filename for an asset to be bundled.
	 */
	public static function get_filename( $path ) {

		// Remove any URL parameters.
		// This is important for sites using WordPress VIP or Jetpack Photon.
		$url_parts = parse_url( $path );
		if ( empty( $url_parts['path'] ) ) {
			return '';
		}

		return str_replace( ' ', '', basename( $url_parts['path'] ) );
	}

	/**
	 * Displays support information for the plugin.
	 *
	 * @param string $format The format in which to return the information.
	 * @param bool $with_padding Whether to include leading line breaks.
	 *
	 * @access public
	 * @return string The HTML for the support info block.
	 */
	public static function get_support_info( $format = 'html', $with_padding = true ) {

		// Construct base support info block.
		$support_info = sprintf(
			'%s <a href="%s">%s</a> %s <a href="%s">%s</a>.',
			__(
				'If you need assistance, please reach out for support on',
				'apple-news'
			),
			esc_url( self::$wordpress_org_support_url ),
			__( 'WordPress.org', 'apple-news' ),
			__( 'or', 'apple-news' ),
			esc_url( self::$github_support_url ),
			__( 'GitHub', 'apple-news' )
		);

		// Remove tags, if requested.
		if ( 'text' === $format ) {
			$support_info = strip_tags( $support_info );
		}

		// Add leading padding, if requested.
		if ( $with_padding ) {
			if ( 'text' === $format ) {
				$support_info = "\n\n" . $support_info;
			} else {
				$support_info = '<br /><br />' . $support_info;
			}
		}

		return $support_info;
	}

	/**
	 * Constructor. Registers action hooks.
	 *
	 * @access public
	 */
	public function __construct() {
		add_action(
			'admin_enqueue_scripts',
			array( $this, 'action_admin_enqueue_scripts' )
		);
		add_action(
			'plugins_loaded',
			array( $this, 'action_plugins_loaded' )
		);
	}

	/**
	 * Enqueues scripts and styles for the admin interface.
	 *
	 * @param string $hook The initiator of the action hook.
	 *
	 * @access public
	 */
	public function action_admin_enqueue_scripts( $hook ) {

		// Ensure we are in an appropriate context.
		if ( ! in_array( $hook, $this->_contexts, true ) ) {
			return;
		}

		// Ensure media modal assets are enqueued.
		wp_enqueue_media();

		// Enqueue styles.
		wp_enqueue_style(
			$this->plugin_slug . '_cover_art_css',
			plugin_dir_url( __FILE__ ) .  '../assets/css/cover-art.css',
			array(),
			self::$version
		);

		// Enqueue scripts.
		wp_enqueue_script(
			$this->plugin_slug . '_cover_art_js',
			plugin_dir_url( __FILE__ ) .  '../assets/js/cover-art.js',
			array( 'jquery' ),
			self::$version,
			true
		);

		// Localize scripts.
		wp_localize_script( $this->plugin_slug . '_cover_art_js', 'apple_news_cover_art', array(
			'image_sizes' => Admin_Apple_News::get_image_sizes(),
			'image_too_small' => esc_html__( 'You must select an image that is at least the height and width specified above.', 'apple-news' ),
			'media_modal_button' => esc_html__( 'Select image', 'apple-news' ),
			'media_modal_title' => esc_html__( 'Choose an image', 'apple-news' ),
		) );
	}

	/**
	 * Action hook callback for plugins_loaded.
	 *
	 * @since 1.3.0
	 */
	public function action_plugins_loaded() {

		// Determine if the database version and code version are the same.
		$current_version = get_option( 'apple_news_version' );
		if ( version_compare( $current_version, self::$version, '>=' ) ) {
			return;
		}

		// Handle upgrade to version 1.3.0.
		if ( version_compare( $current_version, '1.3.0', '<' ) ) {
			$this->upgrade_to_1_3_0();
		}

		// Set the database version to the current version in code.
		update_option( 'apple_news_version', self::$version );
	}

	/**
	 * Create the default theme, if it does not exist.
	 *
	 * @todo Update this to use the new Theme class.
	 *
	 * @access public
	 */
	public function create_default_theme() {

		// Determine if a current theme exists.
		$themes = new \Admin_Apple_Themes;
		$active_theme = $themes->get_active_theme();
		if ( ! empty( $active_theme ) ) {
			return;
		}

		// Build the theme formatting settings from the base settings array.
		$formatting = new \Admin_Apple_Settings_Section_Formatting( '' );
		$formatting_settings = $formatting->get_settings();
		$wp_settings = get_option( self::$option_name, array() );
		$theme_settings = array();
		foreach ( $wp_settings as $setting_key => $setting_value ) {
			if ( isset( $formatting_settings[ $setting_key ] ) ) {
				$theme_settings[ $setting_key ] = $setting_value;
			}
		}

		// Save the theme and make it active.
		$name = __( 'Default', 'apple-news' );
		$themes->save_theme( $name, $theme_settings, true );
		$themes->set_theme( $name, true );
	}

	/**
	 * Initialize the value of api_autosync_delete if not set.
	 *
	 * @access public
	 */
	public function migrate_api_settings() {

		// Use the value of api_autosync_update for api_autosync_delete if not set
		// since that was the previous value used to determine this behavior.
		$wp_settings = get_option( self::$option_name );
		if ( empty( $wp_settings['api_autosync_delete'] )
		     && ! empty( $wp_settings['api_autosync_update'] )
		) {
			$wp_settings['api_autosync_delete'] = $wp_settings['api_autosync_update'];
			update_option( self::$option_name, $wp_settings, 'no' );
		}
	}

	/**
	 * Migrate legacy blockquote settings to new format.
	 *
	 * @access public
	 */
	public function migrate_blockquote_settings() {

		// Check for the presence of blockquote-specific settings.
		$wp_settings = get_option( self::$option_name );
		if ( $this->_all_keys_exist( $wp_settings, array(
			'blockquote_background_color',
			'blockquote_border_color',
			'blockquote_border_style',
			'blockquote_border_width',
			'blockquote_color',
			'blockquote_font',
			'blockquote_line_height',
			'blockquote_size',
			'blockquote_tracking',
		) ) ) {
			return;
		}

		// Set the background color to 90% of the body background.
		if ( ! isset( $wp_settings['blockquote_background_color'] )
		     && isset( $wp_settings['body_background_color'] )
		) {

			// Get current octets.
			if ( 7 === strlen( $wp_settings['body_background_color'] ) ) {
				$r = hexdec( substr( $wp_settings['body_background_color'], 1, 2 ) );
				$g = hexdec( substr( $wp_settings['body_background_color'], 3, 2 ) );
				$b = hexdec( substr( $wp_settings['body_background_color'], 5, 2 ) );
			} elseif ( 4 === strlen( $wp_settings['body_background_color'] ) ) {
				$r = substr( $wp_settings['body_background_color'], 1, 1 );
				$g = substr( $wp_settings['body_background_color'], 2, 1 );
				$b = substr( $wp_settings['body_background_color'], 3, 1 );
				$r = hexdec( $r . $r );
				$g = hexdec( $g . $g );
				$b = hexdec( $b . $b );
			} else {
				$r = 250;
				$g = 250;
				$b = 250;
			}

			// Darken by 10% and recompile back into a hex string.
			$wp_settings['blockquote_background_color'] = sprintf(
				'#%s%s%s',
				dechex( $r * .9 ),
				dechex( $g * .9 ),
				dechex( $b * .9 )
			);
		}

		// Clone settings, as necessary.
		$wp_settings = $this->_clone_settings(
			$wp_settings,
			array(
				'blockquote_border_color' => 'pullquote_border_color',
				'blockquote_border_style' => 'pullquote_border_style',
				'blockquote_border_width' => 'pullquote_border_width',
				'blockquote_color' => 'body_color',
				'blockquote_font' => 'body_font',
				'blockquote_line_height' => 'body_line_height',
				'blockquote_size' => 'body_size',
				'blockquote_tracking' => 'body_tracking',
			)
		);

		// Store the updated option to save the new setting names.
		update_option( self::$option_name, $wp_settings, 'no' );
	}

	/**
	 * Migrate legacy caption settings to new format.
	 *
	 * @access public
	 */
	public function migrate_caption_settings() {

		// Check for the presence of caption-specific settings.
		$wp_settings = get_option( self::$option_name );
		if ( $this->_all_keys_exist( $wp_settings, array(
			'caption_color',
			'caption_font',
			'caption_line_height',
			'caption_size',
			'caption_tracking',
		) ) ) {
			return;
		}

		// Clone and modify font size, if necessary.
		if ( ! isset( $wp_settings['caption_size'] )
		     && isset( $wp_settings['body_size'] )
		     && is_numeric( $wp_settings['body_size'] )
		) {
			$wp_settings['caption_size'] = $wp_settings['body_size'] - 2;
		}

		// Clone settings, as necessary.
		$wp_settings = $this->_clone_settings(
			$wp_settings,
			array(
				'caption_color' => 'body_color',
				'caption_font' => 'body_font',
				'caption_line_height' => 'body_line_height',
				'caption_tracking' => 'body_tracking',
			)
		);

		// Store the updated option to save the new setting names.
		update_option( self::$option_name, $wp_settings, 'no' );
	}

	/**
	 * Migrates standalone customized JSON to each installed theme.
	 *
	 * @access public
	 */
	public function migrate_custom_json_to_themes() {

		// Get a list of all themes that need to be updated.
		$themes = new Admin_Apple_Themes();
		$all_themes = \Apple_Exporter\Theme::get_registry();

		// Get a list of components that may have customized JSON.
		$component_factory = new \Apple_Exporter\Component_Factory();
		$component_factory->initialize();
		$components = $component_factory::get_components();

		// Iterate over components and look for customized JSON for each.
		$json_templates = array();
		foreach ( $components as $component_class ) {

			// Negotiate the component key.
			$component = new $component_class;
			$component_key = $component->get_component_name();

			// Try to get the custom JSON for this component.
			$custom_json = get_option( 'apple_news_json_' . $component_key );
			if ( empty( $custom_json ) || ! is_array( $custom_json ) ) {
				continue;
			}

			// Loop over custom JSON and add each.
			foreach ( $custom_json as $legacy_key => $values ) {
				$new_key = str_replace( 'apple_news_json_', '', $legacy_key );
				$json_templates[ $component_key ][ $new_key ] = $values;
			}
		}

		// Ensure there is custom JSON to save.
		if ( empty( $json_templates ) ) {
			return;
		}

		// Loop over themes and apply to each.
		foreach ( $all_themes as $theme ) {
			$theme_settings = $themes->get_theme( $theme );
			$theme_settings['json_templates'] = $json_templates;
			$themes->save_theme( $theme, $theme_settings, true );
		}

		// Remove custom JSON standalone options.
		$component_keys = array_keys( $json_templates );
		foreach ( $component_keys as $component_key ) {
			delete_option( 'apple_news_json_' . $component_key );
		}
	}

	/**
	 * Migrate legacy header settings to new format.
	 *
	 * @access public
	 */
	public function migrate_header_settings() {

		// Check for presence of any legacy header setting.
		$wp_settings = get_option( self::$option_name );
		if ( empty( $wp_settings['header_font'] )
		     && empty( $wp_settings['header_color'] )
		     && empty( $wp_settings['header_line_height'] )
		) {
			return;
		}

		// Clone settings, as necessary.
		$wp_settings = $this->_clone_settings( $wp_settings, array(
			'header1_color' => 'header_color',
			'header2_color' => 'header_color',
			'header3_color' => 'header_color',
			'header4_color' => 'header_color',
			'header5_color' => 'header_color',
			'header6_color' => 'header_color',
			'header1_font' => 'header_font',
			'header2_font' => 'header_font',
			'header3_font' => 'header_font',
			'header4_font' => 'header_font',
			'header5_font' => 'header_font',
			'header6_font' => 'header_font',
			'header1_line_height' => 'header_line_height',
			'header2_line_height' => 'header_line_height',
			'header3_line_height' => 'header_line_height',
			'header4_line_height' => 'header_line_height',
			'header5_line_height' => 'header_line_height',
			'header6_line_height' => 'header_line_height',
		) );

		// Remove legacy settings.
		unset( $wp_settings['header_color'] );
		unset( $wp_settings['header_font'] );
		unset( $wp_settings['header_line_height'] );

		// Store the updated option to remove the legacy setting names.
		update_option( self::$option_name, $wp_settings, 'no' );
	}

	/**
	 * Attempt to migrate settings from an older version of this plugin.
	 *
	 * @access public
	 */
	public function migrate_settings() {

		// Attempt to load settings from the option.
		$wp_settings = get_option( self::$option_name );
		if ( false !== $wp_settings ) {
			return;
		}

		// For each potential value, see if the WordPress option exists.
		// If so, migrate its value into the new array format.
		// If it doesn't exist, just use the default value.
		$settings = new \Apple_Exporter\Settings();
		$all_settings = $settings->all();
		$migrated_settings = array();
		foreach ( $all_settings as $key => $default ) {
			$value = get_option( $key, $default );
			$migrated_settings[ $key ] = $value;
		}

		// Store these settings.
		update_option( self::$option_name, $migrated_settings, 'no' );

		// Delete the options to clean up.
		array_map( 'delete_option', array_keys( $migrated_settings ) );
	}

	/**
	 * Removes formatting settings from the primary settings object.
	 *
	 * @todo Update this to use the new Theme class.
	 *
	 * @access public
	 */
	public function remove_global_formatting_settings() {

		// Loop through formatting settings and remove them from saved settings.
		$formatting = new \Admin_Apple_Settings_Section_Formatting( '' );
		$formatting_settings = array_keys( $formatting->get_settings() );
		$wp_settings = get_option( self::$option_name, array() );
		foreach ( $formatting_settings as $setting_key ) {
			if ( isset( $wp_settings[ $setting_key ] ) ) {
				unset( $wp_settings[ $setting_key ] );
			}
		}

		// Update the option.
		update_option( self::$option_name, $wp_settings, false );
	}

	/**
	 * Upgrades settings and data formats to be compatible with version 1.3.0.
	 *
	 * @access public
	 */
	public function upgrade_to_1_3_0() {

		// Determine if themes have been created yet.
		$themes = new \Admin_Apple_Themes;
		$theme_list = \Apple_Exporter\Theme::get_registry();
		if ( empty( $theme_list ) ) {
			$this->migrate_settings();
			$this->migrate_header_settings();
			$this->migrate_caption_settings();
			$this->migrate_blockquote_settings();
		}

		// Create the default theme, if it does not exist.
		$this->create_default_theme();

		// Move any custom JSON that might have been defined into the theme(s).
		$this->migrate_custom_json_to_themes();

		// Migrate API settings.
		$this->migrate_api_settings();

		// Remove all formatting settings from the primary settings array.
		$this->remove_global_formatting_settings();
	}

	/**
	 * Verifies that the list of keys provided all exist in the settings array.
	 *
	 * @param array $compare The array to compare against the list of keys.
	 * @param array $keys The keys to check.
	 *
	 * @access private
	 * @return bool True if all keys exist in the array, false if not.
	 */
	private function _all_keys_exist( $compare, $keys ) {
		if ( ! is_array( $compare ) || ! is_array( $keys ) ) {
			return false;
		}

		return ( count( $keys ) === count(
			array_intersect_key( $compare, array_combine( $keys, $keys ) ) )
		);
	}

	/**
	 * A generic function to assist with splitting settings for new functionality.
	 *
	 * Accepts an array of settings and a settings map to clone settings from one
	 * key to another.
	 *
	 * @param array $wp_settings An array of settings to modify.
	 * @param array $settings_map A settings map in the format $to => $from.
	 *   Example:
	 *   $settings_map = array(
	 *       'blockquote_color' => 'pullquote_color',
	 *   );
	 *
	 * @access private
	 * @return array The modified settings array.
	 */
	private function _clone_settings( $wp_settings, $settings_map ) {

		// Loop over each setting in the map and clone if conditions are favorable.
		foreach ( $settings_map as $to => $from ) {
			if ( ! isset( $wp_settings[ $to ] ) && isset( $wp_settings[ $from ] ) ) {
				$wp_settings[ $to ] = $wp_settings[ $from ];
			}
		}

		return $wp_settings;
	}
}
