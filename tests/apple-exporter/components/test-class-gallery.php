<?php

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Gallery as Gallery;

class Gallery_Test extends Component_TestCase {

	public function testGeneratedJSON() {
		$this->settings->set( 'use_remote_images', 'no' );

		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		// get_file_contents and write_tmp_files must be caleld with the specified params
		$workspace->bundle_source( 'filename-1.jpg', 'http://someurl.com/filename-1.jpg' )->shouldBeCalled();
		$workspace->bundle_source( 'another-filename-2.jpg', 'http://someurl.com/another-filename-2.jpg' )->shouldBeCalled();

		// Pass the mock workspace as a dependency
		$component = new Gallery( '<div class="gallery"><img
			src="http://someurl.com/filename-1.jpg" alt="Example" /><img
			src="http://someurl.com/another-filename-2.jpg" alt="Example" /></div>',
			$workspace->reveal(), $this->settings, $this->styles, $this->layouts );

		// Test for valid JSON
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

	public function testGeneratedJSONRemoteImages() {
		$this->settings->set( 'use_remote_images', 'yes' );

		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		// get_file_contents and write_tmp_files must be caleld with the specified params
		$workspace->bundle_source( 'filename-1.jpg', 'http://someurl.com/filename-1.jpg' )->shouldNotBeCalled();
		$workspace->bundle_source( 'another-filename-2.jpg', 'http://someurl.com/another-filename-2.jpg' )->shouldNotBeCalled();

		// Pass the mock workspace as a dependency
		$component = new Gallery( '<div class="gallery"><img
			src="http://someurl.com/filename-1.jpg" alt="Example" /><img
			src="http://someurl.com/another-filename-2.jpg" alt="Example" /></div>',
			$workspace->reveal(), $this->settings, $this->styles, $this->layouts );

		// Test for valid JSON
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

	public function testFilter() {
		$this->settings->set( 'use_remote_images', 'no' );

		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		// get_file_contents and write_tmp_files must be caleld with the specified params
		$workspace->bundle_source( 'filename-1.jpg', 'http://someurl.com/filename-1.jpg' )->shouldBeCalled();
		$workspace->bundle_source( 'another-filename-2.jpg', 'http://someurl.com/another-filename-2.jpg' )->shouldBeCalled();

		// Pass the mock workspace as a dependency
		$component = new Gallery( '<div class="gallery"><img
			src="http://someurl.com/filename-1.jpg" alt="Example" /><img
			src="http://someurl.com/another-filename-2.jpg" alt="Example" /></div>',
			$workspace->reveal(), $this->settings, $this->styles, $this->layouts );

		add_filter( 'apple_news_gallery_json', function( $json ) {
			$json['layout'] = 'fancy-layout';
			return $json;
		} );

		// Test for valid JSON
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

}

