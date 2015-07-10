<?php
namespace Exporter\Components;

/**
 * A tweet embed code consists of a blockquote followed by a script tag. Parse
 * the blockquote only and ignore the script tag, as all we need is the URL.
 *
 * @since 0.2.0
 */
class Tweet extends Component {

	public static function node_matches( $node ) {
		// Check if the body of a node is solely a tweet URL
		$is_twitter_url = $node->nodeName == 'p' && preg_match(
			'#^https?://(?:www\.)?twitter.com/(?:\#!/)?([^/]*)/status(?:es)?/(\d+)$#',
			trim( $node->nodeValue ) );

		if ( self::node_has_class( $node, 'twitter-tweet' ) || $is_twitter_url ) {
			return $node;
		}

		return null;
	}

	protected function build( $text ) {
		// Find tweeter URL in HTML string
		if ( ! preg_match_all( '/https?:\/\/(?:www\.)?twitter.com\/(?:#!\/)?([^\/]*)\/status(?:es)?\/(\d+)/', $text, $matches, PREG_SET_ORDER ) ) {
			return null;
		}

		$matches = array_pop( $matches );

		$url = 'https://twitter.com/' . $matches[1] . '/status/' . $matches[2];
		$this->json = array(
			'role' => 'tweet',
			'URL'  => $url,
		);
	}

}

