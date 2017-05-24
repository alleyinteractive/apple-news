<?php

use Apple_Exporter\Exporter_Content as Exporter_Content;
use Apple_Exporter\Settings as Settings;
use Apple_Exporter\Builders\Advertising_Settings as Advertising_Settings;

class Test_Class_Advertising_Settings extends WP_UnitTestCase {

	public function setup() {
		$themes = new Admin_Apple_Themes;
		$themes->setup_theme_pages();
		$this->theme = new \Apple_Exporter\Theme;
		$this->theme->set_name( \Apple_Exporter\Theme::get_active_theme_name() );
		$this->theme->load();
		$this->settings = new Settings();
		$this->content = new Exporter_Content( 1, 'My Title', '<p>Hello, World!</p>' );
	}

	/**
	 * Cleanup to be run after every execution.
	 *
	 * @access public
	 */
	public function tearDown() {
		$this->theme = new \Apple_Exporter\Theme;
		$this->theme->set_name( \Apple_Exporter\Theme::get_active_theme_name() );
		$this->assertTrue( $this->theme->save() );
	}

	public function testDefaultAdSettings() {
		$builder = new Advertising_Settings( $this->content, $this->settings );
		$result = $builder->to_array();
		$this->assertEquals( 2, count( $result ) );
		$this->assertEquals( 1, $result['frequency'] );
		$this->assertEquals( 1, count( $result['layout'] ) );
		$this->assertEquals( 15, $result['layout']['margin']['top'] );
		$this->assertEquals( 15, $result['layout']['margin']['bottom'] );
	}

	public function testNoAds() {

		// Setup.
		$settings = $this->theme->all_settings();
		$settings['enable_advertisement'] = 'no';
		$this->theme->load( $settings );
		$this->assertTrue( $this->theme->save() );

		// Test.
		$builder = new Advertising_Settings( $this->content, $this->settings );
		$result = $builder->to_array();
		$this->assertEquals( 0, count( $result ) );
	}

	public function testCustomAdFrequency() {

		// Setup.
		$settings = $this->theme->all_settings();
		$settings['ad_frequency'] = 5;
		$this->theme->load( $settings );
		$this->assertTrue( $this->theme->save() );

		// Test.
		$builder = new Advertising_Settings( $this->content, $this->settings );
		$result  = $builder->to_array();
		$this->assertEquals( 2, count( $result ) );
		$this->assertEquals( 5, $result['frequency'] );
		$this->assertEquals( 1, count( $result['layout'] ) );
		$this->assertEquals( 15, $result['layout']['margin']['top'] );
		$this->assertEquals( 15, $result['layout']['margin']['bottom'] );
	}

	public function testCustomAdMargin() {

		// Setup.
		$settings = $this->theme->all_settings();
		$settings['ad_margin'] = 20;
		$this->theme->load( $settings );
		$this->assertTrue( $this->theme->save() );

		// Test.
		$builder = new Advertising_Settings( $this->content, $this->settings );
		$result  = $builder->to_array();
		$this->assertEquals( 2, count( $result ) );
		$this->assertEquals( 1, $result['frequency'] );
		$this->assertEquals( 1, count( $result['layout'] ) );
		$this->assertEquals( 20, $result['layout']['margin']['top'] );
		$this->assertEquals( 20, $result['layout']['margin']['bottom'] );
	}
}
