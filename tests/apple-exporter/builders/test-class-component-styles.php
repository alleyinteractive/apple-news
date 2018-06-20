<?php
/**
 * Publish to Apple News Tests: Component_Styles_Tests class
 *
 * Contains a class which is used to test \Apple_Exporter\Builders\Component_Styles.
 *
 * @pacakge Apple_News
 * @subpackage Tests
 */

use \Apple_Exporter\Exporter_Content as Exporter_Content;
use \Apple_Exporter\Settings as Settings;
use \Apple_Exporter\Builders\Component_Styles as Component_Styles;

/**
 * A class which is used to test \Apple_Exporter\Builders\Component_Styles.
 */
class Component_Styles_Tests extends WP_UnitTestCase {

	/**
	 * Instructions to be executed before each test.
	 *
	 * @access public
	 */
	public function setup() {
		$this->settings = new Settings();
		$this->content  = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>' );
	}

	/**
	 * Tests the functionality of the builder.
	 *
	 * @see \Apple_Exporter\Builders\Component_Styles::build()
	 *
	 * @access public
	 */
	public function testBuiltArray() {
		$styles = new Component_Styles( $this->content, $this->settings );
		$styles->register_style( 'some-name', array( 'my-key' => 'my value' ) );
		$result = $styles->to_array();

		$this->assertEquals( 1, count( $result ) );
		$this->assertEquals( array( 'my-key' => 'my value' ), $result[ 'some-name' ] );
	}
}
