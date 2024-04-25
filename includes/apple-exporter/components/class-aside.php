<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Aside class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

use Apple_Exporter\Export_String;
use Apple_Exporter\Exporter_Content;
use Apple_Exporter\Settings;
use Apple_Exporter\Theme;
use Apple_Actions\Index\Export;
use Admin_Apple_Sections;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;

/**
 * A component to handle aside content.
 *
 * @since 2.5.0
 */
class Aside extends Component {
	/**
	 * Store the html for the component.
	 */
	protected $html;

	/**
	 * Store the post id for the component.
	 */
	protected static $post_id;

	/**
	 * Look for node matches for this component.
	 *
	 * @param DOMElement $node The node to examine for matches.
	 * @access public
	 * @return DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		// Note: we can't use the component get_setting method or settings array here, because this is a static class.
		$class = get_option( 'apple_news_settings' )['aside_component_class'] ?? '';
		if ( empty( $class ) ) {
			return null;
		}

		if (
			self::node_has_class( $node, $class )
		) {
			return $node;
		}

		return null;
	}

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role'       => 'aside',
				// 'layout'     => 'aside-layout',
				'components' => '#components#',
			]
		);

		$this->register_layout(
			'aside-layout',
			__( 'Aside Layout', 'apple-news' ),
			[
				'ignoreDocumentMargin' => true,
				'ignoreDocumentGutter' => true,
				'columnStart'          => 1,
				'columnSpan'           => 4,
			]
		);

		$this->register_style(
			'aside',
			'aside',
			[],
			'textStyle'
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 * @access protected
	 */
	protected function build( $html ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$content    = new Export_String();
		$element    = $content->get_root_element( $html );
		$inner_html = $content->get_inner_html_for_element( $element );

		$content->set_settings( $this->settings );
		$content->set_post_id( get_the_ID() );

		// Convert the sidebar HTML into components.
		$inner_json = $content->get_json_for_html( $inner_html );

		// Loop over the generated JSON and further filter the components.
		$components = $inner_json['components'] ?? [];

		// Ensure any non-sidebar layouts are registered.
		foreach ( $inner_json['componentLayouts'] ?? [] as $layout_name => $layout_json ) {
			$this->layouts->register_layout( $layout_name, $layout_json );
		}

		// Ensure any non-sidebar text styles are registered.
		foreach ( $inner_json['componentTextStyles'] ?? [] as $style_name => $style_json ) {
			// $this->styles->register_style( $style_name, $style_json );
		}

		$this->register_json(
			'json',
			[
				'#components#'       => $components,
			]
		);
	}
}
