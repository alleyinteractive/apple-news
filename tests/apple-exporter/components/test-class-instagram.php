<?php
/**
 * Publish to Apple News Tests: Instagram_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Instagram.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Instagram;

/**
 * A class which is used to test the Apple_Exporter\Components\Instagram class.
 */
class Instagram_Test extends Component_TestCase {

	/**
	 * Contains a templated embed string for use in tests.
	 *
	 * Since this string is intended to be used with sprintf, all literal % signs
	 * are escaped.
	 *
	 * @access private
	 * @var string
	 */
	private $_embed = <<<HTML
<blockquote class="instagram-media" data-instgrm-captioned data-instgrm-version="4" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:658px; padding:0; width:99.375%%; width:-webkit-calc(100%% - 2px); width:calc(100%% - 2px);"><div style="padding:8px;"> <div style=" background:#F8F8F8; line-height:0; margin-top:40px; padding:50%% 0; text-align:center; width:100%%;"> <div style=" background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAMAAAApWqozAAAAGFBMVEUiIiI9PT0eHh4gIB4hIBkcHBwcHBwcHBydr+JQAAAACHRSTlMABA4YHyQsM5jtaMwAAADfSURBVDjL7ZVBEgMhCAQBAf//42xcNbpAqakcM0ftUmFAAIBE81IqBJdS3lS6zs3bIpB9WED3YYXFPmHRfT8sgyrCP1x8uEUxLMzNWElFOYCV6mHWWwMzdPEKHlhLw7NWJqkHc4uIZphavDzA2JPzUDsBZziNae2S6owH8xPmX8G7zzgKEOPUoYHvGz1TBCxMkd3kwNVbU0gKHkx+iZILf77IofhrY1nYFnB/lQPb79drWOyJVa/DAvg9B/rLB4cC+Nqgdz/TvBbBnr6GBReqn/nRmDgaQEej7WhonozjF+Y2I/fZou/qAAAAAElFTkSuQmCC); display:block; height:44px; margin:0 auto -44px; position:relative; top:-22px; width:44px;"></div></div> <p style=" margin:8px 0 0 0; padding:0 4px;"> <a href="%s" style=" color:#000; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none; word-wrap:break-word;" target="_top">Belén 1</a></p> <p style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; line-height:17px; margin-bottom:0; margin-top:8px; overflow:hidden; padding:8px 0 7px; text-align:center; text-overflow:ellipsis; white-space:nowrap;">Una foto publicada por @gosukiwi el <time style=" font-family:Arial,sans-serif; font-size:14px; line-height:17px;" datetime="2012-06-10T22:10:01+00:00">10 de Jun de 2012 a la(s) 3:10 PDT</time></p></div></blockquote>
HTML;

	/**
	 * A data provider for the testTransform function.
	 *
	 * @see self::testTransform()
	 *
	 * @access public
	 * @return array Parameters to use when calling testTransform.
	 */
	public function dataTransform() {
		return array(
			array( 'http://www.instagram.com/p/LtaiGnryiu/' ),
			array( 'https://www.instagram.com/p/LtaiGnryiu/' ),
			array( 'http://instagram.com/p/LtaiGnryiu/' ),
			array( 'https://instagram.com/p/LtaiGnryiu/' ),
			array( 'http://instagr.am/p/LtaiGnryiu/' ),
			array( 'https://instagr.am/p/LtaiGnryiu/' ),
		);
	}

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @access public
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_instagram_json( $json ) {
		$json['URL'] = 'https://instagram.com/p/test/';

		return $json;
	}

	/**
	 * Test the `apple_news_instagram_json` filter.
	 *
	 * @access public
	 */
	public function testFilterJSON() {

		// Setup.
		$component = new Instagram(
			sprintf( $this->_embed, 'https://instagram.com/p/LtaiGnryiu/' ),
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		add_filter(
			'apple_news_instagram_json',
			array( $this, 'filter_apple_news_instagram_json' )
		);

		// Test.
		$result = $component->to_array();
		$this->assertEquals( 'https://instagram.com/p/test/', $result['URL'] );

		// Teardown.
		remove_filter(
			'apple_news_instagram_json',
			array( $this, 'filter_apple_news_instagram_json' )
		);
	}

	/**
	 * Ensures an embed without a URL is not incorrectly transformed.
	 *
	 * @access public
	 */
	public function testInvalidMarkup() {

		// Setup.
		$component = new Instagram(
			sprintf( $this->_embed, 'invalid-content-no-url' ),
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			null,
			$component->to_array()
		);
	}

	/**
	 * Ensures that given test parameters properly transform into components.
	 *
	 * @dataProvider dataTransform
	 *
	 * @param string $url The URL to use.
	 *
	 * @access public
	 */
	public function testTransform( $url ) {

		// Setup.
		$components = array();
		$components[] = new Instagram(
			$url,
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$components[] = new Instagram(
			sprintf( $this->_embed, $url ),
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		foreach ( $components as $component ) {
			$this->assertEquals(
				array(
					'role' => 'instagram',
					'URL' => $url,
				),
				$component->to_array()
			);
		}
	}
}
