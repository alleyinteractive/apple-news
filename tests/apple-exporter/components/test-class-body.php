<?php
/**
 * Publish to Apple News tests: Body_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the behavior of the Apple_Exporter\Components\Body class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Body_Test extends Apple_News_Testcase {

	/**
	 * A data provider that arguments for `test_dropcap_determination` tests.
	 *
	 * @return array An array of arrays representing function arguments.
	 */
	public function data_dropcap_determination() {
		return [
			// No dropcap -- punctuation first character.
			[
				'default-body',
				100,
				'yes',
				<<<HTML
<p>"Go-Gurt," but to stay.</p>
HTML
				,
			],
			// No dropcap -- dropcap minimum character requirement not met.
			[
				'default-body',
				100,
				'no',
				<<<HTML
<p>I hope to keep this briefing brief. But briefly, before I begin this brief briefing...</p>
HTML
				,
			],
			// Dropcap applied -- minimum character opt out.
			[
				'dropcapBodyStyle',
				500,
				'yes',
				<<<HTML
<p>I am not optimistic about the optics of us opting out, opined the opulent Optometrist.</p>
HTML
				,
			],
			// Dropcap applied -- minimum character requirement met, no opt out, no punctuation first character.
			[
				'dropcapBodyStyle',
				50,
				'no',
				<<<HTML
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
HTML
				,
			],
		];
	}

	/**
	 * A data provider that supplies empty HTML signatures to ensure that they
	 * are not erroneously transformed into empty body elements.
	 *
	 * @return array An array of arrays representing function arguments.
	 */
	public function data_empty_html() {
		return [
			// Test classic editor, multiple line breaks.
			[
				<<<HTML
A



B
HTML
				,
			],

			// Test classic editor, multiple line breaks with &nbsp.
			[
				<<<HTML
A

&nbsp;

B
HTML
				,
			],

			// Test classic editor, extra line breaks at the end.
			[
				<<<HTML
A

B


HTML
				,
			],

			// Test classic editor, extra line breaks at the end with a non-breaking space.
			[
				<<<HTML
A

B

&nbsp;
HTML
				,
			],

			// Test Gutenberg editor, empty paragraph tag.
			[
				<<<HTML
<!-- wp:paragraph -->
<p>A</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>B</p>
<!-- /wp:paragraph -->
HTML
				,
			],

			// Test Gutenberg editor, paragraph tag containing a single space.
			[
				<<<HTML
<!-- wp:paragraph -->
<p>A</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p> </p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>B</p>
<!-- /wp:paragraph -->
HTML
				,
			],

			// Test Gutenberg editor, paragraph tag containing a non-breaking space.
			[
				<<<HTML
<!-- wp:paragraph -->
<p>A</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>&nbsp;</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>B</p>
<!-- /wp:paragraph -->
HTML
				,
			],

			// Test Gutenberg editor, extra paragraph at the end.
			[
				<<<HTML
<!-- wp:paragraph -->
<p>A</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>B</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->
HTML
				,
			],

			// Test Gutenberg editor, extra paragraph at the end containing a space.
			[
				<<<HTML
<!-- wp:paragraph -->
<p>A</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>B</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p> </p>
<!-- /wp:paragraph -->
HTML
				,
			],

			// Test Gutenberg editor, extra paragraph at the end containing a non-breaking space.
			[
				<<<HTML
<!-- wp:paragraph -->
<p>A</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>B</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>&nbsp;</p>
<!-- /wp:paragraph -->
HTML
				,
			],

			// Test Gutenberg editor, extra paragraph at the end containing a non-breaking space surrounded by a link tag.
			[
				<<<HTML
<!-- wp:paragraph -->
<p>A</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>B</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a href="https://www.apple.com/">&nbsp;</a></p>
<!-- /wp:paragraph -->
HTML
				,
			],
		];
	}

	/**
	 * A data provider for the test_link_types function.
	 *
	 * @see https://developer.apple.com/documentation/apple_news/supportedurls
	 *
	 * @return array An array of arrays representing function arguments.
	 */
	public function data_link_types() {
		return [
			// Standard link, non-https.
			[ 'http://www.example.org', true ],

			// Standard link, https.
			[ 'https://www.example.org', true ],

			// Root-relative URL. Should be permitted, but auto-converted to a fully qualified URL.
			[ '/test', true ],

			// Anchor link (hash-based URL) should be permitted.
			[ '#test', true ],

			// Apple News article URL.
			[ 'https://apple.news/A5vHgPPmQSvuIxPjeXLTdGQ', true ],

			// Apple News article URL with a hash reference.
			[ 'https://apple.news/A5vHgPPmQSvuIxPjeXLTdGQ#TextComponent-1', true ],

			// A Stocks app URL.
			[ 'stocks://?symbol=AAPL', true ],

			// An Apple Music URL, non-https.
			[ 'music://abc123', true ],

			// An Apple Music URL, https.
			[ 'musics://abc123', true ],

			// A mailto link.
			[ 'mailto:example@example.org', true ],

			// A hosted calendar.
			[ 'webcal://abc123', true ],

			// An unsupported protocol.
			[ 'badprotocol://abc123', false ],
		];
	}

	/**
	 * Returns an array of arrays representing function arguments to the
	 * test_code_formatting, test_filter, and test_filter_html function.
	 */
	public function data_generic() {
		return [
			[ [ 'cover', 'slug', 'title', 'byline' ], 2 ],
			[ [ 'cover', 'slug', 'title', 'author', 'date' ], 3 ],
		];
	}

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_body_json( $json ) {
		$json['textStyle'] = 'fancy-body';

		return $json;
	}

	/**
	 * A filter function to modify the HTML enabled flag for this component.
	 *
	 * @param bool $enabled Whether HTML support is enabled for this component.
	 *
	 * @return bool Whether HTML support is enabled for this component.
	 */
	public function filter_apple_news_body_html_enabled( $enabled ) {
		return ! $enabled;
	}

	/**
	 * Tests code formatting.
	 */
	public function test_code_formatting() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'title', 'author' ] ] );
		$content = <<<HTML
<!-- wp:paragraph -->
<p>Lorem ipsum. <a href="https://www.wordpress.org">Dolor sit amet.</a></p>
<!-- /wp:paragraph -->

<!-- wp:preformatted -->
<pre class="wp-block-preformatted">Preformatted text.</pre>
<!-- /wp:preformatted -->

<!-- wp:paragraph -->
<p>Testing a <code>code sample</code>.</p>
<!-- /wp:paragraph -->
HTML;
		$post_id = self::factory()->post->create( [ 'post_content' => $content ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'body', $json['components'][2]['role'] );
		$this->assertEquals( 'html', $json['components'][2]['format'] );
		$this->assertEquals( '<p>Lorem ipsum. <a href="https://www.wordpress.org">Dolor sit amet.</a></p>', $json['components'][2]['text'] );
		$this->assertEquals( 'body', $json['components'][3]['role'] );
		$this->assertEquals( 'html', $json['components'][3]['format'] );
		$this->assertEquals( '<pre>Preformatted text.</pre>', $json['components'][3]['text'] );
		$this->assertEquals( 'body', $json['components'][4]['role'] );
		$this->assertEquals( 'html', $json['components'][4]['format'] );
		$this->assertEquals( '<p>Testing a <code>code sample</code>.</p>', $json['components'][4]['text'] );
	}

	/**
	 * Test's dropcap configuration and conditionals.
	 *
	 * @dataProvider data_dropcap_determination
	 *
	 * @param string $body_style              The style of the first paragraph.  `dropcapBodyStyle` if dropcap styling is applied or `default-body` if not.
	 * @param int    $dropcap_minimum         The minimum number of characters in the first paragraph before dropcap stylings are applied.
	 * @param string $dropcap_minimum_opt_out Choice to opt out of minimum character rule, 'yes' or 'no'.
	 * @param html   $content                 The html content of the post.
	 */
	public function test_dropcap_determination( $body_style, $dropcap_minimum, $dropcap_minimum_opt_out, $content ) {
		$this->set_theme_settings(
			[
				'dropcap_minimum'         => $dropcap_minimum,
				'dropcap_minimum_opt_out' => $dropcap_minimum_opt_out,
			]
		);
		$post_id = self::factory()->post->create( [ 'post_content' => $content ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( $body_style, $json['components'][3]['textStyle'] );
	}

	/**
	 * Tests handling for empty HTML content.
	 *
	 * @dataProvider data_empty_html
	 *
	 * @param string $post_content The post content for the post.
	 */
	public function test_empty_html_content( $post_content ) {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'title', 'author' ] ] );
		$post_id = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json    = $this->get_json_for_post( $post_id );

		// There should only be two body components, one containing A, one containing B.
		$this->assertEquals( 4, count( $json['components'] ) );
		$this->assertEquals( '<p>A</p>', $json['components'][2]['text'] );
		$this->assertEquals( '<p>B</p>', $json['components'][3]['text'] );
	}

	/**
	 * Test the `apple_news_body_json` filter.
	 *
	 * @dataProvider data_generic
	 *
	 * @param string[] $meta_order The order of meta components to use.
	 * @param int      $index      The index of the component in the JSON to target.
	 */
	public function test_filter( $meta_order, $index ) {
		$this->set_theme_settings( [ 'meta_component_order' => $meta_order ] );
		add_filter( 'apple_news_body_json', [ $this, 'filter_apple_news_body_json' ] );

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create();
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'body', $json['components'][ $index ]['role'] );
		$this->assertEquals( 'fancy-body', $json['components'][ $index ]['textStyle'] );

		// Teardown.
		remove_filter( 'apple_news_body_json', [ $this, 'filter_apple_news_body_json' ] );
	}

	/**
	 * Test the `apple_news_body_html_enabled` filter.
	 *
	 * @dataProvider data_generic
	 *
	 * @param string[] $meta_order The order of meta components to use.
	 * @param int      $index      The index of the component in the JSON to target.
	 */
	public function test_filter_html( $meta_order, $index ) {
		$this->set_theme_settings( [ 'meta_component_order' => $meta_order ] );
		// Test before filter.
		$post_id = self::factory()->post->create( [ 'post_content' => 'Test content.' ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'body', $json['components'][ $index ]['role'] );
		$this->assertEquals( 'html', $json['components'][ $index ]['format'] );
		$this->assertEquals( '<p>Test content.</p>', $json['components'][ $index ]['text'] );


		// Add filter and test to ensure HTML mode is not used.
		add_filter( 'apple_news_body_html_enabled', [ $this, 'filter_apple_news_body_html_enabled' ] );
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'body', $json['components'][ $index ]['role'] );
		$this->assertEquals( 'markdown', $json['components'][ $index ]['format'] );
		$this->assertEquals( 'Test content.', $json['components'][ $index ]['text'] );
		remove_filter( 'apple_news_body_html_enabled', [ $this, 'filter_apple_news_body_html_enabled' ] );
	}

	/**
	 * Ensures that the body-layout-last class is properly applied.
	 */
	public function test_layouts() {
		// Create a post with empty body content to force the body-layout-last bug to appear.
		$post_id = self::factory()->post->create( [ 'post_content' => '' ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertNotEquals( 'body-layout-last', $json['components'][ count( $json['components'] ) - 1 ]['layout'] );
	}

	/**
	 * Given an expected result and an actual link, verifies that the link URL is
	 * correctly processed. Used to ensure that valid link types (not just http/s,
	 * but also mailto, webcal, stocks, etc.) are supported, and that unsupported
	 * types are stripped out.
	 *
	 * @dataProvider data_link_types
	 *
	 * @param string $link        The link, which will be added as the href parameter in an anchor tag in the test post that the test creates.
	 * @param bool   $should_work Whether the link is expected to work in Apple News Format or not.
	 */
	public function test_link_types( $link, $should_work ) {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'title', 'author' ] ] );
		$content = <<<HTML
<!-- wp:paragraph -->
<p>Lorem ipsum <a href="$link">dolor sit amet</a>.</p>
<!-- /wp:paragraph -->
HTML;
		$post_id = self::factory()->post->create( [ 'post_content' => $content ] );
		$json    = $this->get_json_for_post( $post_id );

		// Negotiate expected value and test.
		if ( str_starts_with( $link, '/' ) ) {
			$link = 'https://www.example.org' . $link;
		}
		$expected = $should_work
			? sprintf( '<p>Lorem ipsum <a href="%s">dolor sit amet</a>.</p>', $link )
			: '<p>Lorem ipsum dolor sit amet.</p>';
		$this->assertEquals( $expected, $json['components'][2]['text'] );
	}

	/**
	 * Tests body settings.
	 */
	public function test_settings() {
		$this->set_theme_settings(
			[
				'body_font'                      => 'AmericanTypewriter',
				'body_size'                      => 20,
				'body_color'                     => '#abcdef',
				'body_color_dark'                => '#bcdef0',
				'body_link_color'                => '#fedcba',
				'body_link_color_dark'           => '#edcba0',
				'body_line_height'               => 28,
				'body_tracking'                  => 50,
				'dropcap_background_color'       => '#abcabc',
				'dropcap_background_color_dark'  => '#bcabc0',
				'dropcap_color'                  => '#defdef',
				'dropcap_color_dark'             => '#efdef0',
				'dropcap_font'                   => 'AmericanTypewriter-Bold',
				'dropcap_minimum_opt_out'        => 'yes',
				'dropcap_number_of_characters'   => 15,
				'dropcap_number_of_lines'        => 10,
				'dropcap_number_of_raised_lines' => 5,
				'dropcap_padding'                => 20,
			]
		);
		$content = <<<HTML
<!-- wp:paragraph -->
<p>Paragraph 1.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Paragraph 2.</p>
<!-- /wp:paragraph -->
HTML;
		$post_id = self::factory()->post->create( [ 'post_content' => $content ] );
		$json    = $this->get_json_for_post( $post_id );

		// Validate body settings in generated JSON.
		$this->assertEquals( 'AmericanTypewriter', $json['componentTextStyles']['default-body']['fontName'] );
		$this->assertEquals( 20, $json['componentTextStyles']['default-body']['fontSize'] );
		$this->assertEquals( '#abcdef', $json['componentTextStyles']['default-body']['textColor'] );
		$this->assertEquals( '#fedcba', $json['componentTextStyles']['default-body']['linkStyle']['textColor'] );
		$this->assertEquals( 28, $json['componentTextStyles']['default-body']['lineHeight'] );
		$this->assertEquals( 0.5, $json['componentTextStyles']['default-body']['tracking'] );
		$this->assertEquals( '#bcdef0', $json['componentTextStyles']['default-body']['conditional']['textColor'] );
		$this->assertEquals( '#edcba0', $json['componentTextStyles']['default-body']['conditional']['linkStyle']['textColor'] );
		$this->assertEquals( '#abcabc', $json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['backgroundColor'] );
		$this->assertEquals( '#defdef', $json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['textColor'] );
		$this->assertEquals( 'AmericanTypewriter-Bold', $json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['fontName'] );
		$this->assertEquals( 15, $json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['numberOfCharacters'] );
		$this->assertEquals( 10, $json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['numberOfLines'] );
		$this->assertEquals( 5, $json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['numberOfRaisedLines'] );
		$this->assertEquals( 20, $json['componentTextStyles']['dropcapBodyStyle']['dropCapStyle']['padding'] );
		$this->assertEquals( '#bcabc0', $json['componentTextStyles']['dropcapBodyStyle']['conditional']['dropCapStyle']['backgroundColor'] );
		$this->assertEquals( '#efdef0', $json['componentTextStyles']['dropcapBodyStyle']['conditional']['dropCapStyle']['textColor'] );
		$this->assertEquals( '#bcdef0', $json['componentTextStyles']['dropcapBodyStyle']['conditional']['textColor'] );
		$this->assertEquals( '#edcba0', $json['componentTextStyles']['dropcapBodyStyle']['conditional']['linkStyle']['textColor'] );
	}

	/**
	 * Tests 0 values in tokens.
	 */
	public function test_settings_zero_value_in_token() {
		$this->set_theme_settings( [ 'body_line_height' => 0 ] );
		$content = <<<HTML
<!-- wp:paragraph -->
<p>Paragraph 1.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Paragraph 2.</p>
<!-- /wp:paragraph -->
HTML;
		$post_id = self::factory()->post->create( [ 'post_content' => $content ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 0, $json['componentTextStyles']['default-body']['lineHeight'] );
	}

	/**
	 * Test the setting to disable the initial dropcap.
	 */
	public function test_without_dropcap() {
		$this->set_theme_settings(
			[
				'initial_dropcap'      => 'no',
				'meta_component_order' => [ 'cover', 'slug', 'title', 'byline' ],
			]
		);
		$content = <<<HTML
<!-- wp:paragraph -->
<p>Paragraph 1.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Paragraph 2.</p>
<!-- /wp:paragraph -->
HTML;
		$post_id = self::factory()->post->create( [ 'post_content' => $content ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( '<p>Paragraph 1.</p>', $json['components'][2]['text'] );
		$this->assertEquals( 'default-body', $json['components'][2]['textStyle'] );
		$this->assertEquals( '<p>Paragraph 2.</p>', $json['components'][3]['text'] );
		$this->assertEquals( 'default-body', $json['components'][3]['textStyle'] );
	}

	/**
	 * Tests alignment textStyles.
	 */
	public function test_alignment_textstyles() {
		$content = <<<HTML
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Paragraph 1.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"right"} -->
<p class="has-text-align-right">Paragraph 2.</p>
<!-- /wp:paragraph -->
HTML;

		$post_id = self::factory()->post->create( [ 'post_content' => $content ] );
		$json    = $this->get_json_for_post( $post_id );

		$center = array_filter( $json['components'], fn ( $c ) => '<p>Paragraph 1.</p>' === $c['text'] );
		$right  = array_filter( $json['components'], fn ( $c ) => '<p>Paragraph 2.</p>' === $c['text'] );

		$this->assertSame( 'default-body-center', array_pop( $center )['textStyle'] );
		$this->assertSame( 'default-body-right', array_pop( $right )['textStyle'] );
	}
}
