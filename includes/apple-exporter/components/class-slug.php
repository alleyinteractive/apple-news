<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Slug class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

/**
 * Represents an article slug (a word or phrase that appears near the article title).
 *
 * @since 2.2.0
 */
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
	 * @since 2.2.0
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
		$theme = \Apple_Exporter\Theme::get_used();

		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role' => 'heading',
				'text' => '#text#',
			]
		);

		$this->register_spec(
			'default-slug',
			__( 'Style', 'apple-news' ),
			(
				[
					'textAlignment' => '#text_alignment#',
					'fontName'      => '#slug_font#',
					'fontSize'      => '#slug_size#',
					'lineHeight'    => '#slug_line_height#',
					'tracking'      => '#slug_tracking#',
					'textColor'     => '#slug_color#',
				] + (
				! empty( $theme->get_value( 'slug_color_dark' ) )
					? [
						'conditional' => [
							'textColor'  => '#slug_color_dark#',
							'conditions' => [
								'minSpecVersion'       => '1.14',
								'preferredColorScheme' => 'dark',
							],
						],
					]
					: []
				)
			)
		);

		$this->register_spec(
			'slug-layout',
			__( 'Layout', 'apple-news' ),
			[
				'margin' => [
					'top'    => 30,
					'bottom' => 0,
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
			'json',
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

		$this->register_style(
			'default-slug',
			'default-slug',
			[
				'#text_alignment#'   => $this->find_text_alignment(),
				'#slug_font#'        => $theme->get_value( 'slug_font' ),
				'#slug_size#'        => intval( $theme->get_value( 'slug_size' ) ),
				'#slug_line_height#' => intval( $theme->get_value( 'slug_line_height' ) ),
				'#slug_tracking#'    => intval( $theme->get_value( 'slug_tracking' ) ) / 100,
				'#slug_color#'       => $theme->get_value( 'slug_color' ),
			],
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
			[],
			'layout'
		);
	}
}
