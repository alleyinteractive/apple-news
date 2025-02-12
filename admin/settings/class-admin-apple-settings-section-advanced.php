<?php
/**
 * Publish to Apple News: Admin_Apple_Settings_Section_Advanced class
 *
 * @package Apple_News
 */

/**
 * Describes a WordPress setting section
 *
 * @since 0.6.0
 */
class Admin_Apple_Settings_Section_Advanced extends Admin_Apple_Settings_Section {

	/**
	 * Slug of the advanced settings section.
	 *
	 * @var string
	 * @access protected
	 */
	protected $slug = 'advanced-options';

	/**
	 * Constructor.
	 *
	 * @param string $page The page that this section belongs to.
	 * @access public
	 */
	public function __construct( $page ) {
		// Set the name.
		$this->name = __( 'Advanced Settings', 'apple-news' );

		// Add the settings.
		$this->settings = [
			'component_alerts'            => [
				'label'       => __( 'Component Alerts', 'apple-news' ),
				'type'        => [ 'none', 'warn', 'fail' ],
				'description' => __( 'If a post has a component that is unsupported by Apple News, choose "none" to generate no alert, "warn" to provide an admin warning notice, or "fail" to generate a notice and stop publishing.', 'apple-news' ),
			],
			'use_remote_images'           => [
				'label'       => __( 'Use Remote Images?', 'apple-news' ),
				'type'        => [ 'yes', 'no' ],
				'description' => __( 'Allow the Apple News API to retrieve images remotely rather than bundle them. This setting is recommended if you are having any issues with publishing images. If your images are not publicly accessible, such as on a development site, you cannot use this feature.', 'apple-news' ),
			],
			'full_bleed_images'           => [
				'label'       => __( 'Use Full-Bleed Images?', 'apple-news' ),
				'type'        => [ 'yes', 'no' ],
				'description' => __( 'If set to yes, images that are centered or have no alignment will span edge-to-edge rather than being constrained within the body margins.', 'apple-news' ),
			],
			'deduplicate_cover_media'     => [
				'label'       => __( 'Deduplicate Cover Media?', 'apple-news' ),
				'type'        => [ 'yes', 'no' ],
				'description' => __( 'If set to yes, any image, video, or other content selected as an article\'s Cover Media will not appear again in the article body in Apple News.', 'apple-news' ),
			],
			'html_support'                => [
				'label'       => __( 'Enable HTML support?', 'apple-news' ),
				'type'        => [ 'yes', 'no' ],
				'description' => sprintf(
					// Translators: Placeholder 1 is an opening <a> tag, placeholder 2 is </a>.
					__( 'If set to no, certain text fields will use Markdown instead of %1$sApple News HTML format%2$s. As of version 1.4.0, HTML format is the preferred output format. Support for Markdown may be removed in the future.', 'apple-news' ),
					'<a href="' . esc_url( 'https://developer.apple.com/documentation/apple_news/apple_news_format/components/using_html_with_apple_news_format' ) . '">',
					'</a>'
				),
			],
			'in_article_position'         => [
				'label'       => __( 'Position of In Article Module', 'apple-news' ),
				'type'        => 'number',
				'min'         => 3,
				'max'         => 99,
				'step'        => 1,
				'description' => __( 'If you have configured an In Article module via Customize JSON, the position that the module should be inserted into. Defaults to 3, which is after the third content block in the article body (e.g., the third paragraph).', 'apple-news' ),
			],
			'aside_component_class'       => [
				'label'       => __( 'Aside Content CSS Class', 'apple-news' ),
				'type'        => 'text',
				'size'        => 100,
				'description' => __( 'Enter a CSS class name that will be used to generate the Aside component. Do not prefix with a period.', 'apple-news' ),
				'required'    => false,
			],
			'recipe_component_class'      => [
				'label'       => __( 'Recipe Content CSS Class', 'apple-news' ),
				'type'        => 'text',
				'size'        => 100,
				'description' => __( 'Enter a CSS class name that will be used to generate the Recipe component. Do not prefix with a period.', 'apple-news' ),
				'required'    => false,
			],
			'recipe_component_use_schema' => [
				'label'       => __( 'Build Recipe Component From Structured Data', 'apple-news' ),
				'type'        => [ 'yes', 'no' ],
				'description' => __( 'Set to yes if you want the recipe JSON-LD to replace the HTML markup inside the specified CSS class. Set to no if you want to render the HTML markup inside the specified CSS class as-is.', 'apple-news' ),
			],
			'excluded_selectors'          => [
				'label'       => __( 'Selectors', 'apple-news' ),
				'type'        => 'text',
				'size'        => 100,
				'description' => sprintf(
				/* translators: %s: <code> tag */
					__( 'Enter a comma-separated list of CSS class or ID selectors, like %s. Elements in post content matching these selectors will be removed from the content published to Apple News.', 'apple-news' ),
					'<code>.my-class, #my-id</code>',
				),
				'required'    => false,
			],
		];

		// Add the groups.
		$this->groups = [
			'alerts'    => [
				'label'    => __( 'Alerts', 'apple-news' ),
				'settings' => [ 'component_alerts' ],
			],
			'images'    => [
				'label'    => __( 'Image Settings', 'apple-news' ),
				'settings' => [ 'use_remote_images', 'full_bleed_images' ],
			],
			'cover'     => [
				'label'    => __( 'Cover Media Settings', 'apple-news' ),
				'settings' => [ 'deduplicate_cover_media' ],
			],
			'format'    => [
				'label'    => __( 'Format Settings', 'apple-news' ),
				'settings' => [ 'html_support', 'in_article_position' ],
			],
			'aside'     => [
				'label'    => __( 'Aside Component', 'apple-news' ),
				'settings' => [ 'aside_component_class' ],
			],
			'recipe'    => [
				'label'    => __( 'Recipe Component', 'apple-news' ),
				'settings' => [ 'recipe_component_class', 'recipe_component_use_schema' ],
			],
			'selectors' => [
				'label'    => __( 'Excluded Elements', 'apple-news' ),
				'settings' => [ 'excluded_selectors' ],
			],
		];

		parent::__construct( $page );
	}

	/**
	 * Gets section info.
	 *
	 * @access public
	 * @return string Information about this section.
	 */
	public function get_section_info() {
		return __( 'Advanced publishing settings for Apple News.', 'apple-news' );
	}
}
