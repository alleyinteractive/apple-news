<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Aside class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

use Apple_Exporter\Component_Factory;
use Apple_Exporter\Theme;
use DOMDocument;
use DOMElement;

/**
 * A component to handle aside content.
 *
 * @since 2.5.0
 */
class Aside extends Component {
	/**
	 * Store the html for the component.
	 *
	 * @var string
	 */
	protected $html;

	/**
	 * Store the post id for the component.
	 *
	 * @var int
	 */
	protected static $post_id;

	/**
	 * Look for node matches for this component.
	 *
	 * @param DOMElement $node The node to examine for matches.
	 * @access public
	 * @return DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		// Note: we can't use the component get_setting method or settings array here, because this is a static class.
		$class = get_option( 'apple_news_settings' )['aside_component_class'] ?? '';

		if ( $class && self::node_has_class( $node, $class ) ) {
			return $node;
		}

		return null;
	}

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role'       => 'aside',
				'layout'     => 'aside-layout',
				'components' => '#components#',
			],
		);

		$this->register_spec(
			'default-aside',
			__( 'Aside Style', 'apple-news' ),
			[
				'border' => [
					'all'    => [
						'color' => '#aside_border_color#',
						'style' => 'solid',
						'width' => 3,
					],
					'top'    => true,
					'bottom' => true,
					'left'   => false,
					'right'  => false,
				],
			],
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 * @access protected
	 */
	protected function build( $html ) {
		$theme = Theme::get_used();

		$dom = new DOMDocument();
		$dom->loadHTML( $html );
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$element = $dom->documentElement->firstElementChild->firstElementChild;

		// Avoid an infinite loop from detecting the aside again.
		$element->removeAttribute( 'class' );

		$this->register_json(
			'json',
			[
				'#components#' => array_map(
					fn ( Component $component ) => $component->to_array(),
					Component_Factory::get_components_from_node( $element ),
				),
			],
		);

		$this->set_anchor_position(
			match ( $theme->get_value( 'aside_alignment' ) ) {
				'left' => self::ANCHOR_LEFT,
				default => self::ANCHOR_RIGHT,
			}
		);

		$this->register_component_style(
			'default-aside',
			'default-aside',
		);
	}
}
