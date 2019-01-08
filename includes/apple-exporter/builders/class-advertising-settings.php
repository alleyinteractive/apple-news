<?php
/**
 * Publish to Apple News: \Apple_Exporter\Builders\Advertising_Settings class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Builders
 */

namespace Apple_Exporter\Builders;

/**
 * Builds advertising settings for the JSON export.
 *
 * @since 0.4.0
 */
class Advertising_Settings extends Builder {

	/**
	 * Build the component.
	 *
	 * @access protected
	 */
	protected function build() {
		$advertising_settings = array();

		// Get advertising settings from the theme.
		$theme                = \Apple_Exporter\Theme::get_used();
		$enable_advertisement = $theme->get_value( 'enable_advertisement' );
		$ad_frequency         = intval( $theme->get_value( 'ad_frequency' ) );

		if ( 'yes' === $enable_advertisement && $ad_frequency > 0 ) {
			$advertising_settings['frequency'] = $ad_frequency;
			$ad_margin                         = intval( $theme->get_value( 'ad_margin' ) );
			if ( ! empty( $ad_margin ) ) {
				$advertising_settings['layout'] = array(
					'margin' => array(
						'top'    => $ad_margin,
						'bottom' => $ad_margin,
					),
				);
			}
		}

		return apply_filters( 'apple_news_advertising_settings', $advertising_settings, $this->content_id() );
	}
}
