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
	 * Tests body settings.
	 *
	 * @access public
	 */
	public function testSettings() {

		// Setup.
		$content = new Exporter_Content(
			3,
			'Title',
			'<img src="http://someurl.com/filename.jpg" alt="Example" />'
		);

		// Set image settings.
		$this->settings->full_bleed_images = 'yes';

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = json_decode( $exporter->export(), true );

		// Validate image settings in generated JSON.
		$this->assertEquals(
			true,
			$json['componentLayouts']['full-width-image']['ignoreDocumentMargin']
		);
	}
}
