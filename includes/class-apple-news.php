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
	 */
	const GITHUB_SUPPORT = 'https://github.com/alleyinteractive/apple-news/issues';

	/**
	 * Option name for settings.
	 */
	const OPTION_NAME = 'apple_news_settings';

	/**
	 * Link to support for the plugin on WordPress.org.
	 */
	const WP_SUPPORT = 'https://wordpress.org/support/plugin/publish-to-apple-news';

	/**
	 * Plugin version.
	 *
	 * @var string
	 * @access public
	 */
	public static $version = '1.2.1';

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
			esc_url( self::WP_SUPPORT ),
			__( 'WordPress.org', 'apple-news' ),
			__( 'or', 'apple-news' ),
			esc_url( self::GITHUB_SUPPORT ),
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
		     && ! empty( $wp_settings['api_autosync_update'] ) ) {

			$wp_settings['api_autosync_delete'] = $wp_settings['api_autosync_update'];
			update_option( self::OPTION_NAME, $wp_settings, 'no' );
		}

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
		if ( isset( $wp_settings['caption_color'] )
		     && isset( $wp_settings['caption_font'] )
		     && isset( $wp_settings['caption_line_height'] )
		     && isset( $wp_settings['caption_size'] )
		     && isset( $wp_settings['caption_tracking'] )
		) {
			return $wp_settings;
		}

		// Migrate settings, as necessary.
		$settings = array( 'color', 'font', 'line_height', 'size', 'tracking' );
		foreach ( $settings as $setting ) {
			$body_setting = 'body_' . $setting;
			$caption_setting = 'caption_' . $setting;
			if ( ! isset( $wp_settings[ $caption_setting ] )
			     && isset( $wp_settings[ $body_setting ] )
			) {
				$wp_settings[ $caption_setting ] = $wp_settings[ $body_setting ];

				// Adjust font size down by 2 to match legacy handling.
				if ( 'size' === $setting ) {
					$wp_settings[ $caption_setting ] -= 2;
				}
			}
		}

		// Store the updated option to save the new setting names.
		update_option( self::OPTION_NAME, $wp_settings, 'no' );

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

		// Check for presence of legacy font setting.
		if ( ! empty( $wp_settings['header_font'] ) ) {
			$wp_settings['header1_font'] = $wp_settings['header_font'];
			$wp_settings['header2_font'] = $wp_settings['header_font'];
			$wp_settings['header3_font'] = $wp_settings['header_font'];
			$wp_settings['header4_font'] = $wp_settings['header_font'];
			$wp_settings['header5_font'] = $wp_settings['header_font'];
			$wp_settings['header6_font'] = $wp_settings['header_font'];
			unset( $wp_settings['header_font'] );
		}

		// Check for presence of legacy color setting.
		if ( ! empty( $wp_settings['header_color'] ) ) {
			$wp_settings['header1_color'] = $wp_settings['header_color'];
			$wp_settings['header2_color'] = $wp_settings['header_color'];
			$wp_settings['header3_color'] = $wp_settings['header_color'];
			$wp_settings['header4_color'] = $wp_settings['header_color'];
			$wp_settings['header5_color'] = $wp_settings['header_color'];
			$wp_settings['header6_color'] = $wp_settings['header_color'];
			unset( $wp_settings['header_color'] );
		}

		// Check for presence of legacy line height setting.
		if ( ! empty( $wp_settings['header_line_height'] ) ) {
			$wp_settings['header1_line_height'] = $wp_settings['header_line_height'];
			$wp_settings['header2_line_height'] = $wp_settings['header_line_height'];
			$wp_settings['header3_line_height'] = $wp_settings['header_line_height'];
			$wp_settings['header4_line_height'] = $wp_settings['header_line_height'];
			$wp_settings['header5_line_height'] = $wp_settings['header_line_height'];
			$wp_settings['header6_line_height'] = $wp_settings['header_line_height'];
			unset( $wp_settings['header_line_height'] );
		}

		// Store the updated option to remove the legacy setting names.
		update_option( self::OPTION_NAME, $wp_settings, 'no' );

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
		update_option( self::OPTION_NAME, $migrated_settings, 'no' );

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
}
