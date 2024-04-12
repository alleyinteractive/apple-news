<?php
/**
 * Publish to Apple News tests: Date_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the behavior of the Apple_Exporter\Components\Date class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Date_Test extends Apple_News_Testcase {

	/**
	 * Tests the render method for the component.
	 */
	public function test_render() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'date' ] ] );

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create( [ 'post_date_gmt' => '1970-01-01 12:34:56' ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'byline', $json['components'][0]['role'] );
		$this->assertEquals( 'Jan 1, 1970 | 12:34 PM', $json['components'][0]['text'] );
	}

	/**
	 * Tests date settings.
	 */
	public function test_settings() {
		$this->set_theme_settings(
			[
				'date_color'       => '#abcdef',
				'date_color_dark'  => '#123456',
				'date_font'        => 'AmericanTypewriter',
				'date_line_height' => 12,
				'date_size'        => 34,
				'date_tracking'    => 56,
			]
		);

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create( [ 'post_date_gmt' => '1970-01-01 12:34:56' ] );
		$json    = $this->get_json_for_post( $post_id );

		// Validate date settings in generated JSON.
		$this->assertEquals( '#abcdef', $json['componentTextStyles']['default-date']['textColor'] );
		$this->assertEquals( '#123456', $json['componentTextStyles']['default-date']['conditional'][0]['textColor'] );
		$this->assertEquals( 'AmericanTypewriter', $json['componentTextStyles']['default-date']['fontName'] );
		$this->assertEquals( 12, $json['componentTextStyles']['default-date']['lineHeight'] );
		$this->assertEquals( 34, $json['componentTextStyles']['default-date']['fontSize'] );
		$this->assertEquals( 0.56, $json['componentTextStyles']['default-date']['tracking'] );
		$this->assertEquals( 'Jan 1, 1970 | 12:34 PM', $json['components'][2]['text'] );
		$this->assertEquals( 'byline', $json['components'][2]['role'] );
	}
}
