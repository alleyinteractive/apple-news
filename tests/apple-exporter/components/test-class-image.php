<?php
/**
 * Publish to Apple News tests: Apple_News_Image_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Components\Image;

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Image class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Image_Test extends Apple_News_Component_TestCase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_image_json( $json ) {
		$json['layout'] = 'default-image';

		return $json;
	}

	/**
	 * Test component role ('image' or 'photo') based on postmeta selection.
	 */
	public function test_component_role() {
		// Create test post.
		$post_id = self::factory()->post->create(
			[
				'post_content' => '<img src="https://placeimg.com/640/480/any" alt="Example" />',
			]
		);

		// Create a new attachment and assign it as the featured image for the cover component.
		set_post_thumbnail(
			$post_id,
			$this->get_new_attachment( 0 )
		);

		// Check that the default component role is 'photo'.
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'photo', $json['components'][1]['components'][3]['role'] );

		// Toggle component type meta.
		update_post_meta( $post_id, 'apple_news_use_image_component', true );

		// Check that the component role is now 'image' after meta toggle.
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'image', $json['components'][1]['components'][3]['role'] );
	}

	/**
	 * Test Image component matching and JSON
	 * output with HTML markup for an image.
	 */
	public function test_transform_image() {
		$this->settings->set( 'html_support', 'yes' );
		$this->settings->set( 'use_remote_images', 'yes' );

		$html = '<img src="https://placeimg.com/640/480/any" alt="Example" align="left" />';

		$component = new Image(
			$html,
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Build the node.
		$node = self::build_node( $html );

		// Test the match is not `null` and that it matches the original node.
		$this->assertNotNull( $component->node_matches( $node ) );
		$this->assertEquals(
			$component->node_matches( $node ),
			$node
		);

		// Get the JSON.
		$json = $component->to_array();

		// Test the JSON.
		$this->assertEquals( 'photo', $json['role'] );
		$this->assertEquals( 'https://placeimg.com/640/480/any', $json['URL'] );
	}

	/**
	 * Test Image component matching with HTML markup for an image deeply nested in a figure tag.
	 *
	 * @see https://github.com/alleyinteractive/apple-news/issues/867.
	 */
	public function test_transform_linked_image() {
		$this->settings->set( 'html_support', 'yes' );
		$this->settings->set( 'use_remote_images', 'yes' );

		$html = <<<HTML
<figure>
	<a href="#">
		<img src="https://placeimg.com/640/480/any" alt="Example" align="left" />
</a>
</figure>
HTML;

		$component = new Image(
			$html,
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Build the node.
		$node = self::build_node( $html );

		$this->assertSame( $node, $component->node_matches( $node ) );
	}

	/**
	 * Test Image component matching and JSON output
	 * with HTML5 markup for an image with a caption.
	 */
	public function test_transform_image_caption() {
		$this->settings->set( 'html_support', 'yes' );
		$this->settings->set( 'use_remote_images', 'yes' );

		$html_caption = <<<HTML
	<figure class="wp-caption">
		<img src="https://placeimg.com/640/480/any" alt="Example" align="left" />
		<figcaption class="wp-caption-text">
			Sed <strong>ac metus</strong> sagittis <em>urna feugiat</em> interdum. Duis vel blandit nisi, id tempus sem. Credit: <a href="https://domain.suffix">Domain</a>
		</figcaption>
	</figure>
HTML;

		// Assign an Image component.
		$component = new Image(
			$html_caption,
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Build the node.
		$node = self::build_node( $html_caption );

		// Test the match is not `null` and that it matches the original node.
		$this->assertNotNull( $component->node_matches( $node ) );
		$this->assertEquals(
			$component->node_matches( $node ),
			$node
		);

		// Get the JSON.
		$json = $component->to_array();

		// Test the JSON.
		$this->assertEquals( 'container', $json['role'] );
		$this->assertContains( $json['components'], $json );
		$this->assertEquals( 'photo', $json['components'][0]['role'] );
		$this->assertEquals( 'https://placeimg.com/640/480/any', $json['components'][0]['URL'] );
		$this->assertEquals( 'Sed <strong>ac metus</strong> sagittis <em>urna feugiat</em> interdum. Duis vel blandit nisi, id tempus sem. Credit: <a href="https://domain.suffix">Domain</a>', $json['components'][0]['caption']['text'] );
	}

	/**
	 * Test empty src attribute.
	 */
	public function test_empty_src() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$component = new Image(
			'<img src="" alt="Example" align="left" />',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result    = $component->to_array();

		// Test.
		$this->assertEmpty( $result );
	}

	/**
	 * Test the `apple_news_image_json` filter.
	 */
	public function test_filter() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'no' );
		$this->prophecized_workspace->bundle_source(
			'test-filter-filename.jpg',
			'https://www.example.org/test-filter-filename.jpg'
		)->shouldBeCalled();
		$component = new Image(
			'<img src="https://www.example.org/test-filter-filename.jpg" alt="Example" />',
			$this->prophecized_workspace->reveal(),
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_image_json',
			[ $this, 'filter_apple_news_image_json' ]
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals( 'default-image', $result['layout'] );

		// Teardown.
		remove_filter(
			'apple_news_image_json',
			[ $this, 'filter_apple_news_image_json' ]
		);
	}

	/**
	 * Test src attribute that is just a fragment.
	 */
	public function test_fragment_src() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$component = new Image(
			'<img src="#fragment" alt="Example" align="left" />',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result    = $component->to_array();

		// Test.
		$this->assertEmpty( $result );
	}

	/**
	 * Test standard JSON export.
	 */
	public function test_generated_json() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'no' );
		$this->prophecized_workspace->bundle_source(
			'image-json-filename.jpg',
			'https://www.example.org/image-json-filename.jpg'
		)->shouldBeCalled();
		$component = new Image(
			'<img src="https://www.example.org/image-json-filename.jpg" alt="Example" align="left" />',
			$this->prophecized_workspace->reveal(),
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result    = $component->to_array();

		// Test.
		$this->assertEquals( 'photo', $result['role'] );
		$this->assertEquals( 'bundle://image-json-filename.jpg', $result['URL'] );
		$this->assertEquals( 'anchored-image', $result['layout'] );
	}

	/**
	 * Test remote image JSON export.
	 */
	public function test_generated_json_remote_images() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$this->prophecized_workspace->bundle_source(
			'filename.jpg',
			'https://www.example.org/filename.jpg'
		)->shouldNotBeCalled();
		$component = new Image(
			'<img src="https://www.example.org/filename.jpg" alt="Example" align="left" />',
			$this->prophecized_workspace->reveal(),
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result    = $component->to_array();

		// Test.
		$this->assertEquals( 'photo', $result['role'] );
		$this->assertEquals( 'https://www.example.org/filename.jpg', $result['URL'] );
		$this->assertEquals( 'anchored-image', $result['layout'] );
	}

	/**
	 * Test relative src attribute.
	 */
	public function test_relative_src() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$component = new Image(
			'<img src="/relative/path/to/image.jpg" alt="Example" align="left" />',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result    = $component->to_array();

		// Test.
		$this->assertEquals( 'photo', $result['role'] );
		$this->assertEquals( 'https://www.example.org/relative/path/to/image.jpg', $result['URL'] );
		$this->assertEquals( 'anchored-image', $result['layout'] );
	}

	/**
	 * Tests dark color setting for image captions
	 */
	public function test_dark_colors() {
		$this->set_theme_settings(
			[
				'caption_color_dark' => '#abcdef',
			]
		);

		$html      = <<<HTML
<figure>
	<img src="https://www.example.org/filename.jpg" alt="Example">
	<figcaption class="wp-caption-text">Caption Text</figcaption>
</figure>
HTML;
		$component = new Image(
			$html,
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result    = $component->to_array();
		$this->assertEquals(
			'#abcdef',
			$result['components'][1]['textStyle']['conditional']['textColor']
		);
	}

	/**
	 * Tests image and image caption settings.
	 */
	public function test_settings() {

		// Setup.
		$this->settings->full_bleed_images = 'yes';
		$this->set_theme_settings(
			[
				'caption_color'       => '#abcdef',
				'caption_font'        => 'AmericanTypewriter',
				'caption_line_height' => 28,
				'caption_size'        => 20,
				'caption_tracking'    => 50,
			]
		);
		$html      = <<<HTML
<figure>
	<img src="https://www.example.org/filename.jpg" alt="Example">
	<figcaption class="wp-caption-text">Caption Text</figcaption>
</figure>
HTML;
		$component = new Image(
			$html,
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result    = $component->to_array();

		// Test.
		$this->assertEquals( true, $result['layout']['ignoreDocumentMargin'] );
		$this->assertEquals(
			'#abcdef',
			$result['components'][1]['textStyle']['textColor']
		);
		$this->assertEquals(
			'AmericanTypewriter',
			$result['components'][1]['textStyle']['fontName']
		);
		$this->assertEquals(
			28,
			$result['components'][1]['textStyle']['lineHeight']
		);
		$this->assertEquals(
			20,
			$result['components'][1]['textStyle']['fontSize']
		);
		$this->assertEquals(
			0.5,
			$result['components'][1]['textStyle']['tracking']
		);
		$this->assertFalse(
			isset( $json['components'][1]['textStyle']['conditional'] )
		);
	}

	/**
	 * Ensures that the lightbox font is set to the same font face as the image caption.
	 */
	public function test_lightbox_font() {
		$this->set_theme_settings(
			[
				'caption_font'         => 'Menlo-Regular',
				'meta_component_order' => [ 'title', 'byline' ],
			]
		);

		// Create an image and give it a caption.
		$image_id = $this->get_new_attachment( 0, 'Test Caption!' );

		// Create a test post with the image with the caption.
		$post_id = self::factory()->post->create(
			[
				'post_content' => $this->get_image_with_caption( $image_id ),
			]
		);

		// Ensure that the font set on the lightbox is the same as the font set on the caption above.
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			'Menlo-Regular',
			$json['components'][2]['components'][0]['caption']['textStyle']['fontName']
		);
	}
}
