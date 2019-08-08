<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\Spotify class
 *
 * Contains a class which is used to transform Spotify embeds into Apple News format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.2.0
 */

namespace Apple_Exporter\Components;

/**
 * A class to transform an Spotify embed into an Spotify Apple News component.
 *
 * @since 0.2.0
 */
class Spotify extends Component {

	/**
	 * Spotify URLs to validate.
	 *
	 * @return void
	 */
	public static function validateUrl( $url = '' ) {
		return (
			preg_match( '/https?:\/\/(?:embed\.|open\.)(?:spotify\.com\/)(?:track\/|\?uri=spotify:track:)((\w|-){22})/', $url )
		);
	}

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 *
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {

		// Match the src attribute against a classname (gutenberg) or spotify regex (classic)
		if (
			'figure' === $node->nodeName && self::node_has_class( $node, 'is-provider-spotify' )
			|| $node->hasChildNodes() && 'iframe' === $node->childNodes[0]->nodeName && self::validateUrl( $node->childNodes[0]->getAttribute( 'src' ) )
		) {
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
			'spotify-json',
			__( 'Spotify JSON', 'apple-news' ),
			array(
				'layout'     => 'spotify-layout',
				'role'       => 'container',
				'components' => '#components#',
			)
		);

		$this->register_spec(
			'spotify-layout',
			__( 'Embed Layout', 'apple-news' ),
			array(
				'margin'      => array(
					'top'    => 15,
					'bottom' => 15,
				),
			)
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 *
	 * @access protected
	 */
	protected function build( $html ) {

		$caption              = '';
		$hide_article_caption = true;
		$link                 = '';
		$title                = '';
		$url                  = '';

		// If we have a url, parse.
		if ( preg_match( '#<iframe.*?title="(.*?)".*?src="(.*?)"(.*?)>#', $html, $matches ) ) {
			$title = $matches[1];
			$url   = $matches[2];
			$link  = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'View on Spotify.', 'apple-news' ) . '</a>';

			// If caption exists, set as caption.
			$hide_article_caption = false;
			if ( preg_match( '#figcaption>(.*?)</figcaption#', $html, $caption_matches ) ) {
				$caption = $caption_matches[1];
				$hide_article_caption = false;
			}
		}

		$registration_array = [
			'#components#' => [
				[
					'role'   => 'heading2',
					'text'   => $title,
					'format' => 'html',
				],
			],
		];

		if ( ! empty( $caption ) ) {
			$registration_array['#components#'][] = [
				'role'      => 'caption',
				'text'      => $caption,
				'format'    => 'html',
				'textStyle' => [
					'fontSize' => 16,
				],
				'hidden'    => $hide_article_caption,
			];
		}

		$registration_array['#components#'][] = [
			'role'      => 'body',
			'text'      => $link,
			'format'    => 'html',
			'textStyle' => [
				'fontSize' => 14,
			],
		];

		$this->register_json(
			'spotify-json',
			$registration_array
		);

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Register the layout for the table.
		$this->register_layout( 'spotify-layout', 'spotify-layout' );
	}
}
