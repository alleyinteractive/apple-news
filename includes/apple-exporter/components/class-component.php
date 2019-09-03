<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Component class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

require_once __DIR__ . '/../class-markdown.php';

use \Apple_Exporter\Component_Spec;
use \Apple_Exporter\Exporter_Content;
use \Apple_Exporter\Parser;

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
	 * @var \Apple_Exporter\Builders\Component_Text_Styles
	 * @access protected
	 */
	protected $styles;

	/**
	 * Component styles for this component.
	 *
	 * @since 1.4.0
	 * @var \Apple_Exporter\Builders\Component_Styles
	 * @access protected
	 */
	protected $component_styles;

	/**
	 * Layouts for this component.
	 *
	 * @since 0.4.0
	 * @var \Apple_Exporter\Builders\Component_Layouts
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
	 * Specs for this component.
	 *
	 * @since 1.2.4
	 * @var array
	 * @access public
	 */
	public $specs;

	/**
	 * Variable to store the parsed text alignment
	 *
	 * @var string
	 */
	protected $text_alignment;

	/**
	 * Allowed HTML tags for components that support it.
	 *
	 * @since 1.2.7
	 * @var array
	 * @access public
	 */
	public $allowed_html = array(
		'p'          => array(),
		'strong'     => array(),
		'b'          => array(),
		'em'         => array(),
		'i'          => array(),
		'a'          => array(
			'href' => array(),
		),
		'ul'         => array(),
		'ol'         => array(),
		'li'         => array(),
		'br'         => array(),
		'sub'        => array(),
		'sup'        => array(),
		'del'        => array(),
		's'          => array(),
		'pre'        => array(),
		'code'       => array(),
		'samp'       => array(),
		'footer'     => array(),
		'aside'      => array(),
		'blockquote' => array(),
	);

	/**
	 * Constructor.
	 *
	 * @since 1.4.0 Added component styles parameter.
	 *
	 * @param string                                         $text             The HTML for this component.
	 * @param \Apple_Exporter\Workspace                      $workspace        The workspace used to prepare this component.
	 * @param \Apple_Exporter\Settings                       $settings         Settings in use during this run.
	 * @param \Apple_Exporter\Builders\Component_Text_Styles $styles           Registered text styles.
	 * @param \Apple_Exporter\Builders\Component_Layouts     $layouts          Registered layouts.
	 * @param \Apple_Exporter\Parser                         $parser           The parser in use during this run.
	 * @param \Apple_Exporter\Builders\Component_Styles      $component_styles Registered text styles.
	 * @access public
	 */
	public function __construct(
		$text = null,
		$workspace = null,
		$settings = null,
		$styles = null,
		$layouts = null,
		$parser = null,
		$component_styles = null
	) {
		// Register specs for this component.
		$this->register_specs();

		// If all params are null, then this was just used to get spec data.
		// Exit.
		if ( 0 === func_num_args() ) {
			return;
		}

		$this->workspace        = $workspace;
		$this->settings         = $settings;
		$this->styles           = $styles;
		$this->component_styles = $component_styles;
		$this->layouts          = $layouts;
		$this->text             = $text;
		$this->json             = null;

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
	 * Given a DOMElement, if it matches the component, return the relevant node to
	 * work on. Otherwise, return null.
	 *
	 * This function is intended to be overwritten by child classes.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		return null;
	}

	/**
	 * Use PHP's HTML parser to generate valid HTML out of potentially broken
	 * input.
	 *
	 * @param string $html The HTML to be cleaned.
	 * @access protected
	 * @return string The cleaned HTML.
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
		// If HTML support is enabled, provide an extra level of validation for supported tags.
		if ( ! empty( $this->json['text'] ) && $this->html_enabled() ) {
			$this->json['text'] = wp_kses( $this->json['text'], $this->allowed_html );
		}

		return apply_filters( 'apple_news_' . $this->get_component_name() . '_json', $this->json );
	}

	/**
	 * Set a JSON value.
	 *
	 * @param string $name  The name of the key to set in the JSON.
	 * @param mixed  $value The value to set in the JSON.
	 * @access public
	 */
	public function set_json( $name, $value ) {
		if ( ! empty( $name ) ) {
			$this->json[ $name ] = $value;
		}
	}

	/**
	 * Get a JSON value
	 *
	 * @param string $name The name of the key to look up in the JSON.
	 * @access public
	 * @return mixed The value corresponding to the key.
	 */
	public function get_json( $name ) {
		// TODO - how is this used?
		return ( isset( $this->json[ $name ] ) ) ? $this->json[ $name ] : null;
	}

	/**
	 * Set the anchor position.
	 *
	 * @param int $position The position of the anchor to set.
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
	 * @param string $source   The path or URL of the resource which is going to be bundled.
	 * @param string $filename The name of the file to be created.
	 * @return string The URL to use for this asset in the JSON.
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
	 * @param string $filename The name of the file to be created.
	 * @param string $source   The path or URL of the resource which is going to be bundled.
	 */
	protected function bundle_source( $filename, $source ) {
		$this->workspace->bundle_source( $filename, $source );
	}

	/**
	 * Gets an exporter setting.
	 *
	 * @since 0.4.0
	 * @param string $name The name of the setting to look up.
	 * @access protected
	 * @return mixed The value of the setting.
	 */
	protected function get_setting( $name ) {
		return $this->settings->get( $name );
	}

	/**
	 * Whether HTML format is enabled for this component type.
	 *
	 * This function is intended to be overridden in child classes, but to call
	 * this parent function with the value it intends to set. This function
	 * will use the provided value if HTML support is enabled via settings.
	 *
	 * @param bool $enabled Optional. Whether to enable HTML for this component. Defaults to false.
	 *
	 * @access protected
	 * @return bool Whether HTML format is enabled for this component type.
	 */
	protected function html_enabled( $enabled = false ) {
		if ( empty( $this->settings->html_support )
			|| 'yes' !== $this->settings->html_support
		) {
			return false;
		}

		/**
		 * Lets themes and plugins override HTML settings for a component.
		 *
		 * @param bool $enabled Whether HTML is enabled for this component.
		 */
		return apply_filters(
			'apple_news_' . $this->get_component_name() . '_html_enabled',
			$enabled
		);
	}

	/**
	 * Sets an exporter setting.
	 *
	 * @since 0.4.0
	 * @param string $name  The name of the setting to set.
	 * @param mixed  $value The value of the setting to set.
	 * @access protected
	 * @return boolean True on success, false on failure.
	 */
	protected function set_setting( $name, $value ) {
		// TODO - how is this used?
		return $this->settings->set( $name, $value );
	}

	/**
	 * Store specs that can be used for managing component JSON using an admin screen.
	 *
	 * @since 1.2.4
	 * @param string $name  The name of the spec to be registered.
	 * @param string $label The label for the spec.
	 * @param array  $spec  The spec definition to register.
	 * @access protected
	 */
	protected function register_spec( $name, $label, $spec ) {
		// Store as a multidimensional array with the label and spec, indexed by name.
		$this->specs[ $name ] = new Component_Spec( $this->get_component_name(), $name, $label, $spec );
	}

	/**
	 * Get a spec to use for creating component JSON.
	 *
	 * @since 1.2.4
	 * @param string $spec_name The name of the spec to fetch.
	 * @access protected
	 * @return array The spec definition.
	 */
	protected function get_spec( $spec_name ) {
		if ( ! isset( $this->specs[ $spec_name ] ) ) {
			return null;
		}

		return $this->specs[ $spec_name ];
	}

	/**
	 * Set the JSON for the component.
	 *
	 * @since 1.2.4
	 * @param string $spec_name The spec to use for defining the JSON.
	 * @param array  $values    Values to substitute for placeholders in the spec.
	 * @access protected
	 */
	protected function register_json( $spec_name, $values = array() ) {
		$component_spec = $this->get_spec( $spec_name );
		if ( ! empty( $component_spec ) ) {
			$post_id    = ( ! empty( $this->workspace->content_id ) )
				? $this->workspace->content_id
				: 0;
			$this->json = $component_spec->substitute_values( $values, $post_id );
		}
	}

	/**
	 * Using the style service, register a new style.
	 *
	 * @since 0.4.0
	 * @param string $name      The name of the style.
	 * @param string $spec_name The spec to use for defining the JSON.
	 * @param array  $values    Values to substitute for placeholders in the spec.
	 * @param array  $property  The JSON property to set with the style.
	 * @access protected
	 */
	protected function register_style( $name, $spec_name, $values = array(), $property = null ) {
		$component_spec = $this->get_spec( $spec_name );
		if ( ! empty( $component_spec ) ) {
			$post_id = ( ! empty( $this->workspace->content_id ) )
				? $this->workspace->content_id
				: 0;
			$json    = $component_spec->substitute_values( $values, $post_id );
			$this->styles->register_style( $name, $json );
			$this->set_json( $property, $name );
		}
	}

	/**
	 * Using the component style service, register a new component style.
	 *
	 * @since 1.4.0
	 *
	 * @param string $name      The name of the style.
	 * @param string $spec_name The spec to use for defining the JSON.
	 * @param array  $values    Values to substitute for placeholders in the spec.
	 * @param array  $property  The JSON property to set with the style.
	 * @access protected
	 */
	protected function register_component_style( $name, $spec_name, $values = array(), $property = null ) {
		$component_spec = $this->get_spec( $spec_name );
		if ( ! empty( $component_spec ) ) {
			$post_id = ( ! empty( $this->workspace->content_id ) )
				? $this->workspace->content_id
				: 0;
			$json    = $component_spec->substitute_values( $values, $post_id );
			$this->component_styles->register_style( $name, $json );
			$this->set_json( $property, $name );
		}
	}

	/**
	 * Using the layouts service, register a new layout.
	 *
	 * @since 0.4.0
	 * @param string $name      The name of the layout.
	 * @param string $spec_name The spec to use for defining the JSON.
	 * @param array  $values    Values to substitute for placeholders in the spec.
	 * @param array  $property  The JSON property to set with the layout.
	 * @access protected
	 */
	protected function register_layout( $name, $spec_name, $values = array(), $property = null ) {
		$component_spec = $this->get_spec( $spec_name );
		if ( ! empty( $component_spec ) ) {
			$post_id = ( ! empty( $this->workspace->content_id ) )
				? $this->workspace->content_id
				: 0;
			$json    = $component_spec->substitute_values( $values, $post_id );
			$this->layouts->register_layout( $name, $json );
			$this->set_json( $property, $name );
		}
	}

	/**
	 * Register a new layout which will be displayed as full-width, so no need to
	 * specify columnStart and columnSpan in the layout specs. This is useful
	 * because when the body is centered, the full-width layout spans the same
	 * columns as the body.
	 *
	 * @param string $name      The name of the layout.
	 * @param string $spec_name The spec to use for defining the JSON.
	 * @param array  $values    Values to substitute for placeholders in the spec.
	 * @param array  $property  The JSON property to set with the layout.
	 * @access protected
	 */
	protected function register_full_width_layout( $name, $spec_name, $values = array(), $property = null ) {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Initial colStart and colSpan.
		$col_start = 0;
		$col_span  = $theme->get_layout_columns();

		// If the body is centered, don't span the full width, but the same width of the body.
		if ( 'center' === $theme->get_value( 'body_orientation' ) ) {
			$col_start = floor( ( $theme->get_layout_columns() - $theme->get_body_column_span() ) / 2 );
			$col_span  = $theme->get_body_column_span();
		}

		/**
		 * Merge this into the existing spec.
		 * These values just get hardcoded in the spec since the above logic
		 * would make them impossible to override manually.
		 * Changes to this should really be handled by the above plugin settings.
		 */
		if ( isset( $this->specs[ $spec_name ] ) ) {
			$this->specs[ $spec_name ]->spec = array_merge(
				$this->specs[ $spec_name ]->spec,
				array(
					'columnStart' => $col_start,
					'columnSpan'  => $col_span,
				)
			);
		}

		// Register the layout as normal.
		$this->register_layout( $name, $spec_name, $values, $property );
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
	 * @param \DOMElement $node      The node to examine for matches.
	 * @param string      $classname The name of the class to look up.
	 * @access protected
	 * @return boolean True if the node has the class, false if it does not.
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
	 * @param string $html The HTML to parse into text for processing.
	 * @access protected
	 */
	abstract protected function build( $html );

	/**
	 * Register all specs used by this component.
	 *
	 * @abstract
	 * @access public
	 */
	abstract public function register_specs();

	/**
	 * Get all specs used by this component.
	 *
	 * @return array
	 * @access public
	 */
	public function get_specs() {
		return $this->specs;
	}

	/**
	 * Gets the name of this component from the class name.
	 *
	 * @return string
	 */
	public function get_component_name() {
		$class_name              = get_class( $this );
		$class_name_path         = explode( '\\', $class_name );
		$class_name_no_namespace = end( $class_name_path );
		return strtolower( $class_name_no_namespace );
	}

	/**
	 * Check if the remote file exists for this node.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @return boolean
	 * @access protected
	 */
	protected static function remote_file_exists( $node ) {

		// Try to get a URL from the src attribute of the HTML.
		$html = $node->ownerDocument->saveXML( $node );
		$path = self::url_from_src( $html );
		if ( empty( $path ) ) {
			return false;
		}

		// Fork for method of retrieval if running on VIP.
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV ) {
			$result = vip_safe_wp_remote_get( $path );
		} else {
			$result = wp_safe_remote_get( $path );
		}

		// Check the headers in case of an error.
		return ( ! is_wp_error( $result )
			&& ! empty( $result['response']['code'] )
			&& $result['response']['code'] < 400
		);
	}

	/**
	 * Returns a full URL from the first `src` parameter in the provided HTML that
	 * has content.
	 *
	 * @param string $html The HTML to examine for `src` parameters.
	 *
	 * @return string A URL on success, or a blank string on failure.
	 */
	protected static function url_from_src( $html ) {

		// Try to find src values in the provided HTML.
		if ( ! preg_match_all( '/src=[\'"]([^\'"]+)[\'"]|background-image:url\((.*?)\)/im', $html, $matches ) ) {
			return '';
		}

		// Loop through matches, returning the first valid URL found, matching on src=.
		foreach ( $matches[1] as $url ) {

			// Run the URL through the formatter.
			$url = Exporter_Content::format_src_url( $url );

			// If the URL passes validation, return it.
			if ( ! empty( $url ) ) {
				return $url;
			}
		}

		// Matching on background-image:url.
		foreach ( $matches[2] as $url ) {

			// Run the URL through the formatter.
			$url = Exporter_Content::format_src_url( $url );

			// If the URL passes validation, return it.
			if ( ! empty( $url ) ) {
				return $url;
			}
		}

		return '';
	}

	/**
	 * Get iframe/embed node.
	 *
	 * @param \DOMElement $node The node to examine.
	 * @return bool True if the figure is an iframe.
	 */
	public static function is_embed_figure( $node ) {

		// Set default.
		$has_figure_iframe = false;

		// Return false if we don't have any child nodes.
		if ( ! $node->hasChildNodes() ) {
			return $has_figure_iframe;
		}

		// Loop those child nodes.
		foreach ( $node->childNodes as $child ) {

			// Return false if we don't have children, or if is an image.
			if ( ! $child->hasChildNodes() || 'img' === $child->nodeName ) {
				return $has_figure_iframe;
			}

			// Loop subchildren.
			foreach ( $child->childNodes as $c ) {

				// Return true if we're seeing an iframe.
				if ( 'iframe' === $c->nodeName ) {
					return true;
				}
			}
		}

		return $has_figure_iframe;
	}
}
