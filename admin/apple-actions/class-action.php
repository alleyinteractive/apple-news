<?php
namespace Apple_Actions;

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
	abstract public function perform();

	/**
	 * Gets a setting by name which was loaded from WordPress options.
	 *
	 * @since 0.4.0
	 */
	protected function get_setting( $name ) {
		return $this->settings->get( $name );
	}

}
