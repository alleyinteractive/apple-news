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

use Apple_Exporter\Components\Quote;
use Apple_Exporter\Exporter;
use Apple_Exporter\Exporter_Content;

/**
 * A class which is used to test the Apple_Exporter\Components\Quote class.
 */
class Quote_Test extends Component_TestCase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @access public
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_quote_json( $json ) {
		$json['textStyle'] = 'fancy-quote';

		return $json;
	}

	/**
	 * Test the `apple_news_quote_json` filter.
	 *
	 * @access public
	 */
	public function testFilter() {

		// Setup.
		$component = new Quote(
			'<blockquote><p>my quote</p></blockquote>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_quote_json',
			array( $this, 'filter_apple_news_quote_json' )
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals( 'fancy-quote', $result['textStyle'] );

		// Teardown.
		remove_filter(
			'apple_news_quote_json',
			array( $this, 'filter_apple_news_quote_json' )
		);
	}

	/**
	 * Tests quote settings.
	 *
	 * @access public
	 */
	public function testSettingsPullquote() {

		// Setup.
		$content = new Exporter_Content(
			3,
			'Title',
			'<blockquote class="apple-news-pullquote"><p>my quote</p></blockquote>'
		);

		// Set quote settings.
		$this->settings->pullquote_font = 'TestFontName';
		$this->settings->pullquote_size = 20;
		$this->settings->pullquote_color = '#abcdef';
		$this->settings->pullquote_line_height = 28;
		$this->settings->pullquote_tracking = 50;
		$this->settings->pullquote_transform = 'uppercase';

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = json_decode( $exporter->export(), true );

		// Validate body settings in generated JSON.
		$this->assertEquals(
			'TestFontName',
			$json['componentTextStyles']['default-pullquote']['fontName']
		);
		$this->assertEquals(
			20,
			$json['componentTextStyles']['default-pullquote']['fontSize']
		);
		$this->assertEquals(
			'#abcdef',
			$json['componentTextStyles']['default-pullquote']['textColor']
		);
		$this->assertEquals(
			28,
			$json['componentTextStyles']['default-pullquote']['lineHeight']
		);
		$this->assertEquals(
			0.5,
			$json['componentTextStyles']['default-pullquote']['tracking']
		);
		$this->assertEquals(
			'uppercase',
			$json['componentTextStyles']['default-pullquote']['textTransform']
		);
	}

	/**
	 * Tests the transformation process from a blockquote to a Quote component.
	 *
	 * @access public
	 */
	public function testTransformPullquote() {

		// Setup.
		$component = new Quote(
			'<blockquote class="apple-news-pullquote"><p>my quote</p></blockquote>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result_wrapper = $component->to_array();
		$result = $result_wrapper['components'][0];

		// Test.
		$this->assertEquals( 'container', $result_wrapper['role'] );
		$this->assertEquals( 'quote', $result['role'] );
		$this->assertEquals( "my quote\n\n", $result['text'] );
		$this->assertEquals( 'markdown', $result['format'] );
		$this->assertEquals( 'default-pullquote', $result['textStyle'] );
		$this->assertEquals( 'pullquote-layout', $result['layout'] );
	}
}
