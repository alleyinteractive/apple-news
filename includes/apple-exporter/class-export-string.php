<?php
/**
 * Convert a string of html into an Apple News components.
 *
 * @package Apple_News
 * @since 2.5.0
 */

namespace Apple_Exporter;

use Admin_Apple_Sections;
use Apple_Actions\Index\Export;
use Apple_Exporter\Components\Component as Apple_News_Component;
use Apple_Exporter\Settings;
use Apple_Exporter\Theme;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;

/**
 * Class to convert a string of html into Apple News Components.
 */
class Export_String {
	/**
	 * Caches bundles as subcontainers are processed.
	 *
	 * @var array
	 */
	private static array $bundles = [];

	/**
	 * Caches component objects for use in placeholder replacement.
	 *
	 * @var array
	 */
	private static array $containers = [];

	/**
	 * Caches HTML for containers in the order in which they are encountered.
	 *
	 * @var array
	 */
	private static array $container_html = [];

	/**
	 * Caches whether a custom export is happening or not.
	 *
	 * @var bool
	 */
	private static bool $exporting = false;

	/**
	 * Caches the name of the class that initiated the export.
	 *
	 * @var string
	 */
	private static string $exporting_type = '';

	/**
	 * Caches arbitrary HTML for use in export operations.
	 *
	 * @var string
	 */
	private static string $html = '';

	/**
	 * Keeps track of the currently exporting post's ID.
	 *
	 * @var int
	 */
	private static int $post_id = 0;

	/**
	 * Store the settings for the component.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Gets the array of bundle URLs for the currently exporting post.
	 *
	 * @return string[] An array of bundle URLs.
	 */
	public static function get_bundles(): array {
		return self::$bundles;
	}

	/**
	 * Gets the next container component from the queue.
	 *
	 * @return Apple_News_Component The component object.
	 */
	public static function get_container(): Apple_News_Component {
		return array_shift( self::$containers );
	}

	/**
	 * Gets the next blob of container HTML from the queue.
	 *
	 * @return string The container HTML.
	 */
	public static function get_container_html(): string {
		return array_shift( self::$container_html );
	}

	/**
	 * Gets the exporting flag.
	 *
	 * @return bool The exporting flag.
	 */
	public static function get_exporting(): bool {
		return self::$exporting;
	}

	/**
	 * Gets the type of export.
	 *
	 * @return string The type of export.
	 */
	public static function get_exporting_type(): string {
		return self::$exporting_type;
	}

	/**
	 * Gets the cached HTML.
	 *
	 * @return string The cached HTML.
	 */
	public static function get_html(): string {
		return self::$html;
	}

	/**
	 * Gets the currently exporting post ID.
	 *
	 * @return int The currently exporting post ID.
	 */
	public static function get_post_id(): int {
		return self::$post_id;
	}

	/**
	 * Adds a bundle URL to the bundle cache.
	 *
	 * @param string $url The URL to bundle.
	 */
	public static function set_bundle( string $url ) {
		if ( ! empty( $url ) && ! in_array( $url, self::$bundles, true ) ) {
			self::$bundles[] = $url;
		}
	}

	/**
	 * Adds a component to the container placeholder processing queue.
	 *
	 * @param Apple_News_Component $component The component class to add to the queue.
	 */
	public static function set_container( Apple_News_Component $component ) {
		self::$containers[] = $component;
	}

	/**
	 * Adds container HTML to the containers array.
	 *
	 * @param string $html The HTML to cache for the container.
	 */
	public static function set_container_html( string $html ) {
		self::$container_html[] = $html;
	}

	/**
	 * Sets the exporting flag.
	 *
	 * @param bool $exporting The new value for the exporting flag.
	 */
	public static function set_exporting( bool $exporting ) {
		self::$exporting = $exporting;
	}

	/**
	 * Sets the exporting type.
	 *
	 * @param string $exporting_type The name of the class that initiated the export.
	 */
	public static function set_exporting_type( string $exporting_type ) {
		self::$exporting_type = $exporting_type;
	}

	/**
	 * Sets the cached HTML.
	 *
	 * @param string $html The HTML to cache.
	 */
	public static function set_html( string $html ) {
		self::$html = $html;
	}

	/**
	 * Sets the currently exporting post ID.
	 *
	 * @param int $post_id The currently exporting post ID.
	 */
	public static function set_post_id( int $post_id ) {
		self::$post_id = $post_id;
	}

	/**
	 * Given a URL to bundle, adds it to the bundle if necessary, and returns
	 * the modified URL.
	 *
	 * @param string $url The URL to bundle. Can be a bundle:// URL.
	 *
	 * @return string The modified URL.
	 */
	protected function bundle( string $url ): string {
		return ( 0 !== strpos( $url, 'bundle://' ) )
			? $this->maybe_bundle_source( $url )
			: $url;
	}

	/**
	 * Exports the current container, returning JSON, layouts, and styles.
	 *
	 * @return array {
	 *      An associative array with keys for each type.
	 *
	 *      @type array  $componentLayouts    Component layouts for the component.
	 *      @type array  $componentStyles     Component styles for the component.
	 *      @type array  $componentTextStyles Component text styles for the component.
	 *      @type string $json                JSON for the component.
	 * }
	 */
	protected function export_container(): array {
		return [
			'componentLayouts'    => $this->layouts->to_array(),
			'componentStyles'     => $this->component_styles->to_array(),
			'componentTextStyles' => $this->styles->to_array(),
			'json'                => $this->json,
		];
	}

	/**
	 * Given an array of components that exist inside of a container (e.g., a
	 * sidebar or entry component) filters the content in ways that apply to
	 * component inner elements.
	 *
	 * @param array $components The components array to filter.
	 *
	 * @return array The filtered components array.
	 */
	protected function filter_container_components( array $components ): array {
		foreach ( $components as &$component ) {
			if ( ( isset( $component['role'] ) && 'photo' === $component['role'] )
				|| ( isset( $component['components'][0]['role'] ) && 'photo' === $component['components'][0]['role'] )
			) {
				$url = $component['components'][0]['URL'] ?? $component['URL'] ?? '';
				if ( ! empty( $url ) ) {
					$component = [
						'role'   => 'container',
						'name'   => 'motion artwork',
						'layout' => [
							'minimumHeight' => '100cw',
							'columnStart'   => 0,
							'columnSpan'    => 16,
							'margin'        => [
								'top'    => 0,
								'bottom' => 40,
							],
							'conditional'   => [
								'conditions'           => [
									'maxViewportWidth' => 490,
								],
								'ignoreDocumentMargin' => true,
							],
						],
						'style'  => [
							'mask'   => [
								'type'        => 'corners',
								'radius'      => 20,
								'topRight'    => true,
								'bottomRight' => true,
								'topLeft'     => true,
								'bottomLeft'  => true,
							],
							'shadow' => [
								'color'   => '#000',
								'opacity' => 0.1,
								'radius'  => 4,
								'offset'  => [
									'x' => 0,
									'y' => 2,
								],
							],
							'fill'   => [
								'type'              => 'image',
								'URL'               => $this->bundle( $url ),
								'fillMode'          => 'fit',
								'verticalAlignment' => 'top',
							],
						],
					];
				}
			} elseif ( isset( $component['layout'] ) && 'apple-news-embed-layout' === $component['layout'] ) {
				$component['layout'] = [
					'margin' => [
						'top'    => 40,
						'bottom' => 40,
					],
				];
			}
		}

		return $components;
	}

	/**
	 * Given a DOMElement, returns an array of classes on the element, if any.
	 *
	 * @param DOMElement $element The node to examine for classes.
	 *
	 * @return string[] An array of strings representing classes on the DOMNode.
	 */
	protected function get_classes_for_element( DOMElement $element ): array {
		return explode( ' ', $element->getAttribute( 'class' ) );
	}

	/**
	 * Given a DOMElement object, returns inner HTML for the element.
	 *
	 * @param DOMElement $element The element to process into inner HTML.
	 *
	 * @return string The inner HTML for the element.
	 */
	public function get_inner_html_for_element( DOMElement $element ): string {
		$html = '';
		foreach ( $element->childNodes as $node ) {
			$html .= $element->ownerDocument->saveHTML( $node );
		}

		return trim( $html );
	}

	/**
	 * Given arbitrary HTML, generates JSON for it, treating it as a subset of the
	 * article HTML for the currently exporting article.
	 *
	 * @param string $html The HTML to convert to JSON.
	 *
	 * @return array The Apple News Format document JSON, expressed as an associative array.
	 */
	public function get_json_for_html( string $html ): array {
		$this->set_exporting( true );
		$this->set_exporting_type( get_called_class() );
		$this->set_html( $html );
		add_action( 'apple_news_do_fetch_exporter', [ $this, 'action_apple_news_do_fetch_exporter' ] );

		// Cache the arbitrary HTML so the hook can pick it up.
		$this->set_html( $html );

		// Hook up a filter to replace the post's content with the arbitrary HTML we just cached.
		add_filter( 'apple_news_exporter_content', [ $this, 'get_html' ] );

		// For this request, disable all components (e.g., title, byline, etc) and turn off dropcap because we don't need them for what we're doing here.
		$theme               = Theme::get_used();
		$old_dropcap         = $theme->get_value( 'initial_dropcap' );
		$old_component_order = $theme->get_value( 'meta_component_order' );
		$theme->set_value( 'initial_dropcap', 'no' );
		$theme->set_value( 'meta_component_order', [] );
		$theme->use_this();

		// Run the export, which will pick up the arbitrary HTML via the hook.
		$export = new Export(
			$this->settings,
			$this->get_post_id(),
			Admin_Apple_Sections::get_sections_for_post( $this->get_post_id() )
		);
		$json   = json_decode( $export->perform(), true );

		// Unset the arbitrary HTML from the cache.
		$this->set_html( '' );

		// Remove the filter, now that we're done with this targeted export.
		remove_filter( 'apple_news_exporter_content', [ $this, 'get_html' ] );

		// Reset meta component order.
		$theme->set_value( 'initial_dropcap', $old_dropcap );
		$theme->set_value( 'meta_component_order', $old_component_order );
		$theme->use_this();

		// Unset the exporting flag.
		$this->set_exporting_type( '' );
		$this->set_exporting( false );

		return $json;
	}

	/**
	 * Given arbitrary HTML and a class name, returns a DOMNodeList of matches for the class name from the HTML loaded into the xpath property.
	 *
	 * @param string $html  The HTML to analyze for the class name.
	 * @param string $class The class name to look up.
	 *
	 * @return DOMNodeList A DOMNodeList of matches for the given class.
	 */
	protected function get_nodes_by_class( string $html, string $class ): DOMNodeList {
		$nodes = $this->get_xpath_for_html( $html )->query( sprintf( '//div[@class="%s"]', $class ) );

		return $nodes instanceof DOMNodeList
			? $nodes
			: new DOMNodeList();
	}

	/**
	 * Given arbitrary HTML, returns a DOMElement representation of the root node of the given HTML.
	 *
	 * @param string $html The HTML to convert to a DOMElement.
	 *
	 * @return DOMElement A DOMElement representing the root node of the given HTML.
	 */
	public function get_root_element( string $html ): DOMElement {
		$root_element = null;
		$body_element = $this->get_xpath_for_html( $html )->query( '//body' )->item( 0 );
		if ( $body_element instanceof DOMElement ) {
			$root_element = $body_element->childNodes->item( 0 );
		}

		return $root_element instanceof DOMElement
			? $root_element
			: new DOMElement( 'root' );
	}

	/**
	 * Given arbitrary HTML, loads it into a DOMXPath object as a property on this class.
	 *
	 * @param string $html The HTML to load into a DOMXPath object.
	 *
	 * @return ?DOMXPath The DOMXPath object for the provided HTML.
	 */
	private function get_xpath_for_html( string $html ): DOMXPath {
		libxml_use_internal_errors( true );
		$doc = new DOMDocument();
		// @source https://davidwalsh.name/domdocument-utf8-problem
		$doc->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		$xpath = new DOMXPath( $doc );
		libxml_clear_errors();

		return $xpath instanceof DOMXPath
			? $xpath
			: new DOMXPath( new DOMDocument() );
	}

	/**
	 * Store the post id.
	 *
	 * @param int $post_id The post ID being exported.
	 */
	function action_apple_news_do_fetch_exporter( $post_id ) {
		// Skip this action if we're exporting inner HTML.
		if ( $this->get_exporting() ) {
			return;
		}

		// Cache the exporting post ID for future use.
		$this->set_post_id( (int) $post_id );
	}

	/**
	 * Set the settings.
	 *
	 * @param Settings $settings The settings from the component.
	 */
	function set_settings( $settings ) {
		$this->settings = $settings;
	}

}
