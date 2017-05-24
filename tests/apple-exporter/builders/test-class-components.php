<?php
/**
 * Publish to Apple News Tests: Component_Tests class
 *
 * Contains a class which is used to test \Apple_Exporter\Builders\Components.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use \Apple_Exporter\Component_Factory;
use \Apple_Exporter\Exporter_Content;
use \Apple_Exporter\Settings;
use \Apple_Exporter\Workspace;
use \Apple_Exporter\Builders\Components;
use \Apple_Exporter\Builders\Component_Layouts;
use \Apple_Exporter\Builders\Component_Text_Styles;

/**
 * A class which is used to test the \Apple_Exporter\Builders\Components class.
 */
class Component_Tests extends WP_UnitTestCase {

	/**
	 * A data provider for the meta component ordering test.
	 *
	 * @see self::testMetaComponentOrdering()
	 *
	 * @access public
	 * @return array An array of arguments to pass to the test function.
	 */
	public function dataMetaComponentOrdering() {
		return array(
			array(
				array( 'cover', 'title', 'byline' ),
				array( 'header', 'container' ),
				array( 'title', 'byline' ),
			),
			array(
				array( 'byline', 'cover', 'title' ),
				array( 'byline', 'header', 'container' ),
				array( 'title' ),
			),
			array(
				array( 'title', 'byline' ),
				array( 'title', 'byline' ),
				array(),
			),
			array(
				array( 'cover', 'byline' ),
				array( 'header', 'container' ),
				array( 'byline' ),
			),
			array(
				array( 'cover', 'title' ),
				array( 'header', 'container' ),
				array( 'title' ),
			),
		);
	}

	/**
	 * Actions to be run before each test function.
	 *
	 * @access public
	 */
	public function setup() {

		// Setup.
		$themes = new Admin_Apple_Themes;
		$themes->setup_theme_pages();
		$file = dirname( dirname( __DIR__ ) ) . '/data/test-image.jpg';
		$cover = $this->factory->attachment->create_upload_object( $file );
		$this->settings = new Settings;
		$this->content = new Exporter_Content(
			1,
			'My Title',
			'<p>Hello, World!</p>',
			null,
			$cover,
			'Author Name'
		);
		$this->styles = new Component_Text_Styles( $this->content, $this->settings );
		$this->layouts = new Component_Layouts( $this->content, $this->settings );
		Component_Factory::initialize(
			new Workspace( 1 ),
			$this->settings,
			$this->styles,
			$this->layouts
		);
	}

	/**
	 * Actions to be run after every test.
	 *
	 * @access public
	 */
	public function tearDown() {
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( \Apple_Exporter\Theme::get_active_theme_name() );
		$theme->delete();
	}

	/**
	 * Ensures that the specified component order is respected.
	 *
	 * @dataProvider dataMetaComponentOrdering
	 *
	 * @param array $order The meta component order setting to use.
	 * @param array $expected The expected component order after compilation.
	 * @param array $components The expected container components, in order.
	 *
	 * @access public
	 */
	public function testMetaComponentOrdering( $order, $expected, $components ) {

		// Setup.
		$theme = \Apple_Exporter\Theme::get_used();
		$settings = $theme->all_settings();
		$settings['enable_advertisement'] = 'no';
		$settings['meta_component_order'] = $order;
		$theme->load( $settings );
		$this->assertTrue( $theme->save() );
		$builder = new Components( $this->content, $this->settings );
		$result = $builder->to_array();

		// Test.
		for ( $i = 0; $i < count( $expected ); $i ++ ) {
			$this->assertEquals( $expected[ $i ], $result[ $i ]['role'] );
			if ( 'container' === $result[ $i ]['role'] ) {
				for ( $j = 0; $j < count( $components ); $j ++ ) {
					$this->assertEquals(
						$components[ $j ],
						$result[ $i ]['components'][ $j ]['role']
					);
				}
			}
		}
	}
}
