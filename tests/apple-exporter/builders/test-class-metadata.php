<?php
/**
 * Publish to Apple News Tests: Metadata_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Builders\Metadata.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Exporter_Content;
use Apple_Exporter\Settings;
use Apple_Exporter\Builders\Metadata;

/**
 * A class which is used to test the Apple_Exporter\Builders\Metadata class.
 */
class Metadata_Test extends WP_UnitTestCase {

	/**
	 * Actions to be run before each test in this class is executed.
	 *
	 * @access public
	 */
	public function setup() {
		$this->settings = new Settings();
	}

	/**
	 * Ensures that the cover image is properly set in metadata.
	 *
	 * @access public
	 */
	public function testCover() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'no' );
		$content = new Exporter_Content(
			1,
			'My Title',
			'<p>Hello, World!</p>',
			null,
			'/etc/somefile.jpg'
		);
		$builder = new Metadata( $content, $this->settings );
		$result = $builder->to_array();

		// Test.
		$this->assertEquals(
			5,
			count( $result )
		);
		$this->assertEquals(
			'bundle://somefile.jpg',
			$result['thumbnailURL']
		);
	}

	/**
	 * Test adding cover art to a post.
	 *
	 * @access public
	 */
	public function testCoverArt() {

		/**
		 * Due to how the cover art setting is processed, we need to
		 * manually register the image sizes here.
		 */
		$image_sizes = Admin_Apple_News::get_image_sizes();
		foreach ( $image_sizes as $name => $data ) {
			add_image_size( $name, $data['width'], $data['height'], true );
		}

		// Create dummy content.
		$title = 'My Title';
		$content = '<p>Hello, World!</p>';
		$post_id = $this->factory->post->create( array(
			'post_title' => $title,
			'post_content' => $content,
			'post_date' => '2016-04-01 00:00:00',
		) );

		// Create dummy attachment.
		$file = dirname( dirname( __DIR__ ) ) . '/data/test-image.jpg';
		$image = $this->factory->attachment->create_upload_object( $file, $post_id );
		update_post_meta( $image, '_wp_attachment_image_alt', 'Cover art' );

		// Add cover art meta.
		update_post_meta(
			$post_id,
			'apple_news_coverart',
			array(
				'orientation' => 'landscape',
				'apple_news_ca_landscape_12_9' => $image,
				'apple_news_ca_landscape_9_7' => $image,
				'apple_news_ca_landscape_5_5' => $image,
				'apple_news_ca_landscape_4_7' => $image,
				'apple_news_ca_landscape_4_0' => $image,
			)
		);

		// Run the exporter to get the JSON from the metadata.
		$content = new Exporter_Content( $post_id, $title, $content );
		$builder = new Metadata( $content, $this->settings );
		$result = $builder->to_array();

		// Ensure cover art properties were set properly.
		$this->assertEquals(
			'Cover art',
			$result['coverArt'][0]['accessibilityCaption']
		);
		$this->assertEquals(
			'image',
			$result['coverArt'][0]['type']
		);
		$this->assertNotFalse(
			strpos( $result['coverArt'][0]['URL'], '1832x1374.jpg' )
		);
		$this->assertEquals(
			'Cover art',
			$result['coverArt'][1]['accessibilityCaption']
		);
		$this->assertEquals(
			'image',
			$result['coverArt'][1]['type']
		);
		$this->assertNotFalse(
			strpos( $result['coverArt'][1]['URL'], '1376x1032.jpg' )
		);
		$this->assertEquals(
			'Cover art',
			$result['coverArt'][2]['accessibilityCaption']
		);
		$this->assertEquals(
			'image',
			$result['coverArt'][2]['type']
		);
		$this->assertNotFalse(
			strpos( $result['coverArt'][2]['URL'], '1044x783.jpg' )
		);
		$this->assertEquals(
			'Cover art',
			$result['coverArt'][3]['accessibilityCaption']
		);
		$this->assertEquals(
			'image',
			$result['coverArt'][3]['type']
		);
		$this->assertNotFalse(
			strpos( $result['coverArt'][3]['URL'], '632x474.jpg' )
		);
		$this->assertEquals(
			'Cover art',
			$result['coverArt'][4]['accessibilityCaption']
		);
		$this->assertEquals(
			'image',
			$result['coverArt'][4]['type']
		);
		$this->assertNotFalse(
			strpos( $result['coverArt'][4]['URL'], '536x402.jpg' )
		);
	}

	/**
	 * Ensures that a remote cover image is properly set in metadata.
	 *
	 * @access public
	 */
	public function testCoverRemoteImages() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$content = new Exporter_Content(
			1,
			'My Title',
			'<p>Hello, World!</p>',
			null,
			'http://someurl.com/somefile.jpg'
		);
		$builder = new Metadata( $content, $this->settings );
		$result = $builder->to_array();

		// Test.
		$this->assertEquals(
			5,
			count( $result )
		);
		$this->assertEquals(
			'http://someurl.com/somefile.jpg',
			$result['thumbnailURL']
		);
	}

	/**
	 * Ensure dates are properly set in metadata.
	 *
	 * @access public
	 */
	public function testDates() {

		// Setup.
		$title = 'My Title';
		$content = '<p>Hello, World!</p>';
		$post_id = $this->factory->post->create( array(
			'post_title' => $title,
			'post_content' => $content,
			'post_date' => '2016-04-01 00:00:00',
		) );
		$content = new Exporter_Content(
			$post_id,
			$title,
			$content,
			null,
			'/etc/somefile.jpg'
		);
		$builder = new Metadata( $content, $this->settings );
		$result = $builder->to_array();

		// Test.
		$this->assertEquals(
			8,
			count( $result )
		);
		$this->assertEquals(
			'2016-04-01T00:00:00+00:00',
			$result['dateCreated']
		);
		$this->assertEquals(
			'2016-04-01T00:00:00+00:00',
			$result['dateModified']
		);
		$this->assertEquals(
			'2016-04-01T00:00:00+00:00',
			$result['datePublished']
		);
	}

	/**
	 * Ensures that the intro text is properly set in metadata.
	 *
	 * @access public
	 */
	public function testIntro() {

		// Setup.
		$content = new Exporter_Content(
			1,
			'My Title',
			'<p>Hello, World!</p>',
			'This is an intro.'
		);
		$builder = new Metadata( $content, $this->settings );
		$result = $builder->to_array();

		// Test.
		$this->assertEquals(
			5,
			count( $result )
		);
		$this->assertEquals(
			'This is an intro.',
			$result['excerpt']
		);
	}

	/**
	 * Ensures that the cover image and intro text are properly set in metadata.
	 *
	 * @access public
	 */
	public function testIntroAndCover() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'no' );
		$content = new Exporter_Content(
			1,
			'My Title',
			'<p>Hello, World!</p>',
			'This is an intro.',
			'/etc/somefile.jpg'
		);
		$builder = new Metadata( $content, $this->settings );
		$result = $builder->to_array();

		// Test.
		$this->assertEquals(
			6,
			count( $result )
		);
		$this->assertEquals(
			'This is an intro.',
			$result['excerpt']
		);
		$this->assertEquals(
			'bundle://somefile.jpg',
			$result['thumbnailURL']
		);
	}

	/**
	 * Ensures that a remote cover image and intro text are properly set in metadata.
	 *
	 * @access public
	 */
	public function testIntroAndCoverRemoteImages() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$content = new Exporter_Content(
			1,
			'My Title',
			'<p>Hello, World!</p>',
			'This is an intro.',
			'http://someurl.com/somefile.jpg'
		);
		$builder = new Metadata( $content, $this->settings );
		$result = $builder->to_array();

		// Test.
		$this->assertEquals(
			6,
			count( $result )
		);
		$this->assertEquals(
			'This is an intro.',
			$result['excerpt']
		);
		$this->assertEquals(
			'http://someurl.com/somefile.jpg',
			$result['thumbnailURL']
		);
	}

	/**
	 * Ensures metadata is properly generated when no intro and no cover are given.
	 *
	 * @access public
	 */
	public function testNoIntroNoCover() {

		// Setup.
		$content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>' );
		$builder = new Metadata( $content, $this->settings );
		$result = $builder->to_array();

		// Test.
		$this->assertEquals(
			4,
			count( $result )
		);
	}

	/**
	 * Ensures video metadata is properly added.
	 *
	 * @access public
	 */
	public function testVideo() {

		// Setup.
		$this->settings->set( 'use_remote_images', 'yes' );
		$html = <<<HTML
<video class="wp-video-shortcode" id="video-71-1" width="525" height="295" poster="https://example.com/wp-content/uploads/2017/02/ExamplePoster.jpg" preload="metadata" controls="controls">
	<source type="video/mp4" src="https://example.com/wp-content/uploads/2017/02/example-video.mp4?_=1" />
	<a href="https://example.com/wp-content/uploads/2017/02/example-video.mp4">https://example.com/wp-content/uploads/2017/02/example-video.mp4</a>
</video>
HTML;
		$content = new Exporter_Content( 1, 'My Title', $html );
		$builder = new Metadata( $content, $this->settings );
		$result = $builder->to_array();

		// Test.
		$this->assertEquals(
			'https://example.com/wp-content/uploads/2017/02/ExamplePoster.jpg',
			$result['thumbnailURL']
		);
		$this->assertEquals(
			'https://example.com/wp-content/uploads/2017/02/example-video.mp4?_=1',
			$result['videoURL']
		);
	}
}
