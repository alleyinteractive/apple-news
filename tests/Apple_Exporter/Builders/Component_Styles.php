<?php
/**
 * Publish to Apple News Tests: Component_Styles class
 *
 * Contains a class which is used to test \Apple_Exporter\Builders\Component_Styles.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Builders;

use Apple_News\Tests\TestCase;

/**
 * A class which is used to test \Apple_Exporter\Builders\Component_Styles.
 */
class Component_Styles extends TestCase {

	/**
	 * Tests the functionality of the builder.
	 *
	 * @see \Apple_Exporter\Builders\Component_Styles::build()
	 */
	public function test_built_array() {
		$styles = new \Apple_Exporter\Builders\Component_Styles( $this->content, $this->settings );
		$styles->register_style( 'some-name', [ 'my-key' => 'my value' ] );
		$result = $styles->to_array();

		$this->assertEquals( 1, count( $result ) );
		$this->assertEquals( [ 'my-key' => 'my value' ], $result['some-name'] );
	}
}
