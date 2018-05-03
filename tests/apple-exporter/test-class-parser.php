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

	/**
	 * Test the anchor cleaning functions of the parser for Markdown.
	 *
	 * @see \Apple_Exporter\Parser::parse
	 * @access public
	 */
	public function testCleanHTMLMarkdown() {
		update_option( 'siteurl', 'http://wp.dev' );
		update_option( 'home', 'http://wp.dev' );

		// Create a post.
		global $post;
		$post_content = <<<HTML
<a href="https://www.google.com">Absolute link</a>

<a href="/2018/05/03/an-92-test">Root-relative link</a>

<a name="testanchor">Test Anchor</a>

<a href="#testanchor">Anchor Link</a>

<a>Legit empty link</a>

<a href=" ">Link that trims to empty</a>

<a href="thisisntarealurl">Not a real URL</a>
HTML;
		$post = $this->factory->post->create_and_get( array(
			'post_type' => 'article',
			'post_title' => 'Test Article',
			'post_content' => $post_content,
		) );
		$permalink = get_permalink( $post );

		// Convert to Markdown
		$parser = new Parser( 'markdown' );
		$markdown = $parser->parse( apply_filters( 'the_content', $post->post_content ) );

		// Verify.
		$this->assertEquals(
			'[Absolute link](https://www.google.com)'
			. '[Root-relative link](http://wp.dev/2018/05/03/an-92-test)'
			. 'Test Anchor'
			. '[Anchor Link](' . $permalink . '#testanchor)'
			. 'Legit empty link'
			. 'Link that trims to empty'
			. 'Not a real URL',
			str_replace( "\n" , '', $markdown )
		);
	}

	/**
	 * Test the anchor cleaning functions of the parser for HTML.
	 *
	 * @see \Apple_Exporter\Parser::parse
	 * @access public
	 */
	public function testCleanHTML() {
		update_option( 'siteurl', 'http://wp.dev' );
		update_option( 'home', 'http://wp.dev' );

		// Create a post.
		global $post;
		$post_content = <<<HTML
<a href="https://www.google.com">Absolute link</a>

<a href="/2018/05/03/an-92-test">Root-relative link</a>

<a name="testanchor">Test Anchor</a>

<a href="#testanchor">Anchor Link</a>

<a>Legit empty link</a>

<a href=" ">Link that trims to empty</a>

<a href="thisisntarealurl">Not a real URL</a>
HTML;
		$post = $this->factory->post->create_and_get( array(
			'post_type' => 'article',
			'post_title' => 'Test Article',
			'post_content' => $post_content,
		) );
		$permalink = get_permalink( $post );

		// Parse the post with HTML content format.
		$parser = new Parser( 'html' );
		$parsed_html = $parser->parse( apply_filters( 'the_content', $post->post_content ) );

		// Verify.
		$this->assertEquals(
			'<p><a href="https://www.google.com">Absolute link</a></p>'
				. '<p><a href="http://wp.dev/2018/05/03/an-92-test">Root-relative link</a></p>'
				. '<p>Test Anchor</p>'
				. '<p><a href="' . $permalink . '#testanchor">Anchor Link</a></p>'
				. '<p>Legit empty link</p>'
				. '<p>Link that trims to empty</p>'
				. '<p>Not a real URL</p>',
			str_replace( "\n", '', $parsed_html )
		);
	}
}

