<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\Podcast class
 *
 * Contains a class which is used to transform Podcast embeds into Apple News format.
 * Documentation for Apple Podcast players can be found here: https://podcasters.apple.com/support/889-apple-podcasts-embed-player.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 2.4.0
 */

namespace Apple_Exporter\Components;

/**
 * A class to transform a Podcast embed into an Apple News Podcast component.
 *
 * @since 2.4.0
 */
class Podcast extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 *
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		// Handle node if Podcast url is present.
		if ( self::get_podcast_url( $node ) ) {
			return $node;
		}

		return null;
	}

	/**
	 * Register all specs for the component.
	 */
	public function register_specs() {
		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role' => 'podcast',
				'URL'  => '#url#',
			]
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 */
	protected function build( $html ) {
		// Try to get podcast URL.
		$url = self::get_podcast_url( $html );

		// Ensure we got a URL.
		if ( empty( $url ) ) {
			return;
		}

		$this->register_json(
			'json',
			[
				'#url#' => esc_url_raw( $url ),
			]
		);
	}

	/**
	 * A method to get an Podcast URL from provided node.
	 *
	 * @param \DOMElement|string $node The node to parse for the Podcast URL.
	 *
	 * @return string|null The Podcast URL on success, or null on failure.
	 */
	private static function get_podcast_url( $node ) {
		if ( 'string' === gettype( $node ) ) {
			$dom = new \DomDocument();
			$dom->loadHTML( $node );
			$node = $dom;
		}

		$iframe = self::get_iframe_from_node( $node );

		if ( ! $iframe ) {
			return;
		}

		// Pattern match src attribute for apple podcast url.
		$url = $iframe->getAttribute( 'src' );

		if ( empty( $url ) || false === strpos( $url, 'podcasts.apple.com' ) ) {
			return;
		}

		// Remove iframe specific `embed.` prefix on podcast url.
		$url = str_replace( 'embed.podcasts.apple.com', 'podcasts.apple.com', $url );

		// Parse url into component parts.
		$url_comps = wp_parse_url( $url );

		// Reassemble url without query params.
		$url = sprintf(
			'%1$s://%2$s%3$s',
			$url_comps['scheme'],
			$url_comps['host'],
			$url_comps['path']
		);

		// Parse params from url query component.
		if ( ! empty( $url_comps['query'] ) ) {
			parse_str( $url_comps['query'], $params );
		}

		// `?i=` query param contains podcast episode info.
		// If it exists, we need to retain it.
		if ( $params && ! empty( $params['i'] ) ) {
			// Append 'i' pararm to end of url.
			$url .= '?' . http_build_query( [ 'i' => $params['i'] ] );
		}

		return $url;
	}
}
