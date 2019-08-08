<?php
/**
 * Publish to Apple News Tests: Spotify_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Spotify.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Spotify;

/**
 * A class which is used to test the Apple_Exporter\Components\Spotify class.
 */
class Spotify_Test extends Component_TestCase {

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
			[ 'https://open.spotify.com/embed/track/999999999' ],
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
	public function filter_apple_news_spotify_json( $json ) {
		$json['URL'] = 'https://open.spotify.com/embed/track/999999999';

		return $json;
	}

		/**
	 * Test the `apple_news_spotify_json` filter.
	 *
	 * @access public
	 */
	public function testFilterSpotify() {

		// Setup.
		$component = new Spotify(
			'https://open.spotify.com/embed/track/999999999',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_spotify_json',
			[ $this, 'filter_apple_news_spotify_json' ]
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals(
			'https://open.spotify.com/embed/track/999999999',
			$result['URL']
		);

		// Teardown.
		remove_filter(
			'apple_news_spotify_json',
			[ $this, 'filter_apple_news_spotify_json' ]
		);
	}

	/**
	 * Tests the transformation process from an oEmbed URL to a Spotify component.
	 *
	 * @dataProvider data_transform
	 *
	 * @param string $url The URL to test.
	 *
	 * @access public
	 */
	public function testTransformSpotify( $url ) {

		// Setup. Test Block without Caption
		$component = new Spotify(
			'<figure class="wp-block-embed-spotify wp-block-embed is-type-rich is-provider-spotify wp-embed-aspect-9-16 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
			<iframe title="Spotify Embed: Band Name" width="300" height="380" allowtransparency="true" frameborder="0" allow="encrypted-media" src="https://open.spotify.com/embed/track/999999999"></iframe>
			</div></figure>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test Heading
		$this->assertEquals(
			[
				'role' => 'heading2',
				'text' => 'Spotify Embed: Band Name',
			],
			$component->to_array()['components'][0]
		);

		// Test body text / link.
		$this->assertEquals(
			[
				'role'      => 'body',
				'text'      => '<a href="https://open.spotify.com/embed/track/999999999">View on Spotify.</a>',
				'format'    => 'html',
				'textStyle' => [
					'fontSize' => 14,
				],
			],
			$component->to_array()['components'][1]
		);

		// Setup. Embed generate caption from data.
		$component = new Spotify(
			'<figure class="wp-block-embed-spotify wp-block-embed is-type-rich is-provider-spotify wp-embed-aspect-9-16 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
			<iframe title="Spotify Embed: Band Name" width="300" height="380" allowtransparency="true" frameborder="0" allow="encrypted-media" src="https://open.spotify.com/embed/track/999999999"></iframe>
			</div><figcaption>Spotify Caption</figcaption></figure>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test Heading
		$this->assertEquals(
			[
				'role' => 'heading2',
				'text' => 'Spotify Embed: Band Name',
			],
			$component->to_array()['components'][0]
		);

		// Test caption.
		$this->assertEquals(
			[
				'role'      => 'caption',
				'text'      => 'Spotify Caption',
				'format'    => 'html',
				'textStyle' => [
					'fontSize' => 16,
				],
				'hidden'    => false,
			],
			$component->to_array()['components'][1]
		);

		// Test body text / link.
		$this->assertEquals(
			[
				'role'      => 'body',
				'text'      => '<a href="https://open.spotify.com/embed/track/999999999">View on Spotify.</a>',
				'format'    => 'html',
				'textStyle' => [
					'fontSize' => 14,
				],
			],
			$component->to_array()['components'][2]
		);
	}
}
