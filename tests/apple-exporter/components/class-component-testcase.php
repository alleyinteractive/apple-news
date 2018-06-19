<?php

use Apple_Exporter\Exporter_Content as Exporter_Content;
use Apple_Exporter\Settings as Settings;
use Apple_Exporter\Builders\Component_Layouts as Component_Layouts;
use Apple_Exporter\Builders\Component_Text_Styles as Component_Text_Styles;
use Apple_Exporter\Builders\Component_Styles as Component_Styles;

abstract class Component_TestCase extends WP_UnitTestCase {

	protected $prophet;

	/**
	 * Actions to be run before every test.
	 *
	 * @access public
	 */
	public function setup() {
		$themes = new Admin_Apple_Themes;
		$themes->setup_theme_pages();
		$this->prophet          = new \Prophecy\Prophet;
		$this->settings         = new Settings();
		$this->content          = new Exporter_Content( 1, __( 'My Title', 'apple-news' ), '<p>' . __( 'Hello, World!', 'apple-news' ) . '</p>' );
		$this->styles           = new Component_Text_Styles( $this->content, $this->settings );
		$this->layouts          = new Component_Layouts( $this->content, $this->settings );
		$this->component_styles = new Component_Styles( $this->content, $this->settings );
	}

	/**
	 * Actions to be run after every test.
	 *
	 * @access public
	 */
	public function tearDown() {
		$this->prophet->checkPredictions();
		$theme = new \Apple_Exporter\Theme();
		$theme->set_name( \Apple_Exporter\Theme::get_active_theme_name() );
		$theme->save();
	}

	protected function build_node( $html ) {
		// Because PHP's DomDocument doesn't like HTML5 tags, ignore errors.
		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
		libxml_clear_errors( true );

		// Find the first-level nodes of the body tag.
		return $dom->getElementsByTagName( 'body' )->item( 0 )->childNodes->item( 0 );
	}

	/**
	 * A function to ensure that tokens are replaced in a JSON string.
	 *
	 * @param string $json The JSON to check for unreplaced tokens.
	 *
	 * @access protected
	 */
	protected function ensure_tokens_replaced( $json ) {
		preg_match( '/"#[^"#]+#"/', $json, $matches );
		$this->assertEmpty( $matches );
	}
}
