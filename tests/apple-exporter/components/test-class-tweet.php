<?php
/**
 * Publish to Apple News tests: Apple_News_Tweet_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Components\Tweet;

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Tweet class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Tweet_Test extends Apple_News_Component_TestCase {

	/**
	 * A data provider for the testComponent function.
	 *
	 * @return array An array of arrays of function arguments.
	 */
	public function data_tweets() {
		return [
			[
				'<blockquote class="twitter-tweet" lang="en"><p lang="en" dir="ltr">Swift will be open source later this year, available for iOS, OS X, and Linux. <a href="https://t.co/yQhyzxukTn">https://t.co/yQhyzxukTn</a></p>&mdash; Federico Ramirez (@gosukiwi) <a href="https://twitter.com/gosukiwi/status/608069908044390400">June 9, 2015</a></blockquote>',
				'https://twitter.com/gosukiwi/status/608069908044390400',
			],
			[
				'<blockquote class="twitter-tweet" lang="en">WordPress.com (@wordpressdotcom) <a href="https://twitter.com/#!/wordpressdotcom/status/204557548249026561" data-datetime="2012-05-21T13:01:34+00:00">May 21, 2012</a></blockquote>',
				'https://twitter.com/wordpressdotcom/status/204557548249026561',
			],
			[
				'<blockquote class="twitter-tweet" lang="en">WordPress.com (@wordpressdotcom) <a href="https://www.twitter.com/#!/wordpressdotcom/status/204557548249026561" data-datetime="2012-05-21T13:01:34+00:00">May 21, 2012</a></blockquote>',
				'https://twitter.com/wordpressdotcom/status/204557548249026561',
			],
			[
				'<blockquote class="twitter-tweet" lang="en">WordPress.com (@wordpressdotcom) <a href="https://twitter.com/#!/wordpressdotcom/statuses/204557548249026561" data-datetime="2012-05-21T13:01:34+00:00">May 21, 2012</a></blockquote>',
				'https://twitter.com/wordpressdotcom/status/204557548249026561',
			],
			[
				'<blockquote class="twitter-tweet" lang="en"><p><a href="https://twitter.com/foo/status/1111">twitter.com/foo/status/1111</a></p>&mdash; <br />WordPress.com (@wordpressdotcom) <a href="https://twitter.com/#!/wordpressdotcom/status/123" data-datetime="2012-05-21T13:01:34+00:00">May 21, 2012</a></blockquote>',
				'https://twitter.com/wordpressdotcom/status/123',
			],
		];
	}

	/**
	 * Given HTML and an expected URL value, tests the component.
	 *
	 * @dataProvider data_tweets
	 *
	 * @param string $html The HTML to be fed to the component.
	 * @param string $url  The expected URL of the Twitter component.
	 */
	public function test_component( $html, $url ) {
		$component = new Tweet(
			$html,
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		$result = $component->to_array();
		$this->assertEquals( 'tweet', $result['role'] );
		$this->assertEquals( $url, $result['URL'] );
	}

	/**
	 * Ensures invalid Twitter embeds are not converted.
	 */
	public function test_invalid_markup() {
		$component = new Tweet(
			'<blockquote class="twitter-tweet" lang="en">Invalid content. No URL.</blockquote>',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		$this->assertEquals( null, $component->to_array() );
	}

	/**
	 * Tests the behavior of an oEmbed.
	 */
	public function test_matches_a_single_url() {
		$node = $this->build_node( 'https://twitter.com/gosukiwi/status/608069908044390400' );
		$this->assertNotNull( Tweet::node_matches( $node ) );
	}

	/**
	 * Tests the behavior of the apple_news_tweet_json filter.
	 */
	public function test_filter() {
		$component = new Tweet(
			'<blockquote class="twitter-tweet" lang="en"><p><a href="https://twitter.com/foo/status/1111">twitter.com/foo/status/1111</a></p>&mdash; <br />WordPress.com (@wordpressdotcom) <a href="https://twitter.com/#!/wordpressdotcom/status/123" data-datetime="2012-05-21T13:01:34+00:00">May 21, 2012</a></blockquote>',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		add_filter(
			'apple_news_tweet_json',
			function ( $json ) {
				$json['URL'] = 'https://twitter.com/alleydev/status/123';
				return $json;
			}
		);

		$result = $component->to_array();
		$this->assertEquals( 'https://twitter.com/alleydev/status/123', $result['URL'] );
	}
}
