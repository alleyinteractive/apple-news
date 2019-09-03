<?php
/**
 * A custom endpoint for updating a post on Apple News.
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

use \WP_Error;
use \WP_REST_Request;

/**
 * Handle a REST POST request to the /apple-news/v1/update endpoint.
 *
 * @param WP_REST_Request $data Data from query args.
 *
 * @return array|WP_Error Response to the request - either data about a successfully updated article, or error.
 */
function rest_post_update( $data ) {
	return modify_post( (int) $data->get_param( 'id' ), 'update' );
}

/**
 * Initialize this REST Endpoint.
 */
add_action(
	'rest_api_init',
	function () {
		// Register route count argument.
		register_rest_route(
			'apple-news/v1',
			'/update',
			[
				'methods'  => 'POST',
				'callback' => __NAMESPACE__ . '\rest_post_update',
			]
		);
	}
);
