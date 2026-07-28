<?php
/**
 * Publish to Apple News Tests: Layout class
 *
 * Contains a class to test the functionality of the Apple_Exporter\Builders\Layout class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Builders;

use Apple_Exporter\Theme;
use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Apple_Exporter\Builders\Layout class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Layout extends TestCase {

	/**
	 * Tests the behavior of registering a layout.
	 */
	public function test_register_layout() {
		$theme = Theme::get_used();
		$theme->set_value( 'layout_margin', 123 );
		$theme->set_value( 'layout_gutter', 222 );
		$theme->set_value( 'layout_width', 768 );
		$this->assertTrue( $theme->save() );

		$post_id = self::factory()->post->create( [ 'post_content' => '' ] );
		$json    = $this->get_json_for_post( $post_id );

		$this->assertEquals( $theme->get_layout_columns(), $json['layout']['columns'] );
		$this->assertEquals( 768, $json['layout']['width'] );
		$this->assertEquals( 123, $json['layout']['margin'] );
		$this->assertEquals( 222, $json['layout']['gutter'] );
	}

	/**
	 * Test column override functionality.
	 */
	public function test_column_override() {
		$theme   = Theme::get_used();
		$post_id = self::factory()->post->create( [ 'post_content' => '' ] );
		$json    = $this->get_json_for_post( $post_id );

		// Check default, override-less behavior.
		$this->assertEquals( $theme->get_layout_columns(), $json['layout']['columns'] );
		$this->assertEquals( 7, $json['layout']['columns'] );

		// Confirm override applies after 'layout_columns_override' is flipped to 'yes'.
		$theme->set_value( 'layout_columns_override', 'yes' );
		$theme->set_value( 'layout_columns', 6 );
		$this->assertTrue( $theme->save() );
		$json = $this->get_json_for_post( $post_id );

		$this->assertEquals( 6, $theme->get_layout_columns() );
		$this->assertEquals( 6, $json['layout']['columns'] );

		// Reset override and confirm that dynamic computed value is restored.
		$theme->set_value( 'layout_columns_override', 'no' );
		// Also set body_orientation to 'center' to ensure the computed value for layout_columns changes accordingly.
		$theme->set_value( 'body_orientation', 'center' );
		$this->assertTrue( $theme->save() );
		$json = $this->get_json_for_post( $post_id );

		// Confirm override applies after 'layout_columns' theme value change.
		$this->assertEquals( $theme->get_layout_columns(), $json['layout']['columns'] );
		$this->assertEquals( 9, $json['layout']['columns'] );
	}
}
