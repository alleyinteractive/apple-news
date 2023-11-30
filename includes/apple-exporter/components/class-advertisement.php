<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Advertisement class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

/**
 * Represents an Article Format Advertisement. It gets generated automatically
 * so not much to do here but define the static JSON.
 *
 * @since 0.4.0
 */
class Advertisement extends Component {

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
				'role'       => 'banner_advertisement',
				'bannerType' => 'standard',
			]
		);

		$this->register_spec(
			'layout',
			__( 'Layout', 'apple-news' ),
			[
				'margin' => [
					'top'    => 25,
					'bottom' => 25,
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
	protected function build( $html ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$this->register_json(
			'json',
			[]
		);

		$this->set_layout();
	}

	/**
	 * Set the layout for the component.
	 *
	 * @access private
	 */
	private function set_layout() {
		$this->register_full_width_layout(
			'advertisement-layout',
			'layout',
			[],
			'layout'
		);
	}
}
