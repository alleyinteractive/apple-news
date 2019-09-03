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

use \Apple_Exporter\Exporter_Content;

/**
 * A class which is used to transform video elements into Apple News format.
 *
 * @since 0.2.0
 */
class Video extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {

		// Ensure that this is a video tag and that the source exists.
		if ( 'video' === $node->nodeName && self::remote_file_exists( $node ) ) {
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
				'role'     => 'video',
				'URL'      => '#url#',
				'stillURL' => '#still_url#',
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

		// Ensure there is a video source to use.
		if ( ! preg_match( '/src="([^"]+)"/', $html, $matches ) ) {
			return;
		}

		// Ensure the source URL is valid.
		$url = Exporter_Content::format_src_url( $matches[1] );
		if ( empty( $url ) ) {
			return;
		}

		// Set values.
		$values = array(
			'#url#' => esc_url_raw( $url ),
		);

		// Add poster frame, if defined.
		if ( preg_match( '/poster="([^"]+)"/', $html, $poster ) ) {
			$still_url = Exporter_Content::format_src_url( $poster[1] );
			if ( ! empty( $still_url ) ) {
				$values['#still_url#'] = $this->maybe_bundle_source( $poster[1] );
			}
		}

		$this->register_json(
			'json',
			$values
		);
	}
}
