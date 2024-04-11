<?php
/**
 * Publish to Apple News: \Apple_Exporter\Third_Party\Footnotes class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Third_Party
 */

namespace Apple_Exporter;

/**
 * Handles the core Footnotes block.
 *
 * @since 1.4.0
 */
class Footnotes {

	/**
	 * Instance of the class.
	 *
	 * @var Footnotes
	 */
	private static $instance;

	/**
	 * Get class instance.
	 *
	 * @return Footnotes
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Footnotes();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup of the singleton instance.
	 */
	private function setup() {
		// Only do this on export in Apple News context.
		add_action( 'apple_news_do_fetch_exporter', [ $this, 'footnotes' ] );
	}

	/**
	 * Change core footnote render.
	 */
	public function footnotes() {
		if (
			! is_admin()
		) {
			return;
		}

		/**
		 * Allow default rendering of gallery since we have
		 * builtin handling for the default WP galleries.
		 */
		add_filter(
			'render_block_core/footnotes',
			function ( $block_content ) {
				// Change li tags to p tags.
				$new_content = str_replace( [ '<li', '</li' ], [ '<p', '</p' ] , $block_content );
				$new_content = preg_replace( '/<ol.*?>/', '', $new_content );
				$new_content = preg_replace( '/<\/ol.*?>/', '', $new_content );
				// TODO: Add numbers.
				return $new_content;
			},
			5,
			1
		);
	}
}
