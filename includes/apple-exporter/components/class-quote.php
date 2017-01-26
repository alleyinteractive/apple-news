<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\Quote class
 *
 * Contains a class which is used to transform blockquotes into Apple News format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.2.0
 */

namespace Apple_Exporter\Components;

use \DOMElement;

/**
 * A class which is used to transform blockquotes into Apple News format.
 *
 * @since 0.2.0
 */
class Quote extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param DOMElement $node The node to examine.
	 *
	 * @access public
	 * @return DOMElement|null The DOMElement on match, false on no match.
	 */
	public static function node_matches( $node ) {
		return ( 'blockquote' === $node->nodeName ) ? $node : null;
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 *
	 * @access protected
	 */
	protected function build( $html ) {

		// Extract text from blockquote HTML.
		preg_match( '#<blockquote.*?>(.*?)</blockquote>#si', $html, $matches );
		$text = $matches[1];

		// Split for pullquote vs. blockquote.
		if ( 0 === strpos( $html, '<blockquote class="pullquote">' ) ) {
			$this->_build_pullquote( $text );
		} else {
			$this->_build_blockquote( $text );
		}


	}

	/**
	 * Runs the build operation for a blockquote.
	 *
	 * @param string $text The text to use when building the blockquote.
	 *
	 * @access private
	 */
	private function _build_blockquote( $text ) {

		// TODO: WIRE THIS UP
	}

	/**
	 * Runs the build operation for a pullquote.
	 *
	 * @param string $text The text to use when building the pullquote.
	 *
	 * @access private
	 */
	private function _build_pullquote( $text ) {

		// Set JSON for this element.
		$this->json = array(
			'role' => 'container',
			'layout' => array(
				'columnStart' => 3,
				'columnSpan' => 4
			),
			'style' => array(
				'border' => array (
					'all' => array (
						'width' => $this->get_setting( 'pullquote_border_width' ),
						'style' => $this->get_setting( 'pullquote_border_style' ),
						'color' => $this->get_setting( 'pullquote_border_color' ),
					),
					'left' => false,
					'right' => false,
				),
			),
			'components' => array(
				array(
					'role' => 'quote',
					'text' => $this->parser->parse( $text ),
					'format' => $this->parser->format,
					'layout' => 'quote-layout',
					'textStyle' => 'default-pullquote',
				)
			),
		);

		// Set component attributes.
		$this->_set_pullquote_style();
		$this->_set_pullquote_layout();
		$this->_set_pullquote_anchor();
	}

	/**
	 * Sets the anchor settings for this component.
	 *
	 * @access private
	 */
	private function _set_pullquote_anchor() {
		$this->set_anchor_position( Component::ANCHOR_AUTO );
		$this->json['anchor'] = array(
			'targetComponentIdentifier' => 'pullquoteAnchor',
			'originAnchorPosition' => 'top',
			'targetAnchorPosition' => 'top',
			'rangeStart' => 0,
			'rangeLength' => 10,
		);
	}

	/**
	 * Set the layout for the component.
	 *
	 * @access private
	 */
	private function _set_pullquote_layout() {
		$this->register_layout(
			'quote-layout',
			array(
				'margin' => array(
					'top' => 12,
					'bottom' => 12,
				),
			)
		);
	}

	/**
	 * Set the style for the component.
	 *
	 * @access private
	 */
	private function _set_pullquote_style() {
		$this->json['textStyle'] = 'default-pullquote';
		$this->register_style(
			'default-pullquote',
			array(
				'fontName' => $this->get_setting( 'pullquote_font' ),
				'fontSize' => intval( $this->get_setting( 'pullquote_size' ) ),
				'textColor' => $this->get_setting( 'pullquote_color' ),
				'textTransform' => $this->get_setting( 'pullquote_transform' ),
				'lineHeight' => intval( $this->get_setting( 'pullquote_line_height' ) ),
				'textAlignment' => $this->find_text_alignment(),
				'tracking' => intval( $this->get_setting( 'pullquote_tracking' ) ) / 100,
			)
		);
	}
}
