<?php
/**
 * Base plugin class with core plugin information and shared functionality
 * between frontend and backend plugin classes.
 *
 * @author  Federico Ramirez
 * @since   0.2.0
 */
class Apple_News {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 * @access protected
	 */
	protected $plugin_slug = 'apple_news';

	/**
	 * Plugin domain.
	 *
	 * @var string
	 * @access protected
	 */
	protected $plugin_domain = 'apple-news';

	/**
	 * Option name for settings.
	 *
	 * @var string
	 * @access public
	 */
	public static $option_name = 'apple_news_settings';

	/**
	 * Link to support for the plugin on WordPress.org.
	 *
	 * @var string
	 * @access public
	 */
	public static $wordpress_org_support_url = 'https://wordpress.org/support/plugin/publish-to-apple-news';

	/**
	 * Link to support for the plugin on github.
	 *
	 * @var string
	 * @access public
	 */
	public static $github_support_url = 'https://github.com/alleyinteractive/apple-news/issues';

	/**
	 * Plugin version.
	 *
	 * @var string
	 * @access public
	 */
	public static $version = '1.2.0';

	/**
	 * Extracts the filename for bundling an asset.
	 * This functionality is used in a number of classes that do not have a common ancestor.
	 *
	 * @var string
	 * @access protected
	 */
	public static function get_filename( $path ) {
		// Remove any URL parameters.
		// This is important for sites using WordPress VIP or Jetpack Photon.
		$url_parts = parse_url( $path );
		if ( empty( $url_parts['path'] ) ) {
			return '';
		}

		// Get the filename
		$filename = basename( $url_parts['path'] );

		// Remove any spaces and return the filename
		return str_replace( ' ', '', $filename );
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

		// Check for presence of legacy font setting.
		if ( ! empty( $wp_settings['header_font'] ) ) {
			$wp_settings['header1_font'] = $wp_settings['header_font'];
			$wp_settings['header2_font'] = $wp_settings['header_font'];
			$wp_settings['header3_font'] = $wp_settings['header_font'];
			$wp_settings['header4_font'] = $wp_settings['header_font'];
			$wp_settings['header5_font'] = $wp_settings['header_font'];
			$wp_settings['header6_font'] = $wp_settings['header_font'];
		}

		// Check for presence of legacy color setting.
		if ( ! empty( $wp_settings['header_color'] ) ) {
			$wp_settings['header1_color'] = $wp_settings['header_color'];
			$wp_settings['header2_color'] = $wp_settings['header_color'];
			$wp_settings['header3_color'] = $wp_settings['header_color'];
			$wp_settings['header4_color'] = $wp_settings['header_color'];
			$wp_settings['header5_color'] = $wp_settings['header_color'];
			$wp_settings['header6_color'] = $wp_settings['header_color'];
		}

		// Check for presence of legacy line height setting.
		if ( ! empty( $wp_settings['header_line_height'] ) ) {
			$wp_settings['header1_line_height'] = $wp_settings['header_line_height'];
			$wp_settings['header2_line_height'] = $wp_settings['header_line_height'];
			$wp_settings['header3_line_height'] = $wp_settings['header_line_height'];
			$wp_settings['header4_line_height'] = $wp_settings['header_line_height'];
			$wp_settings['header5_line_height'] = $wp_settings['header_line_height'];
			$wp_settings['header6_line_height'] = $wp_settings['header_line_height'];
		}

		return $wp_settings;
	}

	/**
	 * Attempt to migrate settings from an older version of this plugin
	 *
	 * @param Settings $settings
	 */
	public function migrate_settings( $settings ) {
		$migrated_settings = array();

		// For each potential value, see if the WordPress option exists.
		// If so, migrate its value into the new array format.
		// If it doesn't exist, just use the default value.
		foreach ( $settings->all() as $key => $default ) {
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
	 * Displays support information for the plugin.
	 *
	 * @var string $format
	 * @var boolean $with_padding
	 * @return string
	 * @access public
	 */
	public static function get_support_info( $format = 'html', $with_padding = true ) {
		$support_info = sprintf(
			__( 'If you need assistance with this issue, please reach out for support on <a href="%s">WordPress.org</a> or <a href="%s">github</a>.', 'apple-news' ),
			esc_url( self::$wordpress_org_support_url ),
			esc_url( self::$github_support_url )
		);

		if ( 'text' === $format ) {
			$support_info = strip_tags( $support_info );
		}

		if ( $with_padding ) {
			if ( 'text' === $format ) {
				$support_info = "\n\n" . $support_info;
			} else {
				$support_info = '<br /><br />' . $support_info;
			}
		}

		return $support_info;
	}
}
