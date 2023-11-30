<?php
/**
 * Publish to Apple News tests: Audio_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Components\Audio;

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Audio class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Audio_Test extends Apple_News_Component_TestCase {

	/**
	 * Tests basic JSON generation.
	 */
	public function test_generated_json() {
		$component = new Audio(
			'<audio><source src="https://www.example.org/audio-file.mp3?some_query=string"></audio>',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		$json = $component->to_array();
		$this->assertEquals( 'audio', $json['role'] );
		$this->assertEquals( 'https://www.example.org/audio-file.mp3?some_query=string', $json['URL'] );
	}

	/**
	 * Tests HTML formatting with captions.
	 */
	public function test_caption() {
		$component = new Audio(
			'<figure class="wp-block-audio"><audio controls="" src="https://www.example.org/Song-1.mp3"/><figcaption>caption</figcaption></figure>',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			[
				'role'       => 'container',
				'components' => [
					[
						'role' => 'audio',
						'URL'  => 'https://www.example.org/Song-1.mp3',
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
	 * Tests the behavior of the apple_news_audio_json filter.
	 */
	public function test_filter() {
		$component = new Audio(
			'<audio><source src="https://www.example.org/audio-file.mp3?some_query=string"></audio>',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		add_filter(
			'apple_news_audio_json',
			function ( $json ) {
				$json['URL'] = 'https://www.example.org/audio-file.mp3?some_query=string';
				return $json;
			}
		);

		$json = $component->to_array();
		$this->assertEquals( 'audio', $json['role'] );
		$this->assertEquals( 'https://www.example.org/audio-file.mp3?some_query=string', $json['URL'] );
	}
}
