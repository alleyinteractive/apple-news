<?php
/**
 * Publish to Apple News: \Apple_Exporter\Builders\Metadata class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Builders
 */

namespace Apple_Exporter\Builders;

require_once plugin_dir_path( __FILE__ ) . '../../../admin/class-admin-apple-news.php';

use Admin_Apple_News;
use Apple_Exporter\Exporter_Content;
use Apple_News;

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
		$meta = [];

		/**
		 * The content's intro is optional. In WordPress, it's a post's
		 * excerpt. It's an introduction to the article.
		 */
		if ( $this->content_intro() ) {
			$meta['excerpt'] = $this->content_intro();
		}

		// If the content has a cover, use it as thumb.
		$content_cover = $this->content_cover();
		if ( ! empty( $content_cover ) ) {
			$meta['thumbnailURL'] = $this->maybe_bundle_source(
				isset( $content_cover['url'] ) ? $content_cover['url'] : $content_cover
			);
		}

		// Add authors.
		$authors = array_values(
			array_filter(
				explode(
					'APPLE_NEWS_DELIMITER',
					Apple_News::get_authors( 'APPLE_NEWS_DELIMITER', 'APPLE_NEWS_DELIMITER' )
				)
			)
		);
		if ( ! empty( $authors ) ) {
			$meta['authors'] = $authors;
		}

		/**
		 * Add date fields.
		 * We need to get the WordPress post for this
		 * since the date functions are inconsistent.
		 */
		$post = get_post( $this->content_id() );
		if ( ! empty( $post ) ) {
			$post_date     = gmdate( 'c', strtotime( get_gmt_from_date( $post->post_date ) ) );
			$post_modified = gmdate( 'c', strtotime( get_gmt_from_date( $post->post_modified ) ) );

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

		// Extract all video elements that include a poster element.
		if ( preg_match_all( '/<video[^>]+poster="([^"]+)".*?>.*?<\/video>/s', $this->content_text(), $matches ) ) {

			// Loop through matched video elements looking for MP4 files.
			$total = count( $matches[0] );
			for ( $i = 0; $i < $total; $i++ ) {

				// Try to match an MP4 source URL.
				if ( preg_match( '/src="([^\?"]+\.(mp4|m3u8)[^"]*)"/', $matches[0][ $i ], $src ) ) {

					// Include the thumbnail and video URL if the video URL is valid and the suppression meta is absent.
					$url            = Exporter_Content::format_src_url( $src[1] );
					$suppress_video = get_post_meta( $this->content_id(), 'apple_news_suppress_video_url', true );
					if ( ! empty( $url ) && ! $suppress_video ) {
						$meta['videoURL']     = esc_url_raw( $url );
						$meta['thumbnailURL'] = $this->maybe_bundle_source(
							$matches[1][ $i ]
						);

						break;
					}
				}
			}
		}

		/**
		 * Modifies the metadata for a post.
		 *
		 * @param array $meta    Apple News metadata for a post.
		 * @param int   $post_id The ID of the post.
		 */
		return apply_filters( 'apple_news_metadata', $meta, $this->content_id() );
	}
}
