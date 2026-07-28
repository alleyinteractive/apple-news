<?php
/**
 * Publish to Apple News tests: Title class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

/**
 * A class to test the behavior of the Apple_Exporter\Components\Title class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Title extends Component_TestCase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_title_json( $json ) {
		$json['layout'] = 'fancy-layout';

		return $json;
	}

	/**
	 * Test the `apple_news_title_json` filter.
	 */
	public function test_filter() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'title' ] ] );
		add_filter( 'apple_news_title_json', [ $this, 'filter_apple_news_title_json' ] );

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create( [ 'post_title' => 'Test Title' ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'title', $json['components'][0]['role'] );
		$this->assertEquals( 'fancy-layout', $json['components'][0]['layout'] );

		// Teardown.
		remove_filter( 'apple_news_title_json', [ $this, 'filter_apple_news_title_json' ] );
	}

	/**
	 * Tests the render method for the component.
	 */
	public function test_render() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'title' ] ] );

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create( [ 'post_title' => 'Test Title' ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'title', $json['components'][0]['role'] );
		$this->assertEquals( 'Test Title', $json['components'][0]['text'] );
	}

	/**
	 * Tests slug settings.
	 */
	public function test_settings() {
		$this->set_theme_settings(
			[
				'header1_color'        => '#abcdef',
				'header1_color_dark'   => '#123456',
				'header1_font'         => 'AmericanTypewriter',
				'header1_line_height'  => 12,
				'header1_size'         => 34,
				'header1_tracking'     => 56,
				'meta_component_order' => [ 'title' ],
			]
		);

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create( [ 'post_title' => 'Test Title' ] );
		$json    = $this->get_json_for_post( $post_id );

		// Validate title settings in generated JSON.
		$this->assertEquals( '#abcdef', $json['componentTextStyles']['default-title']['textColor'] );
		$this->assertEquals( '#123456', $json['componentTextStyles']['default-title']['conditional']['textColor'] );
		$this->assertEquals( 'AmericanTypewriter', $json['componentTextStyles']['default-title']['fontName'] );
		$this->assertEquals( 12, $json['componentTextStyles']['default-title']['lineHeight'] );
		$this->assertEquals( 34, $json['componentTextStyles']['default-title']['fontSize'] );
		$this->assertEquals( 0.56, $json['componentTextStyles']['default-title']['tracking'] );
	}
}
