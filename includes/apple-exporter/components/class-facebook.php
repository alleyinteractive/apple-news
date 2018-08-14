<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\Facebook class
 *
 * Contains a class which is used to transform Facebook embeds into Apple News format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.2.0
 */

namespace Apple_Exporter\Components;

/**
 * A class to transform a Facebook oEmbed into a Facebook Apple News component.
 *
 * @since 0.2.0
 */
class Facebook extends Component {

	/**
	 * A list of regular expression patterns for whitelisted Facebook oEmbed formats.
	 *
	 * @see https://developer.apple.com/library/prerelease/content/documentation/General/Conceptual/Apple_News_Format_Ref/FacebookPost.html#//apple_ref/doc/uid/TP40015408-CH106-SW1
	 *
	 * @access private
	 * @var array
	 */
	private static $_formats = array(
		'/^https:\/\/www\.facebook\.com\/[^\/]+\/posts\/[^\/]+\/?$/',
		'/^https:\/\/www\.facebook\.com\/[^\/]+\/activity\/[^\/]+\/?$/',
		'/^https:\/\/www\.facebook\.com\/photo.php\?fbid=.+$/',
		'/^https:\/\/www\.facebook\.com\/photos\/[^\/]+\/?$/',
		'/^https:\/\/www\.facebook\.com\/permalink\.php\?story_fbid=.+$/',
	);

	/**
	 * Regular expressions for extracting post URLs from HTML markup.
	 *
	 * @access private
	 * @var array
	 */
	private static $_url_signatures = array(
		'/data-href="([^"]+)"/i',
		'/<(?:fb:)?post\s.*?href="([^"]+)"/i',
	);

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
				'role' => 'facebook_post',
				'URL'  => '#url#',
			)
		);
	}

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {

		// Check for element with just facebook url.
		if ( false !== self::get_facebook_url( $node->nodeValue ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			return $node;
		}

		// Handling for a rendered facebook embed.
		if (
			'div' === $node->nodeName // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			&& self::node_has_class( $node, 'fb-post' )
		) {

			// Extract facebook url from element's data-href property.
			$fb_url = $node->getAttribute( 'data-href' );

			// Ensure we have a valid facebook embed url.
			if (
				! empty( $fb_url )
				&& false !== self::get_facebook_url( $fb_url )
			) {
				return $node;
			}
		}

		// Handling for a rendered WordPress.com Facebook embed.
		$html = $node->ownerDocument->saveXML( $node ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
		if ( preg_match( '/<(?:fb:)?post\s.*?href="([^"]+)"/i', $html, $matches ) ) {

			// Ensure we have a valid Facebook embed URL.
			if ( ! empty( $matches[1] )
				&& false !== self::get_facebook_url( $matches[1] )
			) {
				return $node;
			}
		}

		// facebook not found.
		return null;
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 * @access protected
	 */
	protected function build( $html ) {

		// Check for href properties on rendered embeds.
		foreach ( self::$_url_signatures as $signature ) {
			if ( preg_match( $signature, $html, $matches ) ) {
				$html = $matches[1];
				break;
			}
		}

		// Try to get Facebook URL.
		$url   = self::get_facebook_url( wp_strip_all_tags( $html ) );
		$check = trim( $url );
		if ( empty( $check ) ) {
			return;
		}

		$this->register_json(
			'json',
			array(
				'#url#' => $url,
			)
		);
	}

	/**
	 * A method to get a Facebook URL from a whitelisted set of formats.
	 *
	 * @param string $text The text to parse for the Facebook URL.
	 *
	 * @access private
	 * @return string|false The Facebook URL on success, or false on failure.
	 */
	private static function get_facebook_url( $text ) {

		// Loop through whitelisted formats looking for matches.
		foreach ( self::$_formats as $format ) {
			if ( preg_match( $format, $text ) ) {
				return untrailingslashit( $text );
			}
		}

		return false;
	}
}
