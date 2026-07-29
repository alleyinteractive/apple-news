<?php
/**
 * Publish to Apple News Tests: Exporter_Content class
 *
 * Contains a class to test the functionality of the Apple_Exporter\Theme class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter;

use Apple_News\Tests\TestCase;

/**
 * A class to test the behavior of the Apple_Exporter\Exporter_Content class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Exporter_Content extends TestCase {

	/**
	 * Tests the behavior of the exporter with just a title and body content.
	 */
	public function test_minimal_content() {
		$content = new \Apple_Exporter\Exporter_Content( 3, 'Title', '<p>Example content</p>' );
		$this->assertEquals( '3', $content->id() );
		$this->assertEquals( 'Title', $content->title() );
		$this->assertEquals( '<p>Example content</p>', $content->content() );
		$this->assertEquals( null, $content->intro() );
		$this->assertEquals( null, $content->cover() );
	}

	/**
	 * Tests the behavior of the exporter with a title, body content, an excerpt, and a URL.
	 */
	public function test_complete_content() {
		$content = new \Apple_Exporter\Exporter_Content( 3, 'Title', '<p>Example content</p>', 'some intro', 'example.org' );
		$this->assertEquals( '3', $content->id() );
		$this->assertEquals( 'Title', $content->title() );
		$this->assertEquals( '<p>Example content</p>', $content->content() );
		$this->assertEquals( 'some intro', $content->intro() );
		$this->assertEquals( 'example.org', $content->cover() );
	}

	/**
	 * Tests the ability to set a cover using an array configuration.
	 */
	public function test_complete_content_with_cover_config() {
		$cover   = [
			'caption' => 'Test Caption',
			'url'     => 'https://www.example.org/wp-content/uploads/2020/07/test-image.jpg',
		];
		$content = new \Apple_Exporter\Exporter_Content(
			3,
			'Title',
			'<p>Example content</p>',
			'some intro',
			$cover
		);
		$this->assertEquals( '3', $content->id() );
		$this->assertEquals( 'Title', $content->title() );
		$this->assertEquals( '<p>Example content</p>', $content->content() );
		$this->assertEquals( 'some intro', $content->intro() );
		$this->assertEquals( $cover, $content->cover() );
	}

	/**
	 * Ensure we decode the HTML entities in URLs extracted from HTML attributes.[type]
	 */
	public function test_format_src_url(): void {
		$this->assertEquals(
			'https://www.example.org/some.mp3?one=two&query=arg',
			\Apple_Exporter\Exporter_Content::format_src_url( 'https://www.example.org/some.mp3?one=two&amp;query=arg' )
		);

		// Change the home URL.
		update_option( 'home', 'https://www.custom-example.org' );

		$this->assertEquals(
			'https://www.example.org/some.mp3?one=two&query=arg',
			\Apple_Exporter\Exporter_Content::format_src_url( 'https://www.example.org/some.mp3?one=two&amp;query=arg' )
		);

		$this->assertEquals(
			'https://www.custom-example.org/',
			\Apple_Exporter\Exporter_Content::format_src_url( '/' )
		);

		$this->assertEquals(
			'https://www.custom-example.org',
			\Apple_Exporter\Exporter_Content::format_src_url( 'https://www.custom-example.org' )
		);

		$this->assertNotEquals(
			'https://www.custom-example.org',
			\Apple_Exporter\Exporter_Content::format_src_url( '/' ),
			'Root URL is missing a trailing slash.'
		);

		$this->assertEquals(
			'https://www.example.org',
			\Apple_Exporter\Exporter_Content::format_src_url( 'https://www.example.org' )
		);

		$this->assertNotEquals(
			'https://www.example.org/', // The / here is intentional.
			\Apple_Exporter\Exporter_Content::format_src_url( 'https://www.example.org' ),
			'Root URL has a trailing slash.'
		);

		// Reset the home URL.
		update_option( 'home', 'https://www.example.org' );
	}
}
