<?php
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
	 */
	function __construct( $endpoint, $credentials, $debug = false ) {
		$this->endpoint = $endpoint;
		$this->request  = new Request( $credentials, $debug );
	}

	/**
	 * Sends a new article to a given channel.
	 *
	 * @param string $article The JSON string representing the article
	 * @param array  $bundles An array of file paths for the article attachments
	 *
	 * @since 0.2.0
	 * @param string $article
	 * @param string $channel_uuid
	 * @param array  $bundles
	 * @param array  $meta
	 * @param int    $post_id
	 * @return object
	 * @access public
	 */
	public function post_article_to_channel( $article, $channel_uuid, $bundles = array(), $meta = array(), $post_id = null ) {
		$url = $this->endpoint . '/channels/' . $channel_uuid . '/articles';
		return $this->send_post_request( $url, $article, $bundles, $meta, $post_id );
	}

	/**
	 * Updates an existing article to a given channel.
	 *
	 * @param string $article The JSON string representing the article
	 * @param array  $bundles An array of file paths for the article attachments
	 *
	 * @since 0.2.0
	 * @param string $uid
	 * @param string $revision
	 * @param string $article
	 * @param array  $bundles
	 * @param array  $meta
	 * @param int    $post_id
	 * @return object
	 * @access public
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
	 * @param string $channel_uuid
	 * @return object
	 * @access public
	 */
	public function get_channel( $channel_uuid ) {
		$url = $this->endpoint . '/channels/' . $channel_uuid;
		return $this->send_get_request( $url );
	}

	/**
	 * Gets article information.
	 *
	 * @since 0.2.0
	 * @param int $article_id
	 * @return object
	 * @access public
	 */
	public function get_article( $article_id ) {
		$url = $this->endpoint . '/articles/' . $article_id;
		return $this->send_get_request( $url );
	}

	/**
	 * Deletes an article using a DELETE request.
	 *
	 * @since 0.4.0
	 * @param int $article_id
	 * @return object
	 * @access public
	 */
	public function delete_article( $article_id ) {
		$url = $this->endpoint . '/articles/' . $article_id;
		return $this->send_delete_request( $url );
	}

	/**
	 * Gets all sections in the given channel.
	 *
	 * @since 0.2.0
	 * @param string $channel_uuid
	 * @return object
	 * @access public
	 */
	public function get_sections( $channel_uuid ) {
		$url = $this->endpoint . '/channels/' . $channel_uuid . '/sections';
		return $this->send_get_request( $url );
	}

	/**
	 * Gets information for a section.
	 *
	 * @since 0.2.0
	 * @param string $section_id
	 * @return object
	 * @access public
	 */
	public function get_section( $section_id ) {
		$url = $this->endpoint . '/sections/' . $section_id;
		return $this->send_get_request( $url );
	}

	/**
	 * Send a get request.
	 *
	 * @since 0.2.0
	 * @param string $url
	 * @return object
	 * @access private
	 */
	private function send_get_request( $url ) {
		return $this->request->get( $url );
	}

	/**
	 * Send a delete request.
	 *
	 * @since 0.2.0
	 * @param string $url
	 * @return object
	 * @access private
	 */
	private function send_delete_request( $url ) {
		return $this->request->delete( $url );
	}

	/**
	 * Send a post request.
	 *
	 * @since 0.2.0
	 * @param string $url
	 * @param string $article
	 * @param array  $bundles
	 * @param array  $meta
	 * @param int    $post_id
	 * @return object
	 * @access private
	 */
	private function send_post_request( $url, $article, $bundles, $meta = null, $post_id = null ) {
		return $this->request->post( $url, $article, $bundles, $meta, $post_id );
	}

}
