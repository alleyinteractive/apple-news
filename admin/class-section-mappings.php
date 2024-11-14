<?php
/**
 * Publish to Apple News Admin: Section mappings class
 *
 * Contains a class which is used to manage section mappings.
 *
 * @package Apple_News
 * @since 2.7.0
 */

namespace Apple_News\Admin;

use Apple_News;

class Section_Mappings {
	/**
	 * The page name for the section mappings screen.
	 */
	const PAGE_NAME = 'apple-news-section-mappings';

	/**
	 * Initialize functionality of this class by registering hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', [ __CLASS__, 'action__admin_menu' ], 100 );
	}

	/**
	 * A callback function for the admin_menu action hook.
	 */
	public static function action__admin_menu(): void {
		add_submenu_page(
			'apple_news_index',
			__( 'Apple News Section Mappings', 'apple-news' ),
			__( 'Section Mappings', 'apple-news' ),
			/** This filter is documented in admin/class-admin-apple-settings.php */
			apply_filters( 'apple_news_settings_capability', 'manage_options' ),
			self::PAGE_NAME,
			[ __CLASS__, 'render_submenu_page' ]
		);
	}

	/**
	 * A callback to load section mapping scripts and styles and render target div for the React submenu page.
	 */
	public static function render_submenu_page(): void {
		// Enqueue page specific scripts.
		wp_enqueue_script(
			'apple-news-admin-section-mappings',
			plugins_url( 'build/sectionMappings.js', __DIR__ ),
			[ 'wp-block-editor', 'wp-api-fetch', 'wp-api', 'wp-i18n', 'wp-components', 'wp-element', 'wp-tinymce' ],
			Apple_News::$version,
			true
		);
		wp_enqueue_style( 'wp-edit-blocks' );
		wp_localize_script(
			'apple-news-admin-section-mappings',
			'AppleNewsAutomationConfig',
			[
				'fields'     => Automation::get_fields(),
				'sections'   => \Admin_Apple_Sections::get_sections(),
				'taxonomies' => get_taxonomies( [ 'public' => 'true' ] ),
				'themes'     => \Apple_Exporter\Theme::get_registry(),
			]
		);
		add_filter( 'should_load_block_editor_scripts_and_styles', '__return_true' );

		// Render target div for React app.
		echo '<div id="apple-news-options__section-mappings"></div>';
	}
}
