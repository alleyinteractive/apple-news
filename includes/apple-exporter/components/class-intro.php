<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Intro class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

/**
 * Some Exporter_Content object might have an intro parameter.
 * This component does not need a node so no need to implement match_node.
 *
 * @since 0.2.0
 */
class Intro extends Component {

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			array(
				'role' => 'intro',
				'text' => '#text#',
			)
		);

		$this->register_spec(
			'default-intro',
			__( 'Style', 'apple-news' ),
			array(
				'fontName'   => '#body_font#',
				'fontSize'   => '#body_size#',
				'lineHeight' => '#body_line_height#',
				'textColor'  => '#body_color#',
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

		// If there is no text for this element, bail.
		$check = trim( $html );
		if ( empty( $check ) ) {
			return;
		}

		$this->register_json(
			'json',
			array(
				'#text#' => $html . "\n",
			)
		);

		$this->set_style();
	}

	/**
	 * Set the style for the component.
	 *
	 * @access private
	 */
	private function set_style() {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		$this->register_style(
			'default-intro',
			'default-intro',
			array(
				'#body_font#'        => $theme->get_value( 'body_font' ),
				'#body_size#'        => intval( $theme->get_value( 'body_size' ) ),
				'#body_line_height#' => intval( $theme->get_value( 'body_line_height' ) ),
				'#body_color#'       => $theme->get_value( 'body_color' ),
			),
			'textStyle'
		);
	}

}

