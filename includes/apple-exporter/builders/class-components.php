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
use \Apple_Exporter\Components\Image;
use \Apple_Exporter\Workspace;
use \Apple_News;
use \DOMNode;

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
		$workspace  = new Workspace( $this->content_id() );

		// Loop through body components and process each.
		foreach ( $this->split_into_components() as $component ) {

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

		/**
		 * Process meta components.
		 *
		 * Meta components are handled after the body and then prepended, since they
		 * could change depending on the above body processing, such as if a
		 * thumbnail was used from the body.
		 */
		$components = array_merge( $this->meta_components(), $components );

		// Group body components to improve text flow at all orientations.
		$components = $this->group_body_components( $components );

		return $components;
	}

	/**
	 * Add a pullquote component if needed.
	 *
	 * @param array $components An array of Component objects to analyze.
	 * @access private
	 */
	private function add_pullquote_if_needed( &$components ) {

		// Must we add a pullquote?
		$pullquote          = $this->content_setting( 'pullquote' );
		$pullquote_position = $this->content_setting( 'pullquote_position' );
		$valid_positions    = array( 'top', 'middle', 'bottom' );
		if ( empty( $pullquote ) || ! in_array( $pullquote_position, $valid_positions, true ) ) {
			return;
		}

		// If the position is not top, make some math for middle and bottom.
		$start = 0;
		$total = count( $components );
		if ( 'middle' === $pullquote_position ) {
			$start = floor( $total / 2 );
		} elseif ( 'bottom' === $pullquote_position ) {
			$start = floor( ( $total / 4 ) * 3 );
		}

		// Look for potential anchor targets.
		for ( $position = $start; $position < $total; $position ++ ) {
			if ( $components[ $position ]->can_be_anchor_target() ) {
				break;
			}
		}

		// If none was found, do not add.
		if ( ! $components[ $position ]->can_be_anchor_target() ) {
			return;
		}

		// Build a new component and set the anchor position to AUTO.
		$component = $this->get_component_from_shortname(
			'blockquote',
			'<blockquote class="apple-news-pullquote">' . $pullquote . '</blockquote>'
		);
		$component->set_anchor_position( Component::ANCHOR_AUTO );

		// Anchor the newly created pullquote component to the target component.
		$this->anchor_together( $component, $components[ $position ] );

		// Add component in position.
		array_splice( $components, $position, 0, array( $component ) );
	}

	/**
	 * Add a thumbnail if needed.
	 *
	 * @param array $components An array of Component objects to analyze.
	 * @access private
	 */
	private function add_thumbnail_if_needed( &$components ) {

		// If a thumbnail is already defined, just return.
		if ( $this->content_cover() ) {
			return;
		}

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Otherwise, iterate over the components and look for the first image.
		foreach ( $components as $i => $component ) {

			// Skip anything that isn't an image.
			if ( ! $component instanceof Image ) {
				continue;
			}

			// Get the bundle URL of this class.
			$json_url = $component->get_json( 'URL' );
			if ( empty( $json_url ) ) {
				$json_components = $component->get_json( 'components' );
				if ( ! empty( $json_components[0]['URL'] ) ) {
					$json_url = $json_components[0]['URL'];
				}
			}

			// If we were unsuccessful in getting a URL for the image, bail.
			if ( empty( $json_url ) ) {
				return;
			}

			// Isolate the bundle URL basename.
			$bundle_basename = str_replace( 'bundle://', '', $json_url );

			/**
			 * We need to find the original URL from the bundle meta because it's
			 * needed in order to override the thumbnail.
			 */
			$workspace = new Workspace( $this->content_id() );
			$bundles   = $workspace->get_bundles();

			// If we can't get the bundles, we can't search for the URL, so bail.
			if ( empty( $bundles ) ) {
				return;
			}

			// Try to get the original URL for the image.
			$original_url = '';
			foreach ( $bundles as $bundle_url ) {
				if ( Apple_News::get_filename( $bundle_url ) === $bundle_basename ) {
					$original_url = $bundle_url;
					break;
				}
			}

			// If we can't find the original URL, we can't proceed.
			if ( empty( $original_url ) ) {
				return;
			}

			// Use this image as the cover.
			$this->set_content_property( 'cover', $original_url );

			// If the cover is set to be displayed, remove it from the flow.
			$order = $theme->get_value( 'meta_component_order' );
			if ( is_array( $order ) && in_array( 'cover', $order, true ) ) {
				unset( $components[ $i ] );
				$components = array_values( $components );
			}

			break;
		}
	}

	/**
	 * Anchor components that are marked as can_be_anchor_target.
	 *
	 * @param array $components An array of Component objects to process.
	 * @access private
	 */
	private function anchor_components( &$components ) {

		// If there are not at least two components, ignore anchoring.
		$total = count( $components );
		if ( $total < 2 ) {
			return;
		}

		// Loop through components and search for anchor mappings.
		for ( $i = 0; $i < $total; $i ++ ) {

			// Only operate on components that are anchor targets.
			$component = $components[ $i ];
			if ( $component->is_anchor_target() || Component::ANCHOR_NONE === $component->get_anchor_position() ) {
				continue;
			}

			/**
			 * Anchor this component to the next component. If there is no next
			 * component available, try with the previous one.
			 */
			if ( ! empty( $components[ $i + 1 ] ) ) {
				$target_component = $components[ $i + 1 ];
			} else {
				$target_component = $components[ $i - 1 ];
			}

			// Search for a suitable anchor target.
			$offset = 0;
			while ( ! $target_component->can_be_anchor_target() ) {

				// Determine whether it is possible to seek forward.
				$offset ++;
				if ( empty( $components[ $i + $offset ] ) ) {
					break;
				}

				// Seek to the next target component.
				$target_component = $components[ $i + $offset ];
			}

			// If a suitable anchor target was found, link the two.
			if ( $target_component->can_be_anchor_target() ) {
				$this->anchor_together( $component, $target_component );
			}
		}
	}

	/**
	 * Estimates the number of text lines that would fit next to a square anchor.
	 *
	 * Used when extrapolating to estimate the number of lines that would fit next
	 * to an anchored component at the largest screen size when using an anchor
	 * size ratio calculated using width/height.
	 *
	 * @since 1.2.1
	 *
	 * @access private
	 * @return int Estimated number of text lines that fit next to a square anchor.
	 */
	private function anchor_lines_coefficient() {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		return ceil( 18 / $theme->get_value( 'body_size' ) * 18 );
	}

	/**
	 * Given two components, anchor the first one to the second.
	 *
	 * @param Component $component The anchor.
	 * @param Component $target_component The anchor target.
	 *
	 * @access private
	 */
	private function anchor_together( $component, $target_component ) {

		// Don't anchor something that has already been anchored.
		if ( $target_component->is_anchor_target() ) {
			return;
		}

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Get the component's anchor settings, if set.
		$anchor_json = $component->get_json( 'anchor' );

		// If the component doesn't have its own anchor settings, use the defaults.
		if ( empty( $anchor_json ) ) {
			$anchor_json = array(
				'targetAnchorPosition' => 'center',
				'rangeStart'           => 0,
				'rangeLength'          => 1,
			);
		}

		/**
		 * Regardless of what the component class specifies, add the
		 * targetComponentIdentifier here. There's no way for the class to know what
		 * this is before this point.
		 */
		$anchor_json['targetComponentIdentifier'] = $target_component->uid();

		// Add the JSON back to the component.
		$component->set_json( 'anchor', $anchor_json );

		// Given $component, find out the opposite position.
		$other_position = Component::ANCHOR_LEFT;
		if ( ( Component::ANCHOR_AUTO === $component->get_anchor_position()
				&& 'left' !== $theme->get_value( 'body_orientation' ) )
				|| Component::ANCHOR_LEFT === $component->get_anchor_position()
		) {
			$other_position = Component::ANCHOR_RIGHT;
		}

		/**
		 * The anchor method adds the required layout, thus making the actual
		 * anchoring. This must be called after using the UID, because we need to
		 * distinguish target components from anchor ones and components with
		 * UIDs are always anchor targets.
		 */
		$target_component->set_anchor_position( $other_position );
		$target_component->anchor();
		$component->anchor();
	}

	/**
	 * Estimates the number of chars in a line of text next to an anchored component.
	 *
	 * @since 1.2.1
	 *
	 * @access private
	 * @return int The estimated number of characters per line.
	 */
	private function characters_per_line_anchored() {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Get the body text size in points.
		$body_size = $theme->get_value( 'body_size' );

		// Calculate the base estimated characters per line.
		$cpl = 20 + 230 * pow( M_E, - 0.144 * $body_size );

		// If the alignment is centered, cut CPL in half due to less available space.
		$body_orientation = $theme->get_value( 'body_orientation' );
		if ( 'center' === $body_orientation ) {
			$cpl /= 2;
		}

		// If using a condensed font, boost the CPL.
		$body_font = $theme->get_value( 'body_font' );
		if ( false !== stripos( $body_font, 'condensed' ) ) {
			$cpl *= 1.5;
		}

		// Round up for good measure.
		$cpl = ceil( $cpl );

		/**
		 * Allows for filtering of the estimated characters per line.
		 *
		 * Themes and plugins can modify this value to make it more or less
		 * aggressive, or set this value to 0 to eliminate intelligent grouping of
		 * body blocks.
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
	 * @since 1.2.1
	 * @param Component $component The component to clean up.
	 * @access private
	 */
	private function clean_up_components( &$component ) {

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
	 * @since 1.2.1
	 *
	 * @access private
	 * @return int The estimated number of lines the anchored component occupies.
	 */
	private function get_anchor_buffer( $component ) {

		// If the anchored component is empty, bail.
		if ( empty( $component ) ) {
			return 0;
		}

		// Get the anchor lines coefficient (lines of text for a 1:1 anchor).
		$alc = $this->anchor_lines_coefficient();

		// Determine anchored component size ratio. Defaults to 1 (square).
		$ratio = 1;
		if ( 'container' === $component['role']
			&& ! empty( $component['components'][0]['URL'] )
		) {

			// Calculate base ratio.
			$ratio = $this->get_image_ratio( $component['components'][0]['URL'] );

			// Add some buffer for the caption.
			$ratio /= 1.2;
		} elseif ( 'photo' === $component['role'] && ! empty( $component['URL'] ) ) {
			$ratio = $this->get_image_ratio( $component['URL'] );
		}

		return ceil( $alc / $ratio );
	}

	/**
	 * Given a body node, estimates the number of lines the text occupies.
	 *
	 * @param Component $component The component representing the body.
	 *
	 * @since 1.2.1
	 *
	 * @access private
	 * @return int The estimated number of lines the body text occupies.
	 */
	private function get_anchor_content_lines( $component ) {

		// If the body component is empty, bail.
		if ( empty( $component['text'] ) ) {
			return 0;
		}

		return ceil(
			strlen( $component['text'] ) / $this->characters_per_line_anchored()
		);
	}

	/**
	 * Get a component from the shortname.
	 *
	 * @param string $shortname The shortname to look up.
	 * @param string $html The HTML source to extract from.
	 *
	 * @access private
	 * @return Component The component extracted from the HTML.
	 */
	private function get_component_from_shortname( $shortname, $html = null ) {
		return Component_Factory::get_component( $shortname, $html );
	}

	/**
	 * Get a component from a node.
	 *
	 * @param \DOMElement $node The node to be examined.
	 *
	 * @access private
	 * @return array An array of components matching the node.
	 */
	private function get_components_from_node( $node ) {
		return Component_Factory::get_components_from_node( $node );
	}

	/**
	 * Attempts to get an image ratio from a URL.
	 *
	 * @param string $url The image URL to probe for ratio data.
	 *
	 * @since 1.2.1
	 *
	 * @access private
	 * @return float An image ratio (width/height) for the given image.
	 */
	private function get_image_ratio( $url ) {

		// Strip URL formatting for easier matching.
		$url = urldecode( $url );

		// Attempt to extract the ratio using WordPress.com CDN/Photon format.
		if ( preg_match( '/resize=([0-9]+),([0-9]+)/', $url, $matches ) ) {
			return $matches[1] / $matches[2];
		}

		// Attempt to extract the ratio using standard WordPress size names.
		if ( preg_match( '/-([0-9]+)x([0-9]+)\./', $url, $matches ) ) {
			return $matches[1] / $matches[2];
		}

		// To be safe, fall back to assuming the image is twice as tall as its width.
		return 0.5;
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
	private function group_body_components( $components ) {

		// Initialize.
		$new_components = array();
		$cover_index    = null;
		$anchor_buffer  = 0;
		$prev           = null;
		$current        = null;

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Loop through components, grouping as necessary.
		foreach ( $components as $component ) {

			// Update positioning.
			$prev    = $current;
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
				$temp          = $current;
				$current       = $prev;
				$prev          = $temp;
				$anchor_buffer = $this->get_anchor_buffer( $prev );
			} elseif ( ! empty( $current['identifier'] )
						&& ! empty( $prev['anchor']['targetComponentIdentifier'] )
						&& $prev['anchor']['targetComponentIdentifier']
						=== $current['identifier']
			) {
				$anchor_buffer = $this->get_anchor_buffer( $prev );
			}

			// If the current node is not a body element, force-flatten the buffer.
			if ( 'body' !== $current['role'] ) {
				$anchor_buffer = 0;
			}

			// Keep track of the header position.
			if ( 'header' === $prev['role'] ) {
				$cover_index = count( $new_components );
			}

			// If the previous element is not a body element, or no buffer left, add.
			if ( 'body' !== $prev['role'] || $anchor_buffer <= 0 ) {

				// If the current element is a body element, adjust buffer.
				if ( 'body' === $current['role'] ) {
					$anchor_buffer -= $this->get_anchor_content_lines( $current );
				}

				// Add the node.
				$new_components[] = $prev;
				continue;
			}

			// Merge the body content from the previous node into the current node.
			$anchor_buffer -= $this->get_anchor_content_lines( $current );
			$prev['text']  .= $current['text'];
			$current        = $prev;
		}

		// Add the final element from the loop in its final state.
		$new_components[] = $current;

		// Perform text cleanup on each node.
		array_walk( $new_components, array( $this, 'clean_up_components' ) );

		// If the final node has a role of 'body', add 'body-layout-last' layout.
		$last = count( $new_components ) - 1;
		if ( 'body' === $new_components[ $last ]['role'] ) {
			$new_components[ $last ]['layout'] = 'body-layout-last';
		}

		// Determine if there is a cover in the middle of content.
		if ( null === $cover_index || count( $new_components ) <= $cover_index + 1 ) {
			return $new_components;
		}

		/**
		 * All components after the cover must be grouped to avoid issues with
		 * parallax text scroll.
		 */
		$regrouped_components = array(
			'role'       => 'container',
			'layout'     => array(
				'columnSpan'           => $theme->get_layout_columns(),
				'columnStart'          => 0,
				'ignoreDocumentMargin' => true,
			),
			'style'      => array(
				'backgroundColor' => $theme->get_value( 'body_background_color' ),
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
	private function meta_components() {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Attempt to get the component order.
		$meta_component_order = $theme->get_value( 'meta_component_order' );
		if ( empty( $meta_component_order ) || ! is_array( $meta_component_order ) ) {
			return array();
		}

		// Build array of meta components using specified order.
		$components = array();
		foreach ( $meta_component_order as $i => $component ) {

			// Determine if component is loadable.
			$method = 'content_' . $component;
			if ( ! method_exists( $this, $method ) ) {
				continue;
			}

			// Determine if component has content.
			$content = $this->$method();
			if ( empty( $content ) ) {
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
	private function split_into_components() {

		/**
		 * Loop though the first-level nodes of the body element. Components might
		 * include child-components, like an Cover and Image.
		 */
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
}
