<?php
/**
 * Publish to Apple News Tests: Admin_Apple_Themes_Test class
 *
 * Contains a class which is used to test Admin_Apple_Themes.
 *
 * @package Apple_News
 * @subpackage Tests
 */

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

		// Force creation of a default theme.
		$themes = new \Admin_Apple_Themes();
		$themes->setup_theme_pages();
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

		// Merge any provided settings with default settings.
		$current_settings = $this->settings->all();
		$defaults = $this->getFormattingSettings( $current_settings );
		$settings = wp_parse_args( $settings, $defaults );

		// Add all of these to the $_POST object.
		foreach ( $settings as $key => $value ) {
			$_POST[ $key ] = $value;
		}

		// Invoke the save operation in the themes class.
		$themes = new \Admin_Apple_Themes();
		$themes->action_router();
	}

	/**
	 * A helper function to extract formatting settings from general settings.
	 *
	 * @param array $all_settings An array of settings to filter.
	 *
	 * @access public
	 * @return array The filtered set of formatting settings.
	 */
	public function getFormattingSettings( $all_settings ) {

		// Get only formatting settings.
		$formatting = new Admin_Apple_Settings_Section_Formatting( '' );
		$formatting_settings = $formatting->get_settings();
		$formatting_settings_keys = array_keys( $formatting_settings );

		// Loop through formatting settings and extract them from provided settings.
		$filtered_settings = array();
		foreach ( $formatting_settings_keys as $key ) {
			if ( isset( $all_settings[ $key ] ) ) {
				$filtered_settings[ $key ] = $all_settings[ $key ];
			}
		}

		return $filtered_settings;
	}

	/**
	 * Actions to be run before each test in this class.
	 *
	 * @access public
	 */
	public function setup() {
		parent::setup();

		// Remove the Default and Test Theme themes, if they exist.
		$themes = new \Admin_Apple_Themes();
		delete_option( $themes->theme_key_from_name( 'Default' ) );
		delete_option( $themes->theme_key_from_name( 'Test Theme' ) );

		// Cache default settings for future use.
		$this->settings = new Settings();
	}

	/**
	 * Ensures that the default theme is created properly.
	 *
	 * @access public
	 */
	public function testCreateDefaultTheme() {

		// Setup.
		$themes = new \Admin_Apple_Themes();

		// Create the default theme.
		$this->createDefaultTheme();

		// Ensure the default theme was created.
		$this->assertEquals(
			__( 'Default', 'apple-news' ),
			get_option( $themes::THEME_ACTIVE_KEY )
		);
		$this->assertEquals(
			$this->getFormattingSettings( $this->settings->all() ),
			get_option( $themes->theme_key_from_name( __( 'Default', 'apple-news' ) ) )
		);
		$this->assertEquals(
			array( __( 'Default', 'apple-news' ) ),
			get_option( $themes::THEME_INDEX_KEY )
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
		$this->createNewTheme( $name );

		// Check that the data was saved properly.
		$themes = new \Admin_Apple_Themes();
		$current_settings = $this->settings->all();

		// Array diff against the option value.
		$diff_settings = $this->getFormattingSettings( $current_settings );
		$new_theme_settings = get_option( $themes->theme_key_from_name( $name ) );
		$this->assertEquals( $diff_settings, $new_theme_settings );
	}

	/**
	 * Ensure that a theme can be deleted.
	 */
	public function testDeleteTheme() {

		// Setup.
		$themes = new \Admin_Apple_Themes();

		// Create the default theme.
		$this->createDefaultTheme();

		// Name and create a new theme.
		$name = 'Test Theme';
		$this->createNewTheme( $name );

		// Ensure both themes exist.
		$this->assertEquals(
			array( __( 'Default', 'apple-news' ), $name ),
			get_option( $themes::THEME_INDEX_KEY )
		);
		$this->assertNotEmpty(
			get_option( $themes->theme_key_from_name( __( 'Default', 'apple-news' ) ) )
		);
		$this->assertNotEmpty( get_option( $themes->theme_key_from_name( $name ) ) );

		// Delete the test theme.
		$nonce = wp_create_nonce( 'apple_news_themes' );
		$_POST['apple_news_theme_name'] = $name;
		$_POST['action'] = 'apple_news_delete_theme';
		$_POST['apple_news_theme'] = $name;
		$_POST['page'] = 'apple-news-themes';
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-themes';
		$_REQUEST['_wpnonce'] = $nonce;
		$themes->action_router();

		// Ensure only the default theme exists after deletion.
		$this->assertEquals(
			array( __( 'Default', 'apple-news' ) ),
			get_option( $themes::THEME_INDEX_KEY )
		);
		$this->assertEmpty( get_option( $themes->theme_key_from_name( $name ) ) );
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
		$themes = new \Admin_Apple_Themes();
		$default_settings = get_option(
			$themes->theme_key_from_name( __( 'Default', 'apple-news' ) )
		);
		$test_theme_settings = get_option(
			$themes->theme_key_from_name( 'Test Theme' )
		);
		$this->assertEquals(
			$default_settings['json_templates']['body']['default-body'],
			$default_body
		);
		$this->assertEquals(
			$test_theme_settings['json_templates']['body']['default-body'],
			$default_body
		);
	}

	/**
	 * Ensure that a new theme can be set as the active theme.
	 *
	 * @access public
	 */
	public function testSetTheme() {

		// Setup.
		$themes = new \Admin_Apple_Themes();

		// Create the default theme.
		$this->createDefaultTheme();

		// Name a new theme.
		$name = 'Test Theme';

		// Get Apple News settings and alter a setting to create a new theme.
		$settings_obj = new \Admin_Apple_Settings();
		$settings = $settings_obj->fetch_settings()->all();
		$settings['layout_margin'] = 50;
		$settings_obj->save_settings( $settings );
		$this->createNewTheme( $name );

		// Simulate the form submission to set the theme.
		$nonce = wp_create_nonce( 'apple_news_themes' );
		$_POST['action'] = 'apple_news_set_theme';
		$_POST['apple_news_active_theme'] = $name;
		$_POST['page'] = 'apple-news-themes';
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-themes';
		$_REQUEST['_wpnonce'] = $nonce;
		$themes->action_router();

		// Check that the theme got set.
		$this->assertEquals( $name, get_option( $themes::THEME_ACTIVE_KEY ) );
		$current_settings = $settings_obj->fetch_settings();
		$this->assertEquals( 50, $current_settings['layout_margin'] );
	}
}
