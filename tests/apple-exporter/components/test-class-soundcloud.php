<?php
/**
 * Publish to Apple News Tests: SoundCloud_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\SoundCloud.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\SoundCloud;

/**
 * A class which is used to test the Apple_Exporter\Components\SoundCloud class.
 */
class SoundCloud_Test extends Component_TestCase {

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
			[ 'https://w.soundcloud.com/player/999999999' ],
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
	public function filter_apple_news_soundcloud_json( $json ) {
		$json['URL'] = 'https://w.soundcloud.com/player/999999999';

		return $json;
	}

	/**
	 * Test the `apple_news_soundclound_json` filter.
	 *
	 * @access public
	 */
	public function testFilterSoundCloud() {

		// Setup.
		$component = new SoundCloud(
			'https://w.soundcloud.com/player/999999999',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_soundcloud_json',
			[ $this, 'filter_apple_news_soundcloud_json' ]
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals(
			'https://w.soundcloud.com/player/999999999',
			$result['URL']
		);

		// Teardown.
		remove_filter(
			'apple_news_soundcloud_json',
			[ $this, 'filter_apple_news_soundcloud_json' ]
		);
	}

	/**
	 * Tests the transformation process from an oEmbed URL to a SoundCloud component.
	 *
	 * @dataProvider data_transform
	 *
	 * @param string $url The URL to test.
	 *
	 * @access public
	 */
	public function testTransformSoundCloud( $url ) {

		// Setup. Test Block without Caption
		$component = new SoundCloud(
			'<figure class="wp-block-embed-soundcloud wp-block-embed is-type-rich is-provider-soundcloud wp-embed-aspect-4-3 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper"><iframe title="SONG NAME - Band Name" width="500" height="400" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/999999999"></iframe></div></figure>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test Heading
		$this->assertEquals(
			[
				'role' => 'heading2',
				'text' => 'SONG NAME - Band Name',
				'format' => 'html',
			],
			$component->to_array()['components'][0]
		);

		// Test body text / link.
		$this->assertEquals(
			[
				'role'      => 'body',
				'text'      => '<a href="https://w.soundcloud.com/player/999999999">View on SoundCloud.</a>',
				'format'    => 'html',
				'textStyle' => [
					'fontSize' => 14,
				],
			],
			$component->to_array()['components'][1]
		);

		// Setup. Embed generate caption from data.
		$component = new SoundCloud(
			'<figure class="wp-block-embed-soundcloud wp-block-embed is-type-rich is-provider-soundcloud wp-embed-aspect-4-3 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper"><iframe title="SONG NAME - Band Name" width="500" height="400" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/999999999"></iframe></div><figcaption>Soundcloud Caption</figcaption></figure>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test Heading
		$this->assertEquals(
			[
				'role'   => 'heading2',
				'text'   => 'SONG NAME - Band Name',
				'format' => 'html',
			],
			$component->to_array()['components'][0]
		);

		// Test caption.
		$this->assertEquals(
			[
				'role'      => 'caption',
				'text'      => 'Soundcloud Caption',
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
				'text'      => '<a href="https://w.soundcloud.com/player/999999999">View on SoundCloud.</a>',
				'format'    => 'html',
				'textStyle' => [
					'fontSize' => 14,
				],
			],
			$component->to_array()['components'][2]
		);
	}
}
