<?php
/**
 * Publish to Apple News tests: Byline class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Apple_Exporter\Components\Byline class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Byline extends TestCase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_byline_json( $json ) {
		$json['layout'] = 'fancy-layout';

		return $json;
	}

	/**
	 * Test the `apple_news_byline_json` filter.
	 */
	public function test_filter() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'byline' ] ] );
		add_filter( 'apple_news_byline_json', [ $this, 'filter_apple_news_byline_json' ] );

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create();
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'byline', $json['components'][0]['role'] );
		$this->assertEquals( 'fancy-layout', $json['components'][0]['layout'] );

		// Teardown.
		remove_filter( 'apple_news_byline_json', [ $this, 'filter_apple_news_byline_json' ] );
	}

	/**
	 * Tests the render method for the component.
	 */
	public function test_render() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'byline' ] ] );
		$this->disable_coauthors_support();

		// Create a test post and get JSON for it.
		$user_id = self::factory()->user->create( [ 'display_name' => 'Test Author' ] );
		$post_id = self::factory()->post->create(
			[
				'post_author'   => $user_id,
				'post_date_gmt' => '1970-01-01 12:00:00',
			]
		);
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'byline', $json['components'][0]['role'] );
		$this->assertEquals( 'By Test Author | Jan 1, 1970 | 12:00 PM', $json['components'][0]['text'] );
	}

	/**
	 * Tests byline settings.
	 */
	public function test_settings() {
		$this->set_theme_settings(
			[
				'byline_color'         => '#abcdef',
				'byline_color_dark'    => '#123456',
				'byline_font'          => 'AmericanTypewriter',
				'byline_line_height'   => 12,
				'author_links'         => 'no',
				'byline_size'          => 34,
				'byline_tracking'      => 56,
				'meta_component_order' => [ 'cover', 'slug', 'title', 'byline' ],
			]
		);

		// Create a test post and get JSON for it.
		$user_id = self::factory()->user->create( [ 'display_name' => 'Test Author' ] );
		$post_id = self::factory()->post->create(
			[
				'post_author'   => $user_id,
				'post_date_gmt' => '1970-01-01 12:00:00',
			]
		);
		$json    = $this->get_json_for_post( $post_id );

		// Validate byline settings in generated JSON.
		$this->assertEquals( '#abcdef', $json['componentTextStyles']['default-byline']['textColor'] );
		$this->assertEquals( '#123456', $json['componentTextStyles']['default-byline']['conditional'][0]['textColor'] );
		$this->assertEquals( 'AmericanTypewriter', $json['componentTextStyles']['default-byline']['fontName'] );
		$this->assertEquals( 12, $json['componentTextStyles']['default-byline']['lineHeight'] );
		$this->assertEquals( 34, $json['componentTextStyles']['default-byline']['fontSize'] );
		$this->assertEquals( 0.56, $json['componentTextStyles']['default-byline']['tracking'] );

		$this->set_theme_settings(
			[
				'byline_color'           => '#abcdef',
				'byline_color_dark'      => '#123456',
				'byline_font'            => 'AmericanTypewriter',
				'byline_line_height'     => 12,
				'author_link_color'      => '#ffcc00',
				'author_link_color_dark' => '#ccff00',
				'author_links'           => 'yes',
				'byline_size'            => 34,
				'byline_tracking'        => 56,
				'meta_component_order'   => [ 'cover', 'slug', 'title', 'byline' ],
			]
		);

		// Create a test post and get JSON for it.
		$user_id = self::factory()->user->create( [ 'display_name' => 'Test Author' ] );
		$post_id = self::factory()->post->create(
			[
				'post_author'   => $user_id,
				'post_date_gmt' => '1970-01-01 12:00:00',
			]
		);
		$json    = $this->get_json_for_post( $post_id );

		// Validate byline settings in generated JSON.
		$this->assertEquals( '#ffcc00', $json['componentTextStyles']['default-byline']['linkStyle']['textColor'] );
		$this->assertEquals( '#ccff00', $json['componentTextStyles']['default-byline']['conditional'][1]['linkStyle']['textColor'] );
		$this->assertEquals( 'By <a href="' . esc_url( get_author_posts_url( $user_id ) ) . '" rel="author">Test Author</a> | Jan 1, 1970 | 12:00 PM', $json['components'][1]['text'] );
	}

	/**
	 * Tests byline settings.
	 */
	public function test_coauthors_settings() {
		$this->set_theme_settings(
			[
				'byline_color'           => '#abcdef',
				'byline_color_dark'      => '#123456',
				'byline_font'            => 'AmericanTypewriter',
				'byline_line_height'     => 12,
				'author_link_color'      => '#ffcc00',
				'author_link_color_dark' => '#ccff00',
				'author_links'           => 'yes',
				'byline_size'            => 34,
				'byline_tracking'        => 56,
				'meta_component_order'   => [ 'cover', 'slug', 'title', 'byline' ],
			]
		);

		// Create a test post and get JSON for it.
		$this->enable_coauthors_support();
		global $apple_news_coauthors;
		$author_1             = self::factory()->user->create( [ 'display_name' => 'Test Author 1' ] );
		$author_2             = self::factory()->user->create( [ 'display_name' => 'Test Author 2' ] );
		$apple_news_coauthors = [ $author_1, $author_2 ];
		$post_id              = self::factory()->post->create( [ 'post_date_gmt' => '1970-01-01 12:00:00' ] );
		$json                 = $this->get_json_for_post( $post_id );

		// Validate byline settings in generated JSON.
		$this->assertEquals( 'By <a href="' . esc_url( get_author_posts_url( $author_1 ) ) . '" rel="author">' . get_the_author_meta( 'display_name', $author_1 ) . '</a> and <a href="' . esc_url( get_author_posts_url( $author_2 ) ) . '" rel="author">' . get_the_author_meta( 'display_name', $author_2 ) . '</a> | Jan 1, 1970 | 12:00 PM', $json['components'][1]['text'] );
	}
}
