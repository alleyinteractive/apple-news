<?php
/**
 * Publish to Apple News: \Apple_Exporter\Exporter_Content_Settings class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 */

namespace Apple_Exporter;

/**
 * Settings used for exporting a single "Exporter_Content" element.
 */
class Exporter_Content_Settings {

	/**
	 * Exporter's default settings.
	 *
	 * @var array
	 * @access private
	 */
	private $settings = array(
		'pullquote'          => '',
		'pullquote_position' => 'top',
	);

	/**
	 * Get a setting.
	 *
	 * @param string $name
	 * @return mixed
	 * @access public
	 */
	public function get( $name ) {
		if ( ! array_key_exists( $name, $this->settings ) ) {
			return null;
		}

		return $this->settings[ $name ];
	}

	/**
	 * Sets a setting.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 * @access public
	 */
	public function set( $name, $value ) {
		$this->settings[ $name ] = $value;
		return $value;
	}

	/**
	 * Get all settings.
	 *
	 * @return array
	 * @access public
	 */
	public function all() {
		return $this->settings;
	}

}
