<?php
namespace Apple_Exporter\Components;

use \Apple_Exporter\Exporter_Content;

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

		// Ensure the URL is valid.
		$url = Exporter_Content::format_src_url( $match[1] );
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

}

