<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Footnotes class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

/**
 * A translation of the WordPress Footnotes block.
 *
 * @since 0.2.5
 */
class Footnotes extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		if (
			'ol' === $node->nodeName &&
			self::node_has_class( $node, 'wp-block-footnotes' )
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
			'footnotes-json',
			__( 'Footnotes JSON', 'apple-news' ),
			[
				'role'       => 'container',
				'layout'     => 'body-layout',
				'components' => '#components#',
			]
		);

		$this->register_spec(
			'footnote-json',
			__( 'Footnote JSON', 'apple-news' ),
			[
				'role'       => 'text',
				'text'       => '#text#',
				'identifier' => '#identifier#',
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
		preg_match_all( '/<li.*?>.*?<\/li>/', $html, $matches );
		$items = $matches[0];

		// convert each list item to a paragraph with a number added.
		foreach ( $items as $key => $item ) {
			$count = $key + 1;
			$text = preg_replace(
				'/<li(.*?)>(.*?)<\/li>/',
				"<p$1>${count}. $2</p>",
				$item
			);
			preg_match( '/id="(.*?)"/', $text, $matches );
			$id = $matches[1] ?? null;
			$components[] = [
				'role'       => 'body',
				'text'       => $text,
				'format'     => 'html',
				'identifier' => $id,
			];
		}
		$this->register_json(
			'footnotes-json',
			[
				'#components#' => $components,
			]
		);
	}
}
