<?php

use \Apple_News as Apple_News;
use \Apple_Exporter\Settings as Settings;

class Apple_News_Test extends WP_UnitTestCase {

	public function setup() {
		parent::setup();
		$this->settings = new Settings();
	}

	public function testGetFilename() {
		$url = 'http://someurl.com/image.jpg?w=150&h=150';
		$filename = Apple_News::get_filename( $url );

		$this->assertEquals( 'image.jpg', $filename );
	}

	public function testVersion() {
		$plugin_data = apple_news_get_plugin_data();
		$this->assertEquals( $plugin_data['Version'], Apple_News::$version );
	}

	public function testMigrateSettings() {
		update_option( 'use_remote_images', 'yes' );
		$default_settings = $this->settings->all();
		$apple_news = new Apple_News();
		$apple_news->migrate_settings( $this->settings );

		$migrated_settings = get_option( $apple_news::$option_name );
		$this->assertNotEquals( $default_settings, $migrated_settings );

		$default_settings['use_remote_images'] = 'yes';
		$this->assertEquals( $default_settings, $migrated_settings );
	}

	public function testSupportInfoHTML() {
		$this->assertEquals( '<br /><br />If you need assistance, please reach out for support on <a href="https://wordpress.org/support/plugin/publish-to-apple-news">WordPress.org</a> or <a href="https://github.com/alleyinteractive/apple-news/issues">GitHub</a>.', Apple_News::get_support_info() );
	}

	public function testSupportInfoHTMLNoPadding() {
		$this->assertEquals( 'If you need assistance, please reach out for support on <a href="https://wordpress.org/support/plugin/publish-to-apple-news">WordPress.org</a> or <a href="https://github.com/alleyinteractive/apple-news/issues">GitHub</a>.', Apple_News::get_support_info( 'html', false ) );
	}

	public function testSupportInfoText() {
		$this->assertEquals( "\n\nIf you need assistance, please reach out for support on WordPress.org or GitHub.", Apple_News::get_support_info( 'text' ) );
	}

	public function testSupportInfoTextNoPadding() {
		$this->assertEquals( "If you need assistance, please reach out for support on WordPress.org or GitHub.", Apple_News::get_support_info( 'text', false ) );
	}
}
