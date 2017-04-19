<?php
namespace Apple_Exporter\Components;

/**
 * An HTML audio tag.
 *
 * @since 0.2.0
 */
class Audio extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param DomNode $node
	 * @return mixed
	 * @access public
	 */
	public static function node_matches( $node ) {
		// Is this an audio node?
		if ( 'audio' === $node->nodeName && self::remote_file_exists( $node ) ) {
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
				'role' => 'audio',
				'URL' => '#url#',
			)
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $text
	 * @access protected
	 */
	protected function build( $text ) {
		// Remove initial and trailing tags: <video><p>...</p></video>
		if ( ! preg_match( '/src="([^"]+)"/', $text, $match ) ) {
			return null;
		}

		$url = $match[1];

		// If the URL is root-relative, make it absolute.
		if ( 0 === strpos( $url, '/' ) ) {
			$url = site_url( $url );
		}

		$this->register_json(
			'json',
			array(
				'#url#' => $url,
			)
	 	);
	}

}

