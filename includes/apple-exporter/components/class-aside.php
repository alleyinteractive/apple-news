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
	 * We are providing our own layout below, so don't set one automatically when anchoring.
	 *
	 * @var bool
	 */
	public $needs_layout_if_anchored = false;

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
		$theme = \Apple_Exporter\Theme::get_used();

		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role'       => 'aside',
				'layout'     => 'aside-layout',
				'components' => '#components#',
			],
		);

		$aside_conditional_style = [];
		if ( ! empty( $theme->get_value( 'aside_background_color_dark' ) ) || ! empty( $theme->get_value( 'aside_border_color_dark' ) ) ) {
			$aside_conditional_style = [
				'conditional' => [
					'backgroundColor' => '#aside_background_color_dark#',
					'border'          => [
						'all' => [
							'width' => '#blockquote_border_width#',
							'style' => '#blockquote_border_style#',
							'color' => '#aside_border_color_dark#',
						],
					],
					'conditions'      => [
						'minSpecVersion'       => '1.14',
						'preferredColorScheme' => 'dark',
					],
				],
			];
		}

		$this->register_spec(
			'default-aside',
			__( 'Aside Style', 'apple-news' ),
			array_merge(
				[
					'backgroundColor' => '#aside_background_color#',
					'border'          => [
						'all' => [
							'color' => '#aside_border_color#',
							'style' => '#aside_border_style#',
							'width' => '#aside_border_width#',
						],
					],
				],
				$aside_conditional_style
			)
		);

		$aside_layout = [
			'columnSpan' => 3,
			'padding'    => '#aside_padding#',
			'margin'     => 20,
		];

		$this->register_spec(
			'aside-layout-left',
			__( 'Aside Layout - Left Aligned', 'apple-news' ),
			array_merge(
				[
					'columnStart' => 0,
				],
				$aside_layout
			)
		);

		$this->register_spec(
			'aside-layout-right',
			__( 'Aside Layout - Right Aligned', 'apple-news' ),
			array_merge(
				[
					'columnStart' => 3,
				],
				$aside_layout
			)
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

		$this->register_component_style(
			'default-aside',
			'default-aside',
		);

		$alignment   = $theme->get_value( 'aside_alignment' );
		$layout_name = 'left' === $alignment ? 'aside-layout-left' : 'aside-layout-right';
		$this->register_layout( $layout_name, $layout_name, [], 'layout' );
		$this->anchor_position = 'left' === $alignment ? self::ANCHOR_LEFT : self::ANCHOR_RIGHT;
	}
}
