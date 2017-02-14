<?php
/**
 * Publish to Apple News Tests: Quote_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Quote.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Video;
use Apple_Exporter\Workspace;

/**
 * A class which is used to test the Apple_Exporter\Components\Video class.
 */
class Video_Test extends Component_TestCase {

	/**
	 * Contains test HTML content to feed into the Video object for testing.
	 *
	 * @access private
	 * @var string
	 */
	private $_content = <<<HTML
<video class="wp-video-shortcode" id="video-71-1" width="525" height="295" poster="https://example.com/wp-content/uploads/2017/02/ExamplePoster.jpg" preload="metadata" controls="controls">
	<source type="video/mp4" src="https://example.com/wp-content/uploads/2017/02/example-video.mp4?_=1" />
	<a href="https://example.com/wp-content/uploads/2017/02/example-video.mp4">https://example.com/wp-content/uploads/2017/02/example-video.mp4</a>
</video>
HTML;

	/**
	 * A filter function to modify the video URL in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @access public
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_video_json( $json ) {
		$json['URL'] = 'http://filter.me';

		return $json;
	}

	/**
	 * Test the `apple_news_quote_json` filter.
	 *
	 * @access public
	 */
	public function testFilter() {

		// Setup.
		$component = $this->_get_component();
		add_filter(
			'apple_news_video_json',
			array( $this, 'filter_apple_news_video_json' )
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals(
			'http://filter.me',
			$result['URL']
		);

		// Teardown.
		remove_filter(
			'apple_news_video_json',
			array( $this, 'filter_apple_news_video_json' )
		);
	}

	/**
	 * Tests the transformation process from a video element to a Video component.
	 *
	 * @access public
	 */
	public function testGeneratedJSON() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$component = $this->_get_component();

		// Test.
		$result = $component->to_array();
		$this->assertEquals(
			'https://example.com/wp-content/uploads/2017/02/ExamplePoster.jpg',
			$result['stillURL']
		);
		$this->assertEquals(
			'video',
			$result['role']
		);
		$this->assertEquals(
			'https://example.com/wp-content/uploads/2017/02/example-video.mp4?_=1',
			$result['URL']
		);
	}

	/**
	 * A function to get a basic component for testing using defined content.
	 *
	 * @access private
	 * @return Video A Video object containing the specified content.
	 */
	private function _get_component( $content = '' ) {

		// Negotiate content.
		if ( empty( $content ) ) {
			$content = $this->_content;
		}

		// Build the component.
		$component = new Video(
			$content,
			new Workspace( 1 ),
			$this->settings,
			$this->styles,
			$this->layouts
		);

		return $component;
	}
}
