<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Body class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

use Apple_Exporter\Theme;
use DOMElement;

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
	 * @return array|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		/* phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase */
		// We are only interested in p, pre, ul and ol.
		if ( ! in_array( $node->nodeName, [ 'p', 'pre', 'ul', 'ol' ], true ) ) {
			return null;
		}

		// UTF-8 whitespace values to remove when checking for "empty" content.
		$whitespace = [
			'SPACE'                     => "\x20",
			'NO-BREAK SPACE'            => "\xc2\xa0",
			'OGHAM SPACE MARK'          => "\xe1\x9a\x80",
			'EN QUAD'                   => "\xe2\x80\x80",
			'EM QUAD'                   => "\xe2\x80\x81",
			'EN SPACE'                  => "\xe2\x80\x82",
			'EM SPACE'                  => "\xe2\x80\x83",
			'THREE-PER-EM SPACE'        => "\xe2\x80\x84",
			'FOUR-PER-EM SPACE'         => "\xe2\x80\x85",
			'SIX-PER-EM SPACE'          => "\xe2\x80\x86",
			'FIGURE SPACE'              => "\xe2\x80\x87",
			'PUNCTUATION SPACE'         => "\xe2\x80\x88",
			'THIN SPACE'                => "\xe2\x80\x89",
			'HAIR SPACE'                => "\xe2\x80\x8a",
			'ZERO WIDTH SPACE'          => "\xe2\x80\x8b",
			'NARROW NO-BREAK SPACE'     => "\xe2\x80\xaf",
			'MEDIUM MATHEMATICAL SPACE' => "\xe2\x81\x9f",
			'IDEOGRAPHIC SPACE'         => "\xe3\x80\x80",
		];

		// If the node is p, ul or ol AND it's empty, ignore.
		if ( empty( $node->nodeValue )
			|| empty( str_replace( $whitespace, '', $node->nodeValue ) )
		) {
			return null;
		}

		// Negotiate open and close values.
		$open  = '<' . $node->nodeName . '>';
		$close = '</' . $node->nodeName . '>';
		if ( 'ol' === $node->nodeName || 'ul' === $node->nodeName ) {
			$open .= '<li>';
			$close = '</li>' . $close;
		}

		return self::split_unsupported_elements(
			$node->ownerDocument->saveXML( $node ),
			$node->nodeName,
			$open,
			$close
		);
		/* phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase */
	}

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs(): void {
		$theme        = Theme::get_used();
		$default_spec = $this->get_default_style_spec();
		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role'   => 'body',
				'text'   => '#text#',
				'format' => '#format#',
			]
		);

		$this->register_spec(
			'body-layout',
			__( 'Layout', 'apple-news' ),
			[
				'columnStart' => '#body_offset#',
				'columnSpan'  => '#body_column_span#',
				'margin'      => [
					'top'    => 12,
					'bottom' => 12,
				],
			]
		);

		$this->register_spec(
			'body-layout-last',
			__( 'Layout for Last Component', 'apple-news' ),
			[
				'columnStart' => '#body_offset#',
				'columnSpan'  => '#body_column_span#',
				'margin'      => [
					'top'    => 12,
					'bottom' => 30,
				],
			]
		);

		$this->register_spec(
			'default-body',
			__( 'Default Style', 'apple-news' ),
			$default_spec
		);

		$dropcap_color_dark            = $theme->get_value( 'dropcap_color_dark' );
		$dropcap_background_color_dark = $theme->get_value( 'dropcap_background_color_dark' );

		$dark_colors_exist = ! empty( $dropcap_color_dark ) || ! empty( $dropcap_background_color_dark );

		$conditional = [];
		if ( $dark_colors_exist ) {
			$conditional = [
				'conditional' => array_merge(
					[
						'dropCapStyle' => [
							'numberOfLines' => '#dropcap_number_of_lines#',
						],
						'conditions'   => [
							'minSpecVersion'       => '1.14',
							'preferredColorScheme' => 'dark',
						],
					],
					$default_spec['conditional']
				),
			];
		}

		if ( ! empty( $dropcap_color_dark ) ) {
			$conditional['conditional']['dropCapStyle']['textColor'] = '#dropcap_color_dark#';
		}

		if ( ! empty( $dropcap_background_color_dark ) ) {
			$conditional['conditional']['dropCapStyle']['backgroundColor'] = '#dropcap_background_color_dark#';
		}

		$this->register_spec(
			'dropcapBodyStyle',
			__( 'Drop Cap Style', 'apple-news' ),
			array_merge(
				$this->get_default_style_spec(),
				[
					'dropCapStyle' => [
						'numberOfLines'       => '#dropcap_number_of_lines#',
						'numberOfCharacters'  => '#dropcap_number_of_characters#',
						'padding'             => '#dropcap_padding#',
						'fontName'            => '#dropcap_font#',
						'textColor'           => '#dropcap_color#',
						'numberOfRaisedLines' => '#dropcap_number_of_raised_lines#',
						'backgroundColor'     => '#dropcap_background_color#',
					],
				],
				$conditional
			)
		);

		$this->register_spec(
			'default-text-styles',
			__( 'Default Text Styles', 'apple-news' ),
			$this->get_default_text_styles()
		);
	}

	/**
	 * Split the non-markdownable content for processing.
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
			return [];
		}

		// Try to get matches of unsupported elements to split.
		preg_match( '#<(img|video|audio|iframe).*?(?:>(.*?)</\1>|/?>)#si', $html, $matches );
		if ( empty( $matches ) ) {

			// Ensure the resulting HTML is not devoid of actual content.
			if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
				return [];
			}

			return [
				[
					'name'  => $tag,
					'value' => $html,
				],
			];
		}

		// Split the HTML by the found element into the left and right parts.
		[ $whole, $tag_name ] = $matches;
		[ $left, $right ]     = explode( $whole, $html, 3 );

		// Additional processing for list items.
		if ( 'ol' === $tag || 'ul' === $tag ) {
			$left  = preg_replace( '/(<br\s*\/?>)+$/', '', $left );
			$right = preg_replace( '/^(<br\s*\/?>)+/', '', $right );
			$left  = preg_replace( '/\s*<li>$/is', '', trim( $left ) );
			$right = preg_replace( '/^<\/li>\s*/is', '', trim( $right ) );
		}

		// Augment left and right parts with correct opening and closing tags.
		$left  = force_balance_tags( $left . $close );
		$right = force_balance_tags( $open . $right );

		// Start building the return value.
		$elements = [
			[
				'name'  => $tag_name,
				'value' => $whole,
			],
		];

		// Check for conditions under which left should be added.
		if ( '' !== trim( wp_strip_all_tags( $left ) ) ) {
			$elements = array_merge(
				[
					[
						'name'  => $tag,
						'value' => $left,
					],
				],
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
	 * @param string $html The HTML to parse into text for processing.
	 *
	 * @access protected
	 */
	protected function build( $html ) {

		// If there is no text for this element, bail.
		$html  = $this->parser->parse( $html );
		$check = trim( $html );
		if ( empty( $check ) ) {
			return;
		}

		// Add the JSON for this component.
		$this->register_json(
			'json',
			[
				'#text#'   => $html,
				'#format#' => $this->parser->format,
			]
		);

		// Determine whether to apply dropcap style.
		$theme = Theme::get_used();
		if ( ! $theme->dropcap_applied
			&& $this->dropcap_determination( $theme, $html )
		) {
			$this->set_initial_dropcap_style();
		} else {
			$this->set_default_style();
		}

		$this->set_default_layout();
	}

	/**
	 * Whether HTML format is enabled for this component type.
	 *
	 * @param bool $enabled Optional. Whether to enable HTML support for this component. Defaults to true.
	 *
	 * @access protected
	 * @return bool Whether HTML format is enabled for this component type.
	 */
	protected function html_enabled( $enabled = true ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::html_enabled( $enabled );
	}

	/**
	 * Set the default layout for the component.
	 *
	 * @access private
	 */
	private function set_default_layout() {

		// Get information about the currently loaded theme.
		$theme = Theme::get_used();

		// Register the standard layout.
		$this->register_layout(
			'body-layout',
			'body-layout',
			[
				'#body_offset#'      => $theme->get_body_offset(),
				'#body_column_span#' => $theme->get_body_column_span(),
			],
			'layout'
		);

		// Also pre-register the layout that will be used later for the last body component.
		$this->register_layout(
			'body-layout-last',
			'body-layout-last',
			[
				'#body_offset#'      => $theme->get_body_offset(),
				'#body_column_span#' => $theme->get_body_column_span(),
			]
		);
	}

	/**
	 * Get the default style spec for the component.
	 *
	 * @return array
	 * @access private
	 */
	private function get_default_style_spec() {
		$theme                = Theme::get_used();
		$body_color_dark      = $theme->get_value( 'body_color_dark' );
		$body_link_color_dark = $theme->get_value( 'body_link_color_dark' );
		$dark_colors_exist    = ! empty( $body_color_dark ) || ! empty( $body_link_color_dark );

		$conditional = [];
		if ( $dark_colors_exist ) {
			$conditional = [
				'conditional' => [
					'conditions' => [
						'minSpecVersion'       => '1.14',
						'preferredColorScheme' => 'dark',
					],
				],
			];
		}

		if ( ! empty( $body_color_dark ) ) {
			$conditional['conditional']['textColor'] = '#body_color_dark#';
		}

		if ( ! empty( $body_link_color_dark ) ) {
			$conditional['conditional']['linkStyle'] = [
				'textColor' => '#body_link_color_dark#',
			];
		}

		return array_merge(
			[
				'textAlignment'          => 'left',
				'fontName'               => '#body_font#',
				'fontSize'               => '#body_size#',
				'tracking'               => '#body_tracking#',
				'lineHeight'             => '#body_line_height#',
				'textColor'              => '#body_color#',
				'linkStyle'              => [
					'textColor' => '#body_link_color#',
				],
				'paragraphSpacingBefore' => 18,
				'paragraphSpacingAfter'  => 18,
			],
			$conditional
		);
	}

	/**
	 * Get the default style values for the component.
	 *
	 * @return array
	 * @access private
	 */
	private function get_default_style_values() {

		// Get information about the currently loaded theme.
		$theme = Theme::get_used();

		return [
			'#body_font#'            => $theme->get_value( 'body_font' ),
			'#body_size#'            => intval( $theme->get_value( 'body_size' ) ),
			'#body_tracking#'        => intval( $theme->get_value( 'body_tracking' ) ) / 100,
			'#body_line_height#'     => intval( $theme->get_value( 'body_line_height' ) ),
			'#body_color#'           => $theme->get_value( 'body_color' ),
			'#body_link_color#'      => $theme->get_value( 'body_link_color' ),
			'#body_color_dark#'      => $theme->get_value( 'body_color_dark' ),
			'#body_link_color_dark#' => $theme->get_value( 'body_link_color_dark' ),
		];
	}

	/**
	 * Compile default text styles applied to the document as a whole based on HTML tags.
	 *
	 * @return array
	 */
	private function get_default_text_styles() {
		$theme        = Theme::get_used();
		$conditionals = [];
		if ( ! empty( $theme->get_value( 'cite_color_dark' ) ) ) {
			$conditionals['cite'] = [
				'conditional' => [
					'textColor'  => '#cite_color_dark#',
					'conditions' => [
						'minSpecVersion'       => '1.14',
						'preferredColorScheme' => 'dark',
					],
				],
			];
		}
		if ( ! empty( $theme->get_value( 'monospaced_color_dark' ) ) ) {
			$conditionals['monospaced'] = [
				'conditional' => [
					'textColor'  => '#monospaced_color_dark#',
					'conditions' => [
						'minSpecVersion'       => '1.14',
						'preferredColorScheme' => 'dark',
					],
				],
			];
		}

		return [
			'default-tag-cite' => array_merge(
				[
					'fontName'   => '#cite_font#',
					'fontSize'   => '#cite_size#',
					'tracking'   => '#cite_tracking#',
					'lineHeight' => '#cite_line_height#',
					'textColor'  => '#cite_color',
				],
				$conditionals['cite'] ?? []
			),
			'default-tag-code' => array_merge(
				[
					'fontName'   => '#monospaced_font#',
					'fontSize'   => '#monospaced_size#',
					'tracking'   => '#monospaced_tracking#',
					'lineHeight' => '#monospaced_line_height#',
					'textColor'  => '#monospaced_color',
				],
				$conditionals['monospaced'] ?? []
			),
			'default-tag-pre'  => array_merge(
				[
					'textAlignment'          => 'left',
					'fontName'               => '#monospaced_font#',
					'fontSize'               => '#monospaced_size#',
					'tracking'               => '#monospaced_tracking#',
					'lineHeight'             => '#monospaced_line_height#',
					'textColor'              => '#monospaced_color#',
					'paragraphSpacingBefore' => 18,
					'paragraphSpacingAfter'  => 18,
				],
				$conditionals['monospaced'] ?? []
			),
			'default-tag-samp' => array_merge(
				[
					'fontName'   => '#monospaced_font#',
					'fontSize'   => '#monospaced_size#',
					'tracking'   => '#monospaced_tracking#',
					'lineHeight' => '#monospaced_line_height#',
					'textColor'  => '#monospaced_color#',
				],
				$conditionals['monospaced'] ?? []
			),
		];
	}

	/**
	 * Set the default style for the component.
	 *
	 * @access public
	 */
	public function set_default_style() {
		$this->register_style(
			'default-body',
			'default-body',
			$this->get_default_style_values(),
			'textStyle'
		);
	}

	/**
	 * Determine whether to apply a dropcap style for the component.
	 *
	 * @param Theme  $theme Object that stores theme level dropcap configuration.
	 * @param string $html The HTML to check for dropcap conditions. Should be the first paragraph of the post content.
	 *
	 * @return boolean
	 */
	private function dropcap_determination( $theme, $html ) {
		// Toggle dropcap determination flag so that this logic applies only to the post's first paragraph.
		$theme->dropcap_applied = true;
		$use_dropcap            = true;
		$content                = wp_strip_all_tags( $html );
		$num_chars              = mb_strlen( $content );

		// Check that the theme is configured to apply dropcap styling.
		if ( 'yes' !== $theme->get_value( 'initial_dropcap' ) ) {
			$use_dropcap = false;
		} elseif ( preg_match(
			// Regex-planation: \p{P} searches for punctuation, /u modifier makes it unicode inclusive.
			'/\p{P}$/u',
			// First character of paragraph.
			mb_substr( $content, 0, 1 )
		) ) {
			$use_dropcap = false;
			// Check that the content meets the minimum character number.
		} elseif ( 'yes' !== $theme->get_value( 'dropcap_minimum_opt_out' )
			&& ! ( $num_chars > (int) $theme->get_value( 'dropcap_minimum' ) )
		) {
			$use_dropcap = false;
		}

		/**
		 * Allows for filtering of the dropcap content before return.
		 *
		 * @param bool                  $use_dropcap Whether to apply a dropcap to this paragraph or not.
		 * @param string                $html The post content to filter.
		 * @param Theme                 $theme The theme whose dropcap options are used.
		 * @param string                $post_id The id of the post whose content we're parsing.
		 *
		 *@since 2.4.0
		 */
		return apply_filters( 'apple_news_dropcap', $use_dropcap, $html, $theme, $this->workspace->content_id );
	}

	/**
	 * Set the initial dropcap style for the component.
	 *
	 * @access private
	 */
	private function set_initial_dropcap_style() {

		// Get information about the currently loaded theme.
		$theme = Theme::get_used();

		// Negotiate the number of lines.
		$number_of_lines = absint( $theme->get_value( 'dropcap_number_of_lines' ) );
		if ( $number_of_lines < 2 ) {
			$number_of_lines = 2;
		} elseif ( $number_of_lines > 10 ) {
			$number_of_lines = 10;
		}

		// Start building the custom dropcap body style.
		$dropcap_style = [
			'#dropcap_font#'                   => $theme->get_value( 'dropcap_font' ),
			'#dropcap_number_of_characters#'   => absint( $theme->get_value( 'dropcap_number_of_characters' ) ),
			'#dropcap_number_of_lines#'        => $number_of_lines,
			'#dropcap_number_of_raised_lines#' => absint( $theme->get_value( 'dropcap_number_of_raised_lines' ) ),
			'#dropcap_padding#'                => absint( $theme->get_value( 'dropcap_padding' ) ),
			'#dropcap_color#'                  => $theme->get_value( 'dropcap_color' ),
		];

		// Add the background color, if defined.
		$background_color = $theme->get_value( 'dropcap_background_color' );
		if ( ! empty( $background_color ) ) {
			$dropcap_style['#dropcap_background_color#'] = $background_color;
		}

		$this->register_style(
			'dropcapBodyStyle',
			'dropcapBodyStyle',
			array_merge(
				$this->get_default_style_values(),
				$dropcap_style
			),
			'textStyle'
		);
	}

	/**
	 * This component needs to ensure it didn't end up with empty content.
	 * This will go through sanitize_text_field later as part of the assembled JSON.
	 * Therefore, tags aren't valid, but we need to catch them now
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

		// If the text content evaluates to empty, return an empty array.
		$sanitized_text = sanitize_text_field( $this->json['text'] );
		if ( empty( $sanitized_text ) ) {
			return [];
		}

		return parent::to_array();
	}
}
