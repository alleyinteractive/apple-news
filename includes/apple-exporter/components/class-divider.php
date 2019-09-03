<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Divider class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

/**
 * An HTML's divider (hr) representation.
 *
 * @since 0.2.0
 */
class Divider extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		if ( 'hr' === $node->nodeName ) {
			return $node;
		}

		return null;
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
				'role'   => 'divider',
				'layout' => 'divider-layout',
				'stroke' => array(
					'color' => '#E6E6E6',
					'width' => 1,
				),
			)
		);

		$this->register_spec(
			'divider-layout',
			__( 'Layout', 'apple-news' ),
			array(
				'margin' => array(
					'top'    => 25,
					'bottom' => 25,
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
		$this->register_json(
			'json',
			array()
		);

		$this->register_full_width_layout(
			'divider-layout',
			'divider-layout',
			array()
		);
	}

}

