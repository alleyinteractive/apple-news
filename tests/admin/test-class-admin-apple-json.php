<?php

use Apple_Exporter\Settings as Settings;
use Apple_Exporter\Builders\Component_Layouts as Component_Layouts;
use Apple_Exporter\Builders\Component_Text_Styles as Component_Text_Styles;
use Apple_Exporter\Components\Advertisement as Advertisement;
use Apple_Exporter\Components\Audio as Audio;

class Admin_Apple_JSON_Test extends WP_UnitTestCase {

	public function setup() {
		parent::setup();

		$this->prophet  = new \Prophecy\Prophet;
		$this->settings = new Settings();
		$this->styles   = new Component_Text_Styles( $this->content, $this->settings );
		$this->layouts  = new Component_Layouts( $this->content, $this->settings );
	}

	public function testJSONSaveCustomSpec() {
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

		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		$stored_json_specs = get_option( 'apple_news_json_advertisement' );
		$stored_json = wp_json_encode( $stored_json_specs['apple_news_json_json'], JSON_PRETTY_PRINT );
		$this->assertEquals( $stored_json, $json );
	}

	public function testJSONUseCustomSpec() {
		// Just manually create a custom spec
		update_option( 'apple_news_json_advertisement', array(
			'apple_news_json_json' => array(
				'role' => 'banner_advertisement',
   				'bannerType' => 'double_height',
			),
		) );

		$component = new Advertisement( null, null, $this->settings, $this->styles,
			$this->layouts );
		$json = $component->to_array();

		$this->assertEquals( 'banner_advertisement', $json['role'] );
		$this->assertEquals( 'double_height', $json['bannerType'] );
	}

	public function testJSONSaveValidTokens() {
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

		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		$stored_json_specs = get_option( 'apple_news_json_audio' );
		$stored_json = stripslashes( wp_json_encode( $stored_json_specs['apple_news_json_json'], JSON_PRETTY_PRINT ) );
		$this->assertEquals( $stored_json, $json );
	}

	public function testJSONSaveInvalidTokens() {
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

		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		$stored_json_specs = get_option( 'apple_news_json_audio' );
		$stored_json = wp_json_encode( $stored_json_specs['apple_news_json_json'], JSON_PRETTY_PRINT );

		// Pass the mock workspace as a dependency
		$workspace = $this->prophet->prophesize( '\Exporter\Workspace' );
		$audio = new Audio( '<audio><source src="http://someurl.com/audio-file.mp3?some_query=string"></audio>',
			$workspace->reveal(), $this->settings, $this->styles, $this->layouts );
		$json = $audio->to_array();
		$specs = $audio->get_specs();

		$this->assertEquals( $stored_json_specs, '' );
		$this->assertEquals( $specs['json']->get_spec(), $specs['json']->spec );
	}
}

