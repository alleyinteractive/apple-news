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

use \DOMElement;

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
				'URL' => '#url#',
			)
		);
	}

	/**
	 * Look for node matches for this component.
	 *
	 * @param DOMElement $node The node to examine.
	 *
	 * @access public
	 * @return DOMElement|null The DOMElement on match, false on no match.
	 */
	public static function node_matches( $node ) {

		// Check for element with just facebook url.
		if ( false !== self::_get_facebook_url( $node->nodeValue ) ) {
			return $node;
		}

		// Handling for a rendered facebook embed.
		if (
			'div' === $node->nodeName
			&& (
				self::node_has_class( $node, 'fb-post' )
				|| self::node_has_class( $node, 'fb-video' )
				|| self::node_has_class( $node, 'fb-comment-embed' )
			)
		) {

			// Extract facebook url from element's data-href property.
			$fb_url = $node->getAttribute( 'data-href' );

			// Ensure we have a valid facebook embed url.
			if (
				! empty( $fb_url )
				&& false !== self::_get_facebook_url( $fb_url )
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
	 *
	 * @access protected
	 */
	protected function build( $html ) {

		// Check for data-href property on rendered <div>.
		if ( preg_match( '/data-href="([^"]+)"/i', $html, $data_href ) ) {
			$html = $data_href[1];
		}

		$this->register_json(
			'json',
			array(
				'#url#' => self::_get_facebook_url( strip_tags( $html ) ),
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
	private static function _get_facebook_url( $text ) {

		// Loop through whitelisted formats looking for matches.
		foreach ( self::$_formats as $format ) {
			if ( preg_match( $format, $text ) ) {
				return untrailingslashit( $text );
			}
		}

		return false;
	}
}
