<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\TikTok class
 *
 * Contains a class which is used to transform TikTok embeds into Apple News format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 2.4.0
 */

namespace Apple_Exporter\Components;

/**
 * A class to transform an TikTok embed into an TikTok Apple News component.
 *
 * @since 2.4.0
 */
class TikTok extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		/* phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase */

		// Handling for a Gutenberg TikTok embed.
		if (
			'figure' === $node->nodeName
			&& self::node_has_class( $node, 'wp-block-embed-tiktok' )
		) {
			return $node;
		}

		// Handle TikTok oEmbed URLs.
		if ( false !== self::get_tiktok_url( $node->nodeValue ) ) {
			return $node;
		}

		// Look for full html TikTok embeds.
		if (
			'blockquote' === $node->nodeName
			&& self::node_has_class( $node, 'tiktok-embed' )
		) {
			return $node;
		}

		return null;
		/* phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase */
	}

	/**
	 * Register all specs for the component.
	 */
	public function register_specs() {
		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role' => 'tiktok',
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

		// Try to get URL using oEmbed.
		// Works for Gutenberg block, classic oEmbed, and full html embed.
		$url = self::get_tiktok_url( $html );

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
	 * A method to get an TikTok URL from provided text.
	 *
	 * @param string $text The text to parse for the TikTok URL.
	 *
	 * @see \WP_oEmbed::__construct()
	 *
	 * @return string|false The TikTok URL on success, or false on failure.
	 */
	private static function get_tiktok_url( $text ) {

		// Check for matches against the WordPress oEmbed signature for TikTok.
		if ( preg_match_all(
			'(https?:\/\/(www\.)?tiktok\.com\/@.*\/video\/\d{19})',
			$text,
			$matches
		) ) {
			return $matches[0][0];
		}

		return false;
	}
}
