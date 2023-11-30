<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\End_Of_Article class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

/**
 * Represents an End of Article module. The module initializes as empty and
 * is defined by the user via the Customize JSON feature. If non-empty, it is
 * inserted onto the end of the article body.
 *
 * @since 2.1.0
 */
class End_Of_Article extends Component {

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[]
		);

		$this->register_spec(
			'layout',
			__( 'Layout', 'apple-news' ),
			[]
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
			'end-of-article-layout',
			'layout',
			[],
			'layout'
		);
	}
}
