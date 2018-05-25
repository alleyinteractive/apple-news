<?php
namespace Apple_Exporter\Components;

/**
 * A tweet embed code consists of a blockquote followed by a script tag. Parse
 * the blockquote only and ignore the script tag, as all we need is the URL.
 *
 * @since 0.2.0
 */
class Tweet extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		// Check if the body of a node is solely a tweet URL.
		$is_twitter_url = $node->nodeName === 'p' && preg_match(
			'#https?://(www\.)?twitter\.com/.+?/status(es)?/.*#i',
			trim( $node->nodeValue ) );

		if ( self::node_has_class( $node, 'twitter-tweet' ) || $is_twitter_url ) {
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
				'role' => 'tweet',
				'URL' => '#url#',
			)
		);

		$this->register_spec(
			'tweet-layout',
			__( 'Layout', 'apple-news' ),
			array(
				'margin' => array(
					'top' => 30,
					'bottom' => 30,
				)
			)
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $text The node, rendered to HTML.
	 * @access protected
	 */
	protected function build( $text ) {
		// Find Twitter URL in HTML string.
		if ( ! preg_match_all( '/https?:\/\/(?:www\.)?twitter.com\/(?:#!\/)?([^\/]*)\/status(?:es)?\/(\d+)/', $text, $matches, PREG_SET_ORDER ) ) {
			return;
		}

		$matches = array_pop( $matches );

		$url = 'https://twitter.com/' . $matches[1] . '/status/' . $matches[2];

		$this->register_json(
			'json',
			array(
				'#url#' => $url,
			)
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
			'tweet-layout',
			'tweet-layout',
			array(),
			'layout'
		);
	}

}
