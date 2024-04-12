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
 * @since 2.5.0
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
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role'       => 'container',
				'layout'     => 'body-layout',
				'components' => '#components#',
			]
		);

		$this->register_spec(
			'footnote-json',
			__( 'Individual Footnote JSON', 'apple-news' ),
			[
				'role'       => 'body',
				'text'       => '#text#',
				'format'     => 'html',
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
		$components = [];
		foreach ( $items as $key => $item ) {
			$count = $key + 1;
			$text  = preg_replace(
				'/<li(.*?)>(.*?)<\/li>/',
				"<p$1>${count}. $2</p>",
				$item
			);
			preg_match( '/id="(.*?)"/', $text, $matches );
			$id           = $matches[1] ?? null;
			$this->register_json(
				'footnote-json',
				[
					'#text#'       => $text,
					'format'       => 'html',
					'#identifier#' => $id,
				]
			);
			// The register_json function saves its result to $this->json, so extract it from there and reset it.
			$components[] = $this->json;
			$this->json   = [];
		}
		$this->register_json(
			'json',
			[
				'#components#' => $components,
			]
		);
	}
}
