<?php
/**
 * Publish to Apple News Tests: Apple_News_Parser_Test class
 *
 * Contains a class to test the functionality of the Apple_Exporter\Parser class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Parser;

/**
 * A class to test the behavior of the Apple_Exporter\Parser class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Parser_Test extends Apple_News_Testcase {

	/**
	 * Tests the parser's ability to parse markdown.
	 */
	public function test_parse_markdown(): void {
		// Create a basic HTML post.
		$html = '<html><body><h2>A heading</h2><p><strong>This is strong.</strong><br><a href="https://www.apple.com">This is a link</a></p></body></html>';

		// Convert to Markdown.
		$parser   = new Parser( 'markdown' );
		$markdown = $parser->parse( $html );

		// Verify.
		$this->assertEquals( $markdown, "## A heading\n**This is strong.**\n[This is a link](https://www.apple.com)\n\n" );
	}

	/**
	 * Tests the parser's ability to parse HTML.
	 */
	public function test_parse_html(): void {
		// Create a basic HTML post.
		$html_post = '<h2 id="heading-target" class="someClass">A heading</h2><p><strong>This is strong.</strong><br><a href="https://www.apple.com" target="_blank">This is a link</a></p><div>The div tags will disappear.</div>';

		// Parse only HTML that's valid for Apple News.
		$html = ( new Parser( 'html' ) )->parse( $html_post );

		// Verify.
		$this->assertEquals( $html, 'A heading<p><strong>This is strong.</strong><br><a href="https://www.apple.com">This is a link</a></p>The div tags will disappear.' );
	}

	/**
	 * Test the anchor cleaning functions of the parser for Markdown.
	 *
	 * @see \Apple_Exporter\Parser::parse
	 */
	public function test_clean_html_markdown(): void {
		$post_content = <<<HTML
<a href="https://www.google.com">Absolute link</a>

<a href="/2018/05/03/an-92-test">Root-relative link</a>

<a id="testanchor">Test Anchor</a>

<a href="#testanchor">Anchor Link</a>

<a>Legit empty link</a>

<a href=" ">Link that trims to empty</a>

<a href="thisisntarealurl">Not a real URL</a>
HTML;
		$article      = $this->factory->post->create_and_get(
			[
				'post_type'    => 'article',
				'post_title'   => 'Test Article',
				'post_content' => $post_content,
			]
		);

		// Convert to Markdown.
		$parser   = new Parser( 'markdown' );
		$markdown = $parser->parse( apply_filters( 'the_content', $article->post_content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		// Verify.
		$this->assertEquals(
			'[Absolute link](https://www.google.com)'
			. '[Root-relative link](https://www.example.org/2018/05/03/an-92-test)'
			. 'Test Anchor'
			. '[Anchor Link](#testanchor)'
			. 'Legit empty link'
			. 'Link that trims to empty'
			. 'Not a real URL',
			str_replace( "\n", '', $markdown )
		);
	}

	/**
	 * Test the anchor cleaning functions of the parser for Markdown.
	 *
	 * @see \Apple_Exporter\Parser::parse
	 */
	public function test_clean_html_markdown_with_diffent_home_url(): void {
		update_option( 'home', 'https://www.custom-example.org' );

		$post_content = <<<HTML
<a href="https://www.google.com">Absolute link</a>

<a href="/2018/05/03/an-92-test">Root-relative link</a>

<a id="testanchor">Test Anchor</a>

<a href="#testanchor">Anchor Link</a>

<a>Legit empty link</a>

<a href=" ">Link that trims to empty</a>

<a href="thisisntarealurl">Not a real URL</a>
HTML;
		$article      = $this->factory->post->create_and_get(
			[
				'post_type'    => 'article',
				'post_title'   => 'Test Article',
				'post_content' => $post_content,
			]
		);

		// Convert to Markdown.
		$parser   = new Parser( 'markdown' );
		$markdown = $parser->parse( apply_filters( 'the_content', $article->post_content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		// Verify.
		$this->assertEquals(
			'[Absolute link](https://www.google.com)'
			. '[Root-relative link](https://www.custom-example.org/2018/05/03/an-92-test)'
			. 'Test Anchor'
			. '[Anchor Link](#testanchor)'
			. 'Legit empty link'
			. 'Link that trims to empty'
			. 'Not a real URL',
			str_replace( "\n", '', $markdown )
		);

		// Reset the home URL.
		update_option( 'home', 'https://www.example.org' );
	}

	/**
	 * Test the anchor cleaning functions of the parser for HTML.
	 *
	 * @see \Apple_Exporter\Parser::parse
	 */
	public function test_clean_html(): void {
		$post_content = <<<HTML
<a href="https://www.google.com">Absolute link</a>

<a href="/2018/05/03/an-92-test">Root-relative link</a>

<a id="testanchor">Test Anchor</a>

<a href="#testanchor">Anchor Link</a>

<a>Legit empty link</a>

<a href=" ">Link that trims to empty</a>

<a href="thisisntarealurl">Not a real URL</a>
HTML;
		$article      = $this->factory->post->create_and_get(
			[
				'post_type'    => 'article',
				'post_title'   => 'Test Article',
				'post_content' => $post_content,
			]
		);

		// Parse the post with HTML content format.
		$parser      = new Parser( 'html' );
		$parsed_html = $parser->parse( apply_filters( 'the_content', $article->post_content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		// Verify.
		$this->assertEquals(
			'<p><a href="https://www.google.com">Absolute link</a></p>'
				. '<p><a href="https://www.example.org/2018/05/03/an-92-test">Root-relative link</a></p>'
				. '<p>Test Anchor</p>'
				. '<p><a href="#testanchor">Anchor Link</a></p>'
				. '<p>Legit empty link</p>'
				. '<p>Link that trims to empty</p>'
				. '<p>Not a real URL</p>',
			str_replace( "\n", '', $parsed_html )
		);
	}

	/**
	 * Test the clean HTML function with a different root URL.
	 *
	 * @see \Apple_Exporter\Parser::parse
	 */
	public function test_clean_html_with_diffent_home_url(): void {
		update_option( 'home', 'https://www.custom-example.org' );

		$post_content = <<<HTML
<a href="https://www.google.com">Absolute link</a>

<a href="/2018/05/03/an-92-test">Root-relative link</a>

<a id="testanchor">Test Anchor</a>

<a href="#testanchor">Anchor Link</a>

<a>Legit empty link</a>

<a href=" ">Link that trims to empty</a>

<a href="thisisntarealurl">Not a real URL</a>
HTML;
		$article      = $this->factory->post->create_and_get(
			[
				'post_type'    => 'article',
				'post_title'   => 'Test Article',
				'post_content' => $post_content,
			]
		);

		// Parse the post with HTML content format.
		$parser      = new Parser( 'html' );
		$parsed_html = $parser->parse( apply_filters( 'the_content', $article->post_content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		// Verify.
		$this->assertEquals(
			'<p><a href="https://www.google.com">Absolute link</a></p>'
				. '<p><a href="https://www.custom-example.org/2018/05/03/an-92-test">Root-relative link</a></p>'
				. '<p>Test Anchor</p>'
				. '<p><a href="#testanchor">Anchor Link</a></p>'
				. '<p>Legit empty link</p>'
				. '<p>Link that trims to empty</p>'
				. '<p>Not a real URL</p>',
			str_replace( "\n", '', $parsed_html )
		);

		// Reset the home URL.
		update_option( 'home', 'https://www.example.org' );
	}
}
