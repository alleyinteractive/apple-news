<?php

use Apple_Exporter\Exporter_Content as Exporter_Content;
use Apple_Exporter\Settings as Settings;
use Apple_Exporter\Builders\Metadata as Metadata;

class Metadata_Test extends WP_UnitTestCase {

	public function setup() {
		$this->settings = new Settings();
	}

	public function testNoIntroNoCover() {
		$content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>' );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();
		$this->assertEquals( 4, count( $result ) );
	}

	public function testIntro() {
		$content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>', 'This is an intro.' );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();

		$this->assertEquals( 5, count( $result ) );
		$this->assertEquals( 'This is an intro.', $result[ 'excerpt' ] );
	}

	public function testEmptyIntro() {
		$content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>', '' );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();

		$this->assertEquals( 5, count( $result ) );
		$this->assertEquals( 'Hello, World!', $result[ 'excerpt' ] );
	}

	public function testCover() {
		$content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>', null, '/etc/somefile.jpg' );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();

		$this->assertEquals( 5, count( $result ) );
		$this->assertEquals( 'bundle://somefile.jpg', $result[ 'thumbnailURL' ] );
	}

	public function testIntroAndCover() {
		$content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>', 'This is an intro.', '/etc/somefile.jpg' );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();

		$this->assertEquals( 6, count( $result ) );
		$this->assertEquals( 'This is an intro.', $result[ 'excerpt' ] );
		$this->assertEquals( 'bundle://somefile.jpg', $result[ 'thumbnailURL' ] );
	}

	public function testDates() {
		$title = 'My Title';
		$content = '<p>Hello, World!</p>';

		$post_id = $this->factory->post->create( array(
			'post_title' => $title,
			'post_content' => $content,
			'post_date' => '2016-04-01 00:00:00',
		) );

		$content = new Exporter_Content( $post_id, $title, $content, null, '/etc/somefile.jpg' );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();

		$this->assertEquals( 8, count( $result ) );
		$this->assertEquals( '2016-04-01T00:00:00+00:00', $result[ 'dateCreated' ] );
		$this->assertEquals( '2016-04-01T00:00:00+00:00', $result[ 'dateModified' ] );
		$this->assertEquals( '2016-04-01T00:00:00+00:00', $result[ 'datePublished' ] );
	}

}
