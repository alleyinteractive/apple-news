<?php
/**
 * Publish to Apple News: \Apple_Exporter\Builders\Layout class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Builders
 */

namespace Apple_Exporter\Builders;

/**
 * Manage the article layout.
 *
 * @since 0.4.0
 */
class Layout extends Builder {

	/**
	 * Build the layout
	 *
	 * @return array
	 * @access protected
	 */
	protected function build() {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		/**
		 * Modifies the layout settings from the Apple News formatting options.
		 *
		 * @param array $layout  Layout settings.
		 * @param int   $post_id The ID of the post.
		 */
		return apply_filters(
			'apple_news_layout',
			[
				'columns' => intval( $theme->get_layout_columns() ),
				'width'   => intval( $theme->get_value( 'layout_width' ) ),
				'margin'  => intval( $theme->get_value( 'layout_margin' ) ),  // Defaults to 100.
				'gutter'  => intval( $theme->get_value( 'layout_gutter' ) ),  // Defaults to 20.
			],
			$this->content_id()
		);
	}
}
