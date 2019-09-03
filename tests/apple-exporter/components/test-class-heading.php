<?php
/**
 * Publish to Apple News Tests: Heading_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Heading.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Heading;
use Apple_Exporter\Exporter;
use Apple_Exporter\Exporter_Content;

/**
 * A class which is used to test the Apple_Exporter\Components\Heading class.
 */
class Heading_Test extends Component_TestCase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @access public
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_heading_json( $json ) {
		$json['format'] = 'none';

		return $json;
	}

	/**
	 * Test the `apple_news_heading_json` filter.
	 *
	 * @access public
	 */
	public function testFilter() {

		// Setup.
		$component = new Heading(
			'<h1>This is a heading</h1>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_heading_json',
			array( $this, 'filter_apple_news_heading_json' )
		);

		// Test.
		$json = $component->to_array();
		$this->assertEquals( 'none', $json['format'] );

		// Teardown.
		remove_filter(
			'apple_news_heading_json',
			array( $this, 'filter_apple_news_heading_json' )
		);
	}

	/**
	 * Tests image splitting where the image is wrapped in a link.
	 *
	 * @access public
	 */
	public function testImageSplittingWithLink() {

		// Setup.
		$content = <<<HTML
<h2><a href="https://www.google.com/"><img src="/example-image.jpg" /></a></h2>
HTML;
		$file = dirname( dirname( __DIR__ ) ) . '/data/test-image.jpg';
		$cover = $this->factory->attachment->create_upload_object( $file );
		$content = new Exporter_Content( 3, 'Title', $content, null, $cover );

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = $exporter->export();
		$this->ensure_tokens_replaced( $json );
		$json = json_decode( $json, true );

		// Validate image split in generated JSON.
		$this->assertEquals(
			array(
				'role'   => 'photo',
				'URL'    => 'http://example.org/example-image.jpg',
				'layout' => 'full-width-image',
			),
			$json['components'][1]['components'][1]
		);
	}

	/**
	 * Ensures that headings are not produced from paragraphs.
	 *
	 * @access public
	 */
	public function testInvalidInput() {

		// Setup.
		$component = new Heading(
			'<p>This is not a heading</p>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			null,
			$component->to_array()
		);
	}

	/**
	 * Tests heading 1 settings.
	 *
	 * @access public
	 */
	public function testSettings1() {

		// Setup.
		$content = new Exporter_Content( 3, 'Title', '<h1>Heading</h1>' );

		// Set header settings.
		$theme = \Apple_Exporter\Theme::get_used();
		$settings = $theme->all_settings();
		$settings['header1_font'] = 'AmericanTypewriter';
		$settings['header1_size'] = 60;
		$settings['header1_color'] = '#012345';
		$settings['header1_line_height'] = 66;
		$settings['header1_tracking'] = 6;
		$theme->load( $settings );
		$this->assertTrue( $theme->save() );

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = $exporter->export();
		$this->ensure_tokens_replaced( $json );
		$json = json_decode( $json, true );

		// Validate header settings in generated JSON.
		$this->assertEquals(
			'AmericanTypewriter',
			$json['componentTextStyles']['default-heading-1']['fontName']
		);
		$this->assertEquals(
			60,
			$json['componentTextStyles']['default-heading-1']['fontSize']
		);
		$this->assertEquals(
			'#012345',
			$json['componentTextStyles']['default-heading-1']['textColor']
		);
		$this->assertEquals(
			66,
			$json['componentTextStyles']['default-heading-1']['lineHeight']
		);
		$this->assertEquals(
			0.06,
			$json['componentTextStyles']['default-heading-1']['tracking']
		);
	}

	/**
	 * Tests heading 2 settings.
	 *
	 * @access public
	 */
	public function testSettings2() {

		// Setup.
		$content = new Exporter_Content( 3, 'Title', '<h2>Heading</h2>' );

		// Set header settings.
		$theme = \Apple_Exporter\Theme::get_used();
		$settings = $theme->all_settings();
		$settings['header2_font'] = 'AmericanTypewriter';
		$settings['header2_size'] = 50;
		$settings['header2_color'] = '#123456';
		$settings['header2_line_height'] = 55;
		$settings['header2_tracking'] = 5;
		$theme->load( $settings );
		$this->assertTrue( $theme->save() );

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = $exporter->export();
		$this->ensure_tokens_replaced( $json );
		$json = json_decode( $json, true );

		// Validate header settings in generated JSON.
		$this->assertEquals(
			'AmericanTypewriter',
			$json['componentTextStyles']['default-heading-2']['fontName']
		);
		$this->assertEquals(
			50,
			$json['componentTextStyles']['default-heading-2']['fontSize']
		);
		$this->assertEquals(
			'#123456',
			$json['componentTextStyles']['default-heading-2']['textColor']
		);
		$this->assertEquals(
			55,
			$json['componentTextStyles']['default-heading-2']['lineHeight']
		);
		$this->assertEquals(
			0.05,
			$json['componentTextStyles']['default-heading-2']['tracking']
		);
	}

	/**
	 * Tests heading 3 settings.
	 *
	 * @access public
	 */
	public function testSettings3() {

		// Setup.
		$content = new Exporter_Content( 3, 'Title', '<h3>Heading</h3>' );

		// Set header settings.
		$theme = \Apple_Exporter\Theme::get_used();
		$settings = $theme->all_settings();
		$settings['header3_font'] = 'AmericanTypewriter';
		$settings['header3_size'] = 40;
		$settings['header3_color'] = '#234567';
		$settings['header3_line_height'] = 44;
		$settings['header3_tracking'] = 4;
		$theme->load( $settings );
		$this->assertTrue( $theme->save() );

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = $exporter->export();
		$this->ensure_tokens_replaced( $json );
		$json = json_decode( $json, true );

		// Validate header settings in generated JSON.
		$this->assertEquals(
			'AmericanTypewriter',
			$json['componentTextStyles']['default-heading-3']['fontName']
		);
		$this->assertEquals(
			40,
			$json['componentTextStyles']['default-heading-3']['fontSize']
		);
		$this->assertEquals(
			'#234567',
			$json['componentTextStyles']['default-heading-3']['textColor']
		);
		$this->assertEquals(
			44,
			$json['componentTextStyles']['default-heading-3']['lineHeight']
		);
		$this->assertEquals(
			0.04,
			$json['componentTextStyles']['default-heading-3']['tracking']
		);
	}

	/**
	 * Tests heading 4 settings.
	 *
	 * @access public
	 */
	public function testSettings4() {

		// Setup.
		$content = new Exporter_Content( 3, 'Title', '<h4>Heading</h4>' );

		// Set header settings.
		$theme = \Apple_Exporter\Theme::get_used();
		$settings = $theme->all_settings();
		$settings['header4_font'] = 'AmericanTypewriter';
		$settings['header4_size'] = 30;
		$settings['header4_color'] = '#345678';
		$settings['header4_line_height'] = 33;
		$settings['header4_tracking'] = 3;
		$theme->load( $settings );
		$this->assertTrue( $theme->save() );

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = $exporter->export();
		$this->ensure_tokens_replaced( $json );
		$json = json_decode( $json, true );

		// Validate header settings in generated JSON.
		$this->assertEquals(
			'AmericanTypewriter',
			$json['componentTextStyles']['default-heading-4']['fontName']
		);
		$this->assertEquals(
			30,
			$json['componentTextStyles']['default-heading-4']['fontSize']
		);
		$this->assertEquals(
			'#345678',
			$json['componentTextStyles']['default-heading-4']['textColor']
		);
		$this->assertEquals(
			33,
			$json['componentTextStyles']['default-heading-4']['lineHeight']
		);
		$this->assertEquals(
			0.03,
			$json['componentTextStyles']['default-heading-4']['tracking']
		);
	}

	/**
	 * Tests heading 5 settings.
	 *
	 * @access public
	 */
	public function testSettings5() {

		// Setup.
		$content = new Exporter_Content( 3, 'Title', '<h5>Heading</h5>' );

		// Set header settings.
		$theme = \Apple_Exporter\Theme::get_used();
		$settings = $theme->all_settings();
		$settings['header5_font'] = 'AmericanTypewriter';
		$settings['header5_size'] = 20;
		$settings['header5_color'] = '#456789';
		$settings['header5_line_height'] = 22;
		$settings['header5_tracking'] = 2;
		$theme->load( $settings );
		$this->assertTrue( $theme->save() );

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = $exporter->export();
		$this->ensure_tokens_replaced( $json );
		$json = json_decode( $json, true );

		// Validate header settings in generated JSON.
		$this->assertEquals(
			'AmericanTypewriter',
			$json['componentTextStyles']['default-heading-5']['fontName']
		);
		$this->assertEquals(
			20,
			$json['componentTextStyles']['default-heading-5']['fontSize']
		);
		$this->assertEquals(
			'#456789',
			$json['componentTextStyles']['default-heading-5']['textColor']
		);
		$this->assertEquals(
			22,
			$json['componentTextStyles']['default-heading-5']['lineHeight']
		);
		$this->assertEquals(
			0.02,
			$json['componentTextStyles']['default-heading-5']['tracking']
		);
	}

	/**
	 * Tests heading 6 settings.
	 *
	 * @access public
	 */
	public function testSettings6() {

		// Setup.
		$content = new Exporter_Content( 3, 'Title', '<h6>Heading</h6>' );

		// Set header settings.
		$theme = \Apple_Exporter\Theme::get_used();
		$settings = $theme->all_settings();
		$settings['header6_font'] = 'AmericanTypewriter';
		$settings['header6_size'] = 10;
		$settings['header6_color'] = '#567890';
		$settings['header6_line_height'] = 11;
		$settings['header6_tracking'] = 1;
		$theme->load( $settings );
		$this->assertTrue( $theme->save() );

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = $exporter->export();
		$this->ensure_tokens_replaced( $json );
		$json = json_decode( $json, true );

		// Validate header settings in generated JSON.
		$this->assertEquals(
			'AmericanTypewriter',
			$json['componentTextStyles']['default-heading-6']['fontName']
		);
		$this->assertEquals(
			10,
			$json['componentTextStyles']['default-heading-6']['fontSize']
		);
		$this->assertEquals(
			'#567890',
			$json['componentTextStyles']['default-heading-6']['textColor']
		);
		$this->assertEquals(
			11,
			$json['componentTextStyles']['default-heading-6']['lineHeight']
		);
		$this->assertEquals(
			0.01,
			$json['componentTextStyles']['default-heading-6']['tracking']
		);
	}

	/**
	 * Tests the function to migrate legacy header settings.
	 *
	 * @see Apple_News::migrate_header_settings()
	 *
	 * @access public
	 */
	public function testSettingsMigration() {

		// Test with default settings.
		$this->assertEmpty( $this->settings->header_color );
		$this->assertEmpty( $this->settings->header_font );
		$this->assertEmpty( $this->settings->header_line_height );

		// Set legacy settings to test migration.
		$wp_settings = array(
			'header_color' => '#abcdef',
			'header_font' => 'AmericanTypewriter',
			'header_line_height' => 128,
		);
		update_option( Apple_News::$option_name, $wp_settings );

		// Delete all themes to force recreation.
		$themes = \Apple_Exporter\Theme::get_registry();
		foreach ( $themes as $theme_name ) {
			$theme = new \Apple_Exporter\Theme;
			$theme->set_name( $theme_name );
			$theme->delete();
		}

		// Delete the active theme by force.
		$active_theme = \Apple_Exporter\Theme::get_active_theme_name();
		$theme_key = \Apple_Exporter\Theme::theme_key( $active_theme );
		delete_option( $theme_key );
		delete_option( \Apple_Exporter\Theme::ACTIVE_KEY );

		// Run legacy settings through migrate script.
		$apple_news = new Apple_News;
		$apple_news->upgrade_to_1_3_0();

		// Ensure legacy settings have been stripped.
		$settings = get_option( Apple_News::$option_name );
		$this->assertTrue( empty( $settings['header_color'] ) );
		$this->assertTrue( empty( $settings['header_font'] ) );
		$this->assertTrue( empty( $settings['header_line_height'] ) );

		// Ensure legacy settings were applied to new values.
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( \Apple_Exporter\Theme::get_active_theme_name() );
		$this->assertTrue( $theme->load() );
		$settings = $theme->all_settings();
		$this->assertEquals( '#abcdef', $settings['header1_color'] );
		$this->assertEquals( '#abcdef', $settings['header2_color'] );
		$this->assertEquals( '#abcdef', $settings['header3_color'] );
		$this->assertEquals( '#abcdef', $settings['header4_color'] );
		$this->assertEquals( '#abcdef', $settings['header5_color'] );
		$this->assertEquals( '#abcdef', $settings['header6_color'] );
		$this->assertEquals( 'AmericanTypewriter', $settings['header1_font'] );
		$this->assertEquals( 'AmericanTypewriter', $settings['header2_font'] );
		$this->assertEquals( 'AmericanTypewriter', $settings['header3_font'] );
		$this->assertEquals( 'AmericanTypewriter', $settings['header4_font'] );
		$this->assertEquals( 'AmericanTypewriter', $settings['header5_font'] );
		$this->assertEquals( 'AmericanTypewriter', $settings['header6_font'] );
		$this->assertEquals( 128, $settings['header1_line_height'] );
		$this->assertEquals( 128, $settings['header2_line_height'] );
		$this->assertEquals( 128, $settings['header3_line_height'] );
		$this->assertEquals( 128, $settings['header4_line_height'] );
		$this->assertEquals( 128, $settings['header5_line_height'] );
		$this->assertEquals( 128, $settings['header6_line_height'] );
	}

	/**
	 * Ensures that headings are produced from heading tags.
	 *
	 * @access public
	 */
	public function testValidInput() {

		// Setup.
		$component = new Heading(
			'<h1>This is a heading</h1>',
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$json = $component->to_array();

		// Test.
		$this->assertEquals( 'heading1', $json['role'] );
		$this->assertEquals( 'This is a heading', $json['text'] );
		$this->assertEquals( 'html', $json['format'] );
	}
}
