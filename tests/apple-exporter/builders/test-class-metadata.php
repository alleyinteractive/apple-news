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

	public function testCover() {
		$this->settings->set( 'use_remote_images', 'no' );

		$content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>', null, '/etc/somefile.jpg' );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();

		$this->assertEquals( 5, count( $result ) );
		$this->assertEquals( 'bundle://somefile.jpg', $result[ 'thumbnailURL' ] );
	}

	public function testCoverRemoteImages() {
		$this->settings->set( 'use_remote_images', 'yes' );

		$content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>', null, 'http://someurl.com/somefile.jpg' );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();

		$this->assertEquals( 5, count( $result ) );
		$this->assertEquals( 'http://someurl.com/somefile.jpg', $result[ 'thumbnailURL' ] );
	}

	public function testIntroAndCover() {
		$this->settings->set( 'use_remote_images', 'no' );

		$content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>', 'This is an intro.', '/etc/somefile.jpg' );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();

		$this->assertEquals( 6, count( $result ) );
		$this->assertEquals( 'This is an intro.', $result[ 'excerpt' ] );
		$this->assertEquals( 'bundle://somefile.jpg', $result[ 'thumbnailURL' ] );
	}

	public function testIntroAndCoverRemoteImages() {
		$this->settings->set( 'use_remote_images', 'yes' );

		$content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>', 'This is an intro.', 'http://someurl.com/somefile.jpg' );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();

		$this->assertEquals( 6, count( $result ) );
		$this->assertEquals( 'This is an intro.', $result[ 'excerpt' ] );
		$this->assertEquals( 'http://someurl.com/somefile.jpg', $result[ 'thumbnailURL' ] );
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

	/**
	 * Test adding cover art to a post.
	 *
	 * @access public
	 */
	public function testCoverArt() {

		// Create dummy content.
		$title = 'My Title';
		$content = '<p>Hello, World!</p>';
		$post_id = $this->factory->post->create( array(
			'post_title' => $title,
			'post_content' => $content,
			'post_date' => '2016-04-01 00:00:00',
		) );

		// Create dummy attachments.
		$file = dirname( dirname( __DIR__ ) ) . '/data/test-image.jpg';
		$landscape = $this->factory->attachment->create_upload_object( $file, $post_id );
		update_post_meta( $landscape, '_wp_attachment_image_alt', 'Landscape alt' );
		$portrait = $this->factory->attachment->create_upload_object( $file, $post_id );
		update_post_meta( $portrait, '_wp_attachment_image_alt', 'Portrait alt' );
		$square = $this->factory->attachment->create_upload_object( $file, $post_id );
		update_post_meta( $square, '_wp_attachment_image_alt', 'Square alt' );

		// Add meta for the three cover art sizes to the post.
		update_post_meta( $post_id, 'apple_news_coverart_landscape', $landscape );
		update_post_meta( $post_id, 'apple_news_coverart_portrait', $portrait );
		update_post_meta( $post_id, 'apple_news_coverart_square', $square );

		// Run the exporter to get the JSON from the metadata.
		$content = new Exporter_Content( $post_id, $title, $content );
		$builder = new Metadata( $content, $this->settings );
		$result  = $builder->to_array();

		// Ensure primary cover art properties were set properly for each orientation.
		$this->assertEquals( 'Landscape alt', $result['coverArt'][0]['accessibilityCaption'] );
		$this->assertEquals( 'image', $result['coverArt'][0]['type'] );
		$this->assertEquals( 'Portrait alt', $result['coverArt'][5]['accessibilityCaption'] );
		$this->assertEquals( 'image', $result['coverArt'][5]['type'] );
		$this->assertEquals( 'Square alt', $result['coverArt'][10]['accessibilityCaption'] );
		$this->assertEquals( 'image', $result['coverArt'][10]['type'] );

		// Ensure dimensions were set properly for each orientation.
		$this->assertNotFalse( strpos( $result['coverArt'][0]['URL'], '1832x1374.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][1]['URL'], '1376x1032.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][2]['URL'], '1044x783.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][3]['URL'], '632x474.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][4]['URL'], '536x402.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][5]['URL'], '1122x1496.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][6]['URL'], '840x1120.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][7]['URL'], '687x916.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][8]['URL'], '414x552.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][9]['URL'], '354x472.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][10]['URL'], '1472x1472.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][11]['URL'], '1104x1104.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][12]['URL'], '912x912.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][13]['URL'], '550x550.jpg' ) );
		$this->assertNotFalse( strpos( $result['coverArt'][14]['URL'], '470x470.jpg' ) );
	}
}
