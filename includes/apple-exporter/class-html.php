<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\HTML class
 *
 * Contains a class which is used to filter raw HTML into Apple News HTML format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 1.2.1
 */

namespace Apple_Exporter;

/**
 * A class that filters raw HTML into Apple News HTML format.
 *
 * @since 1.2.1
 */
class HTML {

	/**
	 * An array of allowed HTML tags for Apple News formatted HTML.
	 *
	 * @access private
	 * @var array
	 */
	private $allowed_html = array(
		'a'          => array(
			'href' => true,
		),
		'aside'      => array(),
		'b'          => array(),
		'blockquote' => array(),
		'br'         => array(),
		'caption'    => array(),
		'code'       => array(),
		'del'        => array(),
		'em'         => array(),
		'footer'     => array(),
		'i'          => array(),
		'li'         => array(),
		'ol'         => array(),
		'p'          => array(),
		'pre'        => array(),
		's'          => array(),
		'samp'       => array(),
		'strong'     => array(),
		'sub'        => array(),
		'sup'        => array(),
		'table'      => array(),
		'td'         => array(),
		'th'         => array(),
		'tr'         => array(),
		'tbody'      => array(),
		'thead'      => array(),
		'tfoot'      => array(),
		'ul'         => array(),
	);

	/**
	 * Formats a raw HTML string as Apple News format HTML.
	 *
	 * @param string $html The HTML to format.
	 *
	 * @access public
	 * @return string The formatted HTML.
	 */
	public function format( $html ) {

		// Since wp_kses has an issue with some <script> tags, proactively strip them.
		$html = preg_replace( '/<script[^>]*?>.*?<\/script>/', '', $html );

		// Strip out all tags and attributes other than what is allowed.
		$html = wp_kses( $html, $this->allowed_html );

		// Remove any tempty tags.
		$html = preg_replace( '/<([a-z0-9]+)[^>]*>\s*<\/\1>/', '', $html );

		return $html;
	}
}
