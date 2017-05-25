<?php
/**
 * Publish to Apple News Tests: Byline_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Byline.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Byline;
use Apple_Exporter\Exporter;
use Apple_Exporter\Exporter_Content;

/**
 * A class which is used to test the Apple_Exporter\Components\Byline class.
 */
class Byline_Test extends Component_TestCase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @access public
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_byline_json( $json ) {
		$json['layout'] = 'fancy-layout';

		return $json;
	}

	/**
	 * Test the `apple_news_byline_json` filter.
	 *
	 * @access public
	 */
	public function testFilter() {

		// Setup.
		$component = new Byline(
			'This is the byline',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_byline_json',
			array( $this, 'filter_apple_news_byline_json' )
		);

		// Test.
		$this->assertEquals(
			array(
				'text' => "This is the byline",
				'role' => 'byline',
				'textStyle' => 'default-byline',
				'layout' => 'fancy-layout',
			),
			$component->to_array()
		);

		// Teardown.
		remove_filter(
			'apple_news_byline_json',
			array( $this, 'filter_apple_news_byline_json' )
		);
	}

	/**
	 * Tests byline settings.
	 *
	 * @access public
	 */
	public function testSettings() {

		// Setup.
		$content = new Exporter_Content(
			1,
			__( 'My Title', 'apple-news' ),
			'<p>' . __( 'Hello, World!', 'apple-news' ) . '</p>',
			null,
			null,
			'Test byline'
		);

		// Set byline settings.
		$theme = \Apple_Exporter\Theme::get_used();
		$settings = $theme->all_settings();
		$settings['byline_font'] = 'AmericanTypewriter';
		$settings['byline_size'] = 20;
		$settings['byline_color'] = '#abcdef';
		$settings['byline_line_height'] = 28;
		$settings['byline_tracking'] = 50;
		$theme->load( $settings );
		$this->assertTrue( $theme->save() );

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = $exporter->export();
		$this->ensure_tokens_replaced( $json );
		$json = json_decode( $json, true );

		// Validate byline settings in generated JSON.
		$this->assertEquals(
			'AmericanTypewriter',
			$json['componentTextStyles']['default-byline']['fontName']
		);
		$this->assertEquals(
			20,
			$json['componentTextStyles']['default-byline']['fontSize']
		);
		$this->assertEquals(
			'#abcdef',
			$json['componentTextStyles']['default-byline']['textColor']
		);
		$this->assertEquals(
			28,
			$json['componentTextStyles']['default-byline']['lineHeight']
		);
		$this->assertEquals(
			0.5,
			$json['componentTextStyles']['default-byline']['tracking']
		);
	}

	/**
	 * Test the setting to disable the initial dropcap.
	 *
	 * @access public
	 */
	public function testWithoutDropcap() {

		// Setup.
		$component = new Byline(
			'This is the byline',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			array(
				'text' => "This is the byline",
				'role' => 'byline',
				'textStyle' => 'default-byline',
				'layout' => 'byline-layout',
			),
			$component->to_array()
		);
	}
}
