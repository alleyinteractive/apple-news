<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Slug class
 *
 * @package Apple_Exporter
 */

/**
 * A slug.
 *
 * @since 0.2.0
 */
namespace Apple_Exporter\Components;

class Slug extends Component {

	/**
	 * Get all specs used by this component.
	 *
	 * @return array
	 * @access public
	 */
	public function get_specs() {
		return $this->specs;
	}

	/**
	 * Get a spec to use for creating component JSON.
	 *
	 * @since 1.2.4
	 * @param string $spec_name The name of the spec to fetch.
	 * @access protected
	 * @return array The spec definition.
	 */
	protected function get_spec( $spec_name ) {
		if ( ! isset( $this->specs[ $spec_name ] ) ) {
			return null;
		}

		return $this->specs[ $spec_name ];
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
			array(
				'role' => 'heading',
				'text' => '#text#',
			)
		);

		$this->register_spec(
			'default-slug',
			__( 'Style', 'apple-news' ),
			array(
				'textAlignment' => '#text_alignment#',
				'fontName' => '#slug_font#',
				'fontSize' => '#slug_size#',
				'lineHeight' => '#slug_line_height#',
				'tracking' => '#slug_tracking#',
				'textColor' => '#slug_color#',
			)
		);

		$this->register_spec(
			'slug-layout',
			__( 'Layout', 'apple-news' ),
			array(
				'margin' => array(
					'top' => 30,
					'bottom' => 0,
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
			'default-slug',
			'default-slug',
			array(
				'#text_alignment#' => $this->find_text_alignment(),
				'#slug_font#' => $theme->get_value( 'slug_font' ),
				'#slug_size#' => intval( $theme->get_value( 'slug_size' ) ),
				'#slug_line_height#' => intval( $theme->get_value( 'slug_line_height' ) ),
				'#slug_tracking#' => intval( $theme->get_value( 'slug_tracking' ) ) / 100,
				'#slug_color#' => $theme->get_value( 'slug_color' ),
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
			'slug-layout',
			'slug-layout',
			array(),
			'layout'
		);
	}

}
