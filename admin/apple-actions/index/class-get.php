<?php
/**
 * Publish to Apple News: \Apple_Actions\Index\Get class
 *
 * @package Apple_News
 * @subpackage Apple_Actions\Index
 */

namespace Apple_Actions\Index;

require_once dirname( __DIR__ ) . '/class-api-action.php';

use Apple_Actions\API_Action;

/**
 * A class to handle a get request from the admin.
 *
 * @package Apple_News
 * @subpackage Apple_Actions\Index
 */
class Get extends API_Action {

	/**
	 * Post ID of the content being retrieved.
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Constructor.
	 *
	 * @param \Apple_Exporter\Settings $settings Settings in use during this run.
	 * @param int                      $id       Post ID of the content being retrieved.
	 */
	public function __construct( $settings, $id ) {
		parent::__construct( $settings );
		$this->id = $id;
	}

	/**
	 * Get the post data from Apple News.
	 *
	 * @return object|null
	 */
	public function perform() {
		// Ensure we have a valid ID.
		$apple_id = get_post_meta( $this->id, 'apple_news_api_id', true );

		if ( empty( $apple_id ) ) {
			return null;
		}

		// Get the article from the API.
		try {
			$article = $this->get_api()->get_article( $apple_id );
		} catch ( \Apple_Push_API\Request\Request_Exception $e ) {
			$article = $e->getMessage();

			// Reset the API postmeta if the article is deleted in Apple News.
			if ( is_string( $article ) && str_contains( $article, 'NOT_FOUND (keyPath articleId)' ) ) {
				$this->delete_post_meta( $this->id );
			}
		}

		if ( empty( $article->data ) ) {
			return null;
		}

		return $article;
	}

	/**
	 * Get a specific element of article data from Apple News
	 *
	 * @param string $key     The key to look up in the data.
	 * @param string $default Optional. The default value to fall back to. Defaults to null.
	 * @return mixed
	 */
	public function get_data( $key, $default = null ) {
		$article = $this->perform();
		return ( ! isset( $article->data->$key ) ) ? $default : $article->data->$key;
	}
}
