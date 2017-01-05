<?php

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
	 * @param string $page
	 */
	function __construct( $page ) {
		// Set the name
		$this->name =  __( 'Advanced Settings', 'apple-news' );

		// Add the settings
		$this->settings = array(
			'pullquote_line_height' => array(
				'label'   => __( 'Pull quote Line Height', 'apple-news' ),
				'type'    => 'float',
				'sanitize' => 'floatval',
			),
			'header_line_height' => array(
				'label'   => __( 'Heading Line Height', 'apple-news' ),
				'type'    => 'float',
				'sanitize' => 'floatval',
			),
			'component_alerts' => array(
				'label'   => __( 'Component Alerts', 'apple-news' ),
				'type'    => array( 'none', 'warn', 'fail' ),
				'description' => __( 'If a post has a component that is unsupported by Apple News, choose "none" to generate no alert, "warn" to provide an admin warning notice, or "fail" to generate a notice and stop publishing.', 'apple-news' ),
			),
			'json_alerts' => array(
				'label'   => __( 'JSON Alerts', 'apple-news' ),
				'type'    => array( 'none', 'warn', 'fail' ),
				'description' => __( 'If a post has invalid JSON that may cause display issues in Apple News, choose "none" to generate no alert, "warn" to provide an admin warning notice, or "fail" to generate a notice and stop publishing.', 'apple-news' ),
			),
			'use_remote_images' => array(
				'label'   => __( 'Use Remote Images?', 'apple-news' ),
				'type'    => array( 'yes', 'no' ),
				'description' => __( 'Allow the Apple News API to retrieve images remotely rather than bundle them. This setting is recommended if you are having any issues with publishing images. If your images are not publicly accessible, such as on a development site, you cannot use this feature.', 'apple-news' ),
			),
		);

		// Add the groups
		$this->groups = array(
			'line_heights' => array(
				'label'       => __( 'Line Heights', 'apple-news' ),
				'settings'    => array( 'pullquote_line_height', 'header_line_height' ),
			),
			'alerts' => array(
				'label'       => __( 'Alerts', 'apple-news' ),
				'settings'    => array( 'component_alerts', 'json_alerts' ),
			),
			'images' => array(
				'label'       => __( 'Image Settings', 'apple-news' ),
				'settings'    => array( 'use_remote_images' ),
			),
		);

		parent::__construct( $page );
	}

	/**
	 * Gets section info.
	 *
	 * @return string
	 * @access public
	 */
	public function get_section_info() {
		return __( 'Delete values to restore defaults.', 'apple-news' );
	}
}
