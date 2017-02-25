<?php
/**
 * Publish to Apple News Tests: Body_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Body.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Body;
use Apple_Exporter\Exporter;
use Apple_Exporter\Exporter_Content;

/**
 * A class which is used to test the Apple_Exporter\Components\Body class.
 */
class Body_Test extends Component_TestCase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @access public
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_body_json( $json ) {
		$json['textStyle'] = 'fancy-body';

		return $json;
	}

	/**
	 * Test the `apple_news_body_json` filter.
	 *
	 * @access public
	 */
	public function testFilter() {

		// Setup.
		$this->settings->set( 'initial_dropcap', 'no' );
		$component = new Body(
			'<p>my text</p>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_body_json',
			array( $this, 'filter_apple_news_body_json' )
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals( 'fancy-body', $result['textStyle'] );

		// Teardown.
		remove_filter(
			'apple_news_body_json',
			array( $this, 'filter_apple_news_body_json' )
		);
	}

	/**
	 * Tests HTML formatting.
	 *
	 * @access public
	 */
	public function testHTML() {

		// Setup.
		$this->settings->html_support = 'yes';
		$html = <<<HTML
<p>Lorem ipsum. <a href="https://wordpress.org">Dolor sit amet</a>.</p>
<pre>
	Preformatted text.
</pre>
<p>Testing a <code>code sample</code>.</p>
HTML;
		$component = new Body(
			$html,
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			array(
				'text' => $html,
				'role' => 'body',
				'format' => 'html',
				'textStyle' => 'dropcapBodyStyle',
				'layout' => 'body-layout',
			),
			$component->to_array()
		);
	}

	/**
	 * Tests transformation of lists with nested images.
	 *
	 * @access public
	 */
	public function testLists() {

		// Setup.
		$content = <<<HTML
<ul>
<li>item 1</li>
<li><img src="http://someurl.com/filename.jpg"><br />item 2</li>
<li>item 3</li>
</ul>
HTML;
		$file = dirname( dirname( __DIR__ ) ) . '/data/test-image.jpg';
		$cover = $this->factory->attachment->create_upload_object( $file );
		$content = new Exporter_Content( 3, 'Title', $content, null, $cover );

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = json_decode( $exporter->export(), true );

		// Validate list split in generated JSON.
		$this->assertEquals(
			'body',
			$json['components'][1]['components'][1]['role']
		);
		$this->assertEquals(
			'- item 1',
			$json['components'][1]['components'][1]['text']
		);
		$this->assertEquals(
			'photo',
			$json['components'][1]['components'][2]['role']
		);
		$this->assertEquals(
			'bundle://filename.jpg',
			$json['components'][1]['components'][2]['URL']
		);
		$this->assertEquals(
			'body',
			$json['components'][1]['components'][3]['role']
		);
		$this->assertEquals(
			'- item 2' . "\n" . '- item 3',
			$json['components'][1]['components'][3]['text']
		);
	}

	/**
	 * Tests body settings.
	 *
	 * @access public
	 */
	public function testSettings() {

		// Setup.
		$content = new Exporter_Content(
			3,
			'Title',
			'<p>Lorem ipsum.</p><p>Dolor sit amet.</p>'
		);

		// Set body settings.
		$this->settings->body_font = 'TestFontName';
		$this->settings->body_size = 20;
		$this->settings->body_color = '#abcdef';
		$this->settings->body_link_color = '#fedcba';
		$this->settings->body_line_height = 28;
		$this->settings->body_tracking = 50;
		$this->settings->dropcap_background_color = '#abcabc';
		$this->settings->dropcap_color = '#defdef';
		$this->settings->dropcap_font = 'TestFontName2';
		$this->settings->dropcap_number_of_characters = 15;
		$this->settings->dropcap_number_of_lines = 10;
		$this->settings->dropcap_number_of_raised_lines = 5;
		$this->settings->dropcap_padding = 20;

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = json_decode( $exporter->export(), true );

		// Validate body settings in generated JSON.
		$this->assertEquals(
			'TestFontName',
			$json['componentTextStyles']['default-body']['fontName']
		);
		$this->assertEquals(
			20,
			$json['componentTextStyles']['default-body']['fontSize']
		);
		$this->assertEquals(
			'#abcdef',
			$json['componentTextStyles']['default-body']['textColor']
		);
		$this->assertEquals(
			'#fedcba',
			$json['componentTextStyles']['default-body']['linkStyle']['textColor']
		);
		$this->assertEquals(
			28,
			$json['componentTextStyles']['default-body']['lineHeight']
		);
		$this->assertEquals(
			0.5,
			$json['componentTextStyles']['default-body']['tracking']
		);
		$this->assertEquals(
			'#abcabc',
			$json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['backgroundColor']
		);
		$this->assertEquals(
			'#defdef',
			$json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['textColor']
		);
		$this->assertEquals(
			'TestFontName2',
			$json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['fontName']
		);
		$this->assertEquals(
			15,
			$json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['numberOfCharacters']
		);
		$this->assertEquals(
			10,
			$json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['numberOfLines']
		);
		$this->assertEquals(
			5,
			$json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['numberOfRaisedLines']
		);
		$this->assertEquals(
			20,
			$json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['padding']
		);
	}

	/**
	 * Tests the transformation process from a paragraph to a Body component.
	 *
	 * @access public
	 */
	public function testTransform() {

		// Setup.
		$component = new Body(
			'<p>my text</p>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			array(
				'text' => "my text\n\n",
				'role' => 'body',
				'format' => 'markdown',
				'textStyle' => 'dropcapBodyStyle',
				'layout' => 'body-layout',
			),
			$component->to_array()
		);
	}

	/**
	 * Test the setting to disable the initial dropcap.
	 *
	 * @access public
	 */
	public function testWithoutDropcap() {

		// Setup.
		$this->settings->set( 'initial_dropcap', 'no' );
		$body_component = new Body(
			'<p>my text</p>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			array(
				'text' => "my text\n\n",
				'role' => 'body',
				'format' => 'markdown',
				'textStyle' => 'default-body',
				'layout' => 'body-layout',
			),
			$body_component->to_array()
		);
	}
}
