<?php
namespace Apple_Exporter\Components;

class Title extends Component {

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
				'role' => 'title',
				'text' => '#text#',
				'format' => 'html',
			)
		);

		$this->register_spec(
			'default-title',
			__( 'Style', 'apple-news' ),
			array(
				'fontName' => '#header1_font#',
				'fontSize' => '#header1_size#',
				'lineHeight' => '#header1_line_height#',
				'tracking' => '#header1_tracking#',
				'textColor' => '#header1_color#',
				'textAlignment' => '#text_alignment#',
			)
		);

		$this->register_spec(
			'title-layout',
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
	 * @param string $text
	 * @access protected
	 */
	protected function build( $text ) {
		$this->register_json(
			'json',
			array(
				'#text#' => $text,
			)
		 );

		$this->set_style();
		$this->set_layout();
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
			'default-title',
			'default-title',
			array(
				'#header1_font#' => $theme->get_value( 'header1_font' ),
				'#header1_size#' => intval( $theme->get_value( 'header1_size' ) ),
				'#header1_line_height#' => intval( $theme->get_value( 'header1_line_height' ) ),
				'#header1_tracking#' => intval( $theme->get_value( 'header1_tracking' ) ) / 100,
				'#header1_color#' => $theme->get_value( 'header1_color' ),
				'#text_alignment#' => $this->find_text_alignment(),
			),
			'textStyle'
		 );
	}

	/**
	 * Set the layout for the component.
	 *
	 * @access private
	 */
	private function set_layout() {
		$this->register_layout(
			'title-layout',
			'title-layout',
			array(),
			'layout'
		);
	}

}

