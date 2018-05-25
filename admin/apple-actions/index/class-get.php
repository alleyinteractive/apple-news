<?php

namespace Apple_Actions\Index;

require_once plugin_dir_path( __FILE__ ) . '../class-api-action.php';

use Apple_Actions\API_Action as API_Action;

class Get extends API_Action {

	/**
	 * Current content ID being retrieved.
	 *
	 * @var int
	 * @access private
	 */
	private $id;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings
	 * @param int      $id
	 */
	function __construct( $settings, $id ) {
		parent::__construct( $settings );
		$this->id = $id;
	}

	/**
	 * Get the post data from Apple News.
	 *
	 * @return object
	 * @access public
	 */
	public function perform() {
		// Ensure we have a valid ID.
		$apple_id = get_post_meta( $this->id, 'apple_news_api_id', true );
		if ( empty( $apple_id ) ) {
			return null;
		}

		// Get the article from the API.
		$article = $this->get_api()->get_article( $apple_id );
		if ( empty( $article->data ) ) {
			return null;
		}

		return $article;
	}

	/**
	 * Get a specific element of article data from Apple News
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 * @access public
	 */
	public function get_data( $key, $default = null ) {
		$article = $this->perform();
		return ( ! isset( $article->data->$key ) ) ? $default : $article->data->$key;
	}
}
