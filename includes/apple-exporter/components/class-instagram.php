<?php
namespace Apple_Exporter\Components;

/**
 * Instagram embed code consists of a blockquote followed by a script tag.
 * Parse the blockquote only and ignore the script tag, as all we need is the
 * URL.
 *
 * @since 0.2.0
 */
class Instagram extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param DomNode $node
	 * @return mixed
	 * @static
	 * @access public
	 */
	public static function node_matches( $node ) {
		if ( self::node_has_class( $node, 'instagram-media' ) ) {
			return $node;
		}

		return null;
	}

	/**
	 * Build the component.
	 *
	 * @param string $text
	 * @access protected
	 */
	protected function build( $text ) {
		// Find instagram URL in HTML string
		// Include optional `www.` - the embed processing includes `www.` in the resulting blockquote.
		if ( ! preg_match( '#https?://(www\.)?instagr(\.am|am\.com)/p/([^/]+)/#', $text, $matches ) ) {
			return null;
		}

		$url = $matches[0];

		$this->json = array(
			'role' => 'instagram',
			// Remove `www.` from URL as AN parser doesn't allow for it.
			'URL'  => str_replace( 'www.', '', $url ),
		);
	}

}

