<?php
namespace Apple_Exporter\Builders;

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

		// Extract all video elements that include a poster element.
		if ( preg_match_all( '/<video[^>]+poster="([^"]+)".*?>(.+?)<\/video>/s', $this->content_text(), $matches ) ) {

			// Loop through matched video elements looking for MP4 files.
			for ( $i = 0; $i < count( $matches[2] ); $i ++ ) {

				// Try to match an MP4 source URL.
				if ( preg_match( '/src="([^\?"]+\.mp4)/', $matches[2][ $i ], $src ) ) {
					$meta['thumbnailURL'] = $matches[1][ $i ];
					$meta['videoURL'] = $src[1];

					break;
				}
			}
		}

		return apply_filters( 'apple_news_metadata', $meta, $this->content_id() );
	}
}
