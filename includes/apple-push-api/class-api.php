<?php
/**
 * Publish to Apple News: \Apple_Push_API\API class
 *
 * @package Apple_News
 * @subpackage Apple_Push_API
 */

namespace Apple_Push_API;

use \Apple_Push_API\Request\Request as Request;

/**
 * This class will post provided specified format articles to a channel using
 * the API.
 *
 * @since 0.2.0
 */
class API {

	/**
	 * The endpoint to connect to.
	 *
	 * @var string
	 * @since 0.2.0
	 * @access public
	 */
	private $endpoint;

	/**
	 * The request object, used to send signed POST and GET requests to the
	 * endpoint.
	 *
	 * @var Request
	 * @since 0.2.0
	 * @access public
	 */
	private $request;

	/**
	 * Constructor.
	 *
	 * @param string                      $endpoint    The endpoint to connect to.
	 * @param \Apple_Push_API\Credentials $credentials The credentials to connect with.
	 * @param bool                        $debug       Optional. Whether to debug the request. Defaults to false.
	 * @access public
	 */
	public function __construct( $endpoint, $credentials, $debug = false ) {
		$this->endpoint = $endpoint;
		$this->request  = new Request( $credentials, $debug );
	}

	/**
	 * Sends a new article to a given channel.
	 *
	 * @since 0.2.0
	 * @param string $article      The JSON for the article.
	 * @param string $channel_uuid The unique ID for the channel.
	 * @param array  $bundles      Optional. The bundles to send along with the article. Defaults to an empty array.
	 * @param array  $meta         Optional. Additional metadata to send. Defaults to null.
	 * @param int    $post_id      Optional. The post ID of the post to send. Defaults to null.
	 * @access public
	 * @return object The response body from the API.
	 */
	public function post_article_to_channel( $article, $channel_uuid, $bundles = array(), $meta = array(), $post_id = null ) {
		$url = $this->endpoint . '/channels/' . $channel_uuid . '/articles';
		return $this->send_post_request( $url, $article, $bundles, $meta, $post_id );
	}

	/**
	 * Updates an existing article to a given channel.
	 *
	 * @since 0.2.0
	 * @param string $uid      The unique ID for the article.
	 * @param string $revision The revision ID for this article.
	 * @param string $article  The JSON for the article.
	 * @param array  $bundles  Optional. The bundles to send along with the article. Defaults to an empty array.
	 * @param array  $meta     Optional. Additional metadata to send. Defaults to null.
	 * @param int    $post_id  Optional. The post ID of the post to send. Defaults to null.
	 * @access public
	 * @return object The response body from the API.
	 */
	public function update_article( $uid, $revision, $article, $bundles = array(), $meta = array(), $post_id = null ) {
		$url = $this->endpoint . '/articles/' . $uid;

		// Always add the revision.
		if ( empty( $meta['data'] ) || ! is_array( $meta['data'] ) ) {
			$meta['data'] = array();
		}
		$meta['data']['revision'] = $revision;

		return $this->send_post_request( $url, $article, $bundles, $meta, $post_id );
	}

	/**
	 * Gets channel information.
	 *
	 * @since 0.2.0
	 * @param string $channel_uuid The channel UUID to look up.
	 * @access public
	 * @return object The response body from the API.
	 */
	public function get_channel( $channel_uuid ) {
		$url = $this->endpoint . '/channels/' . $channel_uuid;
		return $this->send_get_request( $url );
	}

	/**
	 * Gets article information.
	 *
	 * @since 0.2.0
	 * @param int $article_id The ID of the article to get.
	 * @return object
	 * @access public The response body from the API.
	 */
	public function get_article( $article_id ) {
		$url = $this->endpoint . '/articles/' . $article_id;
		return $this->send_get_request( $url );
	}

	/**
	 * Deletes an article using a DELETE request.
	 *
	 * @since 0.4.0
	 * @param int $article_id The ID of the article to delete.
	 * @access public
	 * @return object The response body from the API.
	 */
	public function delete_article( $article_id ) {
		$url = $this->endpoint . '/articles/' . $article_id;
		return $this->send_delete_request( $url );
	}

	/**
	 * Gets all sections in the given channel.
	 *
	 * @since 0.2.0
	 * @param string $channel_uuid The channel UUID to look up.
	 * @access public
	 * @return object The response body from the API.
	 */
	public function get_sections( $channel_uuid ) {
		$url = $this->endpoint . '/channels/' . $channel_uuid . '/sections';
		return $this->send_get_request( $url );
	}

	/**
	 * Gets information for a section.
	 *
	 * @since 0.2.0
	 * @param string $section_id The section ID to look up.
	 * @access public
	 * @return object The response body from the API.
	 */
	public function get_section( $section_id ) {
		$url = $this->endpoint . '/sections/' . $section_id;
		return $this->send_get_request( $url );
	}

	/**
	 * Send a get request.
	 *
	 * @since 0.2.0
	 * @param string $url The API URL to use to process the GET request.
	 * @access private
	 * @return object The response body from the API.
	 */
	private function send_get_request( $url ) {
		return $this->request->get( $url );
	}

	/**
	 * Send a delete request.
	 *
	 * @since 0.2.0
	 * @param string $url The API URL to use to process the DELETE request.
	 * @access private
	 * @return object The response body from the API.
	 */
	private function send_delete_request( $url ) {
		return $this->request->delete( $url );
	}

	/**
	 * Send a post request.
	 *
	 * @since 0.2.0
	 * @param string $url     The URL to send the request to.
	 * @param string $article The JSON for the article.
	 * @param array  $bundles The bundles to send along with the article.
	 * @param array  $meta    Optional. Additional metadata to send. Defaults to null.
	 * @param int    $post_id Optional. The post ID of the post to send. Defaults to null.
	 * @access private
	 * @return object The response body from the API request.
	 */
	private function send_post_request( $url, $article, $bundles, $meta = null, $post_id = null ) {
		return $this->request->post( $url, $article, $bundles, $meta, $post_id );
	}

}
