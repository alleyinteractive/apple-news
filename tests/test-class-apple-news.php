<?php
/**
 * Publish to Apple News Tests: Apple_News_Test class
 *
 * Contains a class which is used to test the Apple_News class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use \Apple_Exporter\Settings;

/**
 * A class which is used to test the Apple_News class.
 */
class Apple_News_Test extends WP_UnitTestCase {

	/**
	 * A function containing operations to be run before each test function.
	 *
	 * @access public
	 */
	public function setUp() {
		parent::setup();
		$this->settings = new Settings();
	}

	/**
	 * Ensures that the get_filename function properly returns an image filename.
	 *
	 * @see Apple_News::get_filename()
	 *
	 * @access public
	 */
	public function testGetFilename() {
		$url = 'http://someurl.com/image.jpg?w=150&h=150';
		$filename = Apple_News::get_filename( $url );
		$this->assertEquals( 'image.jpg', $filename );
	}

	/**
	 * Ensures that the migrate_api_settings function migrates settings.
	 *
	 * @see Apple_News::migrate_api_settings()
	 *
	 * @access public
	 */
	public function testMigrateApiSettings() {

		// Setup.
		$legacy_settings = $this->settings->all();
		$legacy_settings['api_autosync_update'] = 'no';
		unset( $legacy_settings['api_autosync_delete'] );
		$apple_news = new Apple_News();
		update_option( $apple_news::$option_name, $legacy_settings );
		$apple_news->migrate_api_settings( $legacy_settings );

		// Ensure the defaults did not overwrite the migrated legacy data.
		$expected_settings = $legacy_settings;
		$expected_settings['api_autosync_delete'] = 'no';
		$migrated_settings = get_option( $apple_news::$option_name );
		$this->assertEquals( $expected_settings, $migrated_settings );
	}

	/**
	 * Ensures that the migrate_settings function properly migrates legacy settings.
	 *
	 * @see Apple_News::migrate_settings()
	 *
	 * @access public
	 */
	public function testMigrateSettings() {

		// Setup.
		update_option( 'use_remote_images', 'yes' );
		$default_settings = $this->settings->all();
		$apple_news = new Apple_News();
		$apple_news->migrate_settings( $this->settings );

		// Ensure the defaults did not overwrite the migrated legacy data.
		$migrated_settings = get_option( $apple_news::$option_name );
		$this->assertNotEquals( $default_settings, $migrated_settings );

		// Ensure the migrated settings match what we expect.
		$default_settings['use_remote_images'] = 'yes';
		$this->assertEquals( $default_settings, $migrated_settings );
	}

	/**
	 * Ensures that the get_support_info returns the correct values.
	 *
	 * @see Apple_News::get_support_info()
	 *
	 * @access public
	 */
	public function testSupportInfo() {

		// Test HTML.
		$this->assertEquals(
			'<br /><br />If you need assistance, please reach out for support on <a href="https://wordpress.org/support/plugin/publish-to-apple-news">WordPress.org</a> or <a href="https://github.com/alleyinteractive/apple-news/issues">GitHub</a>.',
			Apple_News::get_support_info()
		);

		// Test HTML with no padding.
		$this->assertEquals(
			'If you need assistance, please reach out for support on <a href="https://wordpress.org/support/plugin/publish-to-apple-news">WordPress.org</a> or <a href="https://github.com/alleyinteractive/apple-news/issues">GitHub</a>.',
			Apple_News::get_support_info( 'html', false )
		);

		// Test text.
		$this->assertEquals(
			"\n\n" . 'If you need assistance, please reach out for support on WordPress.org or GitHub.',
			Apple_News::get_support_info( 'text' )
		);

		// Test text with no padding.
		$this->assertEquals(
			'If you need assistance, please reach out for support on WordPress.org or GitHub.',
			Apple_News::get_support_info( 'text', false )
		);
	}

	/**
	 * Ensures that the version in Apple_News matches the reported plugin version.
	 *
	 * @see Apple_News::$version
	 *
	 * @access public
	 */
	public function testVersion() {
		$plugin_data = apple_news_get_plugin_data();
		$this->assertEquals( $plugin_data['Version'], Apple_News::$version );
	}
}
