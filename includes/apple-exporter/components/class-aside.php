<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Aside class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

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
		$element    = $this->get_root_element( $html );
		$inner_html = $this->get_inner_html_for_element( $element );

		// Convert the sidebar HTML into components.
		$inner_json = $this->get_json_for_html( $inner_html );

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

	/**
	 * Given a DOMElement object, returns inner HTML for the element.
	 *
	 * @param DOMElement $element The element to process into inner HTML.
	 *
	 * @return string The inner HTML for the element.
	 */
	protected function get_inner_html_for_element( DOMElement $element ): string {
		$html = '';
		foreach ( $element->childNodes as $node ) {
			$html .= $element->ownerDocument->saveHTML( $node );
		}

		return trim( $html );
	}

	/**
	 * Given arbitrary HTML, generates JSON for it, treating it as a subset of the
	 * article HTML for the currently exporting article.
	 *
	 * @param string $html The HTML to convert to JSON.
	 *
	 * @return array The Apple News Format document JSON, expressed as an associative array.
	 */
	protected function get_json_for_html( string $html ): array {
		// Add a placeholder paragraph to the end of the HTML to avoid body-layout-last getting set.
		$html .= '<p>APPLE_MUSIC_BODY_LAYOUT_LAST_PLACEHOLDER</p>';

		$this->html = $html;

		// Hook up a filter to replace the post's content with the arbitrary HTML we just cached.
		add_filter( 'apple_news_exporter_content', [ $this, 'get_html' ] );

		// For this request, disable all components (e.g., title, byline, etc) and turn off dropcap because we don't need them for what we're doing here.
		$theme               = Theme::get_used();
		$old_dropcap         = $theme->get_value( 'initial_dropcap' );
		$old_component_order = $theme->get_value( 'meta_component_order' );
		$theme->set_value( 'initial_dropcap', 'no' );
		$theme->set_value( 'meta_component_order', [] );
		$theme->use_this();

		// Run the export, which will pick up the arbitrary HTML via the hook.
		$export = new Export(
			$this->settings,
			get_the_ID(),
			Admin_Apple_Sections::get_sections_for_post( get_the_ID() )
		);
		$json   = json_decode( $export->perform(), true );

		// Remove our placeholder component.
		array_pop( $json['components'] );

		// Unset the arbitrary HTML.
		$this->html = '';

		// Remove the filter, now that we're done with this targeted export.
		remove_filter( 'apple_news_exporter_content', [ $this, 'get_html' ] );

		// Reset meta component order.
		$theme->set_value( 'initial_dropcap', $old_dropcap );
		$theme->set_value( 'meta_component_order', $old_component_order );
		$theme->use_this();

		return $json;
	}

	/**
	 * Override the html with the current html.
	 *
	 * @param string $content The content to replace.
	 * @return string
	 */
	public function get_html( $content ) {
		return $this->html;
	}

	/**
	 * Given arbitrary HTML, returns a DOMElement representation of the root node of the given HTML.
	 *
	 * @param string $html The HTML to convert to a DOMElement.
	 *
	 * @return DOMElement A DOMElement representing the root node of the given HTML.
	 */
	protected function get_root_element( string $html ): DOMElement {
		$root_element = null;
		$body_element = $this->get_xpath_for_html( $html )->query( '//body' )->item( 0 );
		if ( $body_element instanceof DOMElement ) {
			$root_element = $body_element->childNodes->item( 0 );
		}

		return $root_element instanceof DOMElement
			? $root_element
			: new DOMElement( 'root' );
	}

	/**
	 * Given arbitrary HTML, loads it into a DOMXPath object as a property on this class.
	 *
	 * @param string $html The HTML to load into a DOMXPath object.
	 *
	 * @return ?DOMXPath The DOMXPath object for the provided HTML.
	 */
	private function get_xpath_for_html( string $html ): DOMXPath {
		libxml_use_internal_errors( true );
		$doc = new DOMDocument();
		// @source https://davidwalsh.name/domdocument-utf8-problem
		$doc->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		$xpath = new DOMXPath( $doc );
		libxml_clear_errors();

		return $xpath instanceof DOMXPath
			? $xpath
			: new DOMXPath( new DOMDocument() );
	}
}
