<?php
/**
 * Publish to Apple News: \Apple_Exporter\Third_Party\Jetpack_Tiled_Gallery class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Third_Party
 */

namespace Apple_Exporter\Third_Party;

/**
 * Custom Jetpack tiled gallery handling.
 * This will remove permissible gallery
 * types to force standard WP Gallery output.
 *
 * @since 1.4.0
 */
class Jetpack_Tiled_Gallery {

	/**
	 * Instance of the class.
	 *
	 * @var Jetpack_Tiled_Gallery
	 */
	private static $instance;

	/**
	 * Get class instance.
	 *
	 * @return Jetpack_Tiled_Gallery
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Jetpack_Tiled_Gallery();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup of the singleton instance.
	 */
	private function setup() {
		// Only do this on export in Apple News context.
		add_action( 'apple_news_do_fetch_exporter', array( $this, 'tiled_gallery' ) );
	}

	/**
	 * Disable Jetpack's tiled gallery customizations.
	 */
	public function tiled_gallery() {
		// Condition for admin and Jetpack availability.
		if (
			! is_admin()
			|| ! method_exists( '\Jetpack_Tiled_Gallery', 'gallery_shortcode' )
		) {
			return;
		}

		/**
		 * Allow default rendering of gallery since we have
		 * builtin handling for the default WP galleries.
		 */
		add_filter(
			'jetpack_tiled_gallery_types',
			function() {
				return array();
			}
		);
	}
}
