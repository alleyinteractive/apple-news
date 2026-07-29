<?php
/**
 * Publish to Apple News tests: Author class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Apple_Exporter\Components\Author class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Author extends TestCase {

	/**
	 * Tests the render method for the component.
	 */
	public function test_render() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'author' ] ] );

		// Create a test post and get JSON for it.
		$user_id = self::factory()->user->create( [ 'display_name' => 'Test Author' ] );
		$post_id = self::factory()->post->create(
			[
				'post_author'   => $user_id,
				'post_date_gmt' => '1970-01-01 12:00:00',
			]
		);
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'author', $json['components'][0]['role'] );
		$this->assertEquals( 'By Test Author', $json['components'][0]['text'] );
	}

	/**
	 * Tests author settings.
	 */
	public function test_settings() {
		$this->set_theme_settings(
			[
				'author_color'       => '#abcdef',
				'author_color_dark'  => '#123456',
				'author_font'        => 'AmericanTypewriter',
				'author_line_height' => 12,
				'author_links'       => 'no',
				'author_size'        => 34,
				'author_tracking'    => 56,
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

		// Validate author settings in generated JSON.
		$this->assertEquals( '#abcdef', $json['componentTextStyles']['default-author']['textColor'] );
		$this->assertEquals( '#123456', $json['componentTextStyles']['default-author']['conditional'][0]['textColor'] );
		$this->assertEquals( 'AmericanTypewriter', $json['componentTextStyles']['default-author']['fontName'] );
		$this->assertEquals( 12, $json['componentTextStyles']['default-author']['lineHeight'] );
		$this->assertEquals( 34, $json['componentTextStyles']['default-author']['fontSize'] );
		$this->assertEquals( 0.56, $json['componentTextStyles']['default-author']['tracking'] );

		$this->set_theme_settings(
			[
				'author_color'           => '#abcdef',
				'author_color_dark'      => '#123456',
				'author_font'            => 'AmericanTypewriter',
				'author_line_height'     => 12,
				'author_link_color'      => '#ffcc00',
				'author_link_color_dark' => '#ccff00',
				'author_links'           => 'yes',
				'author_size'            => 34,
				'author_tracking'        => 56,
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

		// Validate author settings in generated JSON.
		$this->assertEquals( '#ffcc00', $json['componentTextStyles']['default-author']['linkStyle']['textColor'] );
		$this->assertEquals( '#ccff00', $json['componentTextStyles']['default-author']['conditional'][1]['linkStyle']['textColor'] );
		$this->assertEquals( 'By <a href="' . esc_url( get_author_posts_url( $user_id ) ) . '" rel="author">Test Author</a>', $json['components'][1]['text'] );
	}

	/**
	 * Tests author settings.
	 */
	public function test_coauthors_settings() {
		$this->set_theme_settings(
			[
				'author_color'           => '#abcdef',
				'author_color_dark'      => '#123456',
				'author_font'            => 'AmericanTypewriter',
				'author_line_height'     => 12,
				'author_link_color'      => '#ffcc00',
				'author_link_color_dark' => '#ccff00',
				'author_links'           => 'yes',
				'author_size'            => 34,
				'author_tracking'        => 56,
				'meta_component_order'   => [ 'cover', 'slug', 'title', 'author' ],
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

		// Validate author settings in generated JSON.
		$this->assertEquals( 'By <a href="' . esc_url( get_author_posts_url( $author_1 ) ) . '" rel="author">' . get_the_author_meta( 'display_name', $author_1 ) . '</a> and <a href="' . esc_url( get_author_posts_url( $author_2 ) ) . '" rel="author">' . get_the_author_meta( 'display_name', $author_2 ) . '</a>', $json['components'][1]['text'] );
	}
}
