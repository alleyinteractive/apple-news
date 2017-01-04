<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Builders\Components class
 *
 * Contains a class for organizing content into components.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.4.0
 */

namespace Apple_Exporter\Builders;

use \Apple_Exporter\Component_Factory;
use \Apple_Exporter\Components\Component;
use \Apple_Exporter\Workspace;
use \Apple_News;

/**
 * A class for organizing content into components.
 *
 * @since 0.4.0
 */
class Components extends Builder {

	/**
	 * Builds an array with all the components of this WordPress content.
	 *
	 * @access protected
	 * @return array An array of component objects representing segmented content.
	 */
	protected function build() {

		// Initialize.
		$components = array();
		$workspace = new Workspace( $this->content_id() );

		// Loop through body components and process each.
		foreach ( $this->_split_into_components() as $component ) {

			// Ensure that the component is valid.
			$component_array = $component->to_array();
			if ( is_wp_error( $component_array ) ) {
				$workspace->log_error(
					'component_errors',
					$component_array->get_error_message()
				);
				continue;
			}

			// Add component to the array to be used in grouping.
			$components[] = $component_array;
		}

		// Process meta components.
		//
		// Meta components are handled after the body and then prepended, since they
		// could change depending on the above body processing, such as if a
		// thumbnail was used from the body.
		$components = array_merge( $this->_meta_components(), $components );

		// Group body components to improve text flow at all orientations.
		$components = $this->_group_body_components( $components );

		return $components;
	}

	/**
	 * Estimates the number of chars in a line of text next to an anchored component.
	 *
	 * @since 1.2.1
	 *
	 * @access private
	 * @return int The estimated number of characters per line.
	 */
	private function _characters_per_line_anchored() {

		// Get the body text size in points.
		$body_size = $this->get_setting( 'body_size' );

		// Calculate the base estimated characters per line.
		$cpl = 20 + 230 * pow( M_E, - 0.144 * $body_size );

		// If the alignment is centered, cut CPL in half due to less available space.
		$body_orientation = $this->get_setting( 'body_orientation' );
		if ( 'center' === $body_orientation ) {
			$cpl /= 2;
		}

		// If using a condensed font, boost the CPL.
		$body_font = $this->get_setting( 'body_font' );
		if ( false !== stripos( $body_font, 'condensed' ) ) {
			$cpl *= 1.5;
		}

		// Round up for good measure.
		$cpl = ceil( $cpl );

		/**
		 * Allows for filtering of the estimated characters per line.
		 *
		 * Themes and plugins can modify this value to make it more or less
		 * aggressive, or provide an arbitrarily high number to effectively
		 * eliminate intelligent grouping of body blocks.
		 *
		 * @since 1.2.1
		 *
		 * @param int $cpl The characters per line value to be filtered.
		 * @param int $body_size The value for the body size setting in points.
		 * @param string $body_orientation The value for the orientation setting.
		 * @param string $body_font The value for the body font setting.
		 */
		$cpl = apply_filters(
			'apple_news_characters_per_line_anchored',
			$cpl,
			$body_size,
			$body_orientation,
			$body_font
		);

		return ceil( absint( $cpl ) );
	}

	/**
	 * Performs additional processing on 'body' nodes to clean up data.
	 *
	 * @param Component &$component The component to clean up.
	 *
	 * @access private
	 */
	private function _clean_up_components( &$component ) {

		// Only process 'body' nodes.
		if ( 'body' !== $component['role'] ) {
			return;
		}

		// Trim the fat.
		$component['text'] = trim( $component['text'] );
	}

	/**
	 * Given an anchored component, estimate the minimum number of lines it occupies.
	 *
	 * @param Component $component The component anchoring to the body.
	 *
	 * @access private
	 * @return int The estimated number of lines the anchored component occupies.
	 */
	private function _get_anchor_buffer( $component ) {

		// If the anchored component is empty, bail.
		if ( empty( $component ) ) {
			return 0;
		}

		// Get the estimated number of characters per line based on configuration.
		$cpl = $this->_characters_per_line_anchored();

		// TODO: Analyze the anchored component to estimate the height in lines.
		$lines = 0;

		return $lines;
	}

	/**
	 * Given a body node, estimates the number of lines the text occupies.
	 *
	 * @param Component $component The component representing the body.
	 *
	 * @access private
	 * @return int The estimated number of lines the body text occupies.
	 */
	private function _get_anchor_content_lines( $component ) {

		// If the body component is empty, bail.
		if ( empty( $component['text'] ) ) {
			return 0;
		}

		return strlen( $component['text'] ) / $this->_characters_per_line_anchored();
	}

	/**
	 * Intelligently group all elements of role 'body'.
	 *
	 * Given an array of components in array format, group all the elements of role
	 * 'body'. Ignore body elements that have an ID, as they are used for anchoring.
	 * Grouping body like this allows the Apple Format interpreter to render proper
	 * paragraph spacing.
	 *
	 * @since 0.6.0
	 *
	 * @param array $components An array of Component objects to group.
	 *
	 * @access private
	 * @return array
	 */
	private function _group_body_components( $components ) {

		// Initialize.
		$new_components = array();
		$cover_index = null;
		$anchor_buffer = 0;
		$prev = null;
		$current = null;

		// Loop through components, grouping as necessary.
		foreach ( $components as $component ) {

			// Update positioning.
			$prev = $current;
			$current = $component;

			// Handle first run.
			if ( null === $prev ) {
				continue;
			}

			// Handle anchors.
			if ( ! empty( $prev['identifier'] )
			     && ! empty( $current['anchor']['targetComponentIdentifier'] )
			     && $prev['identifier']
			        === $current['anchor']['targetComponentIdentifier']
			) {
				// Switch the position of the nodes so the anchor always comes first.
				$temp = $current;
				$current = $prev;
				$prev = $temp;
				$anchor_buffer = $this->_get_anchor_buffer( $prev );
			} elseif ( ! empty( $current['identifier'] )
			           && ! empty( $prev['anchor']['targetComponentIdentifier'] )
			           && $prev['anchor']['targetComponentIdentifier']
			              === $current['identifier']
			) {
				$anchor_buffer = $this->_get_anchor_buffer( $prev );
			}

			// If the current node is not a body node, force-flatten the buffer.
			if ( 'body' !== $current['role'] ) {
				$anchor_buffer = 0;
			} elseif ( $anchor_buffer > 0 ) {
				$anchor_buffer -= $this->_get_anchor_content_lines( $current );
			}

			// Keep track of the header position.
			if ( 'header' === $prev['role'] ) {
				$cover_index = count( $new_components );
			}

				// Add the previous node if we are out of buffer or if it isn't body.
			if ( $anchor_buffer <= 0 || 'body' !== $prev['role'] ) {
				$new_components[] = $prev;
				continue;
			}

			// Merge the body content from the previous node into the current node.
			$current['text'] .= $prev['text'];
		}

		// Add the final element from the loop in its final state.
		$new_components[] = $current;

		// Perform text cleanup on each node.
		array_walk( $new_components, array( $this, '_clean_up_components' ) );

		// If the final node has a role of 'body', add 'body-layout-last' layout.
		$last = count( $new_components ) - 1;
		if ( 'body' === $new_components[ $last ]['role'] ) {
			$new_components[ $last ]['layout'] = 'body-layout-last';
		}

		// Determine if there is a cover in the middle of content.
		if ( null === $cover_index
		     || count( $new_components ) <= $cover_index + 1
		) {
			return $new_components;
		}

		// All components after the cover must be grouped to avoid issues with
		// parallax text scroll.
		$regrouped_components = array(
			'role' => 'container',
			'layout' => array(
				'columnSpan' => $this->get_setting( 'layout_columns' ),
				'columnStart' => 0,
				'ignoreDocumentMargin' => true,
			),
			'style' => array(
				'backgroundColor' => $this->get_setting( 'body_background_color' ),
			),
			'components' => array_slice( $new_components, $cover_index + 1 ),
		);

		return array_merge(
			array_slice( $new_components, 0, $cover_index + 1 ),
			array( $regrouped_components )
		);
	}

	/**
	 * Returns an array of meta component objects.
	 *
	 * Meta components are those which were not created from the HTML content.
	 * These include the title, the cover (i.e. post thumbnail) and the byline.
	 *
	 * @access private
	 * @return array An array of Component objects representing metadata.
	 */
	private function _meta_components() {

		// Attempt to get the component order.
		$meta_component_order = $this->get_setting( 'meta_component_order' );
		if ( empty( $meta_component_order )
		     || ! is_array( $meta_component_order )
		) {
			return array();
		}

		// Build array of meta components using specified order.
		$components = array();
		foreach ( $meta_component_order as $i => $component ) {

			// Determine if component is loadable.
			$method = 'content_' . $component;
			if ( ! method_exists( $this, $method )
			     || ! ( $content = $this->$method() )
			) {
				continue;
			}

			// Attempt to load component.
			$component = $this->get_component_from_shortname( $component, $content );
			if ( ! ( $component instanceof Component ) ) {
				continue;
			}
			$component = $component->to_array();

			// If the cover isn't first, give it a different layout.
			if ( 'header' === $component['role'] && 0 !== $i ) {
				$component['layout'] = 'headerBelowTextPhotoLayout';
			}

			$components[] = $component;
		}

		return $components;
	}

	/**
	 * Split components from the source WordPress content.
	 *
	 * @access private
	 * @return array An array of Component objects representing the content.
	 */
	private function _split_into_components() {

		// Loop though the first-level nodes of the body element. Components might
		// include child-components, like an Cover and Image.
		$components = array();
		foreach ( $this->content_nodes() as $node ) {
			$components = array_merge(
				$components,
				$this->get_components_from_node( $node )
			);
		}

		// Perform additional processing after components have been created.
		$this->add_thumbnail_if_needed( $components );
		$this->anchor_components( $components );
		$this->add_pullquote_if_needed( $components );

		return $components;
	}

	// TODO: REFACTOR FROM HERE

	/**
	 * Anchor components that are marked as can_be_anchor_target.
	 *
	 * @param array &$components
	 *
	 * @access private
	 */
	private function anchor_components( &$components ) {
		$len = count( $components );

		for ( $i = 0; $i < $len; $i ++ ) {

			if ( ! isset( $components[ $i ] ) ) {
				continue;
			}

			$component = $components[ $i ];

			if ( $component->is_anchor_target() || Component::ANCHOR_NONE == $component->get_anchor_position() ) {
				continue;
			}

			// Anchor this component to previous component. If there's no previous
			// component available, try with the next one.
			if ( empty( $components[ $i - 1 ] ) ) {
				// Check whether this is the only component of the article, if it is,
				// just ignore anchoring.
				if ( empty( $components[ $i + 1 ] ) ) {
					return;
				} else {
					$target_component = $components[ $i + 1 ];
				}
			} else {
				$target_component = $components[ $i - 1 ];
			}

			// Skip advertisement elements, they must span all width. If the previous
			// element is an ad, use next instead. If the element is already
			// anchoring something, also skip.
			$counter = 1;
			$len = count( $components );
			while ( ! $target_component->can_be_anchor_target() && $i + $counter < $len ) {
				$target_component = $components[ $i + $counter ];
				$counter ++;
			}

			$this->anchor_together( $component, $target_component );
		}
	}

	/**
	 * Given two components, anchor the first one to the second.
	 *
	 * @param Component $component
	 * @param Component $target_component
	 *
	 * @access private
	 */
	private function anchor_together( $component, $target_component ) {
		if ( $target_component->is_anchor_target() ) {
			return;
		}

		// Get the component's anchor settings, if set
		$anchor_json = $component->get_json( 'anchor' );

		// If the component doesn't have it's own anchor settings, use the defaults.
		if ( empty( $anchor_json ) ) {
			$anchor_json = array(
				'targetAnchorPosition' => 'center',
				'rangeStart' => 0,
				'rangeLength' => 1,
			);
		}

		// Regardless of what the component class specifies,
		// add the targetComponentIdentifier here.
		// There's no way for the class to know what this is before this point.
		$anchor_json['targetComponentIdentifier'] = $target_component->uid();

		// Add the JSON back to the component
		$component->set_json( 'anchor', $anchor_json );

		// Given $component, find out the opposite position.
		$other_position = null;
		if ( Component::ANCHOR_AUTO == $component->get_anchor_position() ) {
			$other_position = 'left' == $this->get_setting( 'body_orientation' ) ? Component::ANCHOR_LEFT : Component::ANCHOR_RIGHT;
		} else {
			$other_position = Component::ANCHOR_LEFT == $component->get_anchor_position() ? Component::ANCHOR_RIGHT : Component::ANCHOR_LEFT;
		}
		$target_component->set_anchor_position( $other_position );
		// The anchor method adds the required layout, thus making the actual
		// anchoring. This must be called after using the UID, because we need to
		// distinguish target components from anchor ones and components with
		// UIDs are always anchor targets.
		$target_component->anchor();
		$component->anchor();
	}

	/**
	 * Add a thumbnail if needed.
	 *
	 * @param array &$components
	 *
	 * @access private
	 */
	private function add_thumbnail_if_needed( &$components ) {
		// If a thumbnail is already defined, just return.
		if ( $this->content_cover() ) {
			return;
		}

		// Otherwise, iterate over the components and look for the first image.
		foreach ( $components as $i => $component ) {
			if ( is_a( $component, 'Apple_Exporter\Components\Image' ) ) {
				// Get the bundle URL of this class.
				$json_url = $component->get_json( 'URL' );
				if ( empty( $json_url ) ) {
					$json_components = $component->get_json( 'components' );
					if ( ! empty( $json_components[0]['URL'] ) ) {
						$json_url = $json_components[0]['URL'];
					}
				}

				if ( empty( $json_url ) ) {
					return;
				}

				// Isolate the bundle URL basename
				$bundle_basename = str_replace( 'bundle://', '', $json_url );

				// We need to find the original URL from the bundle meta because it's needed
				// in order to override the thumbnail.
				$workspace = new Workspace( $this->content_id() );
				$bundles = $workspace->get_bundles();
				if ( empty( $bundles ) ) {
					// We can't proceed without the original URL and something odd has happened here anyway.
					return;
				}

				$original_url = '';
				foreach ( $bundles as $bundle_url ) {
					if ( $bundle_basename == Apple_News::get_filename( $bundle_url ) ) {
						$original_url = $bundle_url;
						break;
					}
				}

				// If we can't find the original URL, we can't proceed.
				if ( empty( $original_url ) ) {
					return;
				}

				// Use this image as the cover and remove it from the body to avoid duplication.
				$this->set_content_property( 'cover', $original_url );
				unset( $components[ $i ] );
				break;
			}
		}
	}

	/**
	 * Add a pullquote component if needed.
	 *
	 * @param array &$components
	 *
	 * @access private
	 */
	private function add_pullquote_if_needed( &$components ) {
		// Must we add a pullquote?
		$pullquote = $this->content_setting( 'pullquote' );
		$pullquote_position = $this->content_setting( 'pullquote_position' );
		$valid_positions = array( 'top', 'middle', 'bottom' );

		if ( empty( $pullquote ) || ! in_array( $pullquote_position, $valid_positions ) ) {
			return;
		}

		// Find position for pullquote
		$start = 0; // Assume top position, which is the easiest, as it's always 0
		$len = count( $components );

		// If the position is not top, make some math for middle and bottom
		if ( 'middle' == $pullquote_position ) {
			// Place it in the middle
			$start = floor( $len / 2 );
		} else if ( 'bottom' == $pullquote_position ) {
			// Start looking at the third quarter
			$start = floor( ( $len / 4 ) * 3 );
		}

		for ( $position = $start; $position < $len; $position ++ ) {
			if ( $components[ $position ]->can_be_anchor_target() ) {
				break;
			}
		}

		// If none was found, do not add
		if ( ! $components[ $position ]->can_be_anchor_target() ) {
			return;
		}

		// Build a new component and set the anchor position to AUTO
		$component = $this->get_component_from_shortname( 'blockquote', "<blockquote>$pullquote</blockquote>" );
		$component->set_anchor_position( Component::ANCHOR_AUTO );

		// Anchor $component to the target component: $components[ $position ]
		$this->anchor_together( $component, $components[ $position ] );

		// Add component in position
		array_splice( $components, $position, 0, array( $component ) );
	}

	/**
	 * Get a component from the shortname.
	 *
	 * @param string $shortname
	 * @param string $html
	 *
	 * @return Component
	 * @access private
	 */
	private function get_component_from_shortname( $shortname, $html = null ) {
		return Component_Factory::get_component( $shortname, $html );
	}

	/**
	 * Get a component from a node.
	 *
	 * @param DomNode $node
	 *
	 * @return Component
	 * @access private
	 */
	private function get_components_from_node( $node ) {
		return Component_Factory::get_components_from_node( $node );
	}

}
