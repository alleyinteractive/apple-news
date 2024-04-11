<?php
/**
 * Publish to Apple News: \Apple_Exporter\Footnotes class
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
		 * Change the ordered list to a series of paragraphs so that each paragraph can have a unique identifier,
		 * which allows us to link to each footnote from the main content.
		 */
		add_filter(
			'render_block_core/footnotes',
			function ( $block_content ) {
				preg_match_all( '/<li.*?>.*?<\/li>/', $block_content, $matches );
				$items = $matches[0];

				// convert each list item to a paragraph with a number added.
				foreach ( $items as $key => $item ) {
					$count = $key + 1;
					$items[$key] = preg_replace(
						'/<li(.*?)>(.*?)<\/li>/',
						"<p$1>${count}. $2</p>",
						$item
					);
				}
				return implode( PHP_EOL, $items );
			},
			5,
			1
		);
	}
}
