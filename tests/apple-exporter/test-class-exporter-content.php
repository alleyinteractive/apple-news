<?php

use Apple_Exporter\Exporter_Content as Exporter_Content;

class Exporter_Content_Test extends WP_UnitTestCase {

	private $prophet;

	public function setup() {
		$this->prophet = new \Prophecy\Prophet;
	}

	public function tearDown() {
		$this->prophet->checkPredictions();
	}

	public function testMinimalContent() {
		$content  = new \Apple_Exporter\Exporter_Content( 3, 'Title', '<p>Example content</p>' );
		$this->assertEquals( '3', $content->id() );
		$this->assertEquals( 'Title', $content->title() );
		$this->assertEquals( '<p>Example content</p>', $content->content() );
		$this->assertEquals( null, $content->intro() );
		$this->assertEquals( null, $content->cover() );
	}

	public function testCompleteContent() {
		$content  = new \Apple_Exporter\Exporter_Content( 3, 'Title', '<p>Example content</p>', 'some intro', 'someurl.com' );
		$this->assertEquals( '3', $content->id() );
		$this->assertEquals( 'Title', $content->title() );
		$this->assertEquals( '<p>Example content</p>', $content->content() );
		$this->assertEquals( 'some intro', $content->intro() );
		$this->assertEquals( 'someurl.com', $content->cover() );
	}

	/**
	 * Ensure we decode the HTML entities in URLs extracted from HTML attributes.[type]
	 */
	public function test_format_src_url() {
		$this->assertEquals(
			'https://example.com/some.mp3?one=two&query=arg',
			\Apple_Exporter\Exporter_Content::format_src_url( 'https://example.com/some.mp3?one=two&amp;query=arg' )
		);
	}

}
