<?php
/**
 * Publish to Apple News Tests: Component_Layouts class
 *
 * Contains a class to test the functionality of the Apple_Exporter\Builders\Component_Layouts class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Builders;

use Apple_Exporter\Components\Component;
use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Apple_Exporter\Builders\Component_Layouts class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Component_Layouts extends TestCase {

	/**
	 * Tests the behavior of registering layouts.
	 */
	public function test_register_layout() {
		$layouts = new \Apple_Exporter\Builders\Component_Layouts( $this->content, $this->settings );
		$layouts->register_layout( 'l1', 'val1' );
		$layouts->register_layout( 'l2', 'val2' );
		$result = $layouts->to_array();

		$this->assertEquals( 2, count( $result ) );
		$this->assertEquals( 'val1', $result['l1'] );
		$this->assertEquals( 'val2', $result['l2'] );
	}

	/**
	 * Tests the behavior of anchor layout left.
	 */
	public function test_left_layout_gets_added() {
		$layouts = new \Apple_Exporter\Builders\Component_Layouts( $this->content, $this->settings );

		$this->assertFalse( array_key_exists( 'anchor-layout-left', $layouts->to_array() ) );

		$component = $this->prophet->prophesize( '\Apple_Exporter\Components\Component' );
		$component->get_anchor_position()
			->willReturn( Component::ANCHOR_LEFT )
			->shouldBeCalled();
		$component->is_anchor_target()
			->willReturn( false )
			->shouldBeCalled();
		$component->set_json( 'layout', 'anchor-layout-left' )->shouldBeCalled();

		$layouts->set_anchor_layout_for( $component->reveal() );

		$this->assertTrue( array_key_exists( 'anchor-layout-left', $layouts->to_array() ) );
	}
}
