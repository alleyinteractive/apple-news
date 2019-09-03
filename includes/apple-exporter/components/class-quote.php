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

/**
 * A class which is used to transform blockquotes into Apple News format.
 *
 * @since 0.2.0
 */
class Quote extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		return ( 'blockquote' === $node->nodeName || ( isset( $node->firstChild->tagName ) && 'blockquote' === $node->firstChild->tagName ) ) ? $node : null;
	}

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		$this->register_spec(
			'blockquote-without-border-json',
			__( 'Blockquote Without Border JSON', 'apple-news' ),
			array(
				'role'       => 'container',
				'layout'     => array(
					'columnStart' => '#body_offset#',
					'columnSpan'  => '#body_column_span#',
					'margin'      => array(
						'bottom' => '#layout_gutter#',
						'top'    => '#layout_gutter#',
					),
				),
				'style'      => array(
					'backgroundColor' => '#blockquote_background_color#',
				),
				'components' => array(
					array(
						'role'      => 'quote',
						'text'      => '#text#',
						'format'    => '#format#',
						'layout'    => 'blockquote-layout',
						'textStyle' => '#default_blockquote#',
					),
				),
			)
		);

		$this->register_spec(
			'blockquote-with-border-json',
			__( 'Blockquote With Border JSON', 'apple-news' ),
			array(
				'role'       => 'container',
				'layout'     => array(
					'columnStart' => '#body_offset#',
					'columnSpan'  => '#body_column_span#',
					'margin'      => array(
						'bottom' => '#layout_gutter#',
						'top'    => '#layout_gutter#',
					),
				),
				'style'      => array(
					'backgroundColor' => '#blockquote_background_color#',
					'border'          => array(
						'all'    => array(
							'width' => '#blockquote_border_width#',
							'style' => '#blockquote_border_style#',
							'color' => '#blockquote_border_color#',
						),
						'bottom' => false,
						'right'  => false,
						'top'    => false,
					),
				),
				'components' => array(
					array(
						'role'      => 'quote',
						'text'      => '#text#',
						'format'    => '#format#',
						'layout'    => 'blockquote-layout',
						'textStyle' => '#default_blockquote#',
					),
				),
			)
		);

		$this->register_spec(
			'blockquote-layout',
			__( 'Blockquote Layout', 'apple-news' ),
			array(
				'contentInset' => array(
					'bottom' => true,
					'left'   => true,
					'right'  => true,
					'top'    => true,
				),
			)
		);

		$this->register_spec(
			'default-blockquote',
			__( 'Blockquote Style', 'apple-news' ),
			array(
				'fontName'      => '#blockquote_font#',
				'fontSize'      => '#blockquote_size#',
				'textColor'     => '#blockquote_color#',
				'lineHeight'    => '#blockquote_line_height#',
				'textAlignment' => '#text_alignment#',
				'tracking'      => '#blockquote_tracking#',
			)
		);

		$this->register_spec(
			'pullquote-without-border-json',
			__( 'Pull quote Without Border JSON', 'apple-news' ),
			array(
				'role'       => 'container',
				'layout'     => array(
					'columnStart' => 3,
					'columnSpan'  => 4,
				),
				'components' => array(
					array(
						'role'      => 'quote',
						'text'      => '#text#',
						'format'    => '#format#',
						'layout'    => 'pullquote-layout',
						'textStyle' => 'default-pullquote',
					),
				),
				'anchor'     => array(
					'targetComponentIdentifier' => 'pullquoteAnchor',
					'originAnchorPosition'      => 'top',
					'targetAnchorPosition'      => 'top',
					'rangeStart'                => 0,
					'rangeLength'               => 10,
				),
			)
		);

		$this->register_spec(
			'pullquote-with-border-json',
			__( 'Pull quote With Border JSON', 'apple-news' ),
			array(
				'role'       => 'container',
				'layout'     => array(
					'columnStart' => 3,
					'columnSpan'  => 4,
				),
				'components' => array(
					array(
						'role'      => 'quote',
						'text'      => '#text#',
						'format'    => '#format#',
						'layout'    => 'pullquote-layout',
						'textStyle' => 'default-pullquote',
					),
				),
				'style'      => array(
					'border' => array(
						'all'   => array(
							'width' => '#pullquote_border_width#',
							'style' => '#pullquote_border_style#',
							'color' => '#pullquote_border_color#',
						),
						'left'  => false,
						'right' => false,
					),
				),
				'anchor'     => array(
					'targetComponentIdentifier' => 'pullquoteAnchor',
					'originAnchorPosition'      => 'top',
					'targetAnchorPosition'      => 'top',
					'rangeStart'                => 0,
					'rangeLength'               => 10,
				),
			)
		);

		$this->register_spec(
			'pullquote-layout',
			__( 'Pull quote Layout', 'apple-news' ),
			array(
				'margin' => array(
					'top'    => 12,
					'bottom' => 12,
				),
			)
		);

		$this->register_spec(
			'default-pullquote',
			__( 'Pull quote Style', 'apple-news' ),
			array(
				'fontName'           => '#pullquote_font#',
				'fontSize'           => '#pullquote_size#',
				'hangingPunctuation' => '#pullquote_hanging_punctuation#',
				'textColor'          => '#pullquote_color#',
				'textTransform'      => '#pullquote_transform#',
				'lineHeight'         => '#pullquote_line_height#',
				'textAlignment'      => '#text_alignment#',
				'tracking'           => '#pullquote_tracking#',
			)
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 * @access protected
	 */
	protected function build( $html ) {
		$is_pullquote = 0 === strpos( $html, '<figure class="wp-block-pullquote' );
		$string_match = $is_pullquote ? '#<figure.*?align(.*?)"><blockquote.*?>(.*?)</blockquote>#si'
			: '#<blockquote(?:\s[^>]*?text-align:\s*(left|center|right))?[^>]*>(.*?)</blockquote>#si';

		// Extract text from blockquote HTML.
		preg_match( $string_match, $html, $matches );
		$this->text_alignment = 3 === count( $matches ) && $matches[1] ? $matches[1] : 'left';
		$this->text_alignment = 'wide' === $this->text_alignment ? 'center' : $this->text_alignment;
		$text                 = isset( $matches[2] ) ? $matches[2] : $matches[1];

		// If there is no text for this element, bail.
		$check = trim( $text );
		if ( empty( $check ) ) {
			return;
		}

		// Split for pullquote vs. blockquote.
		if ( 0 === strpos( $html, '<blockquote class="apple-news-pullquote">' ) ) {
			$this->build_pullquote( $text );
		} else {
			$this->build_blockquote( $text );
		}
	}

	/**
	 * Whether HTML format is enabled for this component type.
	 *
	 * @param bool $enabled Optional. Whether to enable HTML support for this component. Defaults to true.
	 *
	 * @access protected
	 * @return bool Whether HTML format is enabled for this component type.
	 */
	protected function html_enabled( $enabled = true ) {
		return parent::html_enabled( $enabled );
	}

	/**
	 * Processes given text to apply smart quotes on either end of provided text.
	 *
	 * @param string $text The text to process.
	 *
	 * @access private
	 * @return string The modified text.
	 */
	private function apply_hanging_punctuation( $text ) {

		// Trim the fat before beginning.
		$text = trim( $text );

		// If using HTML format, also strip the beginning and ending paragraph tags.
		$text = preg_replace( '/^<p>/i', '', $text );
		$text = preg_replace( '/<\/p>$/i', '', $text );

		// Strip any double quotes already present.
		$modified_text = trim( $text, '"“”' );

		// Add smart quotes around the text.
		$modified_text = '“' . $modified_text . '”';

		/**
		 * Allows for modification of a quote with hanging punctuation enabled.
		 *
		 * @since 1.2.4
		 *
		 * @param string $modified_text The modified text to be filtered.
		 * @param string $text The original text for the quote.
		 */
		$modified_text = apply_filters(
			'apple_news_apply_hanging_punctuation',
			$modified_text,
			$text
		);

		// Re-add removed elements depending on format.
		if ( 'yes' === $this->settings->html_support ) {
			$modified_text = '<p>' . $modified_text . '</p>';
		} else {
			$modified_text .= "\n\n";
		}

		return $modified_text;
	}

	/**
	 * Runs the build operation for a blockquote.
	 *
	 * @param string $text The text to use when building the blockquote.
	 *
	 * @access private
	 */
	private function build_blockquote( $text ) {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Set JSON for this element.
		$values = array(
			'#body_offset#'                 => $theme->get_body_offset(),
			'#body_column_span#'            => $theme->get_body_column_span(),
			'#layout_gutter#'               => $theme->get_value( 'layout_gutter' ),
			'#blockquote_background_color#' => $theme->get_value( 'blockquote_background_color' ),
			'#text#'                        => $this->parser->parse( $text ),
			'#format#'                      => $this->parser->format,
			'#default_blockquote#'          => 'default-blockquote-' . $this->text_alignment,
		);

		// Set component attributes.
		// Determine if there is a border specified.
		if ( 'none' !== $theme->get_value( 'blockquote_border_style' ) ) {
			$values    = $this->set_blockquote_border( $values );
			$spec_name = 'blockquote-with-border-json';
		} else {
			$spec_name = 'blockquote-without-border-json';
		}

		$this->register_json( $spec_name, $values );

		$this->set_blockquote_layout();
		$this->set_blockquote_style();
	}

	/**
	 * Runs the build operation for a pullquote.
	 *
	 * @param string $text The text to use when building the pullquote.
	 *
	 * @access private
	 */
	private function build_pullquote( $text ) {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Apply additional formatting to the text if hanging punctuation is set.
		$text = $this->parser->parse( $text );
		if ( 'yes' === $theme->get_value( 'pullquote_hanging_punctuation' ) ) {
			$text = $this->apply_hanging_punctuation( $text );
		}

		// Set JSON for this element.
		$values = array(
			'#text#'   => $text,
			'#format#' => $this->parser->format,
		);

		// Determine if there is a border specified.
		if ( 'none' !== $theme->get_value( 'pullquote_border_style' ) ) {
			$values    = $this->set_pullquote_border( $values );
			$spec_name = 'pullquote-with-border-json';
		} else {
			$spec_name = 'pullquote-without-border-json';
		}

		// Register the JSON.
		$this->register_json( $spec_name, $values );

		// Set component attributes.
		$this->set_pullquote_anchor();
		$this->set_pullquote_layout();
		$this->set_pullquote_style();
	}

	/**
	 * Set the border for a blockquote.
	 *
	 * @param array $values Token values to be augmented.
	 * @access private
	 * @return array The modified list of token values.
	 */
	private function set_blockquote_border( $values ) {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Determine if there is a border specified.
		if ( 'none' === $theme->get_value( 'blockquote_border_style' ) ) {
			return $values;
		}

		// Set the border.
		return array_merge(
			$values,
			array(
				'#blockquote_border_width#' => $theme->get_value( 'blockquote_border_width' ),
				'#blockquote_border_style#' => $theme->get_value( 'blockquote_border_style' ),
				'#blockquote_border_color#' => $theme->get_value( 'blockquote_border_color' ),
			)
		);
	}

	/**
	 * Set the layout for a blockquote.
	 *
	 * @access private
	 */
	private function set_blockquote_layout() {
		$this->register_layout(
			'blockquote-layout',
			'blockquote-layout'
		);
	}

	/**
	 * Set the style for a blockquote.
	 *
	 * @access private
	 */
	private function set_blockquote_style() {

		// Get information about the currently loaded theme.
		$theme          = \Apple_Exporter\Theme::get_used();
		$text_alignment = $this->find_text_alignment( $this->text );

		$this->register_style(
			'default-blockquote-' . $text_alignment,
			'default-blockquote',
			array(
				'#blockquote_font#'        => $theme->get_value( 'blockquote_font' ),
				'#blockquote_size#'        => intval( $theme->get_value( 'blockquote_size' ) ),
				'#blockquote_color#'       => $theme->get_value( 'blockquote_color' ),
				'#blockquote_line_height#' => intval( $theme->get_value( 'blockquote_line_height' ) ),
				'#text_alignment#'         => $this->find_text_alignment( $this->text ),
				'#blockquote_tracking#'    => intval( $theme->get_value( 'blockquote_tracking' ) ) / 100,
			)
		);
	}

	/**
	 * Sets the anchor settings for a pullquote.
	 *
	 * @access private
	 */
	private function set_pullquote_anchor() {
		$this->set_anchor_position( Component::ANCHOR_AUTO );
	}

	/**
	 * Set the border for a pullquote.
	 *
	 * @param array $values The list of token values to be modified.
	 * @access private
	 * @return array The modified list of token values.
	 */
	private function set_pullquote_border( $values ) {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Set the border.
		return array_merge(
			$values,
			array(
				'#pullquote_border_width#' => $theme->get_value( 'pullquote_border_width' ),
				'#pullquote_border_style#' => $theme->get_value( 'pullquote_border_style' ),
				'#pullquote_border_color#' => $theme->get_value( 'pullquote_border_color' ),
			)
		);
	}

	/**
	 * Set the layout for a pullquote.
	 *
	 * @access private
	 */
	private function set_pullquote_layout() {
		$this->register_layout(
			'pullquote-layout',
			'pullquote-layout'
		);
	}

	/**
	 * Set the style for a pullquote.
	 *
	 * @access private
	 */
	private function set_pullquote_style() {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		$this->register_style(
			'default-pullquote',
			'default-pullquote',
			array(
				'#pullquote_font#'                => $theme->get_value( 'pullquote_font' ),
				'#pullquote_size#'                => intval( $theme->get_value( 'pullquote_size' ) ),
				'#pullquote_hanging_punctuation#' => ( 'yes' === $theme->get_value( 'pullquote_hanging_punctuation' ) ),
				'#pullquote_color#'               => $theme->get_value( 'pullquote_color' ),
				'#pullquote_transform#'           => $theme->get_value( 'pullquote_transform' ),
				'#pullquote_line_height#'         => intval( $theme->get_value( 'pullquote_line_height' ) ),
				'#text_alignment#'                => $this->find_text_alignment(),
				'#pullquote_tracking#'            => intval( $theme->get_value( 'pullquote_tracking' ) ) / 100,
			),
			'textStyle'
		);
	}
}
