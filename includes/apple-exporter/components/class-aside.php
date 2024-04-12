<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Aside class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

use Apple_Exporter\Exporter_Content;

/**
 * A component to handle aside content.
 *
 * @since 2.5.0
 */
class Aside extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		// Note: we can't use the component get_setting method or settings array here, because this is a static class.
		$class = get_option( 'apple_news_settings' )['aside_component_class'] ?? '';
		if ( empty( $class ) ) {
			return null;
		}

		if (
			self::node_has_class( $node, $class )
		) {
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
			[
				'role'       => 'aside',
				// 'layout'     => 'aside-layout',
				'components' => [
					[
						'role'   => 'body',
						'text'   => '#text#',
						'format' => 'html',
					],
				],
			]
		);

		$this->register_layout(
			'aside-layout',
			__( 'Aside Layout', 'apple-news' ),
			[
				'ignoreDocumentMargin' => true,
				'ignoreDocumentGutter' => true,
				'columnStart'          => 1,
				'columnSpan'           => 4,
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
			[
				'#text#'       => $html,
			]
		);
	}
}
