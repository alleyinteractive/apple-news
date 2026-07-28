<?php
/**
 * Publish to Apple News tests: Facebook class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A class to test the behavior of the
 * \Apple_Exporter\Components\Facebook class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Facebook extends Component_TestCase {

	/**
	 * A data provider for the test_transform function.
	 *
	 * @see self::test_transform()
	 *
	 * @return array An array of test data
	 */
	public static function data_transform() {
		return [
			[ 'https://www.facebook.com/page-name/posts/12345' ],
			[ 'https://www.facebook.com/username/posts/12345' ],
			[ 'https://www.facebook.com/username/activity/12345' ],
			[ 'https://www.facebook.com/photo.php?fbid=12345' ],
			[ 'https://www.facebook.com/photos/12345' ],
			[ 'https://www.facebook.com/permalink.php?story_fbid=12345' ],
		];
	}

	/**
	 * A filter function to modify the URL in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_facebook_json( $json ) {
		$json['URL'] = 'https://www.facebook.com/test/posts/54321';

		return $json;
	}

	/**
	 * Test the `apple_news_facebook_json` filter.
	 */
	public function test_filter() {

		// Setup.
		$component = new \Apple_Exporter\Components\Facebook(
			'https://www.facebook.com/test/posts/12345',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_facebook_json',
			[ $this, 'filter_apple_news_facebook_json' ]
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals(
			'https://www.facebook.com/test/posts/54321',
			$result['URL']
		);

		// Teardown.
		remove_filter(
			'apple_news_facebook_json',
			[ $this, 'filter_apple_news_facebook_json' ]
		);
	}

	/**
	 * Tests the transformation process from an oEmbed URL to a Facebook component.
	 *
	 * @param string $url The URL to test.
	 */
	#[DataProvider( 'data_transform' )]
	public function test_transform( $url ) {

		// Setup.
		$component = new \Apple_Exporter\Components\Facebook(
			$url,
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			[
				'role' => 'facebook_post',
				'URL'  => $url,
			],
			$component->to_array()
		);

		// Test the node - correct css class and fb url.
		$node = self::build_node( sprintf( '<div class="fb-post" data-href="%s"></div>', esc_url( $url ) ) );

		$this->assertEquals(
			$component->node_matches( $node ),
			$node
		);

		// Test the node - WordPress.com embed syntax.
		$wpcom_embed_html = '<p><fb:post href="' . esc_url( $url ) . '" data-width="552" class=" fb_iframe_widget" fb-xfbml-state="rendered" fb-iframe-plugin-query="app_id=249643311490&amp;container_width=0&amp;href=https%3A%2F%2Fwww.facebook.com%2FWordPresscom%2Fposts%2F10156452969778980&amp;locale=en_US&amp;sdk=joey&amp;width=552"><span style="vertical-align: bottom; width: 552px; height: 504px;"><iframe name="f1d6c220128422" width="552px" height="1000px" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" allow="encrypted-media" title="fb:post Facebook Social Plugin" src="https://www.facebook.com/v2.3/plugins/post.php?app_id=249643311490&amp;channel=https%3A%2F%2Fstaticxx.facebook.com%2Fconnect%2Fxd_arbiter%2Fr%2FRQ7NiRXMcYA.js%3Fversion%3D42%23cb%3Df31851b8eec581%26domain%3Dexample.wordpress.com%26origin%3Dhttps%253A%252F%252Fexample.wordpress.com%252Ff59b6814ddce14%26relation%3Dparent.parent&amp;container_width=0&amp;href=https%3A%2F%2Fwww.facebook.com%2FWordPresscom%2Fposts%2F10156452969778980&amp;locale=en_US&amp;sdk=joey&amp;width=552" style="border: none; visibility: visible; width: 552px; height: 504px;" class=""></iframe></span></fb:post></p>';
		$node             = self::build_node( $wpcom_embed_html );

		$this->assertEquals(
			$component->node_matches( $node ),
			$node
		);

		$component = new \Apple_Exporter\Components\Facebook(
			$wpcom_embed_html,
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		$this->assertEquals(
			[
				'role' => 'facebook_post',
				'URL'  => $url,
			],
			$component->to_array()
		);

		// Test the node - *incorrect* css class and fb url.
		$node_failure = self::build_node( sprintf( '<div class="invalid-fb-post" data-href="%s"></div>', esc_url( $url ) ) );

		$this->assertEmpty( $component->node_matches( $node_failure ) );

		// Test the node - in-text Facebook URL.
		$node_failure = self::build_node( sprintf( 'Visit us on Facebook at <a href="%s">%s</a>!', esc_url( $url ), esc_html( $url ) ) );

		$this->assertEmpty( $component->node_matches( $node_failure ) );
	}
}
