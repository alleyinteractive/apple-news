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
		$this->settings = array(
			'component_alerts'  => array(
				'label'       => __( 'Component Alerts', 'apple-news' ),
				'type'        => array( 'none', 'warn', 'fail' ),
				'description' => __( 'If a post has a component that is unsupported by Apple News, choose "none" to generate no alert, "warn" to provide an admin warning notice, or "fail" to generate a notice and stop publishing.', 'apple-news' ),
			),
			'json_alerts'       => array(
				'label'       => __( 'JSON Alerts', 'apple-news' ),
				'type'        => array( 'none', 'warn', 'fail' ),
				'description' => __( 'If a post has invalid JSON that may cause display issues in Apple News, choose "none" to generate no alert, "warn" to provide an admin warning notice, or "fail" to generate a notice and stop publishing.', 'apple-news' ),
			),
			'use_remote_images' => array(
				'label'       => __( 'Use Remote Images?', 'apple-news' ),
				'type'        => array( 'yes', 'no' ),
				'description' => __( 'Allow the Apple News API to retrieve images remotely rather than bundle them. This setting is recommended if you are having any issues with publishing images. If your images are not publicly accessible, such as on a development site, you cannot use this feature.', 'apple-news' ),
			),
			'full_bleed_images' => array(
				'label'       => __( 'Use Full-Bleed Images?', 'apple-news' ),
				'type'        => array( 'yes', 'no' ),
				'description' => __( 'If set to yes, images that are centered or have no alignment will span edge-to-edge rather than being constrained within the body margins.', 'apple-news' ),
			),
			'enable_cover_art'  => array(
				'description' => __( 'Enables the <a href="https://developer.apple.com/library/content/documentation/General/Conceptual/Apple_News_Format_Ref/CoverArt.html">cover art</a> feature, which requires additional image sizes to be created. To avoid adding a significant number of additional image crops to your uploads folder, we recommend using an on-demand image resizing service such as <a href="https://jetpack.com/support/photon/">Jetpack\'s Image CDN</a>.', 'apple-news' ),
				'label'       => __( 'Use Cover Art?', 'apple-news' ),
				'type'        => array( 'yes', 'no' ),
			),
			'html_support'      => array(
				'label'       => __( 'Enable HTML support?', 'apple-news' ),
				'type'        => array( 'yes', 'no' ),
				'description' => sprintf(
					// Translators: Placeholder 1 is an opening <a> tag, placeholder 2 is </a>.
					__( 'If set to no, certain text fields will use Markdown instead of %1$sApple News HTML format%2$s. As of version 1.4.0, HTML format is the preferred output format. Support for Markdown may be removed in the future.', 'apple-news' ),
					'<a href="' . esc_url( 'https://developer.apple.com/library/content/documentation/General/Conceptual/Apple_News_Format_Ref/HTMLMarkupforAppleNewsFormat.html' ) . '">',
					'</a>'
				),
			),
		);

		// Add the groups.
		$this->groups = array(
			'alerts' => array(
				'label'    => __( 'Alerts', 'apple-news' ),
				'settings' => array( 'component_alerts', 'json_alerts' ),
			),
			'images' => array(
				'label'    => __( 'Image Settings', 'apple-news' ),
				'settings' => array( 'use_remote_images', 'full_bleed_images', 'enable_cover_art' ),
			),
			'format' => array(
				'label'    => __( 'Format Settings', 'apple-news' ),
				'settings' => array( 'html_support' ),
			),
		);

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
