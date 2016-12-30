<?php
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
	 * Contstructor.
	 *
	 * @param int $id
	 * @param string $title
	 * @param string $content
	 * @param string $intro
	 * @param string $cover
	 * @param string $byline
	 * @param Settings $settings
	 */
	function __construct( $id, $title, $content, $intro = null, $cover = null, $byline = null, $settings = null ) {
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
	 * @return strings
	 * @access public
	 */
	public function title() {
		return $this->title;
	}

	/**
	 * Get the content.
	 *
	 * @return string
	 * @access public
	 */
	public function content() {
		return $this->content;
	}

	/**
	 * Get the content intro.
	 *
	 * @return string
	 * @access public
	 */
	public function intro() {
		return $this->intro;
	}

	/**
	 * Get the content cover.
	 *
	 * @return string
	 * @access public
	 */
	public function cover() {
		return $this->cover;
	}

	/**
	 * Get the content byline.
	 *
	 * @return string
	 * @access public
	 */
	public function byline() {
		return $this->byline;
	}

	/**
	 * Get the content settings.
	 *
	 * @return Settings
	 * @access public
	 */
	public function get_setting( $name ) {
		return $this->settings->get( $name );
	}

	/**
	 * Update a property, useful during content parsing.
	 *
	 * @param string $name
	 * @param mixed $value
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
	 * @return array of DomNodes
	 * @access public
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
