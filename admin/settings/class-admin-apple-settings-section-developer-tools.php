<?php
/**
 * Publish to Apple News Admin Settings: Admin_Apple_Settings_Section_Developer_Tools class
 *
 * Contains a class which is used to handle settings in the developer tools section.
 *
 * @package Apple_News
 * @since 0.6.0
 */

/**
 * A class which is used to handle settings in the developer tools section.
 *
 * @since 0.6.0
 */
class Admin_Apple_Settings_Section_Developer_Tools extends Admin_Apple_Settings_Section {

	/**
	 * Slug of the developer tools section.
	 *
	 * @var string
	 * @access protected
	 */
	protected $slug = 'developer-tools';

	/**
	 * Constructor.
	 *
	 * @param string $page The name of the submenu page that this section is part of.
	 * @access public
	 */
	public function __construct( $page ) {

		// Set the name.
		$this->name = __( 'Developer Tools', 'apple-news' );

		// Add the settings.
		$this->settings = array(
			'apple_news_enable_debugging' => array(
				'label' => __( 'Enable Debugging', 'apple-news' ),
				'type'  => array( 'no', 'yes' ),
			),
			'apple_news_admin_email'      => array(
				'label'    => __( 'Administrator Email', 'apple-news' ),
				'required' => false,
				'type'     => 'string',
			),
		);

		// Add the groups.
		$this->groups = array(
			'debugging_settings' => array(
				'label'    => __( 'Debugging Settings', 'apple-news' ),
				'settings' => array(
					'apple_news_enable_debugging',
					'apple_news_admin_email',
				),
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
		return __(
			'If debugging is enabled, emails will be sent to an administrator for every publish, update or delete action with a detailed API response.',
			'apple-news'
		);
	}
}
