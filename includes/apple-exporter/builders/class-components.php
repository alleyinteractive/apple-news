<?php
namespace Apple_Exporter\Builders;

use \Apple_Exporter\Component_Factory as Component_Factory;
use \Apple_Exporter\Components\Component as Component;
use \Apple_Exporter\Workspace as Workspace;
use \Apple_News as Apple_News;

/**
 * @since 0.4.0
 */
class Components extends Builder {

	/**
	 * Builds an array with all the components of this WordPress content.
	 *
	 * @return array
	 * @access protected
	 */
	protected function build() {
		$components = array();
		$workspace = new Workspace( $this->content_id() );

		// Handle body components first
		foreach ( $this->split_into_components() as $component ) {
			// Check if the component is valid
			$component_array = $component->to_array();
			if ( is_wp_error( $component_array ) ) {
				$workspace->log_error( 'component_errors', $component_array->get_error_message() );
			} else {
				$components[] = $component_array;
			}
		}

		// Meta components are handled after and then prepended since
		// they could change depending on the above body processing,
		// such as if a thumbnail was used from the body.
		$components = array_merge( $this->meta_components(), $components );

		// Group body components to improve text flow at all orientations
		return $this->group_body_components( $components );
	}

	/**
	 * Estimates the number of characters in a line of text next to an image.
	 *
	 * @access private
	 * @return int The estimated number of characters per line.
	 */
	private function _characters_per_image_line() {

		// Get the body text size in points.
		$pt = $this->get_setting( 'body_size' );

		// Calculate the base estimated characters per line.
		$cpl = 20 + 230 * pow( M_E, -0.144 * $pt );

		// If the alignment is centered, cut CPL in half due to less available space.
		if ( 'center' === $this->get_setting( 'body_orientation' ) ) {
			$cpl /= 2;
		}

		// If using a condensed font, boost the CPL.
		if ( false !== stripos( $this->get_setting( 'body_font' ), 'condensed' ) ) {
			$cpl *= 1.5;
		}

		// Round up for good measure.
		$cpl = ceil( $cpl );

		return $cpl;
	}

	/**
	 * Given an array of components in array format, group all the elements of
	 * role 'body'. Ignore body elements that have an ID, as they are used for
	 * anchoring.
	 *
	 * Grouping body like this allows the Apple Format interpreter to render
	 * proper paragraph spacing.
	 *
	 * @since 0.6.0
	 * @param array $components
	 * @return array
	 * @access private
	 */
	private function group_body_components( $components ) {
		$new_components = array();
		$body_collector = null;

		$i   = 0;
		$len = count( $components );
		while( $i < $len ) {
			$component = $components[ $i ];

			// If the component is not body, no need to group, just add.
			if ( 'body' != $component['role'] ) {
				if ( ! is_null( $body_collector ) ) {
					$body_collector['text'] = $body_collector['text'];
					$new_components[] = $body_collector;
					$body_collector   = null;
				}

				$new_components[] = $component;
				$i++;
				continue;
			}

			// If the component is a body, test if it is an anchor target. For
			// grouping an anchor target body several things need to happen:
			if ( isset( $component['identifier'] )               	// The FIRST component must be an anchor target
				&& isset( $components[ $i + 1 ]['anchor'] )      	// The SECOND must be the component to be anchored
				&& isset( $components[ $i + 2 ]['role'] )
				&& 'body' == $components[ $i + 2 ]['role']        	// The THIRD must be a body component
				&& !isset( $components[ $i + 2 ]['identifier'] ) ) 	// which must not be an anchor target for another component
			{
				// Collect
				if ( ! is_null( $body_collector ) ) {
					$body_collector['text'] = $body_collector['text'];
					$new_components[] = $body_collector;
					$body_collector   = null;
				}

				$new_components[] = $components[ $i + 1 ];
				$body_collector   = $component;
				$body_collector['text'] .= $components[ $i + 2 ]['text'];

				$i += 3;
				continue;
			}

			// Another case for anchor target grouping is when the component was anchored
			// to the next element rather than the previous one, in that case:
			if ( isset( $component['identifier'] )               	// The FIRST component must be an anchor target
				&& isset( $components[ $i + 1 ]['role'] )
				&& 'body' == $components[ $i + 1 ]['role']        	// The SECOND must be a body component
				&& !isset( $components[ $i + 1 ]['identifier'] ) )	// which must not be an anchor target for another component
			{
				// Collect
				if ( ! is_null( $body_collector ) ) {
					$body_collector['text'] = $body_collector['text'];
					$new_components[] = $body_collector;
					$body_collector   = null;
				}

				$body_collector = $component;
				$body_collector['text'] .= $components[ $i + 1 ]['text'];

				$i += 2;
				continue;
			}

			// If the component was an anchor target but failed to match the
			// requirements for grouping, just add it, don't group it.
			if ( isset( $component['identifier'] ) ) {
				if ( ! is_null( $body_collector ) ) {
					$body_collector['text'] = $body_collector['text'];
					$new_components[] = $body_collector;
					$body_collector   = null;
				}

				$new_components[] = $component;
			} else {
				// The component is not an anchor target, just collect.
				if ( is_null( $body_collector ) ) {
					$body_collector = $component;
				} else {
					$body_collector['text'] .= $component['text'];
				}
			}

			$i++;
		}

		// Make a final check for the body collector, as it might not be empty
		if ( ! is_null( $body_collector ) ) {
			$body_collector['text'] = $body_collector['text'];
			$new_components[] = $body_collector;
		}

		// Trim all body components before returning.
		// Also set the layout for the final body component.
		$cover_index = null;
		foreach ( $new_components as $i => $component ) {
			if ( 'body' == $component['role'] ) {
				$new_components[ $i ]['text'] = trim( $new_components[ $i ]['text'] );

				if ( ( $i + 1 ) === count( $new_components ) ) {
					$new_components[ $i ]['layout'] = 'body-layout-last';
				}
			}

			// Find the location of the cover for later
			if ( 'header' == $component['role'] ) {
				$cover_index = $i;
			}
		}

		// Finally, all components after the cover must be grouped
		// to avoid issues with parallax text scroll.
		//
		// If no cover was found, this is unnecessary.
		if ( null !== $cover_index ) {
			$regrouped_components = array_slice( $new_components, 0, $cover_index + 1 );

			if ( count( $new_components ) > $cover_index + 1 ) {
				$regrouped_components[] = array(
					'role' => 'container',
					'layout' => array(
						'columnStart' => 0,
						'columnSpan' => $this->get_setting( 'layout_columns' ),
						'ignoreDocumentMargin' => true,
					),
					'style' => array(
						'backgroundColor' => $this->get_setting( 'body_background_color' ),
					),
					'components' => array_slice( $new_components, $cover_index + 1 ),
				);
			}
		} else {
			$regrouped_components = $new_components;
		}

		return $regrouped_components;
	}

	/**
	 * Meta components are those which were not created from the HTML content.
	 * These include the title, the cover (i.e. post thumbnail) and the byline.
	 *
	 * @return array
	 * @access private
	 */
	private function meta_components() {
		$components = array();

		// Get the component order
		$meta_component_order = $this->get_setting( 'meta_component_order' );
		if ( ! empty( $meta_component_order ) && is_array( $meta_component_order ) ) {
			foreach ( $meta_component_order as $i => $component ) {
				$method = 'content_' . $component;
				if ( method_exists( $this, $method ) && $this->$method() ) {
					$component = $this->get_component_from_shortname( $component, $this->$method() )->to_array();

					// Cover needs different margins when it's not first
					if ( 'header' === $component['role'] && 0 !== $i ) {
						$component['layout'] = 'headerBelowTextPhotoLayout';
					}

					$components[] = $component;
				}
			}
		}

		return $components;
	}

	/**
	 * Split components from the source WordPress content.
	 *
	 * @return array
	 * @access private
	 */
	private function split_into_components() {
		// Loop though the first-level nodes of the body element. Components
		// might include child-components, like an Cover and Image.
		$result = array();
		$errors = array();

		foreach ( $this->content_nodes() as $node ) {
			$result = array_merge( $result, $this->get_components_from_node( $node ) );
		}

		// Process the result some more. It gets passed by reference for efficiency.
		// It's not like it's a big memory save but still relevant.
		// FIXME: Maybe this could have been done in a better way?
		$this->add_thumbnail_if_needed( $result );
		$this->anchor_components( $result );
		$this->add_pullquote_if_needed( $result );

		return $result;
	}

	/**
	 * Anchor components that are marked as can_be_anchor_target.
	 *
	 * @param array &$components
	 * @access private
	 */
	private function anchor_components( &$components ) {
		$len = count( $components );

		for ( $i = 0; $i < $len; $i++ ) {

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
			$len     = count( $components );
			while ( ! $target_component->can_be_anchor_target() && $i + $counter < $len ) {
				$target_component = $components[ $i + $counter ];
				$counter++;
			}

			$this->anchor_together( $component, $target_component );
		}
	}

	/**
	 * Given two components, anchor the first one to the second.
	 *
	 * @param Component $component
	 * @param Component $target_component
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
				'targetAnchorPosition'      => 'center',
				'rangeStart'                => 0,
				'rangeLength'               => 1,
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
	 * @access private
	 */
	private function add_pullquote_if_needed( &$components ) {
		// Must we add a pullquote?
		$pullquote          = $this->content_setting( 'pullquote' );
		$pullquote_position = $this->content_setting( 'pullquote_position' );
		$valid_positions    = array( 'top', 'middle', 'bottom' );

		if ( empty( $pullquote ) || !in_array( $pullquote_position, $valid_positions ) ) {
			return;
		}

		// Find position for pullquote
		$start = 0; // Assume top position, which is the easiest, as it's always 0
		$len   = count( $components );

		// If the position is not top, make some math for middle and bottom
		if ( 'middle' == $pullquote_position ) {
			// Place it in the middle
			$start = floor( $len / 2 );
		} else if ( 'bottom' == $pullquote_position ) {
			// Start looking at the third quarter
			$start = floor( ( $len / 4 ) * 3 );
		}

		for ( $position = $start; $position < $len; $position++ ) {
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
	 * @return Component
	 * @access private
	 */
	private function get_components_from_node( $node ) {
		return Component_Factory::get_components_from_node( $node );
	}

}
