<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\Embed_Generic class
 *
 * Contains a class which is used to transform generic embeds into Apple News format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 2.0.0
 */

namespace Apple_Exporter\Components;

/**
 * A class to transform a generic embed into an Apple News component.
 *
 * @since 2.0.0
 */
class Embed_Generic extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 *
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {

		// If we aren't on an element node, bail out.
		if ( 1 !== $node->nodeType ) {
			return null;
		}

		// Check for Gutenberg-style embeds, which have a helpful signature.
		if ( 'figure' === $node->nodeName
			&& false !== strpos( $node->getAttribute( 'class' ), 'wp-block-embed-' )
		) {
			return $node;
		}

		// Check for root-level iframes.
		if ( 'iframe' === $node->nodeName ) {
			return $node;
		}

		// Check for paragraphs containing iframes.
		if ( 'p' === $node->nodeName
			&& $node->hasChildNodes()
			&& 'iframe' === $node->childNodes->item( 0 )->nodeName
		) {
			return $node;
		}

		// Anything else isn't supported out of the box.
		return null;
	}

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		$this->register_spec(
			'embed-generic-json',
			__( 'Embed (generic) JSON', 'apple-news' ),
			[
				'layout'     => 'embed-generic-layout',
				'role'       => 'container',
				'components' => '#components#',
			]
		);

		$this->register_spec(
			'embed-generic-layout',
			__( 'Embed (generic) Layout', 'apple-news' ),
			[
				'margin' => [
					'top'    => 15,
					'bottom' => 15,
				],
			]
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
		$components = [];
		$provider   = '';
		$title      = '';
		$url        = '';

		// Negotiate embed source lookup.
		if ( preg_match( '/wp-block-embed-([0-9a-zA-Z-]+)/', $html, $matches ) ) {
			$provider = $matches[1];
		} else {
			/*
			 * Define a map of domain names to provider slugs to check for as a best guess.
			 * This list is intentionally organized from most specific to least specific, so do not
			 * alphabetize it! The logic here is that a block may contain references to amazon.com either
			 * in-text or via links to Amazon services (AWS, S3, etc) but actually be an embed for a
			 * different provider. Therefore, we will only consider an embed to be from "generic" providers
			 * like Amazon or Imgur if no other, more specific, providers were matched first.
			 */
			$provider_map = [
				'animoto.com'      => 'animoto',
				'cloudup.com'      => 'cloudup',
				'collegehumor.com' => 'collegehumor',
				'polldaddy.com'    => 'crowdsignal',
				'poll.fm'          => 'crowdsignal',
				'survey.fm'        => 'crowdsignal',
				'dailymotion.com'  => 'dailymotion',
				'flickr.com'       => 'flickr',
				'hulu.com'         => 'hulu',
				'issuu.com'        => 'issuu',
				'kickstarter.com'  => 'kickstarter',
				'meetup.com'       => 'meetup-com',
				'mixcloud.com'     => 'mixcloud',
				'reverbnation.com' => 'reverbnation',
				'screencast.com'   => 'screencast',
				'scribd.com'       => 'scribd',
				'slideshare.net'   => 'slideshare',
				'smugmug.com'      => 'smugmug',
				'soundcloud.com'   => 'soundcloud',
				'speakerdeck.com'  => 'speaker-deck',
				'spotify.com'      => 'spotify',
				'ted.com'          => 'ted',
				'tumblr.com'       => 'tumblr',
				'videopress.com'   => 'videopress',
				'wordpress.tv'     => 'wordpress-tv',
				'reddit.com'       => 'reddit',
				'imgur.com'        => 'imgur',
				'tiktok.com'       => 'tiktok',
				'amazon.com'       => 'amazon-kindle',
			];

			// Loop through the provider map, trying to guess the provider based on included domain name.
			foreach ( $provider_map as $domain => $provider_slug ) {
				if ( false !== strpos( $html, $domain ) ) {
					$provider = $provider_slug;
					break;
				}
			}
		}

		// Fork for iframe handling vs. not. Non-iframe detection is less straightforward, so it is best-effort.
		if ( 'tiktok' === $provider && preg_match( '/<blockquote[^>]+?cite=[\'"]([^\'"]+)/', $html, $matches ) ) {
			$url = $matches[1];
		} elseif ( false !== strpos( $html, '<iframe' ) ) {
			// Try to get the source URL.
			if ( preg_match( '/<iframe[^>]+?src=[\'"]([^\'"]+)/', $html, $matches ) ) {
				$url = $matches[1];
			}

			// Try to get the title.
			if ( preg_match( '/<iframe[^>]+?title=[\'"]([^\'"]+)/', $html, $matches ) ) {
				$title = $matches[1];
			}
		} elseif ( preg_match( '/data-(?:url|href)=[\'"]([^\'"]+)/', $html, $matches ) ) {
			$url = $matches[1];
		} elseif ( preg_match( '/<a[^>]+?href=[\'"]([^\'"]+)/', $html, $matches ) ) {
			$url = $matches[1];
		}

		// If the URL is protocol-less, default it to https.
		if ( 0 === strpos( $url, '//' ) ) {
			$url = 'https:' . $url;
		}

		// If no URL was found, bail out.
		if ( empty( $url ) ) {
			return;
		}

		// Map provider strings to the correct human-readable form, with a fallback.
		switch ( $provider ) {
			case 'animoto':
			case 'cloudup':
			case 'crowdsignal':
			case 'dailymotion':
			case 'flickr':
			case 'hulu':
			case 'imgur':
			case 'issuu':
			case 'kickstarter':
			case 'mixcloud':
			case 'reddit':
			case 'screencast':
			case 'scribd':
			case 'slideshare':
			case 'spotify':
			case 'tumblr':
				$provider = ucfirst( $provider );
				break;
			case 'amazon-kindle':
				$provider = 'Amazon';
				break;
			case 'collegehumor':
				$provider = 'CollegeHumor';
				break;
			case 'meetup-com':
				$provider = 'Meetup.com';
				break;
			case 'reverbnation':
				$provider = 'ReverbNation';
				break;
			case 'smugmug':
				$provider = 'SmugMug';
				break;
			case 'soundcloud':
				$provider = 'SoundCloud';
				break;
			case 'speaker-deck':
				$provider = 'Speaker Deck';
				break;
			case 'ted':
				$provider = 'TED';
				break;
			case 'videopress':
				$provider = 'VideoPress';
				break;
			case 'wordpress-tv':
				$provider = 'WordPress.tv';
				break;
			case 'tiktok':
				$provider = 'TikTok';
				break;
			default:
				$provider = __( 'the original site', 'apple-news' );
				break;
		}

		// Add the title, if it is present.
		if ( ! empty( $title ) ) {
			$components[] = [
				'role'   => 'heading2',
				'text'   => $title,
				'format' => 'html',
			];
		}

		// Add the base component.
		$components[] = [
			'role'      => 'body',
			// translators: name of provider.
			'text'      => '<a href="' . esc_url( $url ) . '">' . esc_html( sprintf( __( 'View on %s.', 'apple-news' ), $provider ) ) . '</a>',
			'format'    => 'html',
			'textStyle' => [
				'fontSize' => 14,
			],
		];

		$this->register_json(
			'embed-generic-json',
			[
				'#components#' => $components,
			]
		);
		$this->register_layout( 'embed-generic-layout', 'embed-generic-layout' );
	}
}
