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
	public static $version = '2.0.4';

	/**
	 * Link to support for the plugin on WordPress.org.
	 *
	 * @var string
	 * @access public
	 */
	public static $wordpress_org_support_url = 'https://wordpress.org/support/plugin/publish-to-apple-news';

	/**
	 * Keeps track of whether the plugin is initialized.
	 *
	 * @var bool
	 * @access private
	 */
	private static $is_initialized;

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
	private $contexts = array(
		'post.php',
		'post-new.php',
		'toplevel_page_apple_news_index',
	);

	/**
	 * Maturity ratings.
	 *
	 * @var array
	 * @access public
	 */
	public static $maturity_ratings = array( 'KIDS', 'MATURE', 'GENERAL' );

	/**
	 * Maps a capability to a specific post type, with support for
	 * custom post types.
	 *
	 * @param string $capability The capability to map.
	 * @param string $post_type  The post type to map against.
	 * @return string The mapped capability.
	 */
	public static function get_capability_for_post_type( $capability, $post_type ) {
		$post_type_object = get_post_type_object( $post_type );

		return ! empty( $post_type_object->cap->{$capability} )
			? $post_type_object->cap->{$capability}
			: 'do_not_allow';
	}

	/**
	 * Extracts the filename for bundling an asset.
	 *
	 * This functionality is used in a number of classes that do not have a common
	 * ancestor.
	 *
	 * @param string $path The path to analyze for a filename.
	 * @access public
	 * @return string The filename for an asset to be bundled.
	 */
	public static function get_filename( $path ) {

		// Remove any URL parameters.
		// This is important for sites using WordPress VIP or Jetpack Photon.
		$url_parts = wp_parse_url( $path );
		if ( empty( $url_parts['path'] ) ) {
			return '';
		}

		return str_replace( ' ', '', basename( $url_parts['path'] ) );
	}

	/**
	 * Displays support information for the plugin.
	 *
	 * @param string $format The format in which to return the information.
	 * @param bool   $with_padding Whether to include leading line breaks.
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
			$support_info = wp_strip_all_tags( $support_info );
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
	 * Determines whether the plugin is initialized with the minimum settings.
	 *
	 * @access public
	 * @return bool True if initialized, false if not.
	 */
	public static function is_initialized() {

		// Look up required information in plugin settings, if necessary.
		if ( null === self::$is_initialized ) {
			$settings             = get_option( self::$option_name );
			self::$is_initialized = ( ! empty( $settings['api_channel'] )
				&& ! empty( $settings['api_key'] )
				&& ! empty( $settings['api_secret'] )
			);
		}

		return self::$is_initialized;
	}

	/**
	 * Constructor. Registers action hooks.
	 *
	 * @access public
	 */
	public function __construct() {
		add_action(
			'admin_enqueue_scripts',
			[ $this, 'action_admin_enqueue_scripts' ]
		);
		add_action(
			'enqueue_block_editor_assets',
			[ $this, 'action_enqueue_block_editor_assets' ]
		);
		add_action(
			'plugins_loaded',
			[ $this, 'action_plugins_loaded' ]
		);
		add_filter(
			'update_post_metadata',
			[ $this, 'filter_update_post_metadata' ],
			10,
			5
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
		if ( ! in_array( $hook, $this->contexts, true ) ) {
			return;
		}

		// Ensure media modal assets are enqueued.
		wp_enqueue_media();

		// Enqueue styles.
		wp_enqueue_style(
			$this->plugin_slug . '_cover_art_css',
			plugin_dir_url( __FILE__ ) . '../assets/css/cover-art.css',
			array(),
			self::$version
		);

		// Enqueue scripts.
		wp_enqueue_script(
			$this->plugin_slug . '_cover_art_js',
			plugin_dir_url( __FILE__ ) . '../assets/js/cover-art.js',
			array( 'jquery' ),
			self::$version,
			true
		);

		// Localize scripts.
		wp_localize_script(
			$this->plugin_slug . '_cover_art_js',
			'apple_news_cover_art',
			array(
				'image_sizes'        => Admin_Apple_News::get_image_sizes(),
				'image_too_small'    => esc_html__( 'You must select an image that is at least the height and width specified above.', 'apple-news' ),
				'media_modal_button' => esc_html__( 'Select image', 'apple-news' ),
				'media_modal_title'  => esc_html__( 'Choose an image', 'apple-news' ),
			)
		);
	}

	/**
	 * Enqueues scripts for the block editor.
	 *
	 * @access public
	 */
	public function action_enqueue_block_editor_assets() {
		// Bail if the post type is not one of the Publish to Apple News post types configured in settings.
		if ( ! in_array( get_post_type(), (array) Admin_Apple_Settings_Section::$loaded_settings['post_types'], true ) ) {
			return;
		}

		// Bail if this post isn't using the block editor.
		if ( ! function_exists( 'use_block_editor_for_post' )
			|| ! use_block_editor_for_post( get_the_ID() )
		) {
			return;
		}

		// Add the PluginSidebar.
		wp_enqueue_script(
			'publish-to-apple-news-plugin-sidebar',
			plugins_url( 'build/pluginSidebar.js', __DIR__ ),
			[ 'wp-i18n', 'wp-edit-post' ],
			self::$version,
			true
		);
		$this->inline_locale_data( 'apple-news-plugin-sidebar' );
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

		// Determine if this is a clean install (no settings set yet).
		$settings = get_option( self::$option_name );
		if ( ! empty( $settings ) ) {

			// Handle upgrade to version 1.3.0.
			if ( version_compare( $current_version, '1.3.0', '<' ) ) {
				$this->upgrade_to_1_3_0();
			}

			// Handle upgrade to version 1.4.0.
			if ( version_compare( $current_version, '1.4.0', '<' ) ) {
				$this->upgrade_to_1_4_0();
			}
		}

		// Ensure the default themes are created.
		$this->create_default_theme();

		// Set the database version to the current version in code.
		update_option( 'apple_news_version', self::$version );
	}

	/**
	 * Create the default themes, if they do not exist.
	 *
	 * @access public
	 */
	public function create_default_theme() {

		// Determine if an active theme exists.
		$active_theme = \Apple_Exporter\Theme::get_active_theme_name();
		if ( ! empty( $active_theme ) ) {
			return;
		}

		// Build the theme formatting settings from the base settings array.
		$theme          = new \Apple_Exporter\Theme();
		$options        = \Apple_Exporter\Theme::get_options();
		$wp_settings    = get_option( self::$option_name, array() );
		$theme_settings = array();
		foreach ( $options as $option_key => $option ) {
			if ( isset( $wp_settings[ $option_key ] ) ) {
				$theme_settings[ $option_key ] = $wp_settings[ $option_key ];
			}
		}

		// Negotiate screenshot URL.
		$theme_settings['screenshot_url'] = plugins_url(
			'/assets/screenshots/default.png',
			__DIR__
		);

		// Save the theme and make it active.
		$theme->load( $theme_settings );
		$theme->save();
		$theme->set_active();

		// Load the example themes, if they do not exist.
		$this->load_example_themes();
	}

	/**
	 * A filter callback for update_post_metadata to fix a bug with WordPress
	 * whereby meta values passed via the REST API that require slashing but are
	 * otherwise the same as the existing value in the database will cause a failure
	 * during post save.
	 *
	 * @see \update_metadata
	 *
	 * @param null|bool $check      Whether to allow updating metadata for the given type.
	 * @param int       $object_id  Object ID.
	 * @param string    $meta_key   Meta key.
	 * @param mixed     $meta_value Meta value. Must be serializable if non-scalar.
	 * @param mixed     $prev_value Optional. If specified, only update existing.
	 * @return null|bool True if the conditions are ripe for the fix, otherwise the existing value of $check.
	 */
	public function filter_update_post_metadata( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if ( empty( $prev_value ) ) {
			$old_value = get_metadata( 'post', $object_id, $meta_key );
			if ( 1 === count( $old_value ) ) {
				if ( $old_value[0] === $meta_value ) {
					return true;
				}
			}
		}

		return $check;
	}

	/**
	 * Creates a new Jed instance with specified locale data configuration.
	 *
	 * @param string $to_handle The script handle to attach the inline script to.
	 */
	public function inline_locale_data( $to_handle ) {
		// Define locale data for Jed.
		$locale_data = [
			'' => [
				'domain' => 'publish-to-apple-news',
				'lang'   => get_user_locale(),
			],
		];

		// Pass the Jed configuration to the admin to properly register i18n.
		wp_add_inline_script(
			$to_handle,
			'wp.i18n.setLocaleData( ' . wp_json_encode( $locale_data ) . ", 'publish-to-apple-news' );"
		);
	}

	/**
	 * Initialize the value of api_autosync_delete if not set.
	 *
	 * @access public
	 */
	public function migrate_api_settings() {

		/**
		 * Use the value of api_autosync_update for api_autosync_delete if not set
		 * since that was the previous value used to determine this behavior.
		 */
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
		if ( $this->all_keys_exist(
			$wp_settings,
			array(
				'blockquote_background_color',
				'blockquote_border_color',
				'blockquote_border_style',
				'blockquote_border_width',
				'blockquote_color',
				'blockquote_font',
				'blockquote_line_height',
				'blockquote_size',
				'blockquote_tracking',
			)
		) ) {
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
		$wp_settings = $this->clone_settings(
			$wp_settings,
			array(
				'blockquote_border_color' => 'pullquote_border_color',
				'blockquote_border_style' => 'pullquote_border_style',
				'blockquote_border_width' => 'pullquote_border_width',
				'blockquote_color'        => 'body_color',
				'blockquote_font'         => 'body_font',
				'blockquote_line_height'  => 'body_line_height',
				'blockquote_size'         => 'body_size',
				'blockquote_tracking'     => 'body_tracking',
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
		if ( $this->all_keys_exist(
			$wp_settings,
			array(
				'caption_color',
				'caption_font',
				'caption_line_height',
				'caption_size',
				'caption_tracking',
			)
		) ) {
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
		$wp_settings = $this->clone_settings(
			$wp_settings,
			array(
				'caption_color'       => 'body_color',
				'caption_font'        => 'body_font',
				'caption_line_height' => 'body_line_height',
				'caption_tracking'    => 'body_tracking',
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
		$all_themes = \Apple_Exporter\Theme::get_registry();

		// Get a list of components that may have customized JSON.
		$component_factory = new \Apple_Exporter\Component_Factory();
		$component_factory->initialize();
		$components = $component_factory::get_components();

		// Iterate over components and look for customized JSON for each.
		$json_templates = array();
		foreach ( $components as $component_class ) {

			// Negotiate the component key.
			$component     = new $component_class();
			$component_key = $component->get_component_name();

			// Try to get the custom JSON for this component.
			$custom_json = get_option( 'apple_news_json_' . $component_key );
			if ( empty( $custom_json ) || ! is_array( $custom_json ) ) {
				continue;
			}

			// Loop over custom JSON and add each.
			foreach ( $custom_json as $legacy_key => $values ) {
				$new_key                                      = str_replace( 'apple_news_json_', '', $legacy_key );
				$json_templates[ $component_key ][ $new_key ] = $values;
			}
		}

		// Ensure there is custom JSON to save.
		if ( empty( $json_templates ) ) {
			return;
		}

		// Loop over themes and apply to each.
		foreach ( $all_themes as $theme_name ) {
			$theme = new \Apple_Exporter\Theme();
			$theme->set_name( $theme_name );
			$theme->load();
			$settings                   = $theme->all_settings();
			$settings['json_templates'] = $json_templates;
			$theme->load( $settings );
			$theme->save();
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
		$wp_settings = $this->clone_settings(
			$wp_settings,
			array(
				'header1_color'       => 'header_color',
				'header2_color'       => 'header_color',
				'header3_color'       => 'header_color',
				'header4_color'       => 'header_color',
				'header5_color'       => 'header_color',
				'header6_color'       => 'header_color',
				'header1_font'        => 'header_font',
				'header2_font'        => 'header_font',
				'header3_font'        => 'header_font',
				'header4_font'        => 'header_font',
				'header5_font'        => 'header_font',
				'header6_font'        => 'header_font',
				'header1_line_height' => 'header_line_height',
				'header2_line_height' => 'header_line_height',
				'header3_line_height' => 'header_line_height',
				'header4_line_height' => 'header_line_height',
				'header5_line_height' => 'header_line_height',
				'header6_line_height' => 'header_line_height',
			)
		);

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
		$settings          = new \Apple_Exporter\Settings();
		$all_settings      = $settings->all();
		$migrated_settings = array();
		foreach ( $all_settings as $key => $default ) {
			$value                     = get_option( $key, $default );
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
	 * @access public
	 */
	public function remove_global_formatting_settings() {

		// Loop through formatting settings and remove them from saved settings.
		$formatting_settings = array_keys( \Apple_Exporter\Theme::get_options() );
		$wp_settings         = get_option( self::$option_name, array() );
		foreach ( $formatting_settings as $setting_key ) {
			if ( isset( $wp_settings[ $setting_key ] ) ) {
				unset( $wp_settings[ $setting_key ] );
			}
		}

		// Update the option.
		update_option( self::$option_name, $wp_settings, false );
	}

	/**
	 * Migrates table settings for a theme, using other settings in the
	 * theme to inform sensible defaults.
	 *
	 * @param string $theme_name The theme name for which settings should be migrated.
	 */
	public function migrate_table_settings( $theme_name ) {

		// Load the theme settings from the database.
		$theme = new \Apple_Exporter\Theme();
		$theme->set_name( $theme_name );
		$theme->load();

		// Establish mapping between old settings and new.
		$settings_map = array(
			'table_border_color'            => 'blockquote_border_color',
			'table_border_style'            => 'blockquote_border_style',
			'table_body_background_color'   => 'body_background_color',
			'table_body_color'              => 'body_color',
			'table_body_font'               => 'body_font',
			'table_body_line_height'        => 'body_line_height',
			'table_body_size'               => 'body_size',
			'table_body_tracking'           => 'body_tracking',
			'table_header_background_color' => 'blockquote_background_color',
			'table_header_color'            => 'blockquote_color',
			'table_header_font'             => 'body_font',
			'table_header_line_height'      => 'blockquote_line_height',
			'table_header_size'             => 'blockquote_size',
			'table_header_tracking'         => 'blockquote_tracking',
		);

		// Set the new values based on the old.
		foreach ( $settings_map as $table_setting => $reference_setting ) {
			$theme->set_value(
				$table_setting,
				$theme->get_value( $reference_setting )
			);
		}

		// Default the border width to 1.
		$theme->set_value( 'table_border_width', 1 );

		// Save changes to this theme.
		$theme->save();
	}

	/**
	 * Upgrades settings and data formats to be compatible with version 1.3.0.
	 *
	 * @access public
	 */
	public function upgrade_to_1_3_0() {

		// Determine if themes have been created yet.
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
	 * Upgrades settings and data formats to be compatible with version 1.4.0.
	 *
	 * @access public
	 */
	public function upgrade_to_1_4_0() {

		// Set intelligent defaults for table styles in all themes.
		$theme_list = \Apple_Exporter\Theme::get_registry();
		if ( ! empty( $theme_list ) && is_array( $theme_list ) ) {
			foreach ( $theme_list as $theme ) {
				$this->migrate_table_settings( $theme );
			}
		}

		// Default cover art to on for existing installations.
		$wp_settings                     = get_option( self::$option_name );
		$wp_settings['enable_cover_art'] = 'yes';
		update_option( self::$option_name, $wp_settings, 'no' );
	}

	/**
	 * Load example themes into the theme list.
	 *
	 * @access protected
	 */
	protected function load_example_themes() {

		// Set configuration for example themes.
		$example_themes = array(
			'classic'  => __( 'Classic', 'apple-news' ),
			'colorful' => __( 'Colorful', 'apple-news' ),
			'dark'     => __( 'Dark', 'apple-news' ),
			'default'  => __( 'Default', 'apple-news' ),
			'modern'   => __( 'Modern', 'apple-news' ),
			'pastel'   => __( 'Pastel', 'apple-news' ),
		);

		// Loop over example theme configuration and load each.
		foreach ( $example_themes as $slug => $name ) {

			// Determine if the theme already exists.
			$theme = new \Apple_Exporter\Theme();
			$theme->set_name( $name );
			if ( $theme->load() ) {
				continue;
			}

			// Load the theme data from the JSON configuration file.
			$filename = dirname( __DIR__ ) . '/assets/themes/' . $slug . '.json';
			$options  = json_decode( file_get_contents( $filename ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			// Negotiate screenshot URL.
			$options['screenshot_url'] = plugins_url(
				'/assets/screenshots/' . $slug . '.png',
				__DIR__
			);

			// Save the theme.
			$theme->load( $options );
			$theme->save();
		}
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
	private function all_keys_exist( $compare, $keys ) {
		if ( ! is_array( $compare ) || ! is_array( $keys ) ) {
			return false;
		}

		return ( count( $keys ) === count(
			array_intersect_key( $compare, array_combine( $keys, $keys ) )
		)
		);
	}

	/**
	 * A generic function to assist with splitting settings for new functionality.
	 *
	 * Accepts an array of settings and a settings map to clone settings from one
	 * key to another.
	 *
	 * @param array $wp_settings  An array of settings to modify.
	 * @param array $settings_map A settings map in the format $to => $from.
	 * @access private
	 * @return array The modified settings array.
	 */
	private function clone_settings( $wp_settings, $settings_map ) {

		// Loop over each setting in the map and clone if conditions are favorable.
		foreach ( $settings_map as $to => $from ) {
			if ( ! isset( $wp_settings[ $to ] ) && isset( $wp_settings[ $from ] ) ) {
				$wp_settings[ $to ] = $wp_settings[ $from ];
			}
		}

		return $wp_settings;
	}
}
