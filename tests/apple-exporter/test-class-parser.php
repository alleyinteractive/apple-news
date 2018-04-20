<?php

use Apple_Exporter\Parser as Parser;

class Parser_Test extends WP_UnitTestCase {

	public function testParseMarkdown() {
		// Create a basic HTML post
		$post = '<html><body><h2>A heading</h2><p><strong>This is strong.</strong><br><a href="http://apple.com">This is a link</a></p></body></html>';

		// Convert to Markdown
		$parser = new Parser( 'markdown' );
		$markdown = $parser->parse( $post );

		// Verify
		$this->assertEquals( $markdown, "## A heading\n**This is strong.**\n[This is a link](http://apple.com)\n\n" );
	}

	public function testParseHTML() {
		// Create a basic HTML post
		$post = '<h2 class="someClass">A heading</h2><p><strong>This is strong.</strong><br><a href="http://apple.com" target="_blank">This is a link</a></p><div>The div tags will disappear.</div>';

		// Parse only HTML that's valid for Apple News
		$parser = new Parser( 'html' );
		$markdown = $parser->parse( $post );

		// Verify
		$this->assertEquals( $markdown, 'A heading<p><strong>This is strong.</strong><br><a href="http://apple.com">This is a link</a></p>The div tags will disappear.' );
	}

	public function testCleanHTMLMarkdown() {
		update_option( 'siteurl', 'http://wp.dev' );
		update_option( 'home', 'http://wp.dev' );

		// Create a post.
		global $post;
		$post = $this->factory->post->create_and_get( array(
			'post_type' => 'article',
			'post_title' => 'Test Article',
			'post_content' => '<html><body><a name="anchor"><h2>A heading</h2></a><p><a href="http://avalidurl.com">A valid URL</a><br/><a href="http://someurl.com ">Invalid spaces</a><br/><a href="#anchor">Anchors work!</a><br/><a href="/relative_url">Some relative url</a></p></body></html>',
		) );

		// Convert to Markdown
		$parser = new Parser( 'markdown' );
		$markdown = $parser->parse( $post->post_content );

		// Verify
		$this->assertEquals( $markdown, "## A heading\n[A valid URL](http://avalidurl.com)\n[Invalid spaces](http://someurl.com)\n[Anchors work\!](" . get_permalink( $post ) . "#anchor)\n[Some relative url](http://wp.dev/relative_url)\n\n" );

		// Anchored link testing.
		$post = $this->factory->post->create_and_get( array(
			'post_type' => 'article',
			'post_title' => 'Test Article',
			'post_content' => '<a href="#gotomyanchor">Anchor Link</a> <a href="http://mydomain.com#gotomyanchor">Anchor Link with domain</a> <a href="/relative-path#gotomyanchor">Anchor Link with relative path</a>',
		) );

		$parse_markdown = new Parser( 'markdown' );
		$parsed_markdown = $parse_markdown->parse( $post->post_content );
		$this->assertEquals( $parsed_markdown, '[Anchor Link](' . get_permalink( $post ) . '#gotomyanchor) [Anchor Link with domain](http://mydomain.com#gotomyanchor) [Anchor Link with relative path](http://wp.dev/relative-path#gotomyanchor)' );
	}

	public function testCleanHTML() {
		update_option( 'siteurl', 'http://wp.dev' );
		update_option( 'home', 'http://wp.dev' );

		// Create a post.
		global $post;
		$post = $this->factory->post->create_and_get( array(
			'post_type' => 'article',
			'post_title' => 'Test Article',
			'post_content' => '<a name="anchor"><h2>A heading</h2></a><p><a href="http://avalidurl.com">A valid URL</a><br/><a href="http://someurl.com ">Invalid spaces</a><br/><a href="#anchor">Anchors work!</a><br/><a href="/relative_url">Some relative url</a></p>',
		) );

		// Parse the post with HTML content format.
		$parser = new Parser( 'html' );
		$parsed_html = $parser->parse( $post->post_content );

		// Verify
		$this->assertEquals( $parsed_html, 'A heading<p><a href="http://avalidurl.com">A valid URL</a><br /><a href="http://someurl.com">Invalid spaces</a><br /><a href="' . get_permalink( $post ) . '#anchor">Anchors work!</a><br /><a href="http://wp.dev/relative_url">Some relative url</a></p>' );

		// Anchored link testing.
		$post = $this->factory->post->create_and_get( array(
			'post_type' => 'article',
			'post_title' => 'Test Article',
			'post_content' => '<a href="#gotomyanchor">Anchor Link</a> <a href="http://mydomain.com#gotomyanchor">Anchor Link with domain</a> <a href="/relative-path#gotomyanchor">Anchor Link with relative path</a>',
		) );

		$parser_html = new Parser( 'html' );
		$parsed_html = $parser_html->parse( $post->post_content );
		$this->assertEquals( $parsed_html, '<a href="' . get_permalink( $post ) . '#gotomyanchor">Anchor Link</a> <a href="http://mydomain.com#gotomyanchor">Anchor Link with domain</a> <a href="http://wp.dev/relative-path#gotomyanchor">Anchor Link with relative path</a>' );
	}
}

