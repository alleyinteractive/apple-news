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
	private $_settings = array(
		'api_async' => 'no',
		'api_autosync' => 'yes',
		'api_autosync_delete' => 'yes',
		'api_autosync_update' => 'yes',
		'api_channel' => '',
		'api_key' => '',
		'api_secret' => '',
		'apple_news_admin_email' => '',
		'apple_news_enable_debugging' => 'no',
		'component_alerts' => 'none',
		'full_bleed_images' => 'no',
		'html_support' => 'no',
		'json_alerts' => 'warn',
		'post_types' => array( 'post' ),
		'show_metabox' => 'yes',
		'use_remote_images' => 'no',
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
		if ( isset( $this->_settings[ $name ] ) ) {
			return $this->_settings[ $name ];
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
		if ( isset( $this->_settings[ $name ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Magic method for setting property values.
	 *
	 * @param string $name The setting name to update.
	 * @param mixed $value The new value for the setting.
	 *
	 * @access public
	 */
	public function __set( $name, $value ) {
		$this->_settings[ $name ] = $value;
	}

	/**
	 * When a component is displayed aligned relative to another one, slide the
	 * other component a few columns. This varies for centered and non-centered
	 * layouts, as centered layouts have more columns.
	 *
	 * @since 0.4.0
	 *
	 * @access public
	 * @return int The number of columns for aligned components to span.
	 */
	public function alignment_offset() {
		return ( 'center' === $this->body_orientation ) ? 5 : 3;
	}

	/**
	 * Get all settings.
	 *
	 * @access public
	 * @return array The array of all settings defined in this class.
	 */
	public function all() {
		return $this->_settings;
	}

	/**
	 * Get the body column span.
	 *
	 * @access public
	 * @return int The number of columns for the body to span.
	 */
	public function body_column_span() {
		return ( 'center' === $this->body_orientation ) ? 7 : 6;
	}

	/**
	 * Get the left margin column offset.
	 *
	 * @access public
	 * @return int The number of columns to offset on the left.
	 */
	public function body_offset() {
		switch ( $this->body_orientation ) {
			case 'right':
				return $this->layout_columns - $this->body_column_span;
			case 'center':
				return floor(
					( $this->layout_columns - $this->body_column_span ) / 2
				);
				break;
			default:
				return 0;
		}
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
	 * Get the computed layout columns.
	 *
	 * @access public
	 * @return int The number of layout columns to use.
	 */
	public function layout_columns() {
		return ( 'center' === $this->body_orientation ) ? 9 : 7;
	}

	/**
	 * Set a setting.
	 *
	 * @param string $name The setting key to modify.
	 * @param mixed $value The new value for the setting.
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
