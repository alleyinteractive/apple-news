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

		// API information.
		'api_key' => '',
		'api_secret' => '',
		'api_channel' => '',
		'api_autosync' => 'yes',
		'api_autosync_update' => 'yes',
		'api_autosync_delete' => 'yes',
		'api_async' => 'no',

		'post_types' => array( 'post' ),
		'show_metabox' => 'yes',

		'layout_margin' => 100,
		'layout_gutter' => 20,
		'layout_width' => 1024,

		'body_font' => 'AvenirNext-Regular',
		'body_size' => 18,
		'body_color' => '#4f4f4f',
		'body_link_color' => '#428bca',
		'body_background_color' => '#fafafa',
		'body_orientation' => 'left',
		'body_line_height' => 24,
		'body_tracking' => 0,

		'initial_dropcap' => 'yes',
		'dropcap_font' => 'AvenirNext-Bold',
		'dropcap_color' => '#4f4f4f',

		'byline_font' => 'AvenirNext-Medium',
		'byline_size' => 13,
		'byline_line_height' => 24,
		'byline_tracking' => 0,
		'byline_color' => '#7c7c7c',
		'byline_format' => 'by #author# | #M j, Y | g:i A#',

		'header1_font' => 'AvenirNext-Bold',
		'header2_font' => 'AvenirNext-Bold',
		'header3_font' => 'AvenirNext-Bold',
		'header4_font' => 'AvenirNext-Bold',
		'header5_font' => 'AvenirNext-Bold',
		'header6_font' => 'AvenirNext-Bold',
		'header1_color' => '#333333',
		'header2_color' => '#333333',
		'header3_color' => '#333333',
		'header4_color' => '#333333',
		'header5_color' => '#333333',
		'header6_color' => '#333333',
		'header1_size' => 48,
		'header2_size' => 32,
		'header3_size' => 24,
		'header4_size' => 21,
		'header5_size' => 18,
		'header6_size' => 16,
		'header1_line_height' => 52,
		'header2_line_height' => 36,
		'header3_line_height' => 28,
		'header4_line_height' => 26,
		'header5_line_height' => 24,
		'header6_line_height' => 22,
		'header1_tracking' => 0,
		'header2_tracking' => 0,
		'header3_tracking' => 0,
		'header4_tracking' => 0,
		'header5_tracking' => 0,
		'header6_tracking' => 0,

		'caption_font' => 'AvenirNext-Italic',
		'caption_size' => 16,
		'caption_color' => '#4f4f4f',
		'caption_line_height' => 24,
		'caption_tracking' => 0,

		'pullquote_font' => 'AvenirNext-Bold',
		'pullquote_size' => 48,
		'pullquote_color' => '#53585f',
		'pullquote_border_color' => '#53585f',
		'pullquote_border_style' => 'solid',
		'pullquote_border_width' => '3',
		'pullquote_transform' => 'uppercase',
		'pullquote_line_height' => 48,
		'pullquote_tracking' => 0,

		'blockquote_font' => 'AvenirNext-Regular',
		'blockquote_size' => 18,
		'blockquote_color' => '#4f4f4f',
		'blockquote_border_color' => '#4f4f4f',
		'blockquote_border_style' => 'solid',
		'blockquote_border_width' => '3',
		'blockquote_line_height' => 24,
		'blockquote_tracking' => 0,
		'blockquote_background_color' => '#e1e1e1',

		'monospaced_font' => 'Menlo-Regular',
		'monospaced_size' => 16,
		'monospaced_color' => '#4f4f4f',
		'monospaced_line_height' => 20,
		'monospaced_tracking' => 0,

		'component_alerts' => 'none',
		'json_alerts' => 'warn',

		'use_remote_images' => 'no',
		'full_bleed_images' => 'no',
		'html_support' => 'no',

		// This can either be gallery or mosaic.
		'gallery_type' => 'gallery',

		// Ad settings
		'enable_advertisement' => 'yes',
		'ad_frequency' => 1,
		'ad_margin' => 15,

		// Default component order
		'meta_component_order' => array( 'cover', 'title', 'byline' ),

		// Developer tools
		'apple_news_enable_debugging' => 'no',
		'apple_news_admin_email' => '',
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
