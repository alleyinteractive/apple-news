<?php
/**
 * Publish to Apple News: \Apple_Exporter\Builders\Component_Layouts class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Builders
 */

namespace Apple_Exporter\Builders;

use \Apple_Exporter\Components\Component as Component;

/**
 * Exporter and components can register layouts. This class manages the layouts
 * the final JSON will contain.
 *
 * @since 0.4.0
 */
class Component_Layouts extends Builder {

	/**
	 * All layouts.
	 *
	 * @var array
	 * @access private
	 */
	private $layouts;

	/**
	 * Constructor.
	 *
	 * @param \Apple_Exporter\Exporter_Content          $content The content object to load.
	 * @param \Apple_Exporter\Exporter_Content_Settings $settings The settings object to load.
	 * @access public
	 */
	public function __construct( $content, $settings ) {
		parent::__construct( $content, $settings );
		$this->layouts = array();
	}

	/**
	 * Register a layout into the exporter.
	 *
	 * @since 0.4.0
	 * @param string $name The name of the layout to register.
	 * @param string $spec The spec for the layout.
	 * @access public
	 */
	public function register_layout( $name, $spec ) {
		// Only register once, layouts have unique names.
		if ( $this->layout_exists( $name ) ) {
			return;
		}

		$this->layouts[ $name ] = $spec;
	}

	/**
	 * Returns all layouts registered so far.
	 *
	 * @since 0.4.0
	 * @return array
	 * @access protected
	 */
	protected function build() {
		return apply_filters( 'apple_news_component_layouts', $this->layouts );
	}

	/**
	 * Check if a layout already exists.
	 *
	 * @since 0.4.0
	 * @param string $name The name of the layout to look up.
	 * @access private
	 * @return boolean True if the layout exists, false if not.
	 */
	private function layout_exists( $name ) {
		return array_key_exists( $name, $this->layouts );
	}

	/**
	 * Sets the required layout for a component to anchor another component or
	 * be anchored.
	 *
	 * @param \Apple_Exporter\Components\Component $component The component to anchor.
	 * @access public
	 */
	public function set_anchor_layout_for( $component ) {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Are we anchoring left or right?
		$position = null;
		switch ( $component->get_anchor_position() ) {
			case Component::ANCHOR_NONE:
				return;
			case Component::ANCHOR_LEFT:
				$position = 'left';
				break;
			case Component::ANCHOR_RIGHT:
				$position = 'right';
				break;
			case Component::ANCHOR_AUTO:
				/**
			 * The alignment position is the opposite of the body_orientation
			 * setting. In the case of centered body orientation, use left alignment.
			 * This behaviour was chosen by design.
			 */
				if ( 'left' === $theme->get_value( 'body_orientation' ) ) {
					$position = 'right';
				} else {
					$position = 'left';
				}
				break;
		}

		$layout_name = "anchor-layout-$position";

		if ( ! $this->layout_exists( $layout_name ) ) {

			// Cache settings.
			$body_orientation = $theme->get_value( 'body_orientation' );
			$body_offset      = $theme->get_body_offset();
			$alignment_offset = $theme->get_alignment_offset();
			$body_column_span = $theme->get_body_column_span();
			$layout_columns   = $theme->get_layout_columns();

			/**
			 * Find out the starting column. This is easy enough if we are anchoring
			 * left, but for right side alignment, we have to make some math :)
			 */
			$col_start = $body_offset;
			if ( 'right' === $position ) {
				if ( $component->is_anchor_target() ) {
					$col_start += $alignment_offset;
				} elseif ( 'center' === $body_orientation ) {
					$col_start = $layout_columns - $alignment_offset;
				} else {
					$col_start += $body_column_span - $alignment_offset;
				}
			} elseif ( 'left' === $position && 'center' === $body_orientation ) {
				$col_start = 0;
			}

			/**
			 * Find the column span. For the target element, let's use the same
			 * column span as the Body component, that is, 5 columns, minus the
			 * defined offset. The element to be anchored uses the remaining space.
			 */
			$col_span = 0;
			if ( $component->is_anchor_target() ) {
				$col_span = $body_column_span - $alignment_offset;
			} else {
				$col_span = $alignment_offset;
			}

			// Finally, register the layout.
			$this->register_layout(
				$layout_name,
				array(
					'columnStart' => $col_start,
					'columnSpan'  => $col_span,
				)
			);
		}

		$component->set_json( 'layout', $layout_name );
	}

}
