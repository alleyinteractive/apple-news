<?php
/**
 * Publish to Apple News tests: Quote class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

use Apple_News\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Quote class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Quote extends TestCase {

	/**
	 * Creates a post containing a blockquote and returns the post ID.
	 *
	 * @return int The post ID of the post containing the blockquote.
	 */
	private function get_blockquote() {
		$content = <<<HTML
<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>Test blockquote.</p></blockquote>
<!-- /wp:quote -->
HTML;
		return self::factory()->post->create( [ 'post_content' => $content ] );
	}

	/**
	 * Creates a post containing a pullquote and returns the post ID.
	 *
	 * @return int The post ID of the post containing the pullquote.
	 */
	private function get_pullquote() {
		$content = <<<HTML
<!-- wp:pullquote -->
<figure class="wp-block-pullquote"><blockquote><p>Test pullquote.</p></blockquote></figure>
<!-- /wp:pullquote -->
HTML;
		return self::factory()->post->create( [ 'post_content' => $content ] );
	}

	/**
	 * A data provider for the test_transform_pullquote function.
	 *
	 * @return array An array of arrays representing function arguments.
	 */
	public static function data_transform_pullquote() {
		return [
			[ 'my text', '<p>my text</p>', 'no' ],
			[ 'my text', '<p>“my text”</p>', 'yes' ],
			[ '"my text"', '<p>“my text”</p>', 'yes' ],
			[ '“my text”', '<p>“my text”</p>', 'yes' ],
		];
	}

	/**
	 * A data provider for the test_transform_pullquote_for_theme function.
	 *
	 * @return array An array of arrays representing function arguments.
	 */
	public static function data_transform_pullquote_for_theme() {
		return [
			[ 'classic', [ 'cover', 'slug', 'title', 'byline' ] ],
			[ 'dark', [ 'cover', 'slug', 'title', 'byline' ] ],
			[ 'modern', [ 'slug', 'title', 'byline', 'cover' ] ],
			[ 'pastel', [ 'slug', 'cover', 'title', 'byline' ] ],
		];
	}

	/**
	 * A filter function to modify the hanging punctuation text.
	 *
	 * @param string $modified_text The modified text to be filtered.
	 * @param string $text          The original text for the quote.
	 *
	 * @return string The modified text.
	 */
	public function filter_apple_news_apply_hanging_punctuation( $modified_text, $text ) {
		return '«' . $text . '»';
	}

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_quote_json( $json ) {
		$json['textStyle'] = 'fancy-quote';

		return $json;
	}

	/**
	 * Returns an array of arrays representing function arguments to the
	 * test_filter, test_filter_hanging_punctuation, test_html_in_headings functions.
	 */
	public static function data_test_filter() {
		return [
			[ [ 'title', 'byline' ] ],
			[ [ 'title', 'author' ] ],
		];
	}

	/**
	 * Test the `apple_news_apply_hanging_punctuation` filter.
	 *
	 * @param string[] $meta_order The order of meta components to use.
	 */
	#[DataProvider( 'data_test_filter' )]
	public function test_filter_hanging_punctuation( $meta_order ) {
		$this->set_theme_settings(
			[
				'meta_component_order'          => $meta_order,
				'pullquote_hanging_punctuation' => 'yes',
			]
		);
		add_filter( 'apple_news_apply_hanging_punctuation', [ $this, 'filter_apple_news_apply_hanging_punctuation' ], 10, 2 );
		$json = $this->get_json_for_post( $this->get_pullquote() );
		$this->assertEquals( '<p>«Test pullquote.»</p>', $json['components'][2]['components'][0]['text'] );
		remove_filter( 'apple_news_apply_hanging_punctuation', [ $this, 'filter_apple_news_apply_hanging_punctuation' ] );
	}

	/**
	 * Test the `apple_news_quote_json` filter.
	 */
	public function test_filter_json() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'title', 'author' ] ] );
		add_filter( 'apple_news_quote_json', [ $this, 'filter_apple_news_quote_json' ] );
		$json = $this->get_json_for_post( $this->get_blockquote() );
		$this->assertEquals( 'fancy-quote', $json['components'][2]['textStyle'] );
		remove_filter( 'apple_news_quote_json', [ $this, 'filter_apple_news_quote_json' ] );
	}

	/**
	 * Tests blockquote settings.
	 */
	public function test_settings_blockquote() {
		$this->set_theme_settings(
			[
				'blockquote_background_color'      => '#fedcba',
				'blockquote_background_color_dark' => '#edcbaf',
				'blockquote_border_color'          => '#012345',
				'blockquote_border_color_dark'     => '#123456',
				'blockquote_border_style'          => 'dashed',
				'blockquote_border_width'          => 10,
				'blockquote_color'                 => '#abcdef',
				'blockquote_color_dark'            => '#bcdefa',
				'blockquote_font'                  => 'AmericanTypewriter',
				'blockquote_line_height'           => 28,
				'blockquote_size'                  => 20,
				'blockquote_tracking'              => 50,
				'meta_component_order'             => [ 'cover', 'slug', 'title', 'byline' ],
			]
		);
		$json = $this->get_json_for_post( $this->get_blockquote() );
		$this->assertEquals( '#fedcba', $json['components'][2]['style']['backgroundColor'] );
		$this->assertEquals( '#edcbaf', $json['components'][2]['style']['conditional']['backgroundColor'] );
		$this->assertEquals( '#012345', $json['components'][2]['style']['border']['all']['color'] );
		$this->assertEquals( '#123456', $json['components'][2]['style']['conditional']['border']['all']['color'] );
		$this->assertEquals( 'dashed', $json['components'][2]['style']['border']['all']['style'] );
		$this->assertEquals( 10, $json['components'][2]['style']['border']['all']['width'] );
		$this->assertEquals( '#abcdef', $json['componentTextStyles']['default-blockquote-left']['textColor'] );
		$this->assertEquals( '#bcdefa', $json['componentTextStyles']['default-blockquote-left']['conditional']['textColor'] );
		$this->assertEquals( 'AmericanTypewriter', $json['componentTextStyles']['default-blockquote-left']['fontName'] );
		$this->assertEquals( 28, $json['componentTextStyles']['default-blockquote-left']['lineHeight'] );
		$this->assertEquals( 20, $json['componentTextStyles']['default-blockquote-left']['fontSize'] );
		$this->assertEquals( 0.5, $json['componentTextStyles']['default-blockquote-left']['tracking'] );
	}

	/**
	 * Tests pullquote settings.
	 */
	public function test_settings_pullquote() {
		$this->set_theme_settings(
			[
				'pullquote_color'               => '#abcdef',
				'pullquote_color_dark'          => '#bcdefa',
				'pullquote_font'                => 'AmericanTypewriter',
				'pullquote_hanging_punctuation' => 'yes',
				'pullquote_line_height'         => 28,
				'pullquote_size'                => 20,
				'pullquote_tracking'            => 50,
				'pullquote_transform'           => 'uppercase',
			]
		);
		$json = $this->get_json_for_post( $this->get_pullquote() );
		$this->assertEquals( '#abcdef', $json['componentTextStyles']['default-pullquote-left']['textColor'] );
		$this->assertEquals( '#bcdefa', $json['componentTextStyles']['default-pullquote-left']['conditional']['textColor'] );
		$this->assertEquals( 'AmericanTypewriter', $json['componentTextStyles']['default-pullquote-left']['fontName'] );
		$this->assertTrue( $json['componentTextStyles']['default-pullquote-left']['hangingPunctuation'] );
		$this->assertEquals( 28, $json['componentTextStyles']['default-pullquote-left']['lineHeight'] );
		$this->assertEquals( 20, $json['componentTextStyles']['default-pullquote-left']['fontSize'] );
		$this->assertEquals( 0.5, $json['componentTextStyles']['default-pullquote-left']['tracking'] );
		$this->assertEquals( 'uppercase', $json['componentTextStyles']['default-pullquote-left']['textTransform'] );
	}

	/**
	 * Tests the transformation process from a blockquote to a Quote component.
	 */
	public function test_transform_blockquote() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'title', 'author' ] ] );
		$json = $this->get_json_for_post( $this->get_blockquote() );
		$this->assertEquals( 'container', $json['components'][2]['role'] );
		$this->assertEquals( 'quote', $json['components'][2]['components'][0]['role'] );
		$this->assertEquals( '<p>Test blockquote.</p>', $json['components'][2]['components'][0]['text'] );
		$this->assertEquals( 'html', $json['components'][2]['components'][0]['format'] );
		$this->assertEquals( 'default-blockquote-left', $json['components'][2]['components'][0]['textStyle'] );
		$this->assertEquals( 'blockquote-layout', $json['components'][2]['components'][0]['layout'] );
	}

	/**
	 * Tests the transformation process with text alignment checking.
	 */
	public function test_transform_blockquote_alignment() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'title', 'author' ] ] );
		// Test right alignment.
		$content_right = <<<HTML
<!-- wp:quote -->
<blockquote class="wp-block-quote has-text-align-right"><p>Test blockquote.</p></blockquote>
<!-- /wp:quote -->
HTML;
		$post_id_right = self::factory()->post->create( [ 'post_content' => $content_right ] );
		$json_right    = $this->get_json_for_post( $post_id_right );
		$this->assertEquals( 'default-blockquote-right', $json_right['components'][2]['components'][0]['textStyle'] );
		$this->assertEquals( 'right', $json_right['componentTextStyles']['default-blockquote-right']['textAlignment'] );

		// Test center alignment.
		$content_center = <<<HTML
<!-- wp:quote -->
<blockquote class="wp-block-quote has-text-align-center"><p>Test blockquote.</p></blockquote>
<!-- /wp:quote -->
HTML;
		$post_id_center = self::factory()->post->create( [ 'post_content' => $content_center ] );
		$json_center    = $this->get_json_for_post( $post_id_center );
		$this->assertEquals( 'default-blockquote-center', $json_center['components'][2]['components'][0]['textStyle'] );
		$this->assertEquals( 'center', $json_center['componentTextStyles']['default-blockquote-center']['textAlignment'] );

		// Test all three.
		$content_all = <<<HTML
<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>Test blockquote left.</p></blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote class="wp-block-quote has-text-align-right"><p>Test blockquote right.</p></blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote class="wp-block-quote has-text-align-center"><p>Test blockquote center.</p></blockquote>
<!-- /wp:quote -->
HTML;
		$post_id_all = self::factory()->post->create( [ 'post_content' => $content_all ] );
		$json_all    = $this->get_json_for_post( $post_id_all );
		$this->assertEquals( 'default-blockquote-left', $json_all['components'][2]['components'][0]['textStyle'] );
		$this->assertEquals( 'default-blockquote-right', $json_all['components'][3]['components'][0]['textStyle'] );
		$this->assertEquals( 'default-blockquote-center', $json_all['components'][4]['components'][0]['textStyle'] );
		$this->assertEquals( 'left', $json_all['componentTextStyles']['default-blockquote-left']['textAlignment'] );
		$this->assertEquals( 'right', $json_all['componentTextStyles']['default-blockquote-right']['textAlignment'] );
		$this->assertEquals( 'center', $json_all['componentTextStyles']['default-blockquote-center']['textAlignment'] );
	}

	/**
	 * Tests the transformation process from a pullquote to a Quote component.
	 *
	 * @param string $text                The text to use in the blockquote element.
	 * @param string $expected            The expected text node value after compilation.
	 * @param string $hanging_punctuation The setting value for hanging punctuation.
	 */
	#[DataProvider( 'data_transform_pullquote' )]
	public function test_transform_pullquote( $text, $expected, $hanging_punctuation ) {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'title', 'byline' ] ] );
		$this->set_theme_settings( [ 'pullquote_hanging_punctuation' => $hanging_punctuation ] );
		$content = <<<HTML
<!-- wp:pullquote -->
<figure class="wp-block-pullquote"><blockquote><p>{$text}</p></blockquote></figure>
<!-- /wp:pullquote -->
HTML;
		$post_id = self::factory()->post->create( [ 'post_content' => $content ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'container', $json['components'][2]['role'] );
		$this->assertEquals( 'quote', $json['components'][2]['components'][0]['role'] );
		$this->assertEquals( $expected, $json['components'][2]['components'][0]['text'] );
		$this->assertEquals( 'html', $json['components'][2]['components'][0]['format'] );
		$this->assertEquals( 'default-pullquote-left', $json['components'][2]['components'][0]['textStyle'] );
		$this->assertEquals( 'pullquote-layout', $json['components'][2]['components'][0]['layout'] );
	}

	/**
	 * Ensures the JSON customizations to the pullquote element in the Modern
	 * example theme do not break the article JSON.
	 *
	 * @param string   $theme      The theme slug to test.
	 * @param string[] $meta_order The order of meta components to use.
	 */
	#[DataProvider( 'data_transform_pullquote_for_theme' )]
	public function test_transform_pullquote_for_theme( $theme, $meta_order ) {
		$this->load_example_theme( $theme );
		$this->set_theme_settings( [ 'meta_component_order' => $meta_order ] );
		$json = $this->get_json_for_post( $this->get_pullquote() );
		$this->assertEquals( 'default-pullquote-left', $json['components'][2]['components'][1]['textStyle'] );
	}
}
