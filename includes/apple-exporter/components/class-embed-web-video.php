<?php
namespace Apple_Exporter\Components;

/**
 * An embedded video from Youtube or Vimeo, for example. For now, assume
 * any iframe is an embedded video.
 *
 * @since 0.2.0
 */
class Embed_Web_Video extends Component {

	/**
	 * Regex patterns to match supported embed types.
	 */
	const YOUTUBE_MATCH = '#^https?://(?:www\.)?(?:youtube\.com/watch\?v=([\w\-]+)|youtu\.be/([\w\-]+))[^ ]+$#';
	const VIMEO_MATCH   = '#^https?://(?:.+\.)?vimeo\.com/(:?.+/)?(\d+)$#';

	/**
	 * Checked if this is a valid Vimeo URL.
	 *
	 * @param string $text
	 * @return boolean
	 * @static
	 * @access private
	 */
	private static function is_vimeo_url( $text ) {
		return 1 === preg_match( self::VIMEO_MATCH, trim( $text ) );
	}

	/**
	 * Look for node matches for this component.
	 *
	 * @param DomNode $node
	 * @return mixed
	 * @static
	 * @access public
	 */
	public static function node_matches( $node ) {
		$is_youtube_url = $node->nodeName == 'p' && preg_match( self::YOUTUBE_MATCH, trim( $node->nodeValue ) );
		$is_vimeo_url   = $node->nodeName == 'p' && preg_match( self::VIMEO_MATCH  , trim( $node->nodeValue ) );

		// Is this node valid for further processing?
		if ( $is_youtube_url || $is_vimeo_url ) {
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
		$aspect_ratio = 1.777;
		$src          = null;

		// If a paragraph was matched, it's because it only contains a EWV URL.
		if ( preg_match( '#<p.*?>(.*?)</p>#', $text, $matches ) ) {
			$url = trim( $matches[1] );
			// The URL is either a YouTube or Vimeo video.
			if ( preg_match( self::YOUTUBE_MATCH, $url, $matches ) ) {
				$src = 'https://www.youtube.com/embed/' . ( $matches[1] ?: $matches[2] );
			} else {
				preg_match( self::VIMEO_MATCH, $url, $matches );
				$src = 'https://player.vimeo.com/video/' . $matches[2];
			}
		}

		$this->json = array(
			'role'        => 'embedwebvideo',
			'aspectRatio' => floatval( $aspect_ratio ),
			'URL'         => $src,
		);
	}

}
