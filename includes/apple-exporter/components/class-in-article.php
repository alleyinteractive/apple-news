<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\In_Article class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

/**
 * Represents an In Article module. The module initializes as empty and
 * is defined by the user via the Customize JSON feature. If non-empty, it is
 * inserted into the article body after the block occupying the specified position.
 *
 * @since 2.5.0
 */
class In_Article extends Component {
	/**
	 * Register all specs for the component.
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
			'in-article-layout',
			'layout',
			[],
			'layout'
		);
	}
}
