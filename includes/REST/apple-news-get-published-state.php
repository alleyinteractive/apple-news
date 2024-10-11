<?php
/**
 * This adds custom endpoints for perspective posts.
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
			'/get-published-state/(?P<id>\d+)',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => __NAMESPACE__ . '\get_published_state_response',
				'permission_callback' => '__return_true',
				'schema'              => [
					'description' => __( 'Get the published state of a post.', 'apple-news' ),
					'type'        => 'object',
					'properties'  => [
						'publishState' => [
							'type'        => 'string',
							'description' => __( 'The published state of the post.', 'apple-news' ),
						],
					],
				],
			],
		);
	}
);

/**
 * Get the published state of a post.
 *
 * @param WP_REST_Request $request Full details about the request.
 * @return WP_REST_Response|WP_Error
 */
function get_published_state_response( $request ): WP_REST_Response|WP_Error {
	$id = $request->get_param( 'id' );

	// Ensure Apple News is first initialized.
	$retval = \Apple_News::has_uninitialized_error();

	if ( is_wp_error( $retval ) ) {
		return $retval;
	}

	$response = [];

	if ( ! empty( get_current_user_id() ) ) {
		$response['publishState'] = \Admin_Apple_News::get_post_status( $id );
	}

	return rest_ensure_response( $response );
}
