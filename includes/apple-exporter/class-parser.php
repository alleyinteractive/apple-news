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
		$this->format = ( 'html' === $format  ) ? 'html' : 'markdown';
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

		// Clean up any issues prior to formatting.
		// This needs to be done here to avoid duplicating efforts
		// in the HTML and Markdown classes.
		$html = $this->_clean_html( $html );

		// Fork for format.
		if ( 'html' === $this->format ) {
			return $this->_parse_html( $html );
		} else {
			return $this->_parse_markdown( $html );
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
	private function _parse_html( $html ) {

		// Apply formatting.
		$parser = new HTML();
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
	private function _parse_markdown( $html ) {

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
		$parser = new Markdown();
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
	 * @access private
	 * @param string $html
	 * @return string The clean HTML
	 */
	private function _clean_html( $html ) {
		// Match all <a> tags via regex.
		// We can't use DOMDocument here because some tags will be removed entirely.
		preg_match_all( '/<a(?:.*?)(href|name)=(?:"|\')(.*?)(?:"|\')(?:.*?)>(.*?)<\/a>/m', $html, $a_tags );

		// Check if we got matches
		if ( empty( $a_tags ) ){
			return $html;
		}

		// Iterate over the matches and see what we need to do
		foreach ( $a_tags[1] as $i => $tag_type ) {
			// First, if this is an anchor, those aren't supported so just remove it.
			if ( 'name' === $tag_type ) {
				$html = str_replace( $a_tags[0][ $i ], $a_tags[3][ $i ], $html );
				continue;
			} else {
				// This is an href and we need to figure out if it's OK.
				// First we need to trim the href to be safe.
				$href = trim( $a_tags[2][ $i ] );

				// Now we need to determine if anything further needs to be done
				if ( 0 === stripos( $href, '#' ) ) {
					global $post;

					$permalink = get_permalink( $post );

					if ( false === $permalink ) {
						continue;
					}

					// This is an anchor so prepend the post permalink.
					// Ensure we update href="#myanchor" and not href="http://mydomain.com/path/#myanchor"
					$html = str_replace( '="' . $a_tags[2][ $i ], '="' . $permalink . $a_tags[2][ $i ], $html );
					continue;
				} else if ( 0 !== stripos( $href, '#' ) && false === filter_var( $href, FILTER_VALIDATE_URL ) ) {
					// We have to assume this is a local URL.
					// Prepend it with the site's home URL.
					$href = home_url( $href );
				}

				// If the href changed as a result, update it
				if ( $href !== $a_tags[2][ $i ] ) {
					$updated_tag = str_replace( $a_tags[2][ $i ], $href, $a_tags[0][ $i ] );
					$html = str_replace( $a_tags[0][ $i ], $updated_tag, $html );
				}
			}
		}

		// Return the clean HTML
		return $html;
	}
}
