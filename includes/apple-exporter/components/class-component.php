<?php
namespace Apple_Exporter\Components;

require_once __DIR__ . '/../class-markdown.php';

use Apple_Exporter\Parser;

/**
 * Base component class. All components must inherit from this class and
 * implement its abstract method "build".
 *
 * It provides several helper methods, such as get/set_setting and
 * register_style.
 *
 * @since 0.2.0
 */
abstract class Component {

	/**
	 * Possible anchoring positions
	 */
	const ANCHOR_NONE  = 0;
	const ANCHOR_AUTO  = 1;
	const ANCHOR_LEFT  = 2;
	const ANCHOR_RIGHT = 3;

	/**
	 * Anchorable components are anchored to the previous element that appears in
	 * the position specified. If the previous element is an advertisement,
	 * attaches to the next instead of the previous element.
	 *
	 * @since 0.6.0
	 * @var int
	 * @access public
	 */
	public $anchor_position = self::ANCHOR_NONE;

	/**
	 * If this component is set as a target for an anchor, does it need to fix
	 * it's layout? Defaults to true, components can set this to false if they do
	 * not need an automatic layout assigned to them or want more control.
	 *
	 * Right now, the only component that sets this to false is the body, as it
	 * doesn't need a special layout for anchoring, it just flows around anchored
	 * components.
	 *
	 * @since 0.6.0
	 * @var boolean
	 * @access public
	 */
	public $needs_layout_if_anchored = true;

	/**
	 * Whether this component can be an anchor target.
	 *
	 * @since 0.6.0
	 * @var boolean
	 * @access protected
	 */
	protected $can_be_anchor_target = false;

	/**
	 * Workspace for this component.
	 *
	 * @since 0.2.0
	 * @var Workspace
	 * @access protected
	 */
	protected $workspace;

	/**
	 * Text for this component.
	 *
	 * @since 0.2.0
	 * @var string
	 * @access protected
	 */
	protected $text;

	/**
	 * JSON for this component.
	 *
	 * @since 0.2.0
	 * @var array
	 * @access protected
	 */
	protected $json;

	/**
	 * Settings for this component.
	 *
	 * @since 0.4.0
	 * @var Settings
	 * @access protected
	 */
	protected $settings;

	/**
	 * Styles for this component.
	 *
	 * @since 0.4.0
	 * @var Component_Text_Styles
	 * @access protected
	 */
	protected $styles;

	/**
	 * Layouts for this component.
	 *
	 * @since 0.4.0
	 * @var Component_Layouts
	 * @access protected
	 */
	protected $layouts;

	/**
	 * The parser to use for this component.
	 *
	 * @since 1.2.1
	 *
	 * @access protected
	 * @var Parser
	 */
	protected $parser;

	/**
	 * UID for this component.
	 *
	 * @since 0.4.0
	 * @var string
	 * @access private
	 */
	private $uid;

	/**
	 * Constructor.
	 *
	 * @param string $text
	 * @param Workspace $workspace
	 * @param Settings $settings
	 * @param Component_Text_Styles $styles
	 * @param Component_Layouts $layouts
	 * @param Parser $parser
	 */
	function __construct( $text, $workspace, $settings, $styles, $layouts, $parser = null ) {
		$this->workspace = $workspace;
		$this->settings  = $settings;
		$this->styles    = $styles;
		$this->layouts   = $layouts;
		$this->text      = $text;
		$this->json      = null;

		// Negotiate parser.
		if ( empty( $parser ) ) {

			// Load format from settings.
			$format = ( 'yes' === $this->settings->html_support )
				? 'html'
				: 'markdown';

			// Only allow HTML if the component supports it.
			if ( ! $this->html_enabled() ) {
				$format = 'markdown';
			}

			// Init new parser with the appropriate format.
			$parser = new Parser( $format );
		}
		$this->parser = $parser;

		// Once the text is set, build proper JSON. Store as an array.
		$this->build( $this->text );
	}

	/**
	 * Given a DomNode, if it matches the component, return the relevant node to
	 * work on. Otherwise, return null.
	 *
	 * @param DomNode $node
	 * @return mixed
	 * @access public
	 */
	public static function node_matches( $node ) {
		return null;
	}

	/**
	 * Use PHP's HTML parser to generate valid HTML out of potentially broken
	 * input.
	 *
	 * @param string $html
	 * @return string
	 * @access protected
	 */
	protected static function clean_html( $html ) {
		// Because PHP's DomDocument doesn't like HTML5 tags, ignore errors.
		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
		libxml_clear_errors( true );

		// Find the first-level nodes of the body tag.
		$element = $dom->getElementsByTagName( 'body' )->item( 0 )->childNodes->item( 0 );
		$html    = $dom->saveHTML( $element );
		return preg_replace( '#<[^/>][^>]*></[^>]+>#', '', $html );
	}

	/**
	 * Transforms HTML into an array that describes the component using the build
	 * function.
	 *
	 * @return array
	 * @access public
	 */
	public function to_array() {
		return apply_filters( 'apple_news_' . $this->get_component_name() . '_json', $this->json );
	}

	/**
	 * Set a JSON value.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @access public
	 */
	public function set_json( $name, $value ) {
		$this->json[ $name ] = $value;
	}

	/**
	 * Get a JSON value
	 *
	 * @param string $name
	 * @return mixed
	 * @access public
	 */
	public function get_json( $name ) {
		return ( isset( $this->json[ $name ] ) ) ? $this->json[ $name ] : null;
	}

	/**
	 * Set the anchor position.
	 *
	 * @param int $position
	 * @access public
	 */
	public function set_anchor_position( $position ) {
		$this->anchor_position = $position;
	}

	/**
	 * Get the anchor position.
	 *
	 * @return int
	 * @access public
	 */
	public function get_anchor_position() {
		return $this->anchor_position;
	}

	/**
	 * Sets the anchor layout for this component
	 *
	 * @since 0.6.0
	 * @access public
	 */
	public function anchor() {
		if ( ! $this->needs_layout_if_anchored ) {
			return;
		}

		$this->layouts->set_anchor_layout_for( $this );
	}

	/**
	 * All components that are anchor target have an UID. Return whether this
	 * component is an anchor target.
	 *
	 * @since 0.6.0
	 * @return boolean
	 * @access public
	 */
	public function is_anchor_target() {
		return ! is_null( $this->uid );
	}

	/**
	 * Check if it's can_be_anchor_target and it hasn't been anchored already.
	 *
	 * @return boolean
	 * @access public
	 */
	public function can_be_anchor_target() {
		return $this->can_be_anchor_target && is_null( $this->uid );
	}

	/**
	 * Get the current UID.
	 *
	 * @return string
	 * @access public
	 */
	public function uid() {
		if ( is_null( $this->uid ) ) {
			$this->uid = 'component-' . md5( uniqid( $this->text, true ) );
			$this->set_json( 'identifier', $this->uid );
		}

		return $this->uid;
	}

	/**
	 * Maybe bundles the source based on current settings.
	 * Returns the URL to use based on current setings.
	 *
	 * @param string $source    The path or URL of the resource which is going to
	 *                          be bundled
	 * @param string $filename  The name of the file to be created
	 * @return string 					The URL to use for this asset in the JSON
	 */
	protected function maybe_bundle_source( $source, $filename = null ) {
		if ( 'yes' === $this->get_setting( 'use_remote_images' ) ) {
			return $source;
		} else {
			if ( null === $filename ) {
				$filename = \Apple_News::get_filename( $source );
			}
			$this->bundle_source( $filename, $source );
			return 'bundle://' . $filename;
		}
	}

	/**
	 * Calls the current workspace bundle_source method to allow for
	 * different implementations of the bundling technique.
	 *
	 * @param string $filename  The name of the file to be created
	 * @param string $source    The path or URL of the resource which is going to
	 *                          be bundled
	 */
	protected function bundle_source( $filename, $source ) {
		$this->workspace->bundle_source( $filename, $source );
	}

	// Isolate settings dependency
	// -------------------------------------------------------------------------

	/**
	 * Gets an exporter setting.
	 *
	 * @since 0.4.0
	 * @param string $name
	 * @return mixed
	 * @access protected
	 */
	protected function get_setting( $name ) {
		return $this->settings->get( $name );
	}

	/**
	 * Whether HTML format is enabled for this component type.
	 *
	 * This function is intended to be overridden in child classes.
	 *
	 * @access protected
	 * @return bool Whether HTML format is enabled for this component type.
	 */
	protected function html_enabled() {
		return false;
	}

	/**
	 * Sets an exporter setting.
	 *
	 * @since 0.4.0
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 * @access protected
	 */
	protected function set_setting( $name, $value ) {
		return $this->settings->set( $name, $value );
	}

	/**
	 * Using the style service, register a new style.
	 *
	 * @since 0.4.0
	 * @param string $name
	 * @param array $spec
	 * @access protected
	 */
	protected function register_style( $name, $spec ) {
		$this->styles->register_style( $name, $spec );
	}

	/**
	 * Using the layouts service, register a new layout.
	 *
	 * @since 0.4.0
	 * @param string $name
	 * @param array $spec
	 * @access protected
	 */
	protected function register_layout( $name, $spec ) {
		$this->layouts->register_layout( $name, $spec );
	}

	/**
	 * Register a new layout which will be displayed as full-width, so no need to
	 * specify columnStart and columnSpan in the layout specs. This is useful
	 * because when the body is centered, the full-width layout spans the same
	 * columns as the body.
	 *
	 * @param string $name
	 * @param array $spec
	 * @access protected
	 */
	protected function register_full_width_layout( $name, $spec ) {
		// Initial colStart and colSpan
		$col_start = 0;
		$col_span  = $this->get_setting( 'layout_columns' );

		// If the body is centered, don't span the full width, but the same with of
		// the body.
		if ( 'center' == $this->get_setting( 'body_orientation' ) ) {
			$col_start = floor( ( $this->get_setting( 'layout_columns' ) - $this->get_setting( 'body_column_span' ) ) / 2 );
			$col_span  = $this->get_setting( 'body_column_span' );
		}

		$this->register_layout( $name, array_merge(
			array(
				'columnStart' => $col_start,
				'columnSpan'  => $col_span,
			),
			$spec
		) );
	}

	/**
	 * Returns the text alignment.
	 *
	 * @since 0.8.0
	 *
	 * @access protected
	 * @return string The value for textAlignment.
	 */
	protected function find_text_alignment() {

		// TODO: In a future release, update this logic to respect "align" values.

		return 'left';
	}

	/**
	 * Check if a node has a class.
	 *
	 * @param DomNode $node
	 * @param string $classname
	 * @return boolean
	 * @static
	 * @access protected
	 */
	protected static function node_has_class( $node, $classname ) {
		if ( ! method_exists( $node, 'getAttribute' ) ) {
			return false;
		}

		$classes = trim( $node->getAttribute( 'class' ) );

		if ( empty( $classes ) ) {
			return false;
		}

		return 1 === preg_match( "/(?:\s+|^)$classname(?:\s+|$)/", $classes );
	}

	/**
	 * This function is in charge of transforming HTML into a Article Format
	 * valid array.
	 *
	 * @param string $text
	 * @abstract
	 */
	abstract protected function build( $text );

	/**
	 * Gets the name of this component from the class name.
	 *
	 * @return string
	 */
	public function get_component_name() {
		$class_name = get_class( $this );
		$class_name_path = explode( '\\', $class_name );
		$class_name_no_namespace = end( $class_name_path );
		return strtolower( $class_name_no_namespace );
	}

	/**
	 * Check if the remote file exists for this node.
	 *
	 * @param DomNode $node
	 * @return boolean
	 * @static
	 * @access protected
	 */
	protected static function remote_file_exists( $node ) {
		$html = $node->ownerDocument->saveXML( $node );
		preg_match( '/src="([^"]*?)"/im', $html, $matches );
		$path = $matches[1];

		// Is it a URL? Check the headers in case of 404
		if ( false !== filter_var( $path, FILTER_VALIDATE_URL ) ) {
			if ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV ) {
				$result = vip_safe_wp_remote_get( $path );
			} else {
				$result = wp_safe_remote_get( $path );
			}

			if ( is_wp_error( $result ) || empty( $result['response']['code'] ) || 404 == $result['response']['code'] ) {
				return false;
			} else {
				return true;
			}
		}

		// This could be a local file path.
		// Check that, except on WordPress VIP where this is not possible.
		if ( ! defined( 'WPCOM_IS_VIP_ENV' ) || ! WPCOM_IS_VIP_ENV ) {
			return file_exists( $path );
		}

		// Nothing was found or no further validation is possible.
		return false;
	}

}
