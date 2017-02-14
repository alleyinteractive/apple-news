<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\Video class
 *
 * Contains a class which is used to transform video elements into Apple News format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.2.0
 */

namespace Apple_Exporter\Components;

use \DOMElement;

/**
 * A class which is used to transform video elements into Apple News format.
 *
 * @since 0.2.0
 */
class Video extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param DOMElement $node The node to examine.
	 *
	 * @access public
	 * @return DOMElement|null The DOMElement on match, false on no match.
	 */
	public static function node_matches( $node ) {

		// Ensure that this is a video tag and that the source exists.
		if ( 'video' === $node->nodeName && self::remote_file_exists( $node ) ) {
			return $node;
		}

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

		// Ensure there is a video source to use.
		if ( ! preg_match( '/src="([^"]+)"/', $html, $matches ) ) {
			return;
		}

		// Build initial JSON.
		$this->json = array(
			'role' => 'video',
			'URL' => $matches[1],
		);

		// Add poster frame, if defined.
		if ( preg_match( '/poster="([^"]+)"/', $html, $poster ) ) {
			$this->json['stillURL'] = $this->maybe_bundle_source( $poster[1] );
		}
	}
}
