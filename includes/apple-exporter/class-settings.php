<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Settings class
 *
 * Contains a class which is used to manage user-defined and computed settings.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.4.0
 */

namespace Apple_Exporter;

/**
 * Manages user-defined and computed settings used in exporting.
 *
 * In a WordPress context, these can be loaded as WordPress options defined in the
 * plugin.
 *
 * @since 0.4.0
 */
class Settings {

	/**
	 * Exporter's default settings.
	 *
	 * These settings can be overridden on the plugin settings screen.
	 *
	 * @var array
	 * @access private
	 */
	private $settings = array(
		'api_async'                   => 'no',
		'api_autosync'                => 'yes',
		'api_autosync_delete'         => 'yes',
		'api_autosync_update'         => 'yes',
		'api_channel'                 => '',
		'api_key'                     => '',
		'api_secret'                  => '',
		'apple_news_admin_email'      => '',
		'apple_news_enable_debugging' => 'no',
		'component_alerts'            => 'none',
		'enable_cover_art'            => 'no',
		'full_bleed_images'           => 'no',
		'html_support'                => 'yes',
		'json_alerts'                 => 'warn',
		'post_types'                  => array( 'post' ),
		'show_metabox'                => 'yes',
		'use_remote_images'           => 'yes',
	);

	/**
	 * Magic method to get a computed or stored settings value.
	 *
	 * @param string $name The setting name to retrieve.
	 *
	 * @access public
	 * @return mixed The value for the setting.
	 */
	public function __get( $name ) {

		// Check for computed settings.
		if ( method_exists( $this, $name ) ) {
			return $this->$name();
		}

		// Check for regular settings.
		if ( isset( $this->settings[ $name ] ) ) {
			return $this->settings[ $name ];
		}

		// Fall back to trying to get the setting dynamically from the theme.
		$theme       = \Apple_Exporter\Theme::get_used();
		$method_name = 'get_' . $name;
		if ( method_exists( $theme, $method_name ) ) {
			$value = call_user_func( array( $theme, $method_name ) );

			// Log a deprecated notice, since this is no longer preferred.
			_deprecated_function(
				esc_html__( 'Getting formatting settings through the \\Apple_Exporter\\Settings object', 'apple-news' ),
				'1.3.0',
				esc_html__( 'the \\Apple_Exporter\\Theme object', 'apple-news' )
			);

			return $value;
		}

		// Fall back to trying to get the setting from the theme.
		$value = $theme->get_value( $name );
		if ( null !== $value ) {

			// Log a deprecated notice, since this is no longer preferred.
			_deprecated_function(
				esc_html__( 'Getting formatting settings through the \\Apple_Exporter\\Settings object', 'apple-news' ),
				'1.3.0',
				esc_html__( 'the \\Apple_Exporter\\Theme object', 'apple-news' )
			);

			return $value;
		}

		return null;
	}

	/**
	 * Magic method to determine whether a given property is set.
	 *
	 * @param string $name The setting name to check.
	 *
	 * @access public
	 * @return bool Whether the property is set or not.
	 */
	public function __isset( $name ) {

		// Check for computed settings.
		if ( method_exists( $this, $name ) ) {
			return true;
		}

		// Check for regular settings.
		if ( isset( $this->settings[ $name ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Magic method for setting property values.
	 *
	 * @param string $name  The setting name to update.
	 * @param mixed  $value The new value for the setting.
	 *
	 * @access public
	 */
	public function __set( $name, $value ) {
		$this->settings[ $name ] = $value;
	}

	/**
	 * Get all settings.
	 *
	 * @access public
	 * @return array The array of all settings defined in this class.
	 */
	public function all() {
		return $this->settings;
	}

	/**
	 * Get a setting.
	 *
	 * @param string $name The setting key to retrieve.
	 *
	 * @deprecated 1.2.1 Replaced by magic __get() method.
	 *
	 * @see \Apple_Exporter\Settings::__get()
	 *
	 * @access public
	 * @return mixed The value for the requested setting.
	 */
	public function get( $name ) {
		return $this->$name;
	}

	/**
	 * Set a setting.
	 *
	 * @param string $name  The setting key to modify.
	 * @param mixed  $value The new value for the setting.
	 *
	 * @deprecated 1.2.1 Replaced by magic __set() method.
	 *
	 * @see \Apple_Exporter\Settings::__set()
	 *
	 * @access public
	 * @return mixed The new value for the setting.
	 */
	public function set( $name, $value ) {
		$this->$name = $value;

		return $value;
	}
}
