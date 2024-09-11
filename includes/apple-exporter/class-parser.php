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
	public string $format;

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
	public function parse( $html ): string {

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
		}

		return $this->parse_markdown( $html );
	}

	/**
	 * A function to format the given HTML as Apple News HTML.
	 *
	 * @param string $html The raw HTML to parse.
	 *
	 * @access private
	 * @return string The content, converted to an Apple News HTML string.
	 */
	private function parse_html( string $html ): string {

		/**
		 * Allows for filtering of the formatted content before return.
		 *
		 * @since 1.2.1
		 *
		 * @param string $content The content to filter.
		 * @param string $html The original HTML, before filtering was applied.
		 */
		return apply_filters( 'apple_news_parse_html', ( new HTML() )->format( $html ), $html );
	}

	/**
	 * A function to convert the given HTML into Apple News Markdown.
	 *
	 * @param string $html The raw HTML to parse.
	 *
	 * @access private
	 * @return string The content, converted to an Apple News Markdown string.
	 */
	private function parse_markdown( string $html ): string {

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
	 *
	 * @access private
	 * @return string The clean HTML
	 */
	private function clean_html( string $html ): string {
		$html = $this->remove_empty_a_tags( $html );
		$html = $this->handle_root_relative_urls( $html );
		$html = $this->validate_protocols( $html );
		$html = $this->convert_spaces( $html );

		// Return the clean HTML.
		return trim( $html );
	}

	/**
	 * Remove empty <a> tags from the given HTML content.
	 *
	 * @param string $html The HTML content to remove empty <a> tags from.
	 *
	 * @return string The modified HTML content without empty <a> tags.
	 */
	private function remove_empty_a_tags( string $html ): string {
		// Match all <a> tags via regex.
		// We can't use DOMDocument here because some tags will be removed entirely.
		preg_match_all( '/<a.*?>(.*?)<\/a>/m', $html, $a_tags );

		// Check if we got matches.
		if ( ! empty( $a_tags ) ) {
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
			}
		}

		return $html;
	}

	/**
	 * Handle root-relative URLs in the HTML content.
	 * Replace the root-relative URLs with the absolute
	 * URLs using the site URL.
	 *
	 * @param string $html The HTML content to handle root-relative URLs.
	 *
	 * @return string The modified HTML content with absolute URLs for root-relative ones.
	 */
	private function handle_root_relative_urls( string $html ): string {
		return preg_replace_callback(
			'/(<a[^>]+href=(["\'])\/[^\/].*?\2[^>]*>)/m',
			fn( $matches ) => str_replace( 'href="/', 'href="' . get_home_url() . '/', $matches[0] ),
			$html
		);
	}

	/**
	 * Ensure that the resulting URL uses a supported protocol.
	 * Leave it up to the content creator to ensure the URL is
	 * otherwise valid.
	 *
	 * @param string $html The HTML content to validate.
	 *
	 * @return string The modified HTML content with validated protocols.
	 */
	private function validate_protocols( string $html ): string {
		return preg_replace_callback(
			'/<a[^>]+href="([^"]*)"[^>]*>(.*?)<\/a>/m',
			function ( $matches ) {
				$href    = $matches[1];
				$content = $matches[2];
				if ( ! preg_match( '/^(https?:\/\/|mailto:|musics?:\/\/|stocks:\/\/|webcal:\/\/|#)/', $href ) ) {
					return $content;
				}

				return $matches[0]; // Return whole anchor tag if protocol is fine.
			},
			$html
		);
	}

	/**
	 * Convert non-breaking spaces to regular spaces.
	 *
	 * @param string $html The HTML content to convert.
	 *
	 * @return string The modified HTML content with converted spaces.
	 */
	private function convert_spaces( string $html ): string {
		return str_ireplace( [ '&nbsp;', '&#160;' ], ' ', $html );
	}
}
