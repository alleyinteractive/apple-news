<?php
/**
 * Returns a determination about whether the current user can publish the current post type to Apple News.
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

use \Apple_News;

/**
 * Get API response.
 *
 * @param array $args Args present in the URL.
 *
 * @return array An array that contains the key 'userCanPublish' which is true if the user can publish, false if not.
 */
function get_user_can_publish( $args ) {

	// Ensure there is a post ID provided in the data.
	$id = ! empty( $args['id'] ) ? (int) $args['id'] : 0;
	if ( empty( $id ) ) {
		return [
			'userCanPublish' => false,
		];
	}

	// Try to get the post by ID.
	$post = get_post( $id );
	if ( empty( $post ) ) {
		return [
			'userCanPublish' => false,
		];
	}

	// Ensure the user is authorized to make changes to Apple News posts.
	return [
		'userCanPublish' => current_user_can(
			apply_filters(
				'apple_news_publish_capability',
				Apple_News::get_capability_for_post_type( 'publish_posts', $post->post_type )
			)
		),
	];
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
			'/user-can-publish/(?P<id>\d+)',
			[
				'methods'  => 'GET',
				'callback' => __NAMESPACE__ . '\get_user_can_publish',
			]
		);
	}
);
