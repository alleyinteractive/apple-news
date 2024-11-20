<?php
/**
 * Returns a determination about whether the current user can publish the current post type to Apple News.
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

use Apple_News;
use WP_Error;
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
			'/user-can-publish/(?P<id>\d+)',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => __NAMESPACE__ . '\get_user_can_publish',
				'permission_callback' => '__return_true',
			]
		);
	}
);

/**
 * Get API response.
 *
 * @param WP_REST_Request $request Full details about the request.
 * @return WP_REST_Response|WP_Error
 */
function get_user_can_publish( $request ): WP_REST_Response|WP_Error {
	// Ensure Apple News is first initialized.
	$retval = Apple_News::has_uninitialized_error();

	if ( is_wp_error( $retval ) ) {
		return $retval;
	}

	// Ensure there is a post ID provided in the data.
	$id = (int) $request->get_param( 'id' );

	if ( empty( $id ) ) {
		return rest_ensure_response( [ 'userCanPublish' => false ] );
	}

	// Try to get the post by ID.
	$post = get_post( $id );
	if ( empty( $post ) ) {
		return rest_ensure_response( [ 'userCanPublish' => false ] );
	}

	// Ensure the user is authorized to make changes to Apple News posts.
	$response = [
		'userCanPublish' => current_user_can(
			/** This filter is documented in admin/class-admin-apple-post-sync.php */
			apply_filters(
				'apple_news_publish_capability',
				Apple_News::get_capability_for_post_type( 'publish_posts', $post->post_type )
			)
		),
	];

	return rest_ensure_response( $response );
}
