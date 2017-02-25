<?php
namespace Apple_Exporter\Components;

use \DOMElement;

/**
 * A paragraph component.
 *
 * @since 0.2.0
 */
class Body extends Component {

	/**
	 * Override. This component doesn't need a layout update if marked as the
	 * target of an anchor.
	 *
	 * @var boolean
	 * @access public
	 */
	public $needs_layout_if_anchored = false;

	/**
	 * Quotes can be anchor targets.
	 *
	 * @var boolean
	 * @access protected
	 */
	protected $can_be_anchor_target = true;

	/**
	 * Look for node matches for this component.
	 *
	 * @param DOMElement $node The node to examine for matches.
	 *
	 * @access public
	 * @return array|null An array of matching HTML on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		// We are only interested in p, pre, ul and ol
		if ( ! in_array( $node->nodeName, array( 'p', 'pre', 'ul', 'ol' ) ) ) {
			return null;
		}

		// If the node is p, ul or ol AND it's empty, just ignore.
		if ( empty( $node->nodeValue ) ) {
			return null;
		}

		// Negotiate open and close values.
		$open = '<' . $node->nodeName . '>';
		$close = '</' . $node->nodeName . '>';
		if ( 'ol' === $node->nodeName || 'ul' === $node->nodeName ) {
			$open .= '<li>';
			$close = '</li>' . $close;
		}

		$elements = self::split_unsupported_elements(
			$node->ownerDocument->saveXML( $node ),
			$node->nodeName,
			$open,
			$close
		);

		return $elements;
	}

	/**
	 * Split the non markdownable content for processing.
	 *
	 * @param string $html The HTML to split.
	 * @param string $tag The tag in which to enclose primary content.
	 * @param string $open The opening HTML tag(s) for use in balancing a split.
	 * @param string $close The closing HTML tag(s) for use in balancing a split.
	 *
	 * @access private
	 * @return array An array of HTML components.
	 */
	private static function split_unsupported_elements( $html, $tag, $open, $close ) {

		// Don't bother processing if there is nothing to operate on.
		if ( empty( $html ) ) {
			return array();
		}

		// Try to get matches of unsupported elements to split.
		preg_match( '#<(img|video|audio|iframe).*?(?:>(.*?)</\1>|/?>)#si', $html, $matches );
		if ( empty( $matches ) ) {

			// Ensure the resulting HTML is not devoid of actual content.
			if ( '' === trim( strip_tags( $html ) ) ) {
				return array();
			}

			return array(
				array(
					'name' => $tag,
					'value' => $html,
				),
			);
		}

		// Split the HTML by the found element into the left and right parts.
		list( $whole, $tag_name ) = $matches;
		list( $left, $right ) = explode( $whole, $html, 3 );

		// Additional processing for list items.
		if ( 'ol' === $tag || 'ul' === $tag ) {
			$left = preg_replace( '/<br\s*\/?\s*>/', "\n\n", $left );
			$right = preg_replace( '/<br\s*\/?\s*>/', "\n\n", $right );
			$left = trim( $left );
			$right = trim( $right );
			$left = preg_replace( '/\s*<li>$/is', '', $left );
			$right = preg_replace( '/^<\/li>\s*/is', '', $right );
			$left = str_replace( "\n\n", '<br />', $left );
			$right = str_replace( "\n\n", '<br />', $right );
		}

		// Augment left and right parts with correct opening and closing tags.
		$left = force_balance_tags( $left . $close );
		$right = force_balance_tags( $open . $right );

		// Start building the return value.
		$elements = array(
			array(
				'name' => $tag_name,
				'value' => $whole,
			),
		);

		// Check for conditions under which left should be added.
		if ( '' !== trim( strip_tags( $left ) ) ) {
			$elements = array_merge(
				array(
					array(
						'name' => $tag,
						'value' => $left,
					),
				),
				$elements
			);
		}

		return array_merge(
			$elements,
			self::split_unsupported_elements( $right, $tag, $open, $close )
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $text
	 * @access protected
	 */
	protected function build( $text ) {
		$this->json = array(
			'role'   => 'body',
			'text'   => $this->parser->parse( $text ),
			'format' => $this->parser->format,
		);

		if ( 'yes' == $this->get_setting( 'initial_dropcap' ) ) {
			// Toggle setting. This should only happen in the initial paragraph.
			$this->set_setting( 'initial_dropcap', 'no' );
			$this->set_initial_dropcap_style();
		} else {
			$this->set_default_style();
		}

		$this->set_default_layout();
	}

	/**
	 * Whether HTML format is enabled for this component type.
	 *
	 * @access protected
	 * @return bool Whether HTML format is enabled for this component type.
	 */
	protected function html_enabled() {
		return true;
	}

	/**
	 * Set the default layout for the component.
	 *
	 * @access private
	 */
	private function set_default_layout() {
		$this->json[ 'layout' ] = 'body-layout';
		$this->register_layout( 'body-layout', array(
			'columnStart' => $this->get_setting( 'body_offset' ),
			'columnSpan'  => $this->get_setting( 'body_column_span' ),
			'margin'      => array(
				'top' => 12,
				'bottom' => 12
			),
		) );

		// Also pre-register the layout that will be used later for the last body component
		$this->register_layout( 'body-layout-last', array(
			'columnStart' => $this->get_setting( 'body_offset' ),
			'columnSpan'  => $this->get_setting( 'body_column_span' ),
			'margin'      => array(
				'top' => 12,
				'bottom' => 30
			),
		) );
	}

	/**
	 * Get the default style for the component.
	 *
	 * @return array
	 * @access private
	 */
	private function get_default_style() {
		return array(
			'textAlignment' => 'left',
			'fontName' => $this->get_setting( 'body_font' ),
			'fontSize' => intval( $this->get_setting( 'body_size' ) ),
			'tracking' => intval( $this->get_setting( 'body_tracking' ) ) / 100,
			'lineHeight' => intval( $this->get_setting( 'body_line_height' ) ),
			'textColor' => $this->get_setting( 'body_color' ),
			'linkStyle' => array(
				'textColor' => $this->get_setting( 'body_link_color' )
			),
			'paragraphSpacingBefore' => 18,
			'paragraphSpacingAfter' => 18,
		);
	}

	/**
	 * Set the default style for the component.
	 *
	 * @access public
	 */
	public function set_default_style() {
		$this->json[ 'textStyle' ] = 'default-body';
		$this->register_style( 'default-body', $this->get_default_style() );
	}

	/**
	 * Set the initial dropcap style for the component.
	 *
	 * @access private
	 */
	private function set_initial_dropcap_style() {

		// Negotiate the number of lines.
		$number_of_lines = absint( $this->get_setting( 'dropcap_number_of_lines' ) );
		if ( $number_of_lines < 2 ) {
			$number_of_lines = 2;
		} elseif ( $number_of_lines > 10 ) {
			$number_of_lines = 10;
		}

		// Start building the custom dropcap body style.
		$dropcap_style = array(
			'fontName' => $this->get_setting( 'dropcap_font' ),
			'numberOfCharacters' => absint( $this->get_setting( 'dropcap_number_of_characters' ) ),
			'numberOfLines' => $number_of_lines,
			'numberOfRaisedLines' => absint( $this->get_setting( 'dropcap_number_of_raised_lines' ) ),
			'padding' => absint( $this->get_setting( 'dropcap_padding' ) ),
			'textColor' => $this->get_setting( 'dropcap_color' ),
		);

		// Add the background color, if defined.
		$background_color = $this->get_setting( 'dropcap_background_color' );
		if ( ! empty( $background_color ) ) {
			$dropcap_style['backgroundColor'] = $background_color;
		}

		// Set the text style.
		$this->json['textStyle'] = 'dropcapBodyStyle';

		// Apply the dropcap body style.
		$this->register_style(
			'dropcapBodyStyle',
			array_merge(
				$this->get_default_style(),
				array( 'dropCapStyle' => $dropcap_style )
			)
		);
	}

	/**
	 * This component needs to ensure it didn't end up with empty content.
	 * This will go through sanitize_text_field later as part of the assembled JSON.
	 * Therefore, tags aren't valid but we need to catch them now
	 * or we could encounter a parsing error when it's already too late.
	 *
	 * We also can't do this sooner, such as in build, because at that point
	 * the component could still contain nested, valid tags.
	 *
	 * We don't want to modify the JSON since it will still undergo further processing.
	 * We only want to check if, on its own, this component would end up empty.
	 *
	 * @access public
	 * @return array
	 */
	public function to_array() {
		$sanitized_text = sanitize_text_field( $this->json['text'] );

		if ( empty( $sanitized_text ) ) {
			return new \WP_Error( 'invalid', __( 'empty body component', 'apple-news' ) );
		} else {
			return parent::to_array();
		}
	}
}

