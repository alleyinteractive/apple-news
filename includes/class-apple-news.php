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
	public static $version = '1.2.3';

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
	 * @static
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
			'image_sizes' => Admin_Apple_News::$image_sizes,
			'image_too_small' => esc_html__( 'You must select an image that is at least the minimum height and width specified above.', 'apple-news' ),
			'media_modal_button' => esc_html__( 'Select image', 'apple-news' ),
			'media_modal_title' => esc_html__( 'Choose an image', 'apple-news' ),
		) );
	}

	/**
	 * Initialize the value of api_autosync_delete if not set.
	 *
	 * @param array $wp_settings An array of settings loaded from WP options.
	 *
	 * @access public
	 * @return array The modified settings array.
	 */
	public function migrate_api_settings( $wp_settings ) {

		// Use the value of api_autosync_update for api_autosync_delete if not set
		// since that was the previous value used to determine this behavior.
		if ( empty( $wp_settings['api_autosync_delete'] )
		     && ! empty( $wp_settings['api_autosync_update'] )
		) {
			$wp_settings['api_autosync_delete'] = $wp_settings['api_autosync_update'];
			update_option( self::$option_name, $wp_settings, 'no' );
		}

		return $wp_settings;
	}

	/**
	 * Migrate legacy blockquote settings to new format.
	 *
	 * @param array $wp_settings An array of settings loaded from WP options.
	 *
	 * @access public
	 * @return array The modified settings array.
	 */
	public function migrate_blockquote_settings( $wp_settings ) {

		// Check for the presence of blockquote-specific settings.
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
			return $wp_settings;
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

		return $wp_settings;
	}

	/**
	 * Migrate legacy caption settings to new format.
	 *
	 * @param array $wp_settings An array of settings loaded from WP options.
	 *
	 * @access public
	 * @return array The modified settings array.
	 */
	public function migrate_caption_settings( $wp_settings ) {

		// Check for the presence of caption-specific settings.
		if ( $this->_all_keys_exist( $wp_settings, array(
			'caption_color',
			'caption_font',
			'caption_line_height',
			'caption_size',
			'caption_tracking',
		) ) ) {
			return $wp_settings;
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

		return $wp_settings;
	}

	/**
	 * Migrate legacy header settings to new format.
	 *
	 * @param array $wp_settings An array of settings loaded from WP options.
	 *
	 * @access public
	 * @return array The modified settings array.
	 */
	public function migrate_header_settings( $wp_settings ) {

		// Check for presence of any legacy header setting.
		if ( empty( $wp_settings['header_font'] )
		     && empty( $wp_settings['header_color'] )
		     && empty( $wp_settings['header_line_height'] )
		) {
			return $wp_settings;
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

		return $wp_settings;
	}

	/**
	 * Attempt to migrate settings from an older version of this plugin.
	 *
	 * @param array|object $wp_settings Settings loaded from WP options.
	 *
	 * @access public
	 * @return array The modified settings array.
	 */
	public function migrate_settings( $wp_settings ) {

		// If we are not given an object to update to an array, bail.
		if ( ! is_object( $wp_settings ) ) {
			return $wp_settings;
		}

		// Try to get all settings as an array to be merged.
		$all_settings = $wp_settings->all();
		if ( empty( $all_settings ) || ! is_array( $all_settings ) ) {
			return $wp_settings;
		}

		// For each potential value, see if the WordPress option exists.
		// If so, migrate its value into the new array format.
		// If it doesn't exist, just use the default value.
		$migrated_settings = array();
		foreach ( $all_settings as $key => $default ) {
			$value = get_option( $key, $default );
			$migrated_settings[ $key ] = $value;
		}

		// Store these settings
		update_option( self::$option_name, $migrated_settings, 'no' );

		// Delete the options to clean up
		array_map( 'delete_option', array_keys( $migrated_settings ) );

		return $migrated_settings;
	}

	/**
	 * Validate settings and see if any updates need to be performed.
	 *
	 * @param array|object $wp_settings Settings loaded from WP options.
	 *
	 * @access public
	 * @return array The modified settings array.
	 */
	public function validate_settings( $wp_settings ) {

		// If this option doesn't exist, either the site has never installed
		// this plugin or they may be using an old version with individual
		// options. To be safe, attempt to migrate values. This will happen only
		// once.
		if ( false === $wp_settings ) {
			$wp_settings = $this->migrate_settings( $wp_settings );
		}

		// Check for presence of legacy header settings and migrate to new.
		$wp_settings = $this->migrate_header_settings( $wp_settings );

		// Check for presence of legacy API settings and migrate to new.
		$wp_settings = $this->migrate_api_settings( $wp_settings );

		// Ensure caption settings are set properly.
		$wp_settings = $this->migrate_caption_settings( $wp_settings );

		return $wp_settings;
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
