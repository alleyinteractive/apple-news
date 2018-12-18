<?php
/**
 * Publish to Apple News: \Apple_Exporter\Builders\Metadata class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Builders
 */

namespace Apple_Exporter\Builders;

require_once plugin_dir_path( __FILE__ ) . '../../../admin/class-admin-apple-news.php';

use \Admin_Apple_News;
use \Apple_Exporter\Exporter_Content;

/**
 * A class to handle building metadata.
 *
 * @since 0.4.0
 */
class Metadata extends Builder {

	/**
	 * Build the component.
	 *
	 * @access protected
	 */
	protected function build() {
		$meta = array();

		/**
		 * The content's intro is optional. In WordPress, it's a post's
		 * excerpt. It's an introduction to the article.
		 */
		if ( $this->content_intro() ) {
			$meta['excerpt'] = $this->content_intro();
		}

		// If the content has a cover, use it as thumb.
		if ( $this->content_cover() ) {
			$meta['thumbnailURL'] = $this->maybe_bundle_source(
				$this->content_cover()
			);
		}

		/**
		 * Add date fields.
		 * We need to get the WordPress post for this
		 * since the date functions are inconsistent.
		 */
		$post = get_post( $this->content_id() );
		if ( ! empty( $post ) ) {
			$post_date     = date( 'c', strtotime( get_gmt_from_date( $post->post_date ) ) );
			$post_modified = date( 'c', strtotime( get_gmt_from_date( $post->post_modified ) ) );

			$meta['dateCreated']   = $post_date;
			$meta['dateModified']  = $post_modified;
			$meta['datePublished'] = $post_date;
		}

		// Add canonical URL.
		$meta['canonicalURL'] = get_permalink( $this->content_id() );

		// Add plugin information to the generator metadata.
		$plugin_data = apple_news_get_plugin_data();

		// Add generator information.
		$meta['generatorIdentifier'] = sanitize_title_with_dashes( $plugin_data['Name'] );
		$meta['generatorName']       = $plugin_data['Name'];
		$meta['generatorVersion']    = $plugin_data['Version'];

		// Add cover art.
		$this->add_cover_art( $meta );

		// Extract all video elements that include a poster element.
		if ( preg_match_all( '/<video[^>]+poster="([^"]+)".*?>(.+?)<\/video>/s', $this->content_text(), $matches ) ) {

			// Loop through matched video elements looking for MP4 files.
			$total = count( $matches[2] );
			for ( $i = 0; $i < $total; $i ++ ) {

				// Try to match an MP4 source URL.
				if ( preg_match( '/src="([^\?"]+\.mp4[^"]*)"/', $matches[2][ $i ], $src ) ) {

					// Include the thumbnail and video URL if the video URL is valid.
					$url = Exporter_Content::format_src_url( $src[1] );
					if ( ! empty( $url ) ) {
						$meta['thumbnailURL'] = $this->maybe_bundle_source(
							$matches[1][ $i ]
						);
						$meta['videoURL']     = esc_url_raw( $url );

						break;
					}
				}
			}
		}

		return apply_filters( 'apple_news_metadata', $meta, $this->content_id() );
	}

	/**
	 * Adds metadata for cover art.
	 *
	 * @param array $meta The metadata array to augment.
	 *
	 * @access private
	 */
	private function add_cover_art( &$meta ) {

		// Try to get cover art meta.
		$ca_meta = get_post_meta( $this->content_id(), 'apple_news_coverart', true );

		// Ensure an orientation was specified.
		if ( empty( $ca_meta['orientation'] ) ) {
			return;
		}

		// Ensure the largest size for this orientation has been set.
		if ( empty( $ca_meta[ 'apple_news_ca_' . $ca_meta['orientation'] . '_12_9' ] ) ) {
			return;
		}

		// Loop through the defined image sizes and check for each.
		$image_sizes = Admin_Apple_News::get_image_sizes();
		foreach ( $image_sizes as $key => $data ) {

			// Skip any image sizes that aren't related to cover art.
			if ( 'coverArt' !== $data['type'] ) {
				continue;
			}

			// Skip any image sizes that don't match the specified orientation.
			if ( $ca_meta['orientation'] !== $data['orientation'] ) {
				continue;
			}

			// Skip any image sizes that aren't saved.
			if ( empty( $ca_meta[ $key ] ) ) {
				continue;
			}

			// Try to get information about the specified image.
			$image_id = $ca_meta[ $key ];
			$image    = wp_get_attachment_metadata( $image_id );
			$alt      = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			if ( empty( $image['sizes'] ) ) {
				continue;
			}

			// Skip images that don't meet the minimum size requirements.
			if ( empty( $image['sizes'][ $key ]['width'] )
				|| empty( $image['sizes'][ $key ]['height'] )
				|| $data['width'] !== $image['sizes'][ $key ]['width']
				|| $data['height'] !== $image['sizes'][ $key ]['height']
			) {
				/**
				 * If the full size of the image is *exactly* the requested
				 * dimensions, a crop won't be generated, but we don't want
				 * to fail it either. So we need to check the height and
				 * width of the original also.
				 */
				if ( $data['width'] !== $image['width']
					|| $data['height'] !== $image['height']
				) {
					continue;
				}
			}

			// Bundle source, if necessary.
			$url = wp_get_attachment_image_url( $image_id, $key );
			$url = $this->maybe_bundle_source( $url );

			// Add this crop to the coverArt array.
			$meta['coverArt'][] = array(
				'accessibilityCaption' => $alt,
				'type'                 => 'image',
				'URL'                  => $url,
			);
		}
	}
}
