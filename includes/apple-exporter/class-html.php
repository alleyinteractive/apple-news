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
	private $_allowed_html = array(
		'a' => array(
			'href' => true,
		),
		'aside' => array(),
		'b' => array(),
		'blockquote' => array(),
		'br' => array(),
		'code' => array(),
		'del' => array(),
		'em' => array(),
		'footer' => array(),
		'i' => array(),
		'li' => array(),
		'ol' => array(),
		'p' => array(),
		'pre' => array(),
		's' => array(),
		'samp' => array(),
		'strong' => array(),
		'sub' => array(),
		'sup' => array(),
		'ul' => array(),
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
		return wp_kses( $html, $this->_allowed_html );
	}
}
