<?php
/**
 * Publish to Apple News Tests: Admin_Apple_JSON_Test class
 *
 * Contains a class which is used to test Admin_Apple_JSON.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use \Apple_Exporter\Settings;
use \Apple_Exporter\Builders\Component_Layouts;
use \Apple_Exporter\Builders\Component_Text_Styles;
use \Apple_Exporter\Components\Advertisement;
use \Apple_Exporter\Components\Audio;

/**
 * A class which is used to test the Admin_Apple_JSON class.
 */
class Admin_Apple_JSON_Test extends WP_UnitTestCase {

	/**
	 * Actions to be run before each test in this class.
	 *
	 * @access public
	 */
	public function setup() {
		parent::setup();
		$this->prophet = new \Prophecy\Prophet;
		$this->settings = new Settings();
		$this->styles = new Component_Text_Styles( $this->content, $this->settings );
		$this->layouts = new Component_Layouts( $this->content, $this->settings );
	}

	/**
	 * Ensures that a custom spec is saved properly.
	 *
	 * @access public
	 */
	public function testJSONSaveCustomSpec() {

		// Setup.
		$json = <<<JSON
{
    "role": "banner_advertisement",
    "bannerType": "double_height"
}
JSON;
		$nonce = wp_create_nonce( 'apple_news_json' );
		$_POST['apple_news_component'] = 'Advertisement';
		$_POST['apple_news_action'] = 'apple_news_save_json';
		$_POST['apple_news_json_json'] = $json;
		$_POST['page'] = 'apple-news-json';
		$_POST['redirect'] = false;
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-json';
		$_REQUEST['_wpnonce'] = $nonce;

		// Trigger the save operation.
		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		// Test.
		$stored_json_specs = get_option( 'apple_news_json_advertisement' );
		$stored_json = wp_json_encode(
			$stored_json_specs['apple_news_json_json'],
			JSON_PRETTY_PRINT
		);
		$this->assertEquals( $stored_json, $json );
	}

	/**
	 * Ensure that invalid tokens are not saved in a custom spec.
	 *
	 * @access public
	 */
	public function testJSONSaveInvalidTokens() {

		// Setup.
		$invalid_json = <<<JSON
{
    "role": "audio",
    "URL": "#invalid#"
}
JSON;
		$nonce = wp_create_nonce( 'apple_news_json' );
		$_POST['apple_news_component'] = 'Audio';
		$_POST['apple_news_action'] = 'apple_news_save_json';
		$_POST['apple_news_json_json'] = $invalid_json;
		$_POST['page'] = 'apple-news-json';
		$_POST['redirect'] = false;
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-json';
		$_REQUEST['_wpnonce'] = $nonce;

		// Trigger spec save.
		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		// Load an Audio element to ensure invalid specs did not save.
		$workspace = $this->prophet->prophesize( '\Exporter\Workspace' );
		$audio = new Audio(
			'<audio><source src="http://someurl.com/audio-file.mp3?some_query=string"></audio>',
			$workspace->reveal(),
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$specs = $audio->get_specs();

		// Test.
		$stored_json_specs = get_option( 'apple_news_json_audio' );
		$this->assertEquals( $stored_json_specs, '' );
		$this->assertEquals( $specs['json']->get_spec(), $specs['json']->spec );
	}

	/**
	 * Ensure that valid tokens are saved in the custom JSON spec.
	 *
	 * @access public
	 */
	public function testJSONSaveValidTokens() {

		// Setup.
		$json = <<<JSON
{
    "role": "audio",
    "URL": "http://someurl.com"
}
JSON;
		$nonce = wp_create_nonce( 'apple_news_json' );
		$_POST['apple_news_component'] = 'Audio';
		$_POST['apple_news_action'] = 'apple_news_save_json';
		$_POST['apple_news_json_json'] = $json;
		$_POST['page'] = 'apple-news-json';
		$_POST['redirect'] = false;
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-json';
		$_REQUEST['_wpnonce'] = $nonce;

		// Trigger the spec save.
		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		// Test.
		$stored_json_specs = get_option( 'apple_news_json_audio' );
		$stored_json = stripslashes(
			wp_json_encode(
				$stored_json_specs['apple_news_json_json'],
				JSON_PRETTY_PRINT
			)
		);
		$this->assertEquals( $stored_json, $json );
	}

	/**
	 * Ensure that the custom spec is used on render.
	 *
	 * @access public
	 */
	public function testJSONUseCustomSpec() {

		// Setup.
		update_option( 'apple_news_json_advertisement', array(
			'apple_news_json_json' => array(
				'role' => 'banner_advertisement',
				'bannerType' => 'double_height',
			),
		) );
		$component = new Advertisement(
			null,
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$json = $component->to_array();

		// Test.
		$this->assertEquals( 'banner_advertisement', $json['role'] );
		$this->assertEquals( 'double_height', $json['bannerType'] );
	}
}
