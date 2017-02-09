<?php
/**
 * Publish to Apple News Tests: Facebook_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Facebook.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Facebook;

/**
 * A class which is used to test the Apple_Exporter\Components\Facebook class.
 */
class Facebook_Test extends Component_TestCase {

	/**
	 * A data provider for the test_transform function.
	 *
	 * @see self::test_transform()
	 *
	 * @access public
	 * @return array An array of test data
	 */
	public function data_transform() {
		return array(
			array( 'https://www.facebook.com/page-name/posts/12345' ),
			array( 'https://www.facebook.com/username/posts/12345' ),
			array( 'https://www.facebook.com/username/activity/12345' ),
			array( 'https://www.facebook.com/photo.php?fbid=12345' ),
			array( 'https://www.facebook.com/photos/12345' ),
			array( 'https://www.facebook.com/permalink.php?story_fbid=12345' ),
		);
	}

	/**
	 * A filter function to modify the URL in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @access public
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_facebook_json( $json ) {
		$json['URL'] = 'https://www.facebook.com/test/posts/54321';

		return $json;
	}

	/**
	 * Test the `apple_news_facebook_json` filter.
	 *
	 * @access public
	 */
	public function testFilter() {

		// Setup.
		$component = new Facebook(
			'https://www.facebook.com/test/posts/12345',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_facebook_json',
			array( $this, 'filter_apple_news_facebook_json' )
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals(
			'https://www.facebook.com/test/posts/54321',
			$result['URL']
		);

		// Teardown.
		remove_filter(
			'apple_news_facebook_json',
			array( $this, 'filter_apple_news_facebook_json' )
		);
	}

	/**
	 * Tests the transformation process from an oEmbed URL to a Facebook component.
	 *
	 * @dataProvider data_transform
	 *
	 * @param string $url The URL to test.
	 *
	 * @access public
	 */
	public function testTransform( $url ) {

		// Setup.
		$component = new Facebook(
			$url,
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			array(
				'role' => 'facebook_post',
				'URL' => $url,
			),
			$component->to_array()
		);
	}
}
