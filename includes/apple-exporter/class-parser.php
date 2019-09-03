<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Parser class
 *
 * Contains a class which is used to parse raw HTML into an Apple News format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 1.2.1
 */

namespace Apple_Exporter;

use DOMDocument;
use DOMNodeList;

require_once __DIR__ . '/class-html.php';
require_once __DIR__ . '/class-markdown.php';

/**
 * A class that parses raw HTML into either Apple News HTML or Markdown format.
 *
 * @since 1.2.1
 */
class Parser {

	/**
	 * The format to use. Valid values are 'html' and 'markdown'.
	 *
	 * @access public
	 * @var string
	 */
	public $format;

	/**
	 * Initializes the object with the format setting.
	 *
	 * @param string $format The format to use. Defaults to markdown.
	 *
	 * @access public
	 */
	public function __construct( $format = 'markdown' ) {
		$this->format = ( 'html' === $format ) ? 'html' : 'markdown';
	}

	/**
	 * Transforms raw HTML into Apple News format.
	 *
	 * @param string $html The raw HTML to parse.
	 *
	 * @access public
	 * @return string The filtered content in the format specified.
	 */
	public function parse( $html ) {

		// Don't parse empty input.
		if ( empty( $html ) ) {
			return '';
		}

		/**
		 * Clean up any issues prior to formatting.
		 * This needs to be done here to avoid duplicating efforts
		 * in the HTML and Markdown classes.
		 */
		$html = $this->clean_html( $html );

		// Fork for format.
		if ( 'html' === $this->format ) {
			return $this->parse_html( $html );
		} else {
			return $this->parse_markdown( $html );
		}
	}

	/**
	 * A function to format the given HTML as Apple News HTML.
	 *
	 * @param string $html The raw HTML to parse.
	 *
	 * @access private
	 * @return string The content, converted to an Apple News HTML string.
	 */
	private function parse_html( $html ) {

		// Apply formatting.
		$parser  = new HTML();
		$content = $parser->format( $html );

		/**
		 * Allows for filtering of the formatted content before return.
		 *
		 * @since 1.2.1
		 *
		 * @param string $content The content to filter.
		 * @param string $html The original HTML, before filtering was applied.
		 */
		return apply_filters( 'apple_news_parse_html', $content, $html );
	}

	/**
	 * A function to convert the given HTML into Apple News Markdown.
	 *
	 * @param string $html The raw HTML to parse.
	 *
	 * @access private
	 * @return string The content, converted to an Apple News Markdown string.
	 */
	private function parse_markdown( $html ) {

		// PHP's DOMDocument doesn't like HTML5, so we must ignore errors.
		libxml_use_internal_errors( true );

		// Load the content, forcing the use of UTF-8.
		$dom = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $html );

		// Reset error state.
		libxml_clear_errors();
		libxml_use_internal_errors( false );

		// Find the first-level nodes of the body tag.
		$nodes = $dom->getElementsByTagName( 'body' )->item( 0 )->childNodes;

		// Perform parsing.
		$parser  = new Markdown();
		$content = $parser->parse_nodes( $nodes );

		/**
		 * Allows for filtering of the formatted content before return.
		 *
		 * @since 1.2.1
		 *
		 * @param string $content The content to filter.
		 * @param DOMNodeList $nodes The list of DOMElement nodes used initially.
		 */
		return apply_filters( 'apple_news_parse_markdown', $content, $nodes );
	}

	/**
	 * Handles cleaning up any HTML issues prior to parsing that could affect
	 * both HTML and Markdown format.
	 *
	 * @param string $html The HTML to be cleaned.
	 * @access private
	 * @return string The clean HTML
	 */
	private function clean_html( $html ) {
		// Match all <a> tags via regex.
		// We can't use DOMDocument here because some tags will be removed entirely.
		preg_match_all( '/<a.*?>(.*?)<\/a>/m', $html, $a_tags );

		// Check if we got matches.
		if ( empty( $a_tags ) ) {
			return $html;
		}

		// Iterate over the matches and see what we need to do.
		foreach ( $a_tags[0] as $i => $a_tag ) {
			// If the <a> tag doesn't have content, dump it.
			$content = trim( $a_tags[1][ $i ] );
			if ( empty( $content ) ) {
				$html = str_replace( $a_tag, '', $html );
				continue;
			}

			// If there isn't an href that has content, strip the anchor tag.
			if ( ! preg_match( '/<a[^>]+href="([^"]+)"[^>]*>.*?<\/a>/m', $a_tag, $matches ) ) {
				$html = str_replace( $a_tag, $content, $html );
				continue;
			}

			// If the href value trims to nil, strip the anchor tag.
			$href = trim( $matches[1] );
			if ( empty( $href ) ) {
				$html = str_replace( $a_tag, $a_tags[1][ $i ], $html );
			}

			// Handle anchor links.
			if ( 0 === strpos( $href, '#' ) ) {
				global $post;

				$permalink = get_permalink( $post );

				if ( false === $permalink ) {
					continue;
				}

				$html = str_replace( 'href="' . $href, 'href="' . $permalink . $href, $html );
				continue;
			}

			// Handle root relative URLs.
			if ( 0 === strpos( $href, '/' ) && false === strpos( $href, '//' ) ) {
				$html = str_replace( 'href="' . $href, 'href="' . get_site_url() . $href, $html );
				continue;
			}

			// Ensure that the resulting URL is fully-formed.
			if ( ! preg_match( '/^https?:\/\/[^.]+\.[^.]+/', $href ) ) {
				$html = str_replace( $a_tag, $content, $html );
				continue;
			}
		}

		// Make nonbreaking spaces actual spaces.
		$html = str_ireplace( '&nbsp;', ' ', $html );
		$html = str_replace( '&#160;', ' ', $html );

		// Return the clean HTML.
		return $html;
	}
}
