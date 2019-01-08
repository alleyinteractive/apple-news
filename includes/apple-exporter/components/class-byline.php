<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Byline class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

/**
 * A byline normally describes who wrote the article, the date, etc.
 *
 * @since 0.2.0
 */
class Byline extends Component {

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
				'role' => 'byline',
				'text' => '#text#',
			)
		);

		$this->register_spec(
			'default-byline',
			__( 'Style', 'apple-news' ),
			array(
				'textAlignment' => '#text_alignment#',
				'fontName'      => '#byline_font#',
				'fontSize'      => '#byline_size#',
				'lineHeight'    => '#byline_line_height#',
				'tracking'      => '#byline_tracking#',
				'textColor'     => '#byline_color#',
			)
		);

		$this->register_spec(
			'byline-layout',
			__( 'Layout', 'apple-news' ),
			array(
				'margin' => array(
					'top'    => 10,
					'bottom' => 10,
				),
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
				'#text#' => $html,
			)
		);

		$this->set_default_style();
		$this->set_default_layout();
	}

	/**
	 * Set the default style for the component.
	 *
	 * @access private
	 */
	private function set_default_style() {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		$this->register_style(
			'default-byline',
			'default-byline',
			array(
				'#text_alignment#'     => $this->find_text_alignment(),
				'#byline_font#'        => $theme->get_value( 'byline_font' ),
				'#byline_size#'        => intval( $theme->get_value( 'byline_size' ) ),
				'#byline_line_height#' => intval( $theme->get_value( 'byline_line_height' ) ),
				'#byline_tracking#'    => intval( $theme->get_value( 'byline_tracking' ) ) / 100,
				'#byline_color#'       => $theme->get_value( 'byline_color' ),
			),
			'textStyle'
		);
	}

	/**
	 * Set the default layout for the component.
	 *
	 * @access private
	 */
	private function set_default_layout() {
		$this->register_full_width_layout(
			'byline-layout',
			'byline-layout',
			array(),
			'layout'
		);
	}

}

