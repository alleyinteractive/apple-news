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

	protected $settings;

	/**
	 * Constructor.
	 */
	function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Abstract function implemented by all child class to perform the given action.
	 */
	public abstract function perform();

	/**
	 * Gets a setting by name which was loaded from WordPress options.
	 *
	 * @since 0.4.0
	 */
	protected function get_setting( $name ) {
		return $this->settings->get( $name );
	}

}
