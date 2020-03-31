<?php
/**
 * Publish to Apple News: Admin_Apple_Settings_Section_Post_Types class
 *
 * @package Apple_News
 */

/**
 * Describes a WordPress setting section
 *
 * @since 0.6.0
 */
class Admin_Apple_Settings_Section_Post_Types extends Admin_Apple_Settings_Section {

	/**
	 * Slug of the post types settings section.
	 *
	 * @var string
	 * @access protected
	 */
	protected $slug = 'post-type-options';

	/**
	 * Constructor.
	 *
	 * @param string $page The name of the page.
	 * @access public
	 */
	public function __construct( $page ) {
		// Set the name.
		$this->name = __( 'Post Type Options', 'apple-news' );

		// Add the settings.
		$this->settings = array(
			'show_metabox' => array(
				'label' => __( 'Show a publish meta box on post types that have Apple News enabled.', 'apple-news' ),
				'type'  => array( 'yes', 'no' ),
			),
		);

		// Build the post types to display.
		$post_types = apply_filters(
			'apple_news_post_types',
			get_post_types(
				array(
					'public'  => true,
					'show_ui' => true,
				),
				'objects'
			)
		);

		if ( ! empty( $post_types ) ) {
			$post_type_options = array();
			foreach ( $post_types as $post_type ) {
				$post_type_options[ $post_type->name ] = $post_type->label;
			}

			$this->settings['post_types'] = array(
				'label'    => __( 'Post Types', 'apple-news' ),
				'type'     => $post_type_options,
				'multiple' => true,
				'sanitize' => array( $this, 'sanitize_array' ),
				'size'     => 10,
			);
		}

		// Add the groups.
		$this->groups = array(
			'post_type_settings' => array(
				'label'    => __( 'Post Types', 'apple-news' ),
				'settings' => array( 'post_types', 'show_metabox' ),
			),
		);

		parent::__construct( $page );
	}

	/**
	 * Gets section info.
	 *
	 * @access public
	 * @return string The description for this section.
	 */
	public function get_section_info() {
		return __( 'Choose the post types that are eligible for publishing to Apple News.', 'apple-news' );
	}

}
