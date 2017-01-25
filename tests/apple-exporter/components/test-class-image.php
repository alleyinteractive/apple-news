<?php
/**
 * Publish to Apple News Tests: Image_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Image.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Image;
use Apple_Exporter\Exporter;
use Apple_Exporter\Exporter_Content;
use Apple_Exporter\Workspace;

/**
 * A class which is used to test the Apple_Exporter\Components\Image class.
 */
class Image_Test extends Component_TestCase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @access public
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_image_json( $json ) {
		$json['layout'] = 'default-image';

		return $json;
	}

	/**
	 * Test the `apple_news_image_json` filter.
	 *
	 * @access public
	 */
	public function testFilter() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'no' );
		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		$workspace->bundle_source(
			'filename.jpg',
			'http://someurl.com/filename.jpg'
		)->shouldBeCalled();
		$component = new Image(
			'<img src="http://someurl.com/filename.jpg" alt="Example" />',
			$workspace->reveal(),
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_image_json',
			array( $this, 'filter_apple_news_image_json' )
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals( 'default-image', $result['layout'] );

		// Teardown.
		remove_filter(
			'apple_news_image_json',
			array( $this, 'filter_apple_news_image_json' )
		);
	}

	/**
	 * Test standard JSON export.
	 *
	 * @access public
	 */
	public function testGeneratedJSON() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'no' );
		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		$workspace->bundle_source(
			'filename.jpg',
			'http://someurl.com/filename.jpg'
		)->shouldBeCalled();
		$component = new Image(
			'<img src="http://someurl.com/filename.jpg" alt="Example" align="left" />',
			$workspace->reveal(),
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result = $component->to_array();

		// Test.
		$this->assertEquals( 'photo', $result['role'] );
		$this->assertEquals( 'bundle://filename.jpg', $result['URL'] );
		$this->assertEquals( 'anchored-image', $result['layout'] );
	}

	/**
	 * Test remote image JSON export.
	 *
	 * @access public
	 */
	public function testGeneratedJSONRemoteImages() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		$workspace->bundle_source(
			'filename.jpg',
			'http://someurl.com/filename.jpg'
		)->shouldNotBeCalled();
		$component = new Image(
			'<img src="http://someurl.com/filename.jpg" alt="Example" align="left" />',
			$workspace->reveal(),
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result = $component->to_array();

		// Test.
		$this->assertEquals( 'photo', $result['role'] );
		$this->assertEquals( 'http://someurl.com/filename.jpg', $result['URL'] );
		$this->assertEquals( 'anchored-image', $result['layout'] );
	}

	/**
	 * Tests image and image caption settings.
	 *
	 * @access public
	 */
	public function testSettings() {

		// Setup.
		$this->settings->full_bleed_images = 'yes';
		$this->settings->caption_color = '#abcdef';
		$this->settings->caption_font = 'TestFontName';
		$this->settings->caption_line_height = 28;
		$this->settings->caption_size = 20;
		$this->settings->caption_tracking = 50;
		$html = <<<HTML
<figure>
	<img src="http://someurl.com/filename.jpg" alt="Example">
	<figcaption class="wp-caption-text">Caption Text</figcaption>
</figure>
HTML;
		$component = new Image(
			$html,
			new Workspace( 1 ),
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result = $component->to_array();

		// Test.
		$this->assertEquals( true, $result['layout']['ignoreDocumentMargin'] );
		$this->assertEquals(
			'#abcdef',
			$result['components'][1]['textStyle']['textColor']
		);
		$this->assertEquals(
			'TestFontName',
			$result['components'][1]['textStyle']['fontName']
		);
		$this->assertEquals(
			28,
			$result['components'][1]['textStyle']['lineHeight']
		);
		$this->assertEquals(
			20,
			$result['components'][1]['textStyle']['fontSize']
		);
		$this->assertEquals(
			0.5,
			$result['components'][1]['textStyle']['tracking']
		);
	}
}
