<?php
namespace Apple_Exporter\Builders;

require_once plugin_dir_path( __FILE__ ) . '../../../admin/class-admin-apple-news.php';

use \Admin_Apple_News;

/**
 * @since 0.4.0
 */
class Metadata extends Builder {

	/**
	 * Build the component.
	 *
	 * @param string $text
	 * @access protected
	 */
	protected function build() {
		$meta = array();

		// The content's intro is optional. In WordPress, it's a post's
		// excerpt. It's an introduction to the article.
		if ( $this->content_intro() ) {
			$meta['excerpt'] = $this->content_intro();
		}

		// If the content has a cover, use it as thumb.
		if ( $this->content_cover() ) {
			if ( 'yes' === $this->get_setting( 'use_remote_images' ) ) {
				$thumb_url = $this->content_cover();
			} else {
				$filename = \Apple_News::get_filename( $this->content_cover() );
				$thumb_url = 'bundle://' . $filename;
			}

			$meta['thumbnailURL'] = $thumb_url;
		}

		// Add date fields.
		// We need to get the WordPress post for this
		// since the date functions are inconsistent.
		$post = get_post( $this->content_id() );
		if ( ! empty( $post ) ) {
			$post_date = date( 'c', strtotime( get_gmt_from_date( $post->post_date ) ) );
			$post_modified = date( 'c', strtotime( get_gmt_from_date( $post->post_modified ) ) );

			$meta['dateCreated'] = $post_date;
			$meta['dateModified'] = $post_modified;
			$meta['datePublished'] = $post_date;
		}

		// Add canonical URL.
		$meta['canonicalURL'] = get_permalink( $this->content_id() );

		// Add plugin information to the generator metadata
		$plugin_data = apple_news_get_plugin_data();

		// Add generator information
		$meta['generatorIdentifier'] = sanitize_title_with_dashes( $plugin_data['Name'] );
		$meta['generatorName'] = $plugin_data['Name'];
		$meta['generatorVersion'] = $plugin_data['Version'];

		// Add cover art.
		$this->_add_cover_art( $meta, 'apple_news_coverart_landscape' );
		$this->_add_cover_art( $meta, 'apple_news_coverart_portrait' );
		$this->_add_cover_art( $meta, 'apple_news_coverart_square' );

		return apply_filters( 'apple_news_metadata', $meta, $this->content_id() );
	}

	/**
	 * Adds metadata for cover art.
	 *
	 * @param array &$meta The metadata array to augment.
	 * @param string $size The size key to look up in postmeta.
	 *
	 * @access private
	 */
	private function _add_cover_art( &$meta, $size ) {

		// Try to get cover art image ID.
		$id = get_post_meta( $this->content_id(), $size, true );
		if ( empty( $id ) ) {
			return;
		}

		// Try to get orientation from size.
		$segments = explode( '_', $size );
		$orientation = end( $segments );
		if ( empty( $orientation ) ) {
			return;
		}

		// Get information about the image.
		$image = wp_get_attachment_metadata( $id );
		$alt = get_post_meta( $id, '_wp_attachment_image_alt', true);
		if ( empty( $image['sizes'] ) ) {
			return;
		}

		// Loop over crops and add each.
		foreach ( Admin_Apple_News::$image_sizes as $name => $dimensions ) {

			// Determine if the named image size matches this orientation.
			if ( false === strpos( $name, $orientation ) ) {
				continue;
			}

			// Ensure the specified image dimensions match those of the crop.
			if ( empty( $image['sizes'][ $name ]['width'] )
				|| empty( $image['sizes'][ $name ]['height'] )
				|| $dimensions['width'] !== $image['sizes'][ $name ]['width']
				|| $dimensions['height'] !== $image['sizes'][ $name ]['height']
			) {
				continue;
			}

			// TODO: Handle bundling source.

			// Add this crop to the coverArt array.
			$meta['coverArt'][] = array(
				'accessibilityCaption' => $alt,
				'type' => 'image',
				'URL' => wp_get_attachment_image_url( $id, $name ),
			);
		}
	}
}
