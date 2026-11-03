<?php
/**
 * Publish to Apple News: Admin_Apple_Settings_Section_API class
 *
 * @package Apple_News
 */

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
	 * @param string $page The page that this section belongs to.
	 * @access public
	 */
	public function __construct( $page ) {
		// Set the name.
		$this->name = __( 'API Settings', 'apple-news' );

		// Add the settings.
		$this->settings = [
			'api_config_file'        => [
				// translators: tokens fill in <a> tags.
				'description' => sprintf( __( 'Having trouble? %1$sEnter the contents of your .papi file manually%2$s.', 'apple-news' ), '<a href="#api_config_file">', '</a>' ),
				'type'        => 'file',
			],
			'api_config_file_input'  => [
				'type' => 'textarea',
			],
			'api_channel'            => [
				'type' => 'hidden',
			],
			'api_key'                => [
				'type' => 'hidden',
			],
			'api_secret'             => [
				'type' => 'hidden',
			],
			'api_autosync'           => [
				'label' => __( 'Automatically publish to Apple News when published in WordPress', 'apple-news' ),
				'type'  => [ 'yes', 'no' ],
			],
			'api_autosync_update'    => [
				'label' => __( 'Automatically update in Apple News when updated in WordPress', 'apple-news' ),
				'type'  => [ 'yes', 'no' ],
			],
			'api_autosync_delete'    => [
				'label' => __( 'Automatically delete from Apple News when deleted in WordPress', 'apple-news' ),
				'type'  => [ 'yes', 'no' ],
			],
			'api_autosync_unpublish' => [
				'label' => __( 'Automatically delete from Apple News when unpublished in WordPress', 'apple-news' ),
				'type'  => [ 'yes', 'no' ],
			],
			'api_autosync_trash'     => [
				'label' => __( 'Automatically delete from Apple News when moved to the trash in WordPress', 'apple-news' ),
				'type'  => [ 'yes', 'no' ],
			],
			'api_async'              => [
				'label'       => __( 'Asynchronously publish to Apple News', 'apple-news' ),
				'type'        => [ 'yes', 'no' ],
				'description' => $this->get_async_description(),
			],
			'api_autosync_skip'      => [
				'label'    => __( 'Skip auto-publishing for posts that have any of the following term IDs:', 'apple-news' ),
				'required' => false,
			],
		];

		// Add the groups.
		$this->groups = [
			'apple_news_config_upload' => [
				'label'    => __( 'Upload Channel Configuration File:', 'apple-news' ),
				'settings' => [ 'api_config_file', 'api_config_file_input', 'api_channel', 'api_key', 'api_secret' ],
			],
			'apple_news_options'       => [
				'label'    => __( 'Apple News API Options', 'apple-news' ),
				'settings' => [ 'api_autosync', 'api_autosync_update', 'api_autosync_delete', 'api_autosync_unpublish', 'api_autosync_trash', 'api_async', 'api_autosync_skip' ],
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
		return sprintf(
			// translators: tokens fill in <a> tags.
			__( 'Please upload your Apple News channel configuration file below. Please see %1$sthe Apple News API documentation%2$s and %3$sthe News Publisher documentation%4$s for detailed information. For further assistance, please contact your Apple News representative.', 'apple-news' ),
			'<a target="_blank" href="https://developer.apple.com/documentation/apple_news/apple_news_api/getting_ready_to_publish_and_manage_your_articles">',
			'</a>',
			'<a target="_blank" href="https://support.apple.com/guide/news-publisher/use-your-cms-with-news-publisher-apd88c8447e6/icloud">',
			'</a>'
		);
	}

	/**
	 * Generates the description for the async field since this varies by environment.
	 *
	 * @access private
	 * @return string The description of the async field.
	 */
	private function get_async_description() {
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
			return __( 'This will cause publishing to happen asynchronously using the WordPress VIP jobs system.', 'apple-news' );
		}

		return __( 'This will cause publishing to happen asynchronously using a single scheduled event.', 'apple-news' );
	}
}
