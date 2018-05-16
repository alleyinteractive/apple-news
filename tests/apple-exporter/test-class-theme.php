<?php
/**
 * Publish to Apple News Tests: Theme_Test class
 *
 * Contains a class to test the functionality of the Apple_Exporter\Theme class.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Theme;

/**
 * A class used to test the functionality of the Apple_Exporter\Theme class.
 *
 * @since 1.3.0
 */
class Theme_Test extends WP_UnitTestCase {

	/**
	 * An example filter for the font list.
	 *
	 * @param array $fonts An array of fonts to include.
	 * @access public
	 * @return array The modified font array.
	 */
	public function filter_apple_news_fonts_list( $fonts ) {
		$fonts[] = 'ExampleFont';
		return $fonts;
	}

	/**
	 * Ensure that custom JSON can be deleted.
	 *
	 * @see Apple_Exporter\Theme::load()
	 *
	 * @access public
	 */
	public function testDeleteCustomJSON() {

		// Define custom JSON to be removed.
		$theme_settings = array(
			'json_templates' => array(
				'body' => array(
					'default-body' => array(
						'hyphenation' => false,
					),
				),
			),
		);

		// Create a theme and load the custom settings.
		$theme = new Theme;
		$theme->load( $theme_settings );

		// Ensure the custom JSON templates exist within the theme.
		$this->assertSame(
			$theme_settings['json_templates'],
			$theme->get_value( 'json_templates' )
		);

		// Remove the custom JSON templates and update the theme.
		unset( $theme_settings['json_templates'] );
		$theme->load( $theme_settings );

		// Ensure the custom JSON was removed from the theme.
		$this->assertSame(
			array(),
			$theme->get_value( 'json_templates' )
		);
	}

	/**
	 * Tests the 'apple_news_fonts_list' filter.
	 *
	 * @access public
	 */
	public function testFontFilter() {

		// Test before filter.
		$this->assertFalse( in_array(
			'ExampleFont',
			\Apple_Exporter\Theme::get_fonts(),
			true
		) );

		// Add the filter.
		add_filter(
			'apple_news_fonts_list',
			array( $this, 'filter_apple_news_fonts_list' )
		);

		// Test.
		$this->assertTrue( in_array(
			'ExampleFont',
			\Apple_Exporter\Theme::get_fonts(),
			true
		) );

		// Teardown.
		remove_filter(
			'apple_news_fonts_list',
			array( $this, 'filter_apple_news_fonts_list' )
		);
	}

	/**
	 * Tests the functionality of the get_registry function.
	 *
	 * @see Apple_Exporter\Theme::get_registry()
	 *
	 * @access public
	 */
	public function testGetRegistry() {

		// Setup.
		update_option(
			Theme::INDEX_KEY,
			array( 'Theme 3', 'Theme 2', 'Theme 1' ),
			false
		);
		update_option( Theme::ACTIVE_KEY, 'Theme 2', false );

		// Ensure the get_registry function returns in sorted order with active 1st.
		$this->assertSame(
			array( 'Theme 2', 'Theme 1', 'Theme 3' ),
			Theme::get_registry()
		);
	}
}
