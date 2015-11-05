<?php
namespace Apple_Exporter\Components;

/**
 * An HTML video tag representation.
 *
 * @since 0.2.0
 */
class Video extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param DomNode $node
	 * @return mixed
	 * @static
	 * @access public
	 */
	public static function node_matches( $node ) {
		// Is this an video node?
		if ( 'video' == $node->nodeName && self::remote_file_exists( $node ) ) {
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
		// Remove initial and trailing tags: <video><p>...</p></video>
		if ( ! preg_match( '/src="([^"]+)"/', $text, $match ) ) {
			return null;
		}

		$url = $match[1];

		$this->json = array(
			'role' => 'video',
			'URL'  => $url,
		);
		
		if ( preg_match( '/poster=\"([^\"]+)\"/si', $text, $poster) ) {
			$posterUrl = $poster[1];
			$filename = basename( $posterUrl );
			$this->bundle_source( $filename, $posterUrl );
			$this->json['stillURL'] = 'bundle://' . $filename; 
		}
		
	}

}

