<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Link class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

/**
 * A Link/Anchor component.
 *
 * @since 2.0.0
 */
class Link_Button extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		return 'a' === $node->nodeName // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			&& self::node_has_class( $node, 'wp-block-button__link' )
			&& ! empty( $node->getAttribute( 'href' ) )
			&& ! empty( $node->nodeValue ) // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
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
				'role'      => 'link_button',
				'text'      => '#text#',
				'URL'       => '#url#',
				'style'     => 'default-link-button',
				'layout'    => 'link-button-layout',
				'textStyle' => 'default-link-button-text-style',
			]
		);

		// Register the JSON for the link button layout.
		$this->register_spec(
			'link-button-layout',
			__( 'Button Layout', 'apple-news' ),
			[
				'horizontalContentAlignment' => '#button_horizontal_alignment#',
				'padding'                    => [
					'top'    => 15,
					'bottom' => 15,
					'left'   => 15,
					'right'  => 15,
				],
			]
		);

		// Register the JSON for the link button style.
		$this->register_spec(
			'default-link-button',
			__( 'Link Button Style', 'apple-news' ),
			[
				'backgroundColor' => '#button_background_color#',
				'border'          => [
					'all' => [
						'width' => '#button_border_width#',
						'color' => '#button_border_color#',
					],
				],
				'mask'            => [
					'type'   => 'corners',
					'radius' => '#button_border_radius#',
				],
			]
		);

		// Register the JSON for the link button text style.
		$this->register_spec(
			'default-link-button-text-style',
			__( 'Link Button Text Style', 'apple-news' ),
			[
				'fontName'      => '#button_font_face#',
				'fontSize'      => '#button_font_size#',
				'hyphenation'   => false,
				'lineHeight'    => 18,
				'textAlignment' => 'center',
				'textColor'     => '#button_text_color#',
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

		// Extract the button href and text to register the JSON.
		if ( preg_match( '/<a.+?href="([^"]+)".*?>([^<]+)<\/a>/', $html, $link_button_match ) ) {
			// Negotiate the URL.
			$url = $link_button_match[1];
			if ( 0 === strpos( $url, '/' ) ) {
				$url = home_url( $url );
			} elseif ( 0 === strpos( $url, '#' ) ) {
				$url = trailingslashit( get_the_permalink() ) . $url;
			}

			// Register JSON for this component.
			$this->register_json(
				'json',
				[
					'#url#'  => $url,
					'#text#' => $link_button_match[2],
				]
			);
		} else {
			// If, for some reason, the match failed, bail out.
			return;
		}

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		$this->set_default_style( $theme );
		$this->set_default_layout( $theme );
	}

	/**
	 * Set the default style for the component.
	 *
	 * @param \Apple_Exporter\Theme $theme The currently loaded theme.
	 */
	private function set_default_style( $theme ) {
		// Register component styles.
		$this->register_component_style(
			'default-link-button',
			'default-link-button',
			[
				'#button_background_color#' => $theme->get_value( 'button_background_color' ),
				'#button_border_color#'     => $theme->get_value( 'button_border_color' ),
				'#button_border_radius#'    => (int) $theme->get_value( 'button_border_radius' ),
				'#button_border_width#'     => (int) $theme->get_value( 'button_border_width' ),
			]
		);

		// Register text styles.
		$this->register_style(
			'default-link-button-text-style',
			'default-link-button-text-style',
			[
				'#button_font_face#'  => $theme->get_value( 'button_font_face' ),
				'#button_font_size#'  => (int) $theme->get_value( 'button_font_size' ),
				'#button_text_color#' => $theme->get_value( 'button_text_color' ),
			],
			'textStyle'
		);
	}

	/**
	 * Set the default layout for the component.
	 *
	 * @param \Apple_Exporter\Theme $theme The currently loaded theme.
	 */
	private function set_default_layout( $theme ) {
		// Register layout styles.
		$this->register_layout(
			'link-button-layout',
			'link-button-layout',
			[
				'#button_horizontal_alignment#' => $theme->get_value( 'button_horizontal_alignment' ),
			],
			'layout'
		);
	}
}
