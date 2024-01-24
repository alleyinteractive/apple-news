<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Heading class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

use Apple_Exporter\Theme;
use DOMElement;

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
	 */
	public static array $levels = [ 1, 2, 3, 4, 5, 6 ];

	/**
	 * Look for node matches for this component.
	 *
	 * @param DOMElement $node The node to examine for matches.
	 *
	 * @return array|DOMElement|null The node on success, array in the case of an image,
	 * or null on no match.
	 * @access public
	 */
	public static function node_matches( $node ) {
		$regex = sprintf(
			'#h[%s-%s]#',
			self::$levels[0],
			self::$levels[ count( self::$levels ) - 1 ]
		);

		if ( ! preg_match( $regex, $node->nodeName ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			return null;
		}

		$html = $node->ownerDocument->saveXML( $node ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
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
	public function register_specs(): void {
		$theme = Theme::get_used();

		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role'       => '#heading_level#',
				'text'       => '#text#',
				'format'     => '#format#',
				'identifier' => '#identifier#',
			]
		);

		$this->register_spec(
			'heading-layout',
			__( 'Layout', 'apple-news' ),
			[
				'columnStart' => '#body_offset#',
				'columnSpan'  => '#body_column_span#',
				'margin'      => [
					'bottom' => 15,
					'top'    => 15,
				],
			]
		);

		foreach ( self::$levels as $level ) {
			$conditional = [];
			if ( ! empty( $theme->get_value( 'header' . $level . '_color_dark' ) ) ) {
				$conditional = [
					'conditional' => [
						'textColor'  => '#header' . $level . '_color_dark#',
						'conditions' => [
							'minSpecVersion'       => '1.14',
							'preferredColorScheme' => 'dark',
						],
					],
				];
			}
			$this->register_spec(
				'default-heading-' . $level,
				sprintf(
					// translators: token is the heading level.
					__( 'Level %s Style', 'apple-news' ),
					$level
				),
				array_merge(
					[
						'fontName'      => '#header' . $level . '_font#',
						'fontSize'      => '#header' . $level . '_size#',
						'lineHeight'    => '#header' . $level . '_line_height#',
						'textColor'     => '#header' . $level . '_color#',
						'textAlignment' => '#text_alignment#',
						'tracking'      => '#header' . $level . '_tracking#',
					],
					$conditional
				)
			);
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
	protected function html_enabled( $enabled = true ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		// TODO: The html_enabled methods in all the child classes
		// can be removed with a little refactoring in the future
		// since the parent method is the same and these are only being
		// used to set state.
		return parent::html_enabled( $enabled );
	}

	/**
	 * Split the image parts.
	 *
	 * @param string $html The node, rendered to HTML.
	 *
	 * @access private
	 * @return array An array of split components.
	 */
	private static function split_image( string $html ): array {
		if ( empty( $html ) ) {
			return [];
		}

		// Find the first image inside.
		preg_match( '#<img.*?>#si', $html, $matches );

		if ( ! $matches ) {
			return [
				[
					'name'  => 'heading',
					'value' => $html,
				],
			];
		}

		$image_html   = $matches[0];
		$heading_html = str_replace( $image_html, '', $html );

		return [
			[
				'name'  => 'heading',
				'value' => self::clean_html( $heading_html ),
			],
			[
				'name'  => 'img',
				'value' => $image_html,
			],
		];
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 * @access protected
	 */
	protected function build( $html ): void {
		// Match HTML headings, capture level, id value if set, and heading text.
		if ( 0 === preg_match( '/<h(\d)(?:[^>]*?\sid="([^"]*?)")?[^>]*?>(.*?)<\/h\1>/si', $html, $matches ) ) {
			return;
		}

		$level = intval( $matches[1] );
		$id    = $matches[2] ?? null;
		$text  = $matches[3];

		// Parse and trim the resultant text, and if there is nothing left, bail.
		$text = trim( $this->parser->parse( $text ) );
		if ( empty( $text ) ) {
			return;
		}

		$this->register_json(
			'json',
			[
				'#heading_level#' => 'heading' . $level,
				'#text#'          => $text,
				'#format#'        => $this->parser->format,
				'#identifier#'    => $id,
			]
		);

		$this->set_style( $level );
		$this->set_layout();
	}

	/**
	 * Set the layout for the component.
	 *
	 * @access private
	 */
	private function set_layout(): void {

		// Get information about the currently loaded theme.
		$theme = Theme::get_used();

		$this->register_layout(
			'heading-layout',
			'heading-layout',
			[
				'#body_offset#'      => $theme->get_body_offset(),
				'#body_column_span#' => $theme->get_body_column_span(),
			],
			'layout'
		);
	}

	/**
	 * Set the style for the component.
	 *
	 * @param int $level The heading level (1-6).
	 *
	 * @access private
	 */
	private function set_style( int $level ): void {

		// Get information about the currently loaded theme.
		$theme = Theme::get_used();

		$this->register_style(
			'default-heading-' . $level,
			'default-heading-' . $level,
			[
				'#header' . $level . '_font#'        => $theme->get_value( 'header' . $level . '_font' ),
				'#header' . $level . '_size#'        => intval( $theme->get_value( 'header' . $level . '_size' ) ),
				'#header' . $level . '_line_height#' => intval( $theme->get_value( 'header' . $level . '_line_height' ) ),
				'#header' . $level . '_color#'       => $theme->get_value( 'header' . $level . '_color' ),
				'#header' . $level . '_color_dark#'  => $theme->get_value( 'header' . $level . '_color_dark' ),
				'#text_alignment#'                   => $this->find_text_alignment(),
				'#header' . $level . '_tracking#'    => intval( $theme->get_value( 'header' . $level . '_tracking' ) ) / 100,
			],
			'textStyle'
		);
	}
}
