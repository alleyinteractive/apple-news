<?php
/**
 * Publish to Apple News Components: Embed_Web_Video class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.2.0
 */

namespace Apple_Exporter\Components;

use Apple_Exporter\Components\Component;

/**
 * An embedded video from Youtube or Vimeo, which are the only two providers that Apple supports.
 *
 * @since 0.2.0
 */
class Embed_Web_Video extends Component {

	/**
	 * Regex pattern for a Vimeo video.
	 */
	const VIMEO_MATCH = '#^(https?:)?//(?:.+\.)?vimeo\.com/(:?.+/)?(\d+)(?:\?.*)*$#';

	/**
	 * Regex pattern for a YouTube video.
	 */
	const YOUTUBE_MATCH = '#^https?://(?:www\.)?(?:youtube\.com/((watch\?v=)|(embed/))([\w\-]+)|youtu\.be/([\w\-]+))[^ ]*$#';

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		// First, check to see if the node is a YouTube or Vimeo Gutenberg block, because these are the simplest checks to make.
		$is_figure        = 'figure' === $node->nodeName; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$is_vimeo_block   = self::node_has_class( $node, 'wp-block-embed-vimeo' );
		$is_youtube_block = self::node_has_class( $node, 'wp-block-embed-youtube' );
		if ( $is_figure && ( $is_vimeo_block || $is_youtube_block ) ) {
			return $node;
		}

		// Second, check to see if the node contains a YouTube or Vimeo oEmbed as a text string.
		$inner_text      = trim( $node->nodeValue ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$has_vimeo_url   = (bool) preg_match( self::VIMEO_MATCH, $inner_text );
		$has_youtube_url = (bool) preg_match( self::YOUTUBE_MATCH, $inner_text );
		if ( $has_vimeo_url || $has_youtube_url ) {
			return $node;
		}

		// Third, check to see if the node is, or contains, an iframe with a YouTube or Vimeo video.
		$inner_iframe    = self::get_iframe_from_node( $node );
		$iframe_src      = null !== $inner_iframe ? $inner_iframe->getAttribute( 'src' ) : '';
		$has_vimeo_src   = (bool) preg_match( self::VIMEO_MATCH, $iframe_src );
		$has_youtube_src = (bool) preg_match( self::YOUTUBE_MATCH, $iframe_src );
		if ( $has_vimeo_src || $has_youtube_src ) {
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
			[
				'role'        => 'embedwebvideo',
				'aspectRatio' => '#aspect_ratio#',
				'URL'         => '#url#',
				'layout'      => 'embed-web-video-layout',
			]
		);

		// Register the JSON for the link button layout.
		$this->register_spec(
			'embed-web-video-layout',
			__( 'Web Embed Layout', 'apple-news' ),
			[
				'margin' => [
					'bottom' => 18,
					'top'    => 18,
				],
			]
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 * @access protected
	 */
	protected function build( $html ) {
		$ratio_x = 16;
		$ratio_y = 9;
		$src     = '';

		// Try to extract the aspect ratio from the markup.
		if ( preg_match( '/wp-embed-aspect-([0-9]+)-([0-9]+)/', $html, $match ) ) {
			$maybe_ratio_x = (int) $match[1];
			$maybe_ratio_y = (int) $match[2];
			if ( ! empty( $maybe_ratio_x ) && ! empty( $maybe_ratio_y ) ) {
				$ratio_x = $maybe_ratio_x;
				$ratio_y = $maybe_ratio_y;
			}
		}

		// Get things that look like URLs.
		if ( preg_match_all( '/https?:\/\/[^\'"\s<]+/', $html, $matches ) ) {
			foreach ( $matches[0] as $url ) {
				// Test them against the YouTube and Vimeo URL signatures.
				if ( preg_match( self::YOUTUBE_MATCH, $url, $matches ) ) {
					$src = 'https://www.youtube.com/embed/' . end( $matches );
				} elseif ( preg_match( self::VIMEO_MATCH, $url, $matches ) ) {
					$src = 'https://player.vimeo.com/video/' . end( $matches );
				}

				// If we got a hit, register the JSON and bail out.
				if ( ! empty( $src ) ) {
					$this->register_json(
						'json',
						[
							'#aspect_ratio#' => floor( 1000 * $ratio_x / $ratio_y ) / 1000,
							'#url#'          => $src,
						]
					);
					$this->register_layout( 'embed-web-video-layout', 'embed-web-video-layout' );
				}
			}
		}
	}
}
