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
