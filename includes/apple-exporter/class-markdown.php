<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Markdown class
 *
 * Contains a class which is used to parse raw HTML into Apple News Markdown format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.2.0
 */

namespace Apple_Exporter;

use DOMElement;
use DOMNodeList;

/**
 * This class transforms HTML into Article Format Markdown, which is a subset
 * of Markdown.
 *
 * For elements that are not supported, just skip them and add the contents of
 * the tag.
 *
 * @since 0.2.0
 */
class Markdown {

	/**
	 * List index for OL and UL components being transformed to Markdown.
	 *
	 * @access private
	 * @var int
	 */
	private $list_index = 1;

	/**
	 * List mode for lists being transformed to Markdown.
	 *
	 * @access private
	 * @var string
	 */
	private $list_mode = 'ul';

	/**
	 * Parse an array of nodes into the specified format.
	 *
	 * @param DOMNodeList $nodes A list of DOMElement nodes to be processed.
	 *
	 * @access public
	 * @return string
	 */
	public function parse_nodes( $nodes ) {

		// Loop over each DOMElement and pass off for parsing.
		$result = '';
		foreach ( $nodes as $node ) {
			$result .= $this->parse_node( $node );
		}

		return $result;
	}

	/**
	 * Parse an individual node for markdown.
	 *
	 * @param DOMElement $node The node to process.
	 *
	 * @access private
	 * @return string The processed content, converted to a Markdown string.
	 */
	private function parse_node( $node ) {
		switch ( $node->nodeName ) {
			case 'strong':
				return $this->parse_node_strong( $node );
			case 'i':
			case 'em':
				return $this->parse_node_emphasis( $node );
			case 'br':
				return "\n";
			case 'p':
				return $this->parse_node_paragraph( $node );
			case 'a':
				return $this->parse_node_hyperlink( $node );
			case 'ul':
				return $this->parse_node_unordered_list( $node );
			case 'ol':
				return $this->parse_node_ordered_list( $node );
			case 'li':
				return $this->parse_node_list_item( $node );
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6':
				return $this->parse_node_heading( $node );
			case '#comment':
				return '';
			case '#text':
			default:
				return $this->parse_node_text( $node );
		}
	}

	/**
	 * Convert an <em> node to markdown.
	 *
	 * @param DOMElement $node The node to process.
	 *
	 * @access private
	 * @return string The processed node, converted to a string.
	 */
	private function parse_node_emphasis( $node ) {

		// If there is no text for this node, bail.
		$text  = $this->parse_nodes( $node->childNodes );
		$check = trim( $text );
		if ( empty( $check ) ) {
			return '';
		}

		return '_' . $text . '_';
	}

	/**
	 * Convert a heading node to markdown.
	 *
	 * @param DOMElement $node The node to process.
	 *
	 * @access private
	 * @return string The processed node, converted to a string.
	 */
	private function parse_node_heading( $node ) {

		// If there is no text for this node, bail.
		$text  = $this->parse_nodes( $node->childNodes );
		$check = trim( $text );
		if ( empty( $check ) ) {
			return '';
		}

		return sprintf(
			'%s %s' . "\n",
			str_repeat( '#', intval( substr( $node->nodeName, 1, 1 ) ) ),
			$text
		);
	}

	/**
	 * Convert an <a> node to markdown.
	 *
	 * @param DOMElement $node The node to process.
	 *
	 * @access private
	 * @return string The processed node, converted to a string.
	 */
	private function parse_node_hyperlink( $node ) {

		// Set the URL from the HREF parameter on the tag.
		$url = $node->getAttribute( 'href' );

		// Set the text from the content of the child nodes.
		$text = $this->parse_nodes( $node->childNodes );

		/**
		 * Allows for filtering of the formatted content before return.
		 *
		 * @since 0.2.0
		 *
		 * @param string $url The URL to be filtered.
		 */
		$url = apply_filters( 'apple_news_markdown_hyperlink', $url );

		// If there is no URL or no text, do not add the link.
		$check = trim( $text );
		if ( empty( $url ) || empty( $check ) ) {
			return '';
		}

		return sprintf( '[%s](%s)', $text, esc_url_raw( $url ) );
	}

	/**
	 * Convert an <li> node to markdown.
	 *
	 * @param DOMElement $node The node to process.
	 *
	 * @access private
	 * @return string The processed node, converted to a string.
	 */
	private function parse_node_list_item( $node ) {

		// If there is no text for this node, bail.
		$text  = $this->parse_nodes( $node->childNodes );
		$check = trim( $text );
		if ( empty( $check ) ) {
			return '';
		}

		// Fork for ordered list items.
		if ( 'ol' === $this->list_mode ) {
			return sprintf(
				'%d. %s',
				$this->list_index ++,
				$text
			);
		}

		return '- ' . $text;
	}

	/**
	 * Convert an <ol> node to markdown.
	 *
	 * @param DOMElement $node The node to process.
	 *
	 * @access private
	 * @return string The processed node, converted to a string.
	 */
	private function parse_node_ordered_list( $node ) {
		$this->list_mode  = 'ol';
		$this->list_index = 1;

		// If there is no text for this node, bail.
		$text  = $this->parse_nodes( $node->childNodes );
		$check = trim( $text );
		if ( empty( $check ) ) {
			return '';
		}

		return $text . "\n\n";
	}

	/**
	 * Convert a <p> node to markdown.
	 *
	 * @param DOMElement $node The node to process.
	 *
	 * @access private
	 * @return string The processed node, converted to a string.
	 */
	private function parse_node_paragraph( $node ) {

		// If there is no text for this node, bail.
		$text  = $this->parse_nodes( $node->childNodes );
		$check = trim( $text );
		if ( empty( $check ) ) {
			return '';
		}

		return $text . "\n\n";
	}

	/**
	 * Convert a <strong> node to markdown.
	 *
	 * @param DOMElement $node The node to process.
	 *
	 * @access private
	 * @return string The processed node, converted to a string.
	 */
	private function parse_node_strong( $node ) {

		// If there is no text for this node, bail.
		$text  = $this->parse_nodes( $node->childNodes );
		$check = trim( $text );
		if ( empty( $check ) ) {
			return '';
		}

		return '**' . $text . '**';
	}

	/**
	 * Convert a text node to markdown.
	 *
	 * @param DOMElement $node The node to process.
	 *
	 * @access private
	 * @return string The processed node, converted to a string.
	 */
	private function parse_node_text( $node ) {
		return str_replace( '!', '\\!', $node->nodeValue );
	}

	/**
	 * Convert a <ul> node to markdown.
	 *
	 * @param DOMElement $node The node to process.
	 *
	 * @access private
	 * @return string The processed node, converted to a string.
	 */
	private function parse_node_unordered_list( $node ) {
		$this->list_mode = 'ul';

		// If there is no text for this node, bail.
		$text  = $this->parse_nodes( $node->childNodes );
		$check = trim( $text );
		if ( empty( $check ) ) {
			return '';
		}

		return $text . "\n\n";
	}
}
