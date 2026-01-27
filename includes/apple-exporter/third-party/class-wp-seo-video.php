<?php
/**
 * Publish to Apple News: \Apple_Exporter\Third_Party\WP_SEO_Video class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Third_Party
 */

namespace Apple_Exporter\Third_Party;

/**
 * Custom video embed handling.
 * This unhooks the WP SEO video embed handler
 * during Apple News exports.
 *
 * @since 2.7.3
 */
class WP_SEO_Video {

	/**
	 * Instance of the class.
	 *
	 * @var WP_SEO_Video
	 */
	private static $instance;

	/**
	 * Get class instance.
	 *
	 * @return WP_SEO_Video
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WP_SEO_Video();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup of the singleton instance.
	 */
	private function setup() {
		// Only do this on export in Apple News context.
		add_action( 'apple_news_do_fetch_exporter', [ $this, 'unhook_replace_youtube_block_html' ] );
	}

	/**
	 * Disable WP SEO Video's deferred embed loading feature when exporting.
	 */
	public function unhook_replace_youtube_block_html() {
		global $wpseo_video_embed;
		if ( isset( $wpseo_video_embed ) && $wpseo_video_embed instanceof \WPSEO_Video_Embed ) {
			remove_filter( 'render_block', [ $wpseo_video_embed, 'replace_youtube_block_html' ], 10, 2 );
		}
	}
}
