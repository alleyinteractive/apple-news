<?php
/**
 * A custom endpoint for updating a post on Apple News.
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Initialize this REST Endpoint.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'apple-news/v1',
			'/update',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => __NAMESPACE__ . '\rest_post_update',
				'permission_callback' => '__return_true',
			]
		);
	}
);

/**
 * Handle a REST POST request to the /apple-news/v1/update endpoint.
 *
 * @param WP_REST_Request $request Full details about the request.
 * @return WP_REST_Response|WP_Error
 */
function rest_post_update( $request ): WP_REST_Response|WP_Error {
	$post = modify_post( (int) $request->get_param( 'id' ), 'update' );

	if ( is_wp_error( $post ) ) {
		return $post;
	}

	return rest_ensure_response( $post );
}
