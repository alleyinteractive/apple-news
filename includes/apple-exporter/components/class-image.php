<?php
namespace Apple_Exporter\Components;

use \Apple_Exporter\Exporter as Exporter;

/**
 * Represents a simple image.
 *
 * @since 0.2.0
 */
class Image extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param DomNode $node
	 * @return mixed
	 * @access public
	 */
	public static function node_matches( $node ) {
		// Is this an image node?
		if (
		 	( 'img' === $node->nodeName || 'figure' === $node->nodeName )
			&& self::remote_file_exists( $node )
		) {
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
			'json-without-caption',
			__( 'JSON without caption', 'apple-news' ),
			array(
				'role' => 'photo',
				'URL'  => '#url#',
				'layout' => '#layout#',
			)
		);

		$this->register_spec(
			'json-with-caption',
			__( 'JSON with caption', 'apple-news' ),
			array(
				'role' => 'container',
				'components' => array(
					array(
						'role' => 'photo',
						'URL'  => '#url#',
						'layout' => '#layout#',
						'caption' => '#caption#',
					),
					array(
						'role' => 'caption',
						'text' => '#caption#',
						'textStyle' => array(
							'textAlignment' => '#text_alignment#',
							'fontName' => '#caption_font#',
							'fontSize' => '#caption_size#',
							'tracking' => '#caption_tracking#',
							'lineHeight' => '#caption_line_height#',
							'textColor' => '#caption_color#',
						),
						'layout' => array(
							'margin' => array(
								'top' => 20,
							),
							'ignoreDocumentMargin' => '#full_bleed_images#',
						),
					),
				),
				'layout' => array(
					'ignoreDocumentMargin' => '#full_bleed_images#',
				),
			)
		);

		$this->register_spec(
			'anchored-image',
			__( 'Anchored Layout', 'apple-news' ),
			array(
				'margin' => array(
					'bottom' => 25,
					'top' => 25,
				),
			)
		);

		$this->register_spec(
			'non-anchored-image',
			__( 'Non Anchored Layout', 'apple-news' ),
			array(
				'margin' => array(
					'bottom' => 25,
					'top' => 25,
				),
				'columnSpan' => '#layout_columns_minus_4#',
				'columnStart' => 2,
			)
		);

		$this->register_spec(
			'non-anchored-full-bleed-image',
			__( 'Non Anchored with Full Bleed Images Layout', 'apple-news' ),
			array(
				'margin' => array(
					'bottom' => 25,
					'top' => 25,
				),
				'ignoreDocumentMargin' => true,
			)
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $text The text to convert into a component.
	 *
	 * @access protected
	 */
	protected function build( $text ) {

		// Extract the URL from the text.
		$url = self::url_from_src( $text );

		/**
		 * Allows for an image src value to be filtered before being applied.
		 *
		 * @param string $url The URL to be filtered.
		 * @param string $text The raw text that was parsed for the URL.
		 */
		$url = esc_url_raw( apply_filters( 'apple_news_build_image_src', $url, $text ) );

		// If we don't have a valid URL at this point, bail.
		if ( empty( $url ) ) {
			return;
		}

		// Add the URL as a parameter for replacement.
		$filename = preg_replace( '/\\?.*/', '', \Apple_News::get_filename( $url ) );
		$values = array(
			'#url#'  => $this->maybe_bundle_source( $url, $filename ),
		);

		// Determine image alignment.
		if ( false !== stripos( $text, 'align="left"' )
		     || preg_match( '/class="[^"]*alignleft[^"]*"/i', $text )
		) {
			$this->set_anchor_position( Component::ANCHOR_LEFT );
		} elseif ( false !== stripos( $text, 'align="right"' )
		            || preg_match( '/class="[^"]*alignright[^"]*"/i', $text )
		) {
			$this->set_anchor_position( Component::ANCHOR_RIGHT );
		} else {
			$this->set_anchor_position( Component::ANCHOR_NONE );
		}

		// Check for caption
		if ( preg_match( '#<figcaption.*?>(.*?)</figcaption>#m', $text, $matches ) ) {
			$caption = trim( $matches[1] );
			$values['#caption#'] = $caption;
			$values = $this->group_component( $caption, $values );
			$spec_name = 'json-with-caption';
		} else {
			$spec_name = 'json-without-caption';
		}

		// Full width images have top margin
		// We can't use the standard layout registration due to grouping components
		// with images so instead, send it through as a value.
		if ( Component::ANCHOR_NONE === $this->get_anchor_position() ) {
			$values = $this->register_non_anchor_layout( $values );
		} else {
			$values = $this->register_anchor_layout( $values );
		}

		// Register the JSON
		$this->register_json( $spec_name, $values );
	}

	/**
	 * Register the anchor layout.
	 *
	 * @param array $values
	 * @return array
	 * @access private
	 */
	private function register_anchor_layout( $values ) {
		$this->register_layout(
			'anchored-image',
			'anchored-image'
		);

		$values['#layout#'] = 'anchored-image';
		return $values;
	}

	/**
	 * Register the non-anchor layout.
	 *
	 * @param array $values
	 * @return array
	 * @access private
	 */
	private function register_non_anchor_layout( $values ) {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Set values to merge into the spec
		$layout_values = array();

		if ( 'yes' === $this->get_setting( 'full_bleed_images' ) ) {
			$spec_name = 'non-anchored-full-bleed-image';
		} else {
			$layout_values['#layout_columns_minus_4#'] = $theme->get_layout_columns() - 4;
			$spec_name = 'non-anchored-image';
		}

		// Register the layout.
		$this->register_full_width_layout(
			'full-width-image',
			$spec_name,
			$layout_values
		);

		$values['#layout#'] = 'full-width-image';
		return $values;
	}

	/**
	 * Find the caption alignment to use.
	 *
	 * @return string
	 * @access private
	 */
	private function find_caption_alignment() {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		$text_alignment = null;
		if ( Component::ANCHOR_NONE === $this->get_anchor_position() ) {
			return 'center';
		}

		switch ( $this->get_anchor_position() ) {
			case Component::ANCHOR_LEFT:
				return 'left';
			case Component::ANCHOR_AUTO:
				if ( 'left' === $theme->get_value( 'body_orientation' ) ) {
					return 'right';
				}
		}

		return 'left';
	}

	/**
	 * If the image has a caption, we have to also show a caption component.
	 * Let's instead, return the values as a Container instead of an Image.
	 *
	 * @param string $caption
	 * @param array $values
	 * @return array
	 * @access private
	 */
	private function group_component( $caption, $values ) {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Roll up the image component into a container.
		$values = array_merge(
			$values,
			array(
				'#caption#' => $caption,
				'#text_alignment#' => $this->find_caption_alignment(),
				'#caption_font#' => $theme->get_value( 'caption_font' ),
				'#caption_size#' => intval( $theme->get_value( 'caption_size' ) ),
				'#caption_tracking#' => intval( $theme->get_value( 'caption_tracking' ) ) / 100,
				'#caption_line_height#' => intval( $theme->get_value( 'caption_line_height' ) ),
				'#caption_color#' => $theme->get_value( 'caption_color' ),
				'#full_bleed_images#' => ( 'yes' === $this->get_setting( 'full_bleed_images' ) ),
			)
		);

		return $values;
	}
}
