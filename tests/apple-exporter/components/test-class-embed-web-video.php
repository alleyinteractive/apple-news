<?php
/**
 * Publish to Apple News Tests: Embed_Web_Video_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Embed_Web_Video as Embed_Web_Video;

/**
 * A class to test the behavior of the Embed_Web_Video component.
 */
class Embed_Web_Video_Test extends Component_TestCase {

	/**
	 * A data provider for the testTransformEmbedWebVideo function.
	 *
	 * @see self::testTransformEmbedWebVideo()
	 *
	 * @access public
	 * @return array Parameters to use when calling testTransformEmbedWebVideo.
	 */
	public function dataTransformEmbedWebVideo() {
		return array(
			array(
				'<p>https://www.youtube.com/embed/0qwALOOvUik</p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p>https://www.youtube.com/watch?v=0qwALOOvUik</p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p>https://youtube.com/embed/0qwALOOvUik</p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p>https://youtube.com/watch?v=0qwALOOvUik</p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p>http://www.youtube.com/embed/0qwALOOvUik</p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p>http://www.youtube.com/watch?v=0qwALOOvUik</p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p>http://youtube.com/embed/0qwALOOvUik</p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p>http://youtu.be/0qwALOOvUik</p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p>http://youtube.com/watch?v=0qwALOOvUik</p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p>https://youtu.be/0qwALOOvUik</p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p>https://www.youtube.com/watch?v=J2nN-Yrt1EU</p>',
				'https://www.youtube.com/embed/J2nN-Yrt1EU',
			),
			array(
				'<p>https://www.youtube.com/embed/J2nN-Yrt1EU</p>',
				'https://www.youtube.com/embed/J2nN-Yrt1EU',
			),
			array(
				'<p>https://youtu.be/J2nN-Yrt1EU</p>',
				'https://www.youtube.com/embed/J2nN-Yrt1EU',
			),
			array(
				'<p>https://vimeo.com/12819723</p>',
				'https://player.vimeo.com/video/12819723',
			),
			array(
				'<iframe title="YouTube" width="640" height="360" src="http://www.youtube.com/embed/0qwALOOvUik" frameborder="0" allowfullscreen></iframe>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<iframe title="YouTube" width="640" height="360" src="https://www.youtube.com/embed/0qwALOOvUik" frameborder="0" allowfullscreen></iframe>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<iframe width="640" height="360" src="http://www.youtube.com/embed/0qwALOOvUik?autoplay=1"></iframe>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<iframe width="640" height="360" src="http://www.youtube.com/embed/0qwALOOvUik" />',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<p><a href="http://youtube.com/embed/0qwALOOvUik">http://youtube.com/embed/0qwALOOvUik</a></p>',
				'https://www.youtube.com/embed/0qwALOOvUik',
			),
			array(
				'<iframe src="//player.vimeo.com/video/12819723" width="560" height="315" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
				'https://player.vimeo.com/video/12819723',
			),
			array(
				'<iframe src="//player.vimeo.com/video/12819723?title=0&byline=0&portrait=0" width="560" height="315" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
				'https://player.vimeo.com/video/12819723',
			),
		);
	}

	/**
	 * A filter function to modify the aspect ratio.
	 *
	 * @param array $json An array representing JSON for the component.
	 *
	 * @access public
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_embed_web_video_json( $json ) {
		$json['aspectRatio'] = '1.4';
		return $json;
	}

	/**
	 * Test the `apple_news_embed_web_video_json` filter.
	 *
	 * @access public
	 */
	public function testFilter() {

		// Setup.
		add_filter(
			'apple_news_embed_web_video_json',
			array( $this, 'filter_apple_news_embed_web_video_json' )
		);
		$component = new Embed_Web_Video(
			'<p>https://vimeo.com/12819723</p>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			array(
				'role'        => 'embedwebvideo',
				'URL'         => 'https://player.vimeo.com/video/12819723',
				'aspectRatio' => '1.4',
			),
			$component->to_array()
		);

		// Teardown.
		remove_filter(
			'apple_news_embed_web_video_json',
			array( $this, 'filter_apple_news_embed_web_video_json' )
		);
	}

	/**
	 * Tests the transformation process from a web video URL to an
	 * Embed_Web_Video component.
	 *
	 * Tests a variety of URL formats to ensure that they produce the
	 * proper output JSON using the dataProvider referenced below.
	 *
	 * @dataProvider dataTransformEmbedWebVideo
	 *
	 * @param string $html      The HTML to be matched by the parser.
	 * @param string $final_url The final URL used in the JSON.
	 *
	 * @access public
	 */
	public function testTransformEmbedWebVideo( $html, $final_url ) {

		// Setup.
		$component = new Embed_Web_Video(
			$html,
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			array(
				'role'        => 'embedwebvideo',
				'URL'         => $final_url,
				'aspectRatio' => '1.777',
			),
			$component->to_array()
		);
	}

	/**
	 * Tests an unsupported video provider.
	 *
	 * @access public
	 */
	public function testTransformUnsupportedProvider() {

		// Setup.
		$component = new Embed_Web_Video(
			'<iframe src="//player.notvimeo.com/video/12819723" width="560" height="315" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertNull( $component->to_array() );
	}
}
