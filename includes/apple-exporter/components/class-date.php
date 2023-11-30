<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Date class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

/**
 * A date normally describes who wrote the article, the date, etc.
 *
 * @since 0.2.0
 */
class Date extends Component {

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		$theme = \Apple_Exporter\Theme::get_used();

		$this->register_spec(
			'date-json',
			__( 'Date JSON', 'apple-news' ),
			[
				'role' => 'body',
				'text' => '#text#',
			]
		);

		// date style conditional.
		$date_conditional = [];

		if ( ! empty( $theme->get_value( 'date_color_dark' ) ) ) {
			$date_conditional['conditional'][] = [
				'textColor'  => '#date_color_dark#',
				'conditions' => [
					'minSpecVersion'       => '1.14',
					'preferredColorScheme' => 'dark',
				],
			];
		}

		$this->register_spec(
			'default-date',
			__( 'Style', 'apple-news' ),
			array_merge(
				[
					'textAlignment' => '#text_alignment#',
					'fontName'      => '#date_font#',
					'fontSize'      => '#date_size#',
					'lineHeight'    => '#date_line_height#',
					'tracking'      => '#date_tracking#',
					'textColor'     => '#date_color#',
				],
				$date_conditional
			)
		);

		$this->register_spec(
			'date-layout',
			__( 'Layout', 'apple-news' ),
			[
				'margin' => [
					'bottom' => 10,
				],
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

		// If there is no text for this element, bail.
		$check = trim( $html );
		if ( empty( $check ) ) {
			return;
		}

		$this->register_json(
			'date-json',
			[
				'#text#' => $html,
			]
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

		$date_conditional = [];

		if ( ! empty( $theme->get_value( 'date_color_dark' ) ) ) {
			$date_conditional[] = [
				'#date_color_dark#' => $theme->get_value( 'date_color_dark' ),
			];
		}

		$this->register_style(
			'default-date',
			'default-date',
			array_merge(
				[
					'#text_alignment#'   => $this->find_text_alignment(),
					'#date_font#'        => $theme->get_value( 'date_font' ),
					'#date_size#'        => intval( $theme->get_value( 'date_size' ) ),
					'#date_line_height#' => intval( $theme->get_value( 'date_line_height' ) ),
					'#date_tracking#'    => intval( $theme->get_value( 'date_tracking' ) ) / 100,
					'#date_color#'       => $theme->get_value( 'date_color' ),
				],
				$date_conditional
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
			'date-layout',
			'date-layout',
			[],
			'layout'
		);
	}
}
