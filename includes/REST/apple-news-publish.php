<?php
/**
 * A custom endpoint for publishing a post to Apple News.
 *
 * @package Apple_News
 */
namespace Apple_News\REST;

/**
 * Handle a REST POST request to the /apple-news/v1/publish endpoint.
 *
 * @param array $data Data from query args.
 *
 * @return array|\WP_error Response to the request - either data about a successfully published article, or error.
 */
function rest_post_publish( $data ) {

	// Ensure there is a post ID provided in the data.
	$id = $data->get_param( 'id' );
	if ( empty( $id ) ) {
		return new \WP_Error(
			'apple_news_no_post_id',
			esc_html__( 'No post ID was specified.', 'apple-news' ),
			[
				'status' => 400,
			]
		);
	}

	// TODO: Try to get the post based on ID.
	// TODO: Perform a permissions check to ensure the current user can publish this type of post.
	// TODO: Try to publish the article to the API.
	// TODO: If successful, return info about the published article.

	return [];
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
			'/publish',
			[
				'methods'  => 'POST',
				'callback' => __NAMESPACE__ . '\rest_post_publish',
			]
		);
	}
);
