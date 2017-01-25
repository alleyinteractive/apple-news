<?php

/**
 * Describes a WordPress setting section
 *
 * @since 0.6.0
 */
class Admin_Apple_Settings_Section_API extends Admin_Apple_Settings_Section {

	/**
	 * Slug of the API settings section.
	 *
	 * @var string
	 * @access protected
	 */
	protected $slug = 'api-options';

	/**
	 * Constructor.
	 *
	 * @param string $page
	 */
	function __construct( $page ) {
		// Set the name
		$this->name =  __( 'API Settings', 'apple-news' );

		// Add the settings
		$this->settings = array(
			'api_channel' => array(
				'label'   => __( 'Channel ID', 'apple-news' ),
				'type'    => 'string',
			),
			'api_key' => array(
				'label'   => __( 'API Key ID', 'apple-news' ),
				'type'    => 'string',
			),
			'api_secret' => array(
				'label'   => __( 'API Key Secret', 'apple-news' ),
				'type'    => 'password',
			),
			'api_autosync' => array(
				'label'   => __( 'Automatically publish to Apple News when published in WordPress', 'apple-news' ),
				'type'    => array( 'yes', 'no' ),
			),
			'api_autosync_update' => array(
				'label'   => __( 'Automatically update in Apple News when updated in WordPress', 'apple-news' ),
				'type'    => array( 'yes', 'no' ),
			),
			'api_autosync_delete' => array(
				'label'   => __( 'Automatically delete from Apple News when deleted in WordPress', 'apple-news' ),
				'type'    => array( 'yes', 'no' ),
			),
			'api_async' => array(
				'label'   			=> __( 'Asynchronously publish to Apple News', 'apple-news' ),
				'type'    			=> array( 'yes', 'no' ),
				'description' 	=> $this->get_async_description(),
			),
		);

		// Add the groups
		$this->groups = array(
			'apple_news' => array(
				'label'       => __( 'Apple News API', 'apple-news' ),
				'settings'    => array( 'api_channel', 'api_key', 'api_secret', 'api_autosync', 'api_autosync_update', 'api_autosync_delete', 'api_async' ),
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
		return sprintf(
			'%s <a target="_blank" href="https://developer.apple.com/news-publisher/">%s</a> %s.',
			__( 'Enter your Apple News credentials below. See', 'apple-news' ),
			__( 'the Apple News documentation', 'apple-news' ),
			__( 'for detailed information', 'apple-news' )
		);
	}

	/**
	 * Generates the description for the async field since this varies by environment.
	 *
	 * @return string
	 * @access private
	 */
	private function get_async_description() {
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
			$system = __( 'the WordPress VIP jobs system', 'apple-news' );
		} else {
			$system = __( 'a single scheduled event', 'apple-news' );
		}

		return sprintf(
			__( 'This will cause publishing to happen asynchronously using %s.', 'apple_news' ),
			$system
		);
	}

}
