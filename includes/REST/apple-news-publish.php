<?php
/**
 * A custom endpoint for publishing a post to Apple News.
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
			'/publish',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => __NAMESPACE__ . '\rest_post_publish',
				'permission_callback' => '__return_true',
			]
		);
	}
);

/**
 * Handle a REST POST request to the /apple-news/v1/publish endpoint.
 *
 * @param WP_REST_Request $request Full details about the request.
 * @return WP_REST_Response|WP_Error
 */
function rest_post_publish( $request ): WP_REST_Response|WP_Error {
	$channel_key = $request->get_param( 'channel' );

	// Debug logging.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'Apple News REST publish: channel param = ' . var_export( $channel_key, true ) );
		error_log( 'Apple News REST publish: all params = ' . var_export( $request->get_params(), true ) );
		// phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	$post = modify_post( (int) $request->get_param( 'id' ), 'publish', $channel_key );

	if ( is_wp_error( $post ) ) {
		return $post;
	}

	return rest_ensure_response( $post );
}
