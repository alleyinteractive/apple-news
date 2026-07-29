<?php
/**
 * Publish to Apple News tests: Slug class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Apple_Exporter\Components\Slug class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Slug extends TestCase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_slug_json( $json ) {
		$json['layout'] = 'fancy-layout';

		return $json;
	}

	/**
	 * Test the `apple_news_slug_json` filter.
	 */
	public function test_filter() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'slug' ] ] );
		add_filter( 'apple_news_slug_json', [ $this, 'filter_apple_news_slug_json' ] );

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'apple_news_slug', 'Test Slug' );
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'heading', $json['components'][0]['role'] );
		$this->assertEquals( 'fancy-layout', $json['components'][0]['layout'] );

		// Teardown.
		remove_filter( 'apple_news_slug_json', [ $this, 'filter_apple_news_slug_json' ] );
	}

	/**
	 * Tests the render method for the component.
	 */
	public function test_render() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'slug' ] ] );

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'apple_news_slug', 'Test Slug' );
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'heading', $json['components'][0]['role'] );
		$this->assertEquals( 'Test Slug', $json['components'][0]['text'] );
	}

	/**
	 * Tests slug settings.
	 */
	public function test_settings() {
		$this->set_theme_settings(
			[
				'slug_color'       => '#abcdef',
				'slug_color_dark'  => '#123456',
				'slug_font'        => 'AmericanTypewriter',
				'slug_line_height' => 12,
				'slug_size'        => 34,
				'slug_tracking'    => 56,
			]
		);

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'apple_news_slug', 'Test Slug' );
		$json = $this->get_json_for_post( $post_id );

		// Validate slug settings in generated JSON.
		$this->assertEquals( '#abcdef', $json['componentTextStyles']['default-slug']['textColor'] );
		$this->assertEquals( '#123456', $json['componentTextStyles']['default-slug']['conditional']['textColor'] );
		$this->assertEquals( 'AmericanTypewriter', $json['componentTextStyles']['default-slug']['fontName'] );
		$this->assertEquals( 12, $json['componentTextStyles']['default-slug']['lineHeight'] );
		$this->assertEquals( 34, $json['componentTextStyles']['default-slug']['fontSize'] );
		$this->assertEquals( 0.56, $json['componentTextStyles']['default-slug']['tracking'] );
	}
}
