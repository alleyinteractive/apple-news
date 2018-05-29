<?php
/**
 * Publish to Apple News Tests: Admin_Apple_Themes_Test class
 *
 * Contains a class which is used to test Admin_Apple_Themes.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use \Apple_Exporter\Exporter;
use \Apple_Exporter\Exporter_Content;
use \Apple_Exporter\Settings;

/**
 * A class which is used to test the Admin_Apple_Themes class.
 */
class Admin_Apple_Themes_Test extends WP_UnitTestCase {

	/**
	 * A helper function to create the default theme.
	 *
	 * @access public
	 */
	public function createDefaultTheme() {

		// Create default settings in the database.
		$settings = new \Admin_Apple_Settings();
		$settings->save_settings( $this->settings->all() );

		// Force creation of a default theme if it does not exist.
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( __( 'Default', 'apple-news' ) );
		if ( ! $theme->load() ) {
			$theme->save();
		}
	}

	/**
	 * A helper function to create a new named theme.
	 *
	 * @param string $name The name for the theme.
	 * @param array $settings The settings for the theme.
	 *
	 * @access public
	 */
	public function createNewTheme( $name, $settings = array() ) {

		// Set up the request.
		$nonce = wp_create_nonce( 'apple_news_save_edit_theme' );
		$_POST['apple_news_theme_name'] = $name;
		$_POST['action'] = 'apple_news_save_edit_theme';
		$_POST['page'] = 'apple-news-themes';
		$_POST['redirect'] = false;
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-theme-edit';
		$_REQUEST['_wpnonce'] = $nonce;
		$_REQUEST['action'] = $_POST['action'];

		// Merge any provided settings with default settings.
		$default_theme = new \Apple_Exporter\Theme;
		$defaults = $default_theme->all_settings();
		$settings = wp_parse_args( $settings, $defaults );

		// Add all of these to the $_POST object.
		foreach ( $settings as $key => $value ) {
			$_POST[ $key ] = $value;
		}

		// Invoke the save operation in the themes class.
		$this->themes->action_router();
	}

	/**
	 * Actions to be run before each test in this class.
	 *
	 * @access public
	 */
	public function setup() {
		parent::setup();

		// Store an instance of the Settings class for use in testing.
		$this->settings = new Settings();

		// Store an instance of the Admin_Apple_Themes class for use in testing.
		$this->themes = new \Admin_Apple_Themes();

		// Remove the Default theme, if it exists.
		$default_theme = new \Apple_Exporter\Theme;
		$default_theme->set_name( __( 'Default', 'apple-news' ) );
		if ( $default_theme->load() ) {
			$default_theme->delete();
		}

		// Remove the Test Theme, if it exists.
		$test_theme = new \Apple_Exporter\Theme;
		$test_theme->set_name( 'Test Theme' );
		if ( $test_theme->load() ) {
			$test_theme->delete();
		}
	}

	/**
	 * Ensures that the default theme is created properly.
	 *
	 * @access public
	 */
	public function testCreateDefaultTheme() {

		// Create the default theme.
		$this->createDefaultTheme();

		// Ensure the default theme was created.
		$vanilla_theme = new \Apple_Exporter\Theme;
		$default_theme = new \Apple_Exporter\Theme;
		$default_theme->set_name( __( 'Default', 'apple-news' ) );
		$this->assertEquals(
			__( 'Default', 'apple-news' ),
			\Apple_Exporter\Theme::get_active_theme_name()
		);
		$this->assertTrue( $default_theme->load() );
		$this->assertEquals(
			$vanilla_theme->all_settings(),
			$default_theme->all_settings()
		);
		$this->assertTrue(
			in_array(
				__( 'Default', 'apple-news' ),
				\Apple_Exporter\Theme::get_registry(),
				true
			)
		);
	}

	/**
	 * Ensures themes are able to be created properly.
	 *
	 * @access public
	 */
	public function testCreateTheme() {

		// Set the POST data required to create a new theme.
		$name = 'Test Theme';
		$this->createNewTheme( $name, array( 'body_color' => '#ff0000' ) );

		// Check that the data was saved properly.
		$default_theme = new \Apple_Exporter\Theme;
		$expected_settings = $default_theme->all_settings();
		$expected_settings['body_color'] = '#ff0000';
		$test_theme = new \Apple_Exporter\Theme;
		$test_theme->set_name( 'Test Theme' );
		$test_theme->load();
		$this->assertEquals( $expected_settings, $test_theme->all_settings() );
	}

	/**
	 * Ensure that a theme can be deleted.
	 */
	public function testDeleteTheme() {

		// Create the default theme.
		$this->createDefaultTheme();

		// Name and create a new theme.
		$name = 'Test Theme';
		$this->createNewTheme( $name );

		// Ensure both themes exist.
		$this->assertTrue(
			in_array(
				__( 'Default', 'apple-news' ),
				\Apple_Exporter\Theme::get_registry(),
				true
			)
		);
		$this->assertTrue(
			in_array(
				$name,
				\Apple_Exporter\Theme::get_registry(),
				true
			)
		);
		$default_theme = new \Apple_Exporter\Theme;
		$default_theme->set_name( __( 'Default', 'apple-news' ) );
		$this->assertTrue( $default_theme->load() );
		$test_theme = new \Apple_Exporter\Theme;
		$test_theme->set_name( 'Test Theme' );
		$this->assertTrue( $test_theme->load() );

		// Delete the test theme.
		$nonce = wp_create_nonce( 'apple_news_themes' );
		$_POST['apple_news_theme_name'] = $name;
		$_POST['action'] = 'apple_news_delete_theme';
		$_POST['apple_news_theme'] = $name;
		$_POST['page'] = 'apple-news-themes';
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-themes';
		$_REQUEST['_wpnonce'] = $nonce;
		$_REQUEST['action'] = $_POST['action'];
		$this->themes->action_router();

		// Ensure that the test theme does not exist after deletion.
		$this->assertFalse(
			in_array(
				$name,
				\Apple_Exporter\Theme::get_registry(),
				true
			)
		);
		$this->assertFalse( $test_theme->load() );
	}

	/**
	 * Tests a theme import.
	 *
	 * @access public
	 */
	public function testImportTheme() {

		// Setup.
		$advertisement_json = array(
			'role' => 'banner_advertisement',
			'bannerType' => 'double_height',
		);
		$import_settings = array(
			'layout_margin' => 100,
			'layout_gutter' => 20,
			'json_templates' => array(
				'advertisement' => array(
					'json' => $advertisement_json,
				),
			),
			'theme_name' => 'Test Import Theme',
		);

		// Test.
		$this->assertTrue( $this->themes->import_theme( $import_settings ) );
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( 'Test Import Theme' );
		$this->assertTrue( $theme->load() );
		$theme_settings = $theme->all_settings();
		$this->assertEquals( 100, $theme_settings['layout_margin'] );
		$this->assertEquals( 20, $theme_settings['layout_gutter'] );
		$this->assertEquals(
			$advertisement_json,
			$theme_settings['json_templates']['advertisement']['json']
		);

		// Cleanup.
		$theme->delete();
	}

	/**
	 * Tests a theme import with an invalid JSON spec.
	 *
	 * @access public
	 */
	public function testImportThemeInvalidJSON() {

		// Setup.
		$invalid_json = array(
		    'role' => 'audio',
		    'URL' => '#invalid#',
		);
		$import_settings = array(
			'layout_margin' => 100,
			'layout_gutter' => 20,
			'json_templates' => array(
				'audio' => array(
					'json' => $invalid_json,
				),
			),
			'theme_name' => 'Test Import Theme',
		);

		// Test.
		$this->assertInternalType(
			'string',
			$this->themes->import_theme( $import_settings )
		);
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( 'Test Import Theme' );
		$this->assertFalse( $theme->load() );
	}

	/**
	 * Ensures that JSON customizations from versions prior to 1.3.0 are migrated to
	 * the theme(s).
	 *
	 * @access public
	 */
	public function testJSONMigrateToTheme() {

		// Create the default theme and the Test Theme.
		$this->createDefaultTheme();
		$this->createNewTheme( 'Test Theme' );

		// Define the default-body JSON override we will be testing against.
		$default_body = array(
			'textAlignment' => 'left',
			'fontName' => '#body_font#',
			'fontSize' => '#body_size#',
			'tracking' => '#body_tracking#',
			'lineHeight' => '#body_line_height#',
			'textColor' => '#body_color#',
			'linkStyle' => array(
				'textColor' => '#body_link_color#',
			),
			'paragraphSpacingBefore' => 24,
			'paragraphSpacingAfter' => 24,
		);

		// Add legacy format JSON overrides.
		update_option(
			'apple_news_json_body',
			array( 'apple_news_json_default-body' => $default_body ),
			false
		);

		// Run the function to trigger the settings migration.
		$apple_news = new Apple_News;
		$apple_news->migrate_custom_json_to_themes();

		// Ensure that the default-body override was applied to the themes.
		$default_theme = new \Apple_Exporter\Theme;
		$default_theme->set_name( __( 'Default', 'apple-news' ) );
		$this->assertTrue( $default_theme->load() );
		$test_theme = new \Apple_Exporter\Theme;
		$test_theme->set_name( 'Test Theme' );
		$this->assertTrue( $test_theme->load() );
		$default_settings = $default_theme->all_settings();
		$test_theme_settings = $test_theme->all_settings();
		$this->assertEquals(
			$default_body,
			$default_settings['json_templates']['body']['default-body']
		);
		$this->assertEquals(
			$default_body,
			$test_theme_settings['json_templates']['body']['default-body']
		);
	}

	/**
	 * Ensures that a custom spec is saved properly.
	 *
	 * @access public
	 */
	public function testJSONSaveCustomSpec() {

		// Setup.
		$this->createDefaultTheme();
		$json = <<<JSON
{
    "role": "banner_advertisement",
    "bannerType": "double_height"
}
JSON;
		$nonce = wp_create_nonce( 'apple_news_json' );
		$_POST['apple_news_theme'] = \Apple_Exporter\Theme::get_active_theme_name();
		$_POST['apple_news_component'] = 'Advertisement';
		$_POST['apple_news_action'] = 'apple_news_save_json';
		$_POST['apple_news_json_json'] = $json;
		$_POST['page'] = 'apple-news-json';
		$_POST['redirect'] = false;
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-json';
		$_REQUEST['_wpnonce'] = $nonce;
		$_REQUEST['apple_news_action'] = $_POST['apple_news_action'];

		// Trigger the save operation.
		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		// Test.
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( \Apple_Exporter\Theme::get_active_theme_name() );
		$this->assertTrue( $theme->load() );
		$theme_settings = $theme->all_settings();
		$stored_json = wp_json_encode(
			$theme_settings['json_templates']['advertisement']['json'],
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
		$this->createDefaultTheme();
		$invalid_json = <<<JSON
{
    "role": "audio",
    "URL": "#invalid#"
}
JSON;
		$nonce = wp_create_nonce( 'apple_news_json' );
		$_POST['apple_news_theme'] = \Apple_Exporter\Theme::get_active_theme_name();
		$_POST['apple_news_component'] = 'Audio';
		$_POST['apple_news_action'] = 'apple_news_save_json';
		$_POST['apple_news_json_json'] = $invalid_json;
		$_POST['page'] = 'apple-news-json';
		$_POST['redirect'] = false;
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-json';
		$_REQUEST['_wpnonce'] = $nonce;
		$_REQUEST['apple_news_action'] = $_POST['apple_news_action'];

		// Trigger the save operation.
		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		// Test.
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( \Apple_Exporter\Theme::get_active_theme_name() );
		$this->assertTrue( $theme->load() );
		$theme_settings = $theme->all_settings();
		$this->assertTrue( empty( $theme_settings['json_templates'] ) );
	}

	/**
	 * Ensure that valid tokens are saved in the custom JSON spec.
	 *
	 * @access public
	 */
	public function testJSONSaveValidTokens() {

		// Setup.
		$this->createDefaultTheme();
		$json = <<<JSON
{
    "role": "audio",
    "URL": "http://someurl.com",
    "style": {
        "backgroundColor": "#body_background_color#"
    }
}
JSON;
		$nonce = wp_create_nonce( 'apple_news_json' );
		$_POST['apple_news_theme'] = \Apple_Exporter\Theme::get_active_theme_name();
		$_POST['apple_news_component'] = 'Audio';
		$_POST['apple_news_action'] = 'apple_news_save_json';
		$_POST['apple_news_json_json'] = $json;
		$_POST['page'] = 'apple-news-json';
		$_POST['redirect'] = false;
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-json';
		$_REQUEST['_wpnonce'] = $nonce;
		$_REQUEST['apple_news_action'] = $_POST['apple_news_action'];

		// Trigger the spec save.
		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		// Test.
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( \Apple_Exporter\Theme::get_active_theme_name() );
		$this->assertTrue( $theme->load() );
		$theme_settings = $theme->all_settings();
		$stored_json = stripslashes(
			wp_json_encode(
				$theme_settings['json_templates']['audio']['json'],
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
		$this->createDefaultTheme();
		$json = <<<JSON
{
    "columnStart": "#body_offset#",
    "columnSpan": "#body_column_span#",
    "margin": {
        "top": 50,
        "bottom": "#layout_gutter#"
    }
}
JSON;
		$nonce = wp_create_nonce( 'apple_news_json' );
		$_POST['apple_news_theme'] = \Apple_Exporter\Theme::get_active_theme_name();
		$_POST['apple_news_component'] = 'Body';
		$_POST['apple_news_action'] = 'apple_news_save_json';
		$_POST['apple_news_json_body-layout'] = $json;
		$_POST['page'] = 'apple-news-json';
		$_POST['redirect'] = false;
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-json';
		$_REQUEST['_wpnonce'] = $nonce;
		$_REQUEST['apple_news_action'] = $_POST['apple_news_action'];

		// Trigger the spec save.
		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		// Test.
		$settings = new Admin_Apple_Settings();
		$content  = new Exporter_Content(
			1,
			__( 'My Title', 'apple-news' ),
			'<p>' . __( 'Hello, World!', 'apple-news' ) . '</p>'
		);
		$exporter = new Exporter( $content, null, $settings->fetch_settings() );
		$json = json_decode( $exporter->export(), true );
		$this->assertEquals(
			20,
			$json['componentLayouts']['body-layout']['margin']['bottom']
		);
		$this->assertEquals(
			50,
			$json['componentLayouts']['body-layout']['margin']['top']
		);
	}

	/**
	 * Ensure that postmeta in a custom spec is used on render.
	 *
	 * @access public
	 */
	public function testJSONUseCustomSpecPostmeta() {

		// Setup.
		$this->createDefaultTheme();
		$json = <<<JSON
{
    "columnStart": "#body_offset#",
    "columnSpan": "#postmeta.apple_news_column_span#",
    "margin": {
        "top": 50,
        "bottom": 50
    }
}
JSON;
		$nonce = wp_create_nonce( 'apple_news_json' );
		$_POST['apple_news_theme'] = \Apple_Exporter\Theme::get_active_theme_name();
		$_POST['apple_news_component'] = 'Body';
		$_POST['apple_news_action'] = 'apple_news_save_json';
		$_POST['apple_news_json_body-layout'] = $json;
		$_POST['page'] = 'apple-news-json';
		$_POST['redirect'] = false;
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-json';
		$_REQUEST['_wpnonce'] = $nonce;
		$_REQUEST['apple_news_action'] = $_POST['apple_news_action'];

		// Trigger the spec save.
		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();

		// Test.
		$post_id = $this->factory->post->create();
		$settings = new Admin_Apple_Settings();
		$content  = new Exporter_Content(
			$post_id,
			__( 'My Title', 'apple-news' ),
			'<p>' . __( 'Hello, World!', 'apple-news' ) . '</p>'
		);
		add_post_meta( $post_id, 'apple_news_column_span', 2, true );
		$exporter = new Exporter( $content, null, $settings->fetch_settings() );
		$json = json_decode( $exporter->export(), true );
		$this->assertEquals(
			2,
			$json['componentLayouts']['body-layout']['columnSpan']
		);
	}

	/**
	 * Ensure that a new theme can be set as the active theme.
	 *
	 * @access public
	 */
	public function testSetTheme() {

		// Create the default theme.
		$this->createDefaultTheme();

		// Create a test theme with altered settings.
		$this->createNewTheme( 'Test Theme', array( 'layout_margin' => 50 ) );

		// Simulate the form submission to set the theme.
		$nonce = wp_create_nonce( 'apple_news_themes' );
		$_POST['action'] = 'apple_news_set_theme';
		$_POST['apple_news_active_theme'] = 'Test Theme';
		$_POST['page'] = 'apple-news-themes';
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-themes';
		$_REQUEST['_wpnonce'] = $nonce;
		$_REQUEST['action'] = $_POST['action'];
		$this->themes->action_router();

		// Check that the theme got set.
		$this->assertEquals(
			'Test Theme',
			\Apple_Exporter\Theme::get_active_theme_name()
		);
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( 'Test Theme' );
		$this->assertTrue( $theme->load() );
		$theme_settings = $theme->all_settings();
		$this->assertEquals( 50, $theme_settings['layout_margin'] );
	}
}
