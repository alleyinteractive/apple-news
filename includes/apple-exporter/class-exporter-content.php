<?php
/**
 * Publish to Apple News: \Apple_Exporter\Exporter_Content class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 */

namespace Apple_Exporter;

/**
 * Represents a generic way to represent content that must be exported. This
 * can be filled based on a WordPress post for example.
 *
 * @since 0.2.0
 */
class Exporter_Content {

	/**
	 * ID of the content being exported.
	 *
	 * @var int
	 * @access private
	 */
	private $id;

	/**
	 * Title of the content being exported.
	 *
	 * @var string
	 * @access private
	 */
	private $title;

	/**
	 * The content being exported.
	 *
	 * @var string
	 * @access private
	 */
	private $content;

	/**
	 * Intro for the content being exported.
	 *
	 * @var string
	 * @access private
	 */
	private $intro;

	/**
	 * Cover image for the content being exported.
	 *
	 * @var string
	 * @access private
	 */
	private $cover;

	/**
	 * Byline for the content being exported.
	 *
	 * @var string
	 * @access private
	 */
	private $byline;

	/**
	 * Settings for the content being exported.
	 *
	 * @var Settings
	 * @access private
	 */
	private $settings;

	/**
	 * Formats a URL from a `src` parameter to be compatible with remote sources.
	 *
	 * Will return a blank string if the URL is invalid.
	 *
	 * @param string $url The URL to format.
	 *
	 * @access protected
	 * @return string The formatted URL on success, or a blank string on failure.
	 */
	public static function format_src_url( $url ) {

		// If this is a root-relative path, make absolute.
		if ( 0 === strpos( $url, '/' ) ) {
			$url = site_url( $url );
		}

		// Decode the HTML entities since the URL is from the src attribute.
		$url = html_entity_decode( $url );

		// Escape the URL and ensure it is valid.
		$url = esc_url_raw( $url );
		if ( empty( $url ) ) {
			return '';
		}

		// Ensure the URL begins with http.
		if ( 0 !== strpos( $url, 'http' ) ) {
			return '';
		}

		// Ensure the URL passes filter_var checks.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return '';
		}

		return $url;
	}

	/**
	 * Constructor.
	 *
	 * @param int                      $id       The ID of the post to be exported.
	 * @param string                   $title    The title of the post to be exported.
	 * @param string                   $content  The content of the post to be exported.
	 * @param string                   $intro    Optional. The intro of the post to be exported.
	 * @param string                   $cover    Optional. The cover of the post to be exported.
	 * @param string                   $byline   Optional. The byline of the post to be exported.
	 * @param \Apple_Exporter\Settings $settings Optional. Settings for the exporter.
	 * @access public
	 */
	public function __construct( $id, $title, $content, $intro = null, $cover = null, $byline = null, $settings = null ) {
		$this->id       = $id;
		$this->title    = $title;
		$this->content  = $content;
		$this->intro    = $intro;
		$this->cover    = $cover;
		$this->byline   = $byline;
		$this->settings = $settings ?: new Exporter_Content_Settings();
	}

	/**
	 * Get the content ID.
	 *
	 * @return int
	 * @access public
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Get the content title.
	 *
	 * @access public
	 * @return string The title.
	 */
	public function title() {
		return $this->title;
	}

	/**
	 * Get the content.
	 *
	 * @access public
	 * @return string The content.
	 */
	public function content() {
		return $this->content;
	}

	/**
	 * Get the content intro.
	 *
	 * @access public
	 * @return string The intro.
	 */
	public function intro() {
		return $this->intro;
	}

	/**
	 * Get the content cover.
	 *
	 * @access public
	 * @return string The cover.
	 */
	public function cover() {
		return $this->cover;
	}

	/**
	 * Get the content byline.
	 *
	 * @access public
	 * @return string The byline.
	 */
	public function byline() {
		return $this->byline;
	}

	/**
	 * Get the content settings.
	 *
	 * @param string $name The name of the setting to look up.
	 * @access public
	 * @return mixed The value for the setting.
	 */
	public function get_setting( $name ) {
		return $this->settings->get( $name );
	}

	/**
	 * Update a property, useful during content parsing.
	 *
	 * @param string $name  The name of the setting to set.
	 * @param mixed  $value The value to set for the setting.
	 * @access public
	 */
	public function set_property( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->$name = $value;
		}
	}

	/**
	 * Get the DOM nodes.
	 *
	 * @access public
	 * @return \DOMNodeList A DOMNodeList containing all nodes for the content.
	 */
	public function nodes() {
		// Because PHP's DomDocument doesn't like HTML5 tags, ignore errors.
		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $this->content() );
		libxml_clear_errors( true );

		// Find the first-level nodes of the body tag.
		return $dom->getElementsByTagName( 'body' )->item( 0 )->childNodes;
	}

}
