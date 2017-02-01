<?php

namespace Apple_Actions\Index;

require_once plugin_dir_path( __FILE__ ) . '../class-api-action.php';

use Apple_Actions\API_Action;
use Apple_Exporter\Settings;

class Section extends API_Action {

	/**
	 * Current section ID being retrieved.
	 *
	 * @var string
	 * @access private
	 */
	private $section_id;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings
	 * @param string $section_id
	 */
	function __construct( $settings, $section_id = null ) {
		parent::__construct( $settings );
		$this->section_id = $section_id;
	}

	/**
	 * Get the section data from Apple News.
	 *
	 * @return object
	 * @access public
	 */
	public function perform() {
		// Get the section from the API
		$section = $this->get_api()->get_section( $this->section_id );
		if ( empty( $section->data ) ) {
			return null;
		}

		return $section;
	}

	/**
	 * Get a specific element of section data from Apple News
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 * @access public
	 */
	public function get_data( $key, $default = null ) {
		$section = $this->perform();
		return ( ! isset( $section->data->$key ) ) ? $default : $section->data->$key;
	}

	/**
	 * Get all available sections.
	 * Cache for 5 minutes to avoid too many API requests.
	 *
	 * @param string $type 	Either 'display' or 'raw'.
	 * @return array
	 * @access public
	 */
	public function get_sections() {
		if ( false === ( $sections = get_transient( 'apple_news_sections' ) ) ) {
			$sections = array();
			$channel = $this->get_setting( 'api_channel' );
			if ( ! empty( $channel ) ) {
				try {
					$apple_news_sections = $this->get_api()->get_sections( $channel );
					$sections = ( ! empty( $apple_news_sections->data ) ) ? $apple_news_sections->data : array();
				} catch ( \Apple_Push_API\Request\Request_Exception $e ) {
					$sections = array();
				}

				set_transient( 'apple_news_sections', $sections, 300 );
			}
		}

		return $sections;
	}
}
