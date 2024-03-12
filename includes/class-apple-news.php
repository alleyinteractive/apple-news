<?php
/**
 * Publish to Apple News Includes: Apple_News class
 *
 * Contains a class which is used to manage the Publish to Apple News plugin.
 *
 * @package Apple_News
 * @since 0.2.0
 */

use Apple_Exporter\Component_Factory;
use Apple_Exporter\Settings;
use Apple_Exporter\Theme;

/**
 * Base plugin class with core plugin information and shared functionality
 * between frontend and backend plugin classes.
 *
 * @author Federico Ramirez
 * @since 0.2.0
 */
class Apple_News {

	/**
	 * An array of bundle hashes that match an asset URL to a bundle filename.
	 *
	 * @var array
	 */
	private static array $bundle_hashes = [];

	/**
	 * Link to support for the plugin on GitHub.
	 *
	 * @var string
	 * @access public
	 */
	public static string $github_support_url = 'https://github.com/alleyinteractive/apple-news/issues';

	/**
	 * Option name for settings.
	 *
	 * @var string
	 * @access public
	 */
	public static string $option_name = 'apple_news_settings';

	/**
	 * Plugin version.
	 *
	 * @var string
	 * @access public
	 */
	public static string $version = '2.4.6';

	/**
	 * Link to support for the plugin on WordPress.org.
	 *
	 * @var string
	 * @access public
	 */
	public static string $wordpress_org_support_url = 'https://wordpress.org/support/plugin/publish-to-apple-news';

	/**
	 * Keeps track of whether the plugin is initialized.
	 *
	 * @var bool
	 * @access private
	 */
	private static bool $is_initialized = false;

	/**
	 * Plugin domain.
	 *
	 * @var string
	 * @access protected
	 */
	protected string $plugin_domain = 'apple-news';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 * @access protected
	 */
	protected string $plugin_slug = 'apple_news';

	/**
	 * An array of contexts where assets should be enqueued.
	 *
	 * @var array
	 * @access private
	 */
	private array $contexts = [
		'post.php',
		'post-new.php',
		'toplevel_page_apple_news_index',
	];

	/**
	 * Maturity ratings.
	 *
	 * @var array
	 * @access public
	 */
	public static $maturity_ratings = [ 'KIDS', 'MATURE', 'GENERAL' ];

	/**
	 * A helper function for getting authors for a post, which supports native
	 * WordPress authors as well as Co-Authors Plus. Like the coauthors function,
	 * it must be used in the loop.
	 *
	 * @param ?string $between      Delimiter that should appear between the co-authors.
	 * @param ?string $between_last Delimiter that should appear between the last two co-authors.
	 * @param ?string $before       What should appear before the presentation of co-authors.
	 * @param ?string $after        What should appear after the presentation of co-authors.
	 *
	 * @return string The author list, formatted according to the given options.
	 */
	public static function get_authors( $between = null, $between_last = null, $before = null, $after = null ) {
		global $post;

		// Bail out if we don't have a post.
		if ( empty( $post ) ) {
			return '';
		}

		// Get information about the currently used theme.
		$theme = Theme::get_used();

		/**
		 * Allows for changing the option to use Co-Authors Plus for authorship.
		 * Defaults to using Co-Authors Plus if the `coauthors` function is defined.
		 *
		 * @since 2.1.3
		 *
		 * @param bool $use_cap Whether to use Co-Authors Plus for authors.
		 * @param int  $post_id The post ID being processed.
		 */
		$use_cap = apply_filters( 'apple_news_use_coauthors', function_exists( 'coauthors' ), get_the_ID() );

		// Get theme option for byline links. True if set to yes.
		// Ignore html option if setting metadata.
		$use_author_links = $theme->get_value( 'author_links' ) && 'yes' === $theme->get_value( 'author_links' ) && 'APPLE_NEWS_DELIMITER' !== $between;

		// Handle CAP authorship.
		if ( $use_cap ) {
			return $use_author_links
				? coauthors_posts_links( $between, $between_last, $before, $after, false )
				: coauthors( $between, $between_last, $before, $after, false );
		}

		// Get author from post.
		$post_author_id = intval( $post->post_author );
		$author         = ucfirst( get_the_author_meta( 'display_name', $post_author_id ) );

		// If we have byline links enabled.
		if ( $use_author_links ) {
			/**
			 * Allows for modification of the byline link used by WordPress authors and CoAuthors Plus.
			 *
			 * @since 2.3.0
			 *
			 * @param string $link            The author link to be filtered.
			 * @param int    $author_id       Author id for the URL being modified.
			 * @param string $author_nicename Author nicename for the URL being modified.
			 */
			$byline_url = apply_filters(
				'apple_news_author_author_link',
				get_author_posts_url( $post_author_id ),
				$post_author_id,
				get_the_author_meta( 'nicename', $post_author_id )
			);
			return '<a href="' . esc_url( $byline_url ) . '" rel="author">' . esc_html( $author ) . '</a>';
		}

		return $author;
	}

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
	 *
	 * @access public
	 * @return string The filename for an asset to be bundled.
	 */
	public static function get_filename( $path ) {

		// If we already have a hash for this path, return it.
		if ( isset( self::$bundle_hashes[ $path ] ) ) {
			return self::$bundle_hashes[ $path ];
		}

		// Remove any URL parameters.
		// This is important for sites using WordPress VIP or Jetpack Photon.
		$url_parts = wp_parse_url( $path );
		if ( empty( $url_parts['path'] ) ) {
			return '';
		}

		// Compute base filename.
		$filename = str_replace( ' ', '', basename( $url_parts['path'] ) );

		// Ensure there are no filename collisions with existing bundles.
		$bundle_filenames = array_values( self::$bundle_hashes );
		sort( $bundle_filenames );
		if ( in_array( $filename, $bundle_filenames, true ) ) {
			$file_number    = 1;
			$filename_parts = pathinfo( $filename );
			$pattern        = sprintf(
				'/^%s-([0-9]+)\.%s$/',
				preg_quote( $filename_parts['filename'], '/' ),
				preg_quote( $filename_parts['extension'], '/' )
			);
			foreach ( self::$bundle_hashes as $bundle_filename ) {
				if ( preg_match( $pattern, $bundle_filename, $matches ) ) {
					$file_number = max( $file_number, (int) $matches[1] + 1 );
				}
			}

			// Apply the new filename to avoid collisions.
			$filename = sprintf(
				'%s-%d.%s',
				$filename_parts['filename'],
				$file_number,
				$filename_parts['extension']
			);
		}

		// Store this path/filename pair in the bundle hashes property for future use.
		self::$bundle_hashes[ $path ] = $filename;

		return $filename;
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
	 * Determines whether the currently selected theme is the default theme that
	 * ships with the plugin or not.
	 *
	 * Returns true only if the name of the theme is "Default" and the config
	 * options for the theme match the default theme from the plugin's source
	 * files.
	 *
	 * @return bool True if the default theme is the current active theme, false otherwise.
	 */
	public static function is_default_theme() {
		// If the theme is not named "Default", then it is customized, and is not the default theme.
		$active_theme = Theme::get_active_theme_name();
		if ( __( 'Default', 'apple-news' ) !== $active_theme ) {
			return false;
		}

		// If the theme _is_ named "Default", check its configuration against the default.
		$theme = new Theme();
		$theme->set_name( $active_theme );
		$theme->load();

		return $theme->is_default();
	}

	/**
	 * Determines whether the plugin is initialized with the minimum settings.
	 *
	 * @access public
	 * @return bool True if initialized, false if not.
	 */
	public static function is_initialized(): bool {
		// Check if the necessary plugin settings are initialized.
		if ( false === self::$is_initialized ) {
			$settings = get_option( self::$option_name );

			$has_api_settings = ! empty( $settings['api_channel'] )
								&& ! empty( $settings['api_key'] )
								&& ! empty( $settings['api_secret'] );

			$has_api_config = ! empty( $settings['api_config_file'] )
								|| ! empty( $settings['api_config_file_input'] );

			self::$is_initialized = $has_api_settings || $has_api_config;
		}

		return self::$is_initialized;
	}

	/**
	 * Returns new WP_Error if uninitialized.
	 *
	 * @access public
	 * @return WP_Error|null error if uninitialized.
	 */
	public static function has_uninitialized_error(): WP_Error|null {
		if ( ! self::is_initialized() ) {
			return new WP_Error(
				'apple_news_bad_operation',
				__( 'You must enter your API information on the settings page before using Publish to Apple News.', 'apple-news' ),
				[
					'status' => 400,
				]
			);
		}
		return null;
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
		add_filter(
			'author_link',
			[ $this, 'filter_author_link' ],
			10,
			3
		);
		add_filter(
			'the_author',
			[ $this, 'filter_the_author' ],
			10,
			3
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

		// Enqueue the script for cover images in the classic editor.
		wp_enqueue_script(
			$this->plugin_slug . '_cover_image_js',
			plugin_dir_url( __FILE__ ) . '../assets/js/cover-image.js',
			[ 'jquery' ],
			self::$version,
			true
		);
	}

	/**
	 * Enqueues scripts for the block editor.
	 *
	 * @access public
	 */
	public function action_enqueue_block_editor_assets(): void {
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

		// Get the path to the PHP file containing the dependencies.
		$dependency_file = dirname( __DIR__ ) . '/build/pluginSidebar.asset.php';
		// Validate file is considered successful if it has no issues (0) or is a Windows filepath (2).
		if ( ! file_exists( $dependency_file ) || ! in_array( validate_file( $dependency_file ), [ 0, 2 ], true ) ) {
			return;
		}

		// Try to load the dependencies.
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		$dependencies = require $dependency_file;
		if ( empty( $dependencies['dependencies'] ) || ! is_array( $dependencies['dependencies'] ) ) {
			return;
		}

		// Add the PluginSidebar.
		wp_enqueue_script(
			'publish-to-apple-news-plugin-sidebar',
			plugins_url( 'build/pluginSidebar.js', __DIR__ ),
			$dependencies['dependencies'],
			$dependencies['version'],
			true
		);
		$this->inline_locale_data( 'apple-news-plugin-sidebar' );
	}

	/**
	 * Action hook callback for plugins_loaded.
	 *
	 * @since 1.3.0
	 */
	public function action_plugins_loaded(): void {

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

			// Handle upgrade to version 2.4.0.
			if ( version_compare( $current_version, '2.4.0', '<' ) ) {
				$this->upgrade_to_2_4_0();
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
	public function create_default_theme(): void {

		// Determine if an active theme exists.
		$active_theme = Theme::get_active_theme_name();
		if ( ! empty( $active_theme ) ) {
			return;
		}

		// Build the theme formatting settings from the base settings array.
		$theme          = new Theme();
		$options        = Theme::get_options();
		$wp_settings    = get_option( self::$option_name, [] );
		$theme_settings = [];
		foreach ( array_keys( $options ) as $option_key ) {
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
	 * @param bool|null $check Whether to allow updating metadata for the given type.
	 * @param int       $object_id Object ID.
	 * @param string    $meta_key Meta key.
	 * @param mixed     $meta_value Meta value. Must be serializable if non-scalar.
	 * @param mixed     $prev_value Optional. If specified, only update existing.
	 *
	 * @return null|bool True, if the conditions are ripe for the fix, otherwise the existing value of $check.
	 * @see \update_metadata
	 */
	public function filter_update_post_metadata( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if ( empty( $prev_value ) ) {
			$old_value = get_metadata( 'post', $object_id, $meta_key );
			if ( false !== $old_value && is_array( $old_value ) && 1 === count( $old_value ) ) {
				if ( $old_value[0] === $meta_value ) {
					return true;
				}
			}
		}

		return $check;
	}

	/**
	 * A filter callback for author_link for coauthors URLs.
	 *
	 * @param string $link            The URL to the author's page.
	 * @param int    $author_id       The author's ID.
	 * @param string $author_nicename The author's nice name.
	 * @return string updated $author attribute.
	 */
	public function filter_author_link( $link, $author_id, $author_nicename ) {
		/**
		 * Allows for modification of the byline link used by WordPress authors and CoAuthors Plus.
		 *
		 * @since 2.3.0
		 *
		 * @param string $link            The author link to be filtered.
		 * @param int    $author_id       Author id for the URL being modified.
		 * @param string $author_nicename Author nicename for the URL being modified.
		 */
		return apply_filters( 'apple_news_author_author_link', $link, $author_id, $author_nicename );
	}

	/**
	 * A filter callback for the_author to wrap authors in byline tag if supported.
	 *
	 * @param string $author author name.
	 *
	 * @return string updated $author attribute.
	 */
	public function filter_the_author( $author ) {
		return ucfirst( $author );
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
	public function migrate_api_settings(): void {

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
	public function migrate_blockquote_settings(): void {

		// Check for the presence of blockquote-specific settings.
		$wp_settings = get_option( self::$option_name );
		if ( $this->all_keys_exist(
			$wp_settings,
			[
				'blockquote_background_color',
				'blockquote_border_color',
				'blockquote_border_style',
				'blockquote_border_width',
				'blockquote_color',
				'blockquote_font',
				'blockquote_line_height',
				'blockquote_size',
				'blockquote_tracking',
			]
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
			[
				'blockquote_border_color' => 'pullquote_border_color',
				'blockquote_border_style' => 'pullquote_border_style',
				'blockquote_border_width' => 'pullquote_border_width',
				'blockquote_color'        => 'body_color',
				'blockquote_font'         => 'body_font',
				'blockquote_line_height'  => 'body_line_height',
				'blockquote_size'         => 'body_size',
				'blockquote_tracking'     => 'body_tracking',
			]
		);

		// Store the updated option to save the new setting names.
		update_option( self::$option_name, $wp_settings, 'no' );
	}

	/**
	 * Migrate legacy caption settings to new format.
	 *
	 * @access public
	 */
	public function migrate_caption_settings(): void {

		// Check for the presence of caption-specific settings.
		$wp_settings = get_option( self::$option_name );
		if ( $this->all_keys_exist(
			$wp_settings,
			[
				'caption_color',
				'caption_font',
				'caption_line_height',
				'caption_size',
				'caption_tracking',
			]
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
			[
				'caption_color'       => 'body_color',
				'caption_font'        => 'body_font',
				'caption_line_height' => 'body_line_height',
				'caption_tracking'    => 'body_tracking',
			]
		);

		// Store the updated option to save the new setting names.
		update_option( self::$option_name, $wp_settings, 'no' );
	}

	/**
	 * Migrates standalone customized JSON to each installed theme.
	 *
	 * @access public
	 */
	public function migrate_custom_json_to_themes(): void {

		// Get a list of all themes that need to be updated.
		$all_themes = Theme::get_registry();

		// Get a list of components that may have customized JSON.
		$component_factory = new Component_Factory();
		$component_factory->initialize();
		$components = $component_factory::get_components();

		// Iterate over components and look for customized JSON for each.
		$json_templates = [];
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
			$theme = new Theme();
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
	public function migrate_header_settings(): void {

		// Check for the presence of any legacy header setting.
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
			[
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
			]
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
	public function migrate_settings(): void {

		// Attempt to load settings from the option.
		$wp_settings = get_option( self::$option_name );
		if ( false !== $wp_settings ) {
			return;
		}

		// For each potential value, see if the WordPress option exists.
		// If so, migrate its value into the new array format.
		// If it doesn't exist, just use the default value.
		$settings          = new Settings();
		$all_settings      = $settings->all();
		$migrated_settings = [];
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
	public function remove_global_formatting_settings(): void {

		// Loop through formatting settings and remove them from saved settings.
		$formatting_settings = array_keys( Theme::get_options() );
		$wp_settings         = get_option( self::$option_name, [] );
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
	public function migrate_table_settings( $theme_name ): void {

		// Load the theme settings from the database.
		$theme = new Theme();
		$theme->set_name( $theme_name );
		$theme->load();

		// Establish mapping between old settings and new.
		$settings_map = [
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
		];

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
	public function upgrade_to_1_3_0(): void {

		// Determine if themes have been created yet.
		$theme_list = Theme::get_registry();
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
	public function upgrade_to_1_4_0(): void {

		// Set intelligent defaults for table styles in all themes.
		$theme_list = Theme::get_registry();
		if ( ! empty( $theme_list ) && is_array( $theme_list ) ) {
			foreach ( $theme_list as $theme ) {
				$this->migrate_table_settings( $theme );
			}
		}
	}

	/**
	 * Upgrades settings and data formats to be compatible with version 2.4.0.
	 */
	public function upgrade_to_2_4_0(): void {
		// Update author and byline theme formats to new convention.
		$registry = Theme::get_registry();

		foreach ( $registry as $theme_name ) {
			$theme_object = Admin_Apple_Themes::get_theme_by_name( $theme_name );
			$save_theme   = false;

			// Update author_format.
			if ( 'by #author#' === $theme_object->get_value( 'author_format' ) ) {
				$theme_object->set_value( 'author_format', 'By #author#' );
				$save_theme = true;
			}
			// Update byline_format.
			if ( 'by #author# | #M j, Y | g:i A#' === $theme_object->get_value( 'byline_format' ) ) {
				$theme_object->set_value( 'byline_format', 'By #author# | #M j, Y | g:i A#' );
				$save_theme = true;
			}
			// If theme options have changed, write to db.
			if ( $save_theme ) {
				$theme_object->save();
			}
		}

		$automation = [];

		// Get legacy settings, if they exist.
		$priority_mappings = get_option( 'apple_news_section_priority_mappings', [] );
		$taxonomy_mappings = get_option( 'apple_news_section_taxonomy_mappings', [] );
		$theme_mappings    = get_option( 'apple_news_section_theme_mappings', [] );
		$mapping_taxonomy  = apply_filters( 'apple_news_section_taxonomy', 'category' );

		// Get an ordered list of sections.
		if ( ! empty( $priority_mappings ) ) {
			arsort( $priority_mappings );
			$sections = array_keys( $priority_mappings );
		} elseif ( ! empty( $taxonomy_mappings ) ) {
			$sections = array_keys( $taxonomy_mappings );
		} elseif ( ! empty( $theme_mappings ) ) {
			$sections = array_keys( $theme_mappings );
		} else {
			return;
		}

		// Loop through sections, in priority order, and convert settings to Automation.
		foreach ( $sections as $section_id ) {
			foreach ( $taxonomy_mappings[ $section_id ] ?? [] as $term_id ) {
				// Add the mapping for this term ID to the section ID.
				$automation[] = [
					'field'    => 'links.sections',
					'taxonomy' => $mapping_taxonomy,
					'term_id'  => $term_id,
					'value'    => $section_id,
				];

				// Apply theme mapping, if set.
				if ( ! empty( $theme_mappings[ $section_id ] ) ) {
					$automation[] = [
						'field'    => 'theme',
						'taxonomy' => $mapping_taxonomy,
						'term_id'  => $term_id,
						'value'    => $theme_mappings[ $section_id ],
					];
				}
			}
		}

		// Update Automation settings.
		update_option( Apple_News\Admin\Automation::OPTION_KEY, $automation );
		delete_option( 'apple_news_section_priority_mappings' );
		delete_option( 'apple_news_section_taxonomy_mappings' );
		delete_option( 'apple_news_section_theme_mappings' );
	}

	/**
	 * Load example themes into the theme list.
	 *
	 * @access protected
	 */
	protected function load_example_themes(): void {

		// Set configuration for example themes.
		$example_themes = [
			'classic'  => __( 'Classic', 'apple-news' ),
			'colorful' => __( 'Colorful', 'apple-news' ),
			'dark'     => __( 'Dark', 'apple-news' ),
			'default'  => __( 'Default', 'apple-news' ),
			'modern'   => __( 'Modern', 'apple-news' ),
			'pastel'   => __( 'Pastel', 'apple-news' ),
		];

		// Loop over example theme configuration and load each.
		foreach ( $example_themes as $slug => $name ) {

			// Determine if the theme already exists.
			$theme = new Theme();
			$theme->set_name( $name );
			if ( $theme->load() ) {
				continue;
			}

			// Load the theme data from the JSON configuration file.
			$options = json_decode( file_get_contents( dirname( __DIR__ ) . '/assets/themes/' . $slug . '.json' ), true ); // phpcs:ignore

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
	 *
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
