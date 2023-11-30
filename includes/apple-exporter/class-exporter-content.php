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
	 * Slug of the content being exported.
	 *
	 * @var string
	 * @access private
	 */
	private $slug;

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
	 * Byline for the content being exported.
	 *
	 * @var string
	 * @access private
	 */
	private $author;

	/**
	 * Publication date for the content being exported.
	 *
	 * @var string
	 * @access private
	 */
	private $date;

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
	 * @param string|array             $cover    Optional. The cover of the post to be exported. If string, just the URL. If array, properties are 'url' and 'caption'.
	 * @param string                   $byline   Optional. The byline of the post to be exported.
	 * @param \Apple_Exporter\Settings $settings Optional. Settings for the exporter.
	 * @param string                   $slug     Optional. The slug of the post to be exported.
	 * @param string                   $author   Optional. The author(s) of the post to be exported.
	 * @param string                   $date     Optional. The date of the post to be exported.
	 * @access public
	 */
	public function __construct( $id, $title, $content, $intro = null, $cover = null, $byline = null, $settings = null, $slug = null, $author = null, $date = null ) {
		$this->id       = $id;
		$this->slug     = $slug;
		$this->title    = $title;
		$this->content  = $content;
		$this->intro    = $intro;
		$this->cover    = $cover;
		$this->byline   = $byline;
		$this->settings = ! empty( $settings ) ? $settings : new Exporter_Content_Settings();
		$this->author   = $author;
		$this->date     = $date;
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
	 * Get the content slug.
	 *
	 * @access public
	 * @return string The slug.
	 */
	public function slug() {
		return $this->slug;
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
	 * Get the content author.
	 *
	 * @access public
	 * @return string The author.
	 */
	public function author() {
		return $this->author;
	}

	/**
	 * Get the content date.
	 *
	 * @access public
	 * @return string The byline.
	 */
	public function date() {
		return $this->date;
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
		libxml_clear_errors();

		// Find the first-level nodes of the body tag.
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		return $body ? $body->childNodes : new \DOMNodeList(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
}
