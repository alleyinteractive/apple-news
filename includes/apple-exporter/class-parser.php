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
		$this->format = ( $format === 'html' ) ? 'html' : 'markdown';
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
}
