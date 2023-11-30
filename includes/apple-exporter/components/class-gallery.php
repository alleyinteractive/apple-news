<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\Gallery class
 *
 * Contains a class which is used to transform galleries into Apple News format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.2.0
 */

namespace Apple_Exporter\Components;

use Apple_Exporter\Exporter_Content;
use DOMDocument;

/**
 * A class to translate the output of [gallery] shortcodes into Apple News format.
 *
 * An image gallery is just a container with 'gallery' class and some images
 * inside. The container should be a div, but can be anything as long as it has
 * a 'gallery' class.
 *
 * @since 0.2.0
 */
class Gallery extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		return self::node_has_class( $node, 'gallery' )
			|| self::node_has_class( $node, 'wp-block-gallery' )
			|| self::node_has_class( $node, 'wp-block-jetpack-slideshow' )
				? $node
				: null;
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
				'role'  => '#gallery_type#',
				'items' => '#items#',
			]
		);

		$this->register_spec(
			'gallery-layout',
			__( 'Layout', 'apple-news' ),
			[
				'margin' => [
					'bottom' => 25,
					'top'    => 25,
				],
			]
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 * @access protected
	 */
	protected function build( $html ) {
		$container = null;

		// Convert the text into a NodeList.
		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $html );
		libxml_clear_errors();
		libxml_use_internal_errors( false );

		// See if the gallery is a collection of figures within a figure.
		$figures = $dom->getElementsByTagName( 'figure' );
		if ( ! empty( $figures->item( 0 ) ) && self::node_has_class( $figures->item( 0 ), 'wp-block-gallery' ) ) {
			$container = $figures->item( 0 );
		}

		// See if the gallery is a UL.
		if ( empty( $container ) ) {
			$ul = $dom->getElementsByTagName( 'ul' );
			if ( ! empty( $ul->item( 0 ) ) ) {
				$container = $ul->item( 0 );
			}
		}

		// See if the gallery is a div with a class of "gallery".
		if ( empty( $container ) ) {
			$divs = $dom->getElementsByTagName( 'div' );
			foreach ( $divs as $div ) {
				if ( self::node_has_class( $div, 'gallery' ) ) {
					$container = $div;
					break;
				}
			}
		}

		// Determine if we have items.
		if ( empty( $container->childNodes ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			return;
		}

		// Loop through items and construct slides.
		$theme = \Apple_Exporter\Theme::get_used();
		$items = [];
		foreach ( $container->childNodes as $item ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			// Convert item into HTML for regex matching.
			$item_html = $item->ownerDocument->saveXML( $item ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			// Try to get URL.
			if ( ! preg_match( '/src="([^"]+)"/', $item_html, $matches ) ) {
				continue;
			}

			// Ensure the URL is valid.
			$url = Exporter_Content::format_src_url( $matches[1] );
			if ( empty( $url ) ) {
				continue;
			}

			// Start building the item.
			$content = [
				'URL' => $this->maybe_bundle_source( esc_url_raw( $url ) ),
			];

			// Try to add the caption.
			$caption_regex = '/<(dd|figcaption).*?>(.*)<\/\g1>/s';
			if ( preg_match( $caption_regex, $item_html, $matches ) ) {
				$content['caption'] = [
					'format'    => 'html',
					'text'      => trim( $matches[2] ),
					'textStyle' => [
						'fontName' => $theme->get_value( 'caption_font' ),
					],
				];
			}

			// Try to add the alt text as the accessibility caption.
			if ( preg_match( '/alt="([^"]+)"/', $item_html, $matches ) ) {
				$content['accessibilityCaption'] = sanitize_text_field(
					$matches[1]
				);
			}

			// Add the compiled slide content to the list of items.
			$items[] = $content;
		}

		// Ensure we got items.
		if ( empty( $items ) ) {
			return;
		}

		// Build the JSON.
		$this->register_json(
			'json',
			[
				'#gallery_type#' => $theme->get_value( 'gallery_type' ),
				'#items#'        => $items,
			]
		);

		// Set the layout.
		$this->set_layout();
	}

	/**
	 * Set the layout for the component.
	 *
	 * @access private
	 */
	private function set_layout() {
		$this->register_full_width_layout(
			'gallery-layout',
			'gallery-layout',
			[],
			'layout'
		);
	}
}
