<?php
namespace Apple_Exporter\Components;

/**
 * Represents an HTML header.
 *
 * @since 0.2.0
 */
class Heading extends Component {

	/**
	 * Supported heading levels
	 *
	 * @var array
	 * @access public
	 * @static
	 */
	public static $levels = array( 1, 2, 3, 4, 5, 6 );

	/**
	 * Look for node matches for this component.
	 *
	 * @param DomNode $node
	 * @return mixed
	 * @static
	 * @access public
	 */
	public static function node_matches( $node ) {
		$regex = sprintf(
			'#h[%s-%s]#',
			current( self::$levels ),
			end( self::$levels )
		);
		reset( self::$levels );

		if ( ! preg_match( $regex, $node->nodeName ) ) {
			return null;
		}

		$html = $node->ownerDocument->saveXML( $node );
		if ( preg_match( '#<img.*?>#si', $html ) ) {
			return self::split_image( $html );
		}

		return $node;
	}

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		$this->register_spec(
			'heading-layout',
			__( 'Layout', 'apple-news' ),
			array(
				'columnStart' => '%%body_offset%%',
				'columnSpan' => '%%body_column_span%%',
				'margin' => array(
					'bottom' => 15,
					'top' => 15,
				),
			)
		);

		foreach ( self::$levels as $level ) {
			$this->register_spec(
				'default-heading-' . $level,
				sprintf(
					__( 'Level %s Style', 'apple-news' ),
					$level
				),
				array(
				'fontName' => '%%header' . $level . '_font%%',
				'fontSize' => '%%header' . $level . '_size%%',
				'lineHeight' => '%%header' . $level . '_line_height%%',
				'textColor' => '%%header' . $level . '_color%%',
				'textAlignment' => '%%textAlignment%%',
				'tracking' => '%%header' . $level . '_tracking%%',
			)
			);
		}
	}

	/**
	 * Split the image parts.
	 *
	 * @param string $html
	 * @return array
	 * @access private
	 */
	private static function split_image( $html ) {
		if ( empty( $html ) ) {
			return array();
		}

		// Find the first image inside
		preg_match( '#<img.*?>#si', $html, $matches );

		if ( ! $matches ) {
			return array( array( 'name' => 'heading', 'value' => $html ) );
		}

		$image_html   = $matches[0];
		$heading_html = str_replace( $image_html, '', $html );

		return array(
			array( 'name'  => 'heading', 'value' => self::clean_html( $heading_html ) ),
			array( 'name'  => 'img'    , 'value' => $image_html ),
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $text
	 * @access protected
	 */
	protected function build( $text ) {
		if ( 0 === preg_match( '#<h(\d).*?>(.*?)</h\1>#si', $text, $matches ) ) {
			return;
		}

		$level = intval( $matches[1] );
		// We won't be using markdown*, so we ignore all HTML tags, just fetch the
		// contents.
		// *: No markdown because the apple format doesn't support markdown with
		// textStyle in headings.
		$text = wp_strip_all_tags( $matches[2] );

		$this->json = array(
			'role'   => 'heading' . $level,
			'text'   => trim( $this->parser->parse( $text ) ),
			'format' => $this->parser->format,
		);

		$this->set_style( $level );
		$this->set_layout();
	}

	/**
	 * Set the layout for the component.
	 *
	 * @access private
	 */
	private function set_layout() {
		$this->register_layout(
			'heading-layout',
			'heading-layout',
			 array(
				'columnStart' => $this->get_setting( 'body_offset' ),
				'columnSpan' => $this->get_setting( 'body_column_span' ),
			),
			'layout'
		);
	}

	/**
	 * Set the style for the component.
	 *
	 * @access private
	 */
	private function set_style( $level ) {
		$this->register_style(
			'default-heading-' . $level,
			'default-heading-' . $level,
			array(
				'fontName' => $this->get_setting( 'header' . $level . '_font' ),
				'fontSize' => intval( $this->get_setting( 'header' . $level . '_size' ) ),
				'lineHeight' => intval( $this->get_setting( 'header' . $level . '_line_height' ) ),
				'textColor' => $this->get_setting( 'header' . $level . '_color' ),
				'textAlignment' => $this->find_text_alignment(),
				'tracking' => intval( $this->get_setting( 'header' . $level . '_tracking' ) ) / 100,
			),
			'textStyle'
		);
	}

}

