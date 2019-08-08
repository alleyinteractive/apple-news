<?php
/**
 * Publish to Apple News Tests: WP_Embed_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\WP_Embed.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\WP_Embed;

/**
 * A class which is used to test the Apple_Exporter\Components\WP_Embed class.
 */
class WP_Embed_Test extends Component_TestCase {

	/**
	 * A data provider for the test_transform function.
	 *
	 * @see self::test_transform()
	 *
	 * @access public
	 * @return array An array of test data
	 */
	public function data_transform() {
		return [
			[ 'https://test-url.com/blog-post-url-slug' ],
		];
	}

	/**
	 * A filter function to modify the URL in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @access public
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_wp_embed_json( $json ) {
		$json['URL'] = 'https://test-url.com/blog-post-url-slug';

		return $json;
	}

		/**
	 * Test the `apple_news_wp_embed_json` filter.
	 *
	 * @access public
	 */
	public function testFilterWPEmbed() {

		// Setup.
		$component = new WP_Embed(
			'https://test-url.com/blog-post-url-slug',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_wp_embed_json',
			[ $this, 'filter_apple_news_wp_embed_json' ]
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals(
			'https://test-url.com/blog-post-url-slug',
			$result['URL']
		);

		// Teardown.
		remove_filter(
			'apple_news_wp_embed_json',
			[ $this, 'filter_apple_news_wp_embed_json' ]
		);
	}

	/**
	 * Tests the transformation process from an oEmbed URL to a WP_Embed component.
	 *
	 * @dataProvider data_transform
	 *
	 * @param string $url The URL to test.
	 *
	 * @access public
	 */
	public function testTransformWPEmbed( $url ) {

		// Setup. Heading for embed.
		$component = new WP_Embed(
			'<figure class="wp-block-embed-wordpress wp-block-embed is-type-wp-embed is-provider-alley"><div class="wp-block-embed__wrapper">
			<blockquote class="wp-embedded-content" data-secret="laxVbHVh5h" style="display: none;"><a href="https://test-url.com/blog-post-url-slug">Blog Post Title</a></blockquote><iframe title="Blog Post Title" class="wp-embedded-content" sandbox="allow-scripts" security="restricted" src="https://test-url.com/blog-post-url-slugembed/#?secret=laxVbHVh5h" data-secret="laxVbHVh5h" width="500" height="346" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe></div></figure>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test Heading
		$this->assertEquals(
			[
				'role' => 'heading2',
				'text' => 'WordPress Embed: Blog Post Title.',
			],
			$component->to_array()['components'][0]
		);

		// Setup. Embed generate caption from data.
		$component = new WP_Embed(
			'<figure class="wp-block-embed-wordpress wp-block-embed is-type-wp-embed is-provider-alley"><div class="wp-block-embed__wrapper">
			<blockquote class="wp-embedded-content" data-secret="laxVbHVh5h" style="display: none;"><a href="https://test-url.com/blog-post-url-slug">Blog Post Title</a></blockquote><iframe title="Blog Post Title" class="wp-embedded-content" sandbox="allow-scripts" security="restricted" src="https://test-url.com/blog-post-url-slugembed/#?secret=laxVbHVh5h" data-secret="laxVbHVh5h" width="500" height="346" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe></div></figure>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test for Caption
		$this->assertEquals(
			[
				'role'      => 'body',
				'text'      => '<a href="https://test-url.com/blog-post-url-slug">View on test-url.com.</a>',
				'format'    => 'html',
				'textStyle' => [
					'fontSize' => 14,
				],
			],
			$component->to_array()['components'][1]
		);
	}
}
