<?php
/**
 * Publish to Apple News partials: Options Section Hidden page template
 *
 * @package Apple_News
 */

foreach ( $section->groups() as $group ) {
	do_action( 'apple_news_before_setting_group', $group, true );
	foreach ( $group['settings'] as $setting_name => $setting_meta ) {
		do_action( 'apple_news_before_setting', $setting_name, $setting_meta );
		echo wp_kses(
			$section->render_field(
				array(
					$setting_name,
					$setting_meta['default'],
					$setting_meta['callback'],
				)
			),
			Admin_Apple_Settings_Section::$allowed_html
		);
		do_action( 'apple_news_after_setting', $setting_name, $setting_meta );
	}
	do_action( 'apple_news_after_setting_group', $group, true );
}
