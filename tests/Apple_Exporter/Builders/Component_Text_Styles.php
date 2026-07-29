<?php
/**
 * Publish to Apple News Tests: Component_Text_Styles class
 *
 * Contains a class to test the functionality of the Apple_Exporter\Builders\Component_Text_Styles class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Builders;

use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Apple_Exporter\Builders\Component_Text_Styles class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Component_Text_Styles extends TestCase {

	/**
	 * Tests the behavior of the componentTextStyles builder.
	 */
	public function test_built_array() {
		$styles = new \Apple_Exporter\Builders\Component_Text_Styles( $this->content, $this->settings );
		$styles->register_style( 'some-name', 'my value' );
		$result = $styles->to_array();

		$this->assertEquals( 1, count( $result ) );
		$this->assertEquals( 'my value', $result['some-name'] );
	}
}
