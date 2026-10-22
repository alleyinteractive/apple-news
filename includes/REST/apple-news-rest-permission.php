<?php
/**
 * A shared permission check for the Apple News REST endpoints.
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

use WP_REST_Request;

/**
 * Determine whether the current user may access an Apple News REST endpoint.
 *
 * Endpoints that act on a specific post require the ability to edit that post.
 * All other endpoints require the ability to edit posts.
 *
 * @param string          $route   The endpoint name, e.g. 'publish', 'sections'.
 * @param WP_REST_Request $request The current request.
 * @return bool Whether access is allowed.
 */
function rest_permission( string $route, WP_REST_Request $request ): bool {
	$routes_with_post_id = [
		'delete',
		'get-published-state',
		'publish',
		'update',
		'user-can-publish',
	];

	if ( in_array( $route, $routes_with_post_id, true ) ) {
		$post_id = (int) $request->get_param( 'id' );
		$allowed = ! empty( $post_id ) && current_user_can( 'edit_post', $post_id );
	} else {
		$allowed = current_user_can( 'edit_posts' );
	}

	/**
	 * Filters whether the current user may access an Apple News REST endpoint.
	 *
	 * @param bool            $allowed Whether access is allowed. Default is the result of the capability check.
	 * @param string          $route   The endpoint name, e.g. 'publish', 'sections'.
	 * @param WP_REST_Request $request The current request.
	 */
	return (bool) apply_filters( 'apple_news_rest_permission', $allowed, $route, $request );
}
