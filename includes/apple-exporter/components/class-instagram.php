<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\Instagram class
 *
 * Contains a class which is used to transform Instagram embeds into Apple News format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.2.0
 */

namespace Apple_Exporter\Components;

/**
 * A class to transform an Instagram embed into an Instagram Apple News component.
 *
 * @since 0.2.0
 */
class Instagram extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {

		// Handling for a Gutenberg Instagram embed.
		if (
			'figure' === $node->nodeName
			&& self::node_has_class( $node, 'wp-block-embed-instagram' )
		) {
			return $node;
		}

		// Handle Instagram oEmbed URLs.
		if ( false !== self::get_instagram_url( $node->nodeValue ) ) {
			return $node;
		}

		// Look for old-style full Instagram embeds.
		if ( self::node_has_class( $node, 'instagram-media' ) ) {
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
				'role' => 'instagram',
				'URL'  => '#url#',
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

		// Try to get URL using oEmbed.
		$url = self::get_instagram_url( $html );

		// Fall back to old-style full embed if oEmbed failed.
		if ( empty( $url ) ) {
			if ( preg_match( '#https?://(www\.)?instagr(\.am|am\.com)/p/([^/]+)/#', $html, $matches ) ) {
				$url = $matches[0];
			}
		}

		// Ensure we got a URL.
		if ( empty( $url ) ) {
			return;
		}

		$this->register_json(
			'json',
			array(
				'#url#' => esc_url_raw( $url ),
			)
		);
	}

	/**
	 * A method to get an Instagram URL from provided text.
	 *
	 * @param string $text The text to parse for the Instagram URL.
	 *
	 * @see \WP_oEmbed::__construct()
	 *
	 * @access private
	 * @return string|false The Instagram URL on success, or false on failure.
	 */
	private static function get_instagram_url( $text ) {

		// Check for matches against the WordPress oEmbed signature for Instagram.
		if ( preg_match( '#^https?://(www\.)?instagr(\.am|am\.com)/p/.*$#i', $text ) ) {
			return $text;
		}

		return false;
	}
}
