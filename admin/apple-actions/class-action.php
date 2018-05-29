<?php
/**
 * Publish to Apple News: \Apple_Actions\Action abstract class
 *
 * @package Apple_News
 * @subpackage Apple_Actions
 */

namespace Apple_Actions;

/**
 * An abstract class to represent an API action, such as POST or DELETE.
 *
 * @package Apple_Actions
 * @subpackage Apple_Actions
 */
abstract class Action {

	/**
	 * The settings in use when this action is called.
	 *
	 * @var \Apple_Exporter\Settings
	 * @access protected
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @param \Apple_Exporter\Settings $settings The settings to load.
	 * @access public
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Abstract function implemented by all child class to perform the given action.
	 */
	abstract public function perform();

	/**
	 * Gets a setting by name which was loaded from WordPress options.
	 *
	 * @since 0.4.0
	 * @param string $name The name of the setting to get.
	 * @access protected
	 * @return mixed The value for the setting.
	 */
	protected function get_setting( $name ) {
		return $this->settings->get( $name );
	}

}
