<?php
namespace Apple_Exporter\Components;

/**
 * An HTML's blockquote representation.
 *
 * @since 0.2.0
 */
class Quote extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param DomNode $node
	 * @return mixed
	 * @static
	 * @access public
	 */
	public static function node_matches( $node ) {
		if ( 'blockquote' == $node->nodeName ) {
			return $node;
		}

		return null;
	}

	/**
	 * Build the component.
	 *
	 * @param string $text
	 * @access protected
	 */
	protected function build( $text ) {
		preg_match( '#<blockquote.*?>(.*?)</blockquote>#si', $text, $matches );
		$text = $matches[1];

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
			'components' => array( array(
				'role'   => 'quote',
				'text'   => $this->parser->parse( $text ),
				'format' => 'markdown',
				'layout' => 'quote-layout',
				'textStyle' => 'default-pullquote',
			) ),
		);

		$this->set_style();
		$this->set_layout();
		$this->set_anchor();
	}

	/**
	 * Set the layout for the component.
	 *
	 * @access private
	 */
	private function set_layout() {
		$this->register_layout( 'quote-layout', array(
			'margin' => array(
				'top' => 12,
				'bottom' => 12,
			),
		) );
	}

	/**
	 * Set the style for the component.
	 *
	 * @access private
	 */
	private function set_style() {
		$this->json['textStyle'] = 'default-pullquote';
		$this->register_style( 'default-pullquote', array(
			'fontName' => $this->get_setting( 'pullquote_font' ),
			'fontSize' => intval( $this->get_setting( 'pullquote_size' ) ),
			'textColor' => $this->get_setting( 'pullquote_color' ),
			'textTransform' => $this->get_setting( 'pullquote_transform' ),
			'lineHeight' => intval( $this->get_setting( 'pullquote_line_height' ) ),
			'textAlignment' => $this->find_text_alignment(),
			'tracking' => intval( $this->get_setting( 'pullquote_tracking' ) ) / 100,
		) );
	}

	/**
	 * Sets the anchor settings for this component.
	 *
	 * @access private
	 */
	private function set_anchor() {
		$this->set_anchor_position( Component::ANCHOR_AUTO );

		$this->json['anchor'] = array(
			'targetComponentIdentifier' => 'pullquoteAnchor',
			'originAnchorPosition' => 'top',
			'targetAnchorPosition' => 'top',
			'rangeStart' => 0,
			'rangeLength' => 10,
		);
	}

}

