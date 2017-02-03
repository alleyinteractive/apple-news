<?php
/**
 * Publish to Apple News Tests: Gallery_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Gallery.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use \Apple_Exporter\Components\Gallery;

/**
 * A class which is used to test the Apple_Exporter\Components\Gallery class.
 */
class Gallery_Test extends Component_TestCase {

	/**
	 * Test the apple_news_gallery_json filter.
	 *
	 * @access public
	 */
	public function testFilter() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'no' );
		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		$workspace->bundle_source(
			'filename-1.jpg',
			'http://someurl.com/filename-1.jpg'
		)->shouldBeCalled();
		$workspace->bundle_source(
			'another-filename-2.jpg',
			'http://someurl.com/another-filename-2.jpg'
		)->shouldBeCalled();
		$component = new Gallery(
			'<div class="gallery"><img
			src="http://someurl.com/filename-1.jpg" alt="Example" /><img
			src="http://someurl.com/another-filename-2.jpg" alt="Example" /></div>',
			$workspace->reveal(),
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Add the filter and set a custom layout.
		add_filter( 'apple_news_gallery_json', function( $json ) {
			$json['layout'] = 'fancy-layout';
			return $json;
		} );

		// Ensure the filter properly modified the layout.
		$this->assertEquals(
			array(
				'role' => 'gallery',
				'items' => array(
					array(
						'URL' => 'bundle://filename-1.jpg',
						'accessibilityCaption' => 'Example',
					),
					array(
						'URL' => 'bundle://another-filename-2.jpg',
						'accessibilityCaption' => 'Example',
					),
				),
				'layout' => 'fancy-layout',
			),
			$component->to_array()
		);
	}

	/**
	 * Ensures that the component generates the proper JSON for a simple gallery.
	 *
	 * @access public
	 */
	public function testGeneratedJSON() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'no' );
		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		$workspace->bundle_source(
			'filename-1.jpg',
			'http://someurl.com/filename-1.jpg'
		)->shouldBeCalled();
		$workspace->bundle_source(
			'another-filename-2.jpg',
			'http://someurl.com/another-filename-2.jpg'
		)->shouldBeCalled();
		$component = new Gallery(
			'<div class="gallery"><img
			src="http://someurl.com/filename-1.jpg" alt="Example" /><img
			src="http://someurl.com/another-filename-2.jpg" alt="Example" /></div>',
			$workspace->reveal(),
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test for valid JSON.
		$this->assertEquals(
			array(
				'role' => 'gallery',
				'items' => array(
					array(
						'URL' => 'bundle://filename-1.jpg',
						'accessibilityCaption' => 'Example',
					),
					array(
						'URL' => 'bundle://another-filename-2.jpg',
						'accessibilityCaption' => 'Example',
					),
				),
				'layout' => 'gallery-layout',
			),
			$component->to_array()
		);
	}

	/**
	 * Tests the functionality of the `use_remote_images` setting.
	 *
	 * @access public
	 */
	public function testGeneratedJSONRemoteImages() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		$workspace->bundle_source(
			'filename-1.jpg',
			'http://someurl.com/filename-1.jpg'
		)->shouldNotBeCalled();
		$workspace->bundle_source(
			'another-filename-2.jpg',
			'http://someurl.com/another-filename-2.jpg'
		)->shouldNotBeCalled();
		$component = new Gallery(
			'<div class="gallery"><img
			src="http://someurl.com/filename-1.jpg" alt="Example" /><img
			src="http://someurl.com/another-filename-2.jpg" alt="Example" /></div>',
			$workspace->reveal(),
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Ensure that the URL parameters are using remote images.
		$this->assertEquals(
			array(
				'role' => 'gallery',
				'items' => array(
					array(
						'URL' => 'http://someurl.com/filename-1.jpg',
						'accessibilityCaption' => 'Example',
					),
					array(
						'URL' => 'http://someurl.com/another-filename-2.jpg',
						'accessibilityCaption' => 'Example',
					),
				),
				'layout' => 'gallery-layout',
			),
			$component->to_array()
		);
	}
}
