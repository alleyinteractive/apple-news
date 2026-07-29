<?php
/**
 * Publish to Apple News tests: Video class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

/**
 * A class to test the behavior of the
 * \Apple_Exporter\Components\Video class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Video extends Component_TestCase {

	/**
	 * Contains test HTML content to feed into the Video object for testing.
	 *
	 * @var string
	 */
	private $video_content = <<<HTML
<video class="wp-video-shortcode" id="video-71-1" width="525" height="295" poster="https://www.example.org/wp-content/uploads/2017/02/ExamplePoster.jpg" preload="metadata" controls="controls">
	<source type="video/mp4" src="https://www.example.org/wp-content/uploads/2017/02/example-video.mp4?_=1" />
	<a href="https://www.example.org/wp-content/uploads/2017/02/example-video.mp4">https://www.example.org/wp-content/uploads/2017/02/example-video.mp4</a>
</video>
HTML;

	/**
	 * A filter function to modify the video URL in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_video_json( $json ) {
		$json['URL'] = 'https://www.example.org/filter-me';

		return $json;
	}

	/**
	 * Test the `apple_news_quote_json` filter.
	 */
	public function test_filter() {

		// Setup.
		$component = $this->get_component();
		add_filter(
			'apple_news_video_json',
			[ $this, 'filter_apple_news_video_json' ]
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals(
			'https://www.example.org/filter-me',
			$result['URL']
		);

		// Teardown.
		remove_filter(
			'apple_news_video_json',
			[ $this, 'filter_apple_news_video_json' ]
		);
	}

	/**
	 * Tests the ability for the Video component to get and save caption information
	 */
	public function test_caption() {
		$component = $this->get_component( '<figure class="wp-block-video"><video controls="" src="https://www.example.org/test.mp4"/><figcaption>caption</figcaption></figure>' );

		// Test.
		$this->assertEquals(
			[
				'role'       => 'container',
				'components' => [
					[
						'role' => 'video',
						'URL'  => 'https://www.example.org/test.mp4',
					],
					[
						'role'   => 'caption',
						'text'   => 'caption',
						'format' => 'html',
					],
				],
			],
			$component->to_array()
		);
	}

	/**
	 * Tests the transformation process from a video element to a Video component.
	 */
	public function test_generated_json() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$component = $this->get_component();

		// Test.
		$result = $component->to_array();
		$this->assertEquals(
			'https://www.example.org/wp-content/uploads/2017/02/ExamplePoster.jpg',
			$result['stillURL']
		);
		$this->assertEquals(
			'video',
			$result['role']
		);
		$this->assertEquals(
			'https://www.example.org/wp-content/uploads/2017/02/example-video.mp4?_=1',
			$result['URL']
		);
	}

	/**
	 * A function to get a basic component for testing using defined content.
	 *
	 * @param string $content HTML for the component.
	 *
	 * @return Video A Video object containing the specified content.
	 */
	private function get_component( $content = '' ) {
		return new \Apple_Exporter\Components\Video(
			! empty( $content ) ? $content : $this->video_content,
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);
	}
}
