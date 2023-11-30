<?php
/**
 * Publish to Apple News Admin: Automation class
 *
 * Contains a class which is used to manage Automation settings.
 *
 * @package Apple_News
 * @since 2.4.0
 */

namespace Apple_News\Admin;

use Apple_News;

/**
 * This class is in charge of handling the management of Apple News automation.
 *
 * @since 2.4.0
 */
class Automation {

	/**
	 * The option name for automation.
	 */
	const OPTION_KEY = 'apple_news_automation';

	/**
	 * The page name for the automation settings screen.
	 */
	const PAGE_NAME = 'apple-news-automation';

	/**
	 * The schema for automation rules.
	 */
	const SCHEMA = [
		'type'  => 'array',
		'items' => [
			'type'       => 'object',
			'properties' => [
				'field'    => [
					'default' => '',
					'type'    => 'string',
				],
				'taxonomy' => [
					'default' => '',
					'type'    => 'string',
				],
				'term_id'  => [
					'default' => 0,
					'type'    => 'integer',
				],
				'value'    => [
					'default' => '',
					'type'    => 'string',
				],
			],
		],
	];

	/**
	 * Initialize functionality of this class by registering hooks.
	 */
	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'action__init' ] );
		add_action( 'admin_menu', [ __CLASS__, 'action__admin_menu' ], 100 );
		add_filter( 'apple_news_active_theme', [ __CLASS__, 'filter__apple_news_active_theme' ], 0, 2 );
		add_filter( 'apple_news_article_metadata', [ __CLASS__, 'filter__apple_news_article_metadata' ], 0, 2 );
		add_filter( 'apple_news_exporter_slug', [ __CLASS__, 'filter__apple_news_exporter_slug' ], 0, 2 );
	}

	/**
	 * A callback function for the admin_init action hook.
	 */
	public static function action__init(): void {
		register_setting(
			self::PAGE_NAME,
			self::OPTION_KEY,
			[
				'default'      => [],
				'description'  => __( 'Automation settings for Publish to Apple News.', 'apple-news' ),
				'show_in_rest' => [ 'schema' => self::SCHEMA ],
				'type'         => 'array',
			]
		);
	}

	/**
	 * A callback function for the admin_menu action hook.
	 */
	public static function action__admin_menu(): void {
		add_submenu_page(
			'apple_news_index',
			__( 'Apple News Automation', 'apple-news' ),
			__( 'Automation', 'apple-news' ),
			/** This filter is documented in admin/class-admin-apple-settings.php */
			apply_filters( 'apple_news_settings_capability', 'manage_options' ),
			self::PAGE_NAME,
			[ __CLASS__, 'render_submenu_page' ]
		);
	}

	/**
	 * A callback function for the apple_news_active_theme filter.
	 *
	 * @param string $theme_name The name of the theme to use.
	 * @param ?int   $post_id    The ID of the post being exported.
	 *
	 * @return string The filtered theme name.
	 */
	public static function filter__apple_news_active_theme( $theme_name, $post_id ) {
		if ( $post_id ) {
			$rules = self::get_automation_for_post( $post_id );
			foreach ( $rules as $rule ) {
				if ( 'theme' === ( $rule['field'] ?? '' ) && ! empty( $rule['value'] ) ) {
					$theme_name = $rule['value'];
				}
			}
		}

		return $theme_name;
	}

	/**
	 * A callback function for the apple_news_article_metadata filter.
	 *
	 * @param array $metadata An array of metadata keys and values.
	 * @param int   $post_id  The ID of the post being pushed to Apple News.
	 *
	 * @return array The modified array of metadata.
	 */
	public static function filter__apple_news_article_metadata( $metadata, $post_id ) {
		// Trim down the list of matched rules to only those affecting article metadata.
		$metadata_rules = array_values(
			array_filter(
				self::get_automation_for_post( $post_id ),
				function ( $rule ) {
					return 'article_metadata' === self::get_fields()[ $rule['field'] ]['location'] ?? '';
				}
			)
		);

		// Loop through each matched rule and apply the value to metadata.
		foreach ( $metadata_rules as $rule ) {
			if ( false === strpos( $rule['field'], '.' ) ) {
				$metadata[ $rule['field'] ] = 'true' === $rule['value'];
			}
		}

		return $metadata;
	}

	/**
	 * A callback function for the apple_news_exporter_slug filter.
	 *
	 * @param string $slug    The slug to use.
	 * @param int    $post_id The post ID associated with the slug.
	 *
	 * @return string The filtered slug value.
	 */
	public static function filter__apple_news_exporter_slug( $slug, $post_id ) {
		$rules = self::get_automation_for_post( $post_id );
		foreach ( $rules as $rule ) {
			if ( 'slug.#text#' === ( $rule['field'] ?? '' ) ) {
				$slug = $rule['value'] ?? '';
			}
		}

		return $slug;
	}

	/**
	 * Given a post ID, returns an array of matching automation rules.
	 *
	 * @param int $post_id The post ID to query.
	 *
	 * @return array An array of matching automation rules.
	 */
	public static function get_automation_for_post( int $post_id ): array {
		return array_values(
			array_filter(
				self::get_automation_rules(),
				function ( $rule ) use ( $post_id ) {
					return has_term( $rule['term_id'] ?? '', $rule['taxonomy'] ?? '', $post_id );
				}
			)
		);
	}

	/**
	 * Returns an array of automation rules defined in the database.
	 *
	 * @return array An array of automation rules.
	 */
	public static function get_automation_rules(): array {
		/**
		 * Allow the automation rules to be filtered and set via code.
		 *
		 * @since 2.4.0
		 *
		 * @param array $rules An array of automation rules.
		 */
		return apply_filters( 'apple_news_automation_rules', get_option( self::OPTION_KEY, [] ) );
	}

	/**
	 * Returns an array of valid automation fields with information about data type and
	 * location within what is sent to Apple News.
	 *
	 * @return array An array of fields.
	 */
	public static function get_fields(): array {
		return [
			'isHidden'       => [
				'location' => 'article_metadata',
				'type'     => 'boolean',
				'label'    => __( 'isHidden', 'apple-news' ),
			],
			'isPaid'         => [
				'location' => 'article_metadata',
				'type'     => 'boolean',
				'label'    => __( 'isPaid', 'apple-news' ),
			],
			'isPreview'      => [
				'location' => 'article_metadata',
				'type'     => 'boolean',
				'label'    => __( 'isPreview', 'apple-news' ),
			],
			'isSponsored'    => [
				'location' => 'article_metadata',
				'type'     => 'boolean',
				'label'    => __( 'isSponsored', 'apple-news' ),
			],
			'links.sections' => [
				'location' => 'article_metadata',
				'type'     => 'string',
				'label'    => __( 'Section', 'apple-news' ),
			],
			'slug.#text#'    => [
				'location' => 'component',
				'type'     => 'string',
				'label'    => __( 'Slug', 'apple-news' ),
			],
			'theme'          => [
				'location' => 'exporter',
				'type'     => 'string',
				'label'    => __( 'Theme', 'apple-news' ),
			],
		];
	}

	/**
	 * A callback to load automation settings scripts and styles and render target div for the React submenu page.
	 */
	public static function render_submenu_page(): void {
		// Enqueue page specific scripts.
		wp_enqueue_script(
			'apple-news-admin-settings',
			plugins_url( 'build/adminSettings.js', __DIR__ ),
			[ 'wp-block-editor', 'wp-api-fetch', 'wp-api', 'wp-i18n', 'wp-components', 'wp-element', 'wp-tinymce' ],
			Apple_News::$version,
			true
		);
		wp_enqueue_style( 'wp-edit-blocks' );
		wp_localize_script(
			'apple-news-admin-settings',
			'AppleNewsAutomationConfig',
			[
				'fields'     => self::get_fields(),
				'sections'   => \Admin_Apple_Sections::get_sections(),
				'taxonomies' => get_taxonomies( [ 'public' => 'true' ] ),
				'themes'     => \Apple_Exporter\Theme::get_registry(),
			]
		);
		add_filter( 'should_load_block_editor_scripts_and_styles', '__return_true' );

		// Render target div for React app.
		echo '<div id="apple-news-options__page"></div>';
	}
}
