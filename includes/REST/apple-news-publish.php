<?php
/**
 * A custom endpoint for publishing a post to Apple News.
 *
 * @package Apple_News
 */
namespace Apple_News\REST;

use \Admin_Apple_News;
use \Admin_Apple_Notice;
use \Apple_Actions\Action_Exception;
use \Apple_Actions\Index\Push;
use \WP_Error;

/**
 * Handle a REST POST request to the /apple-news/v1/publish endpoint.
 *
 * @param array $data Data from query args.
 *
 * @return array|WP_Error Response to the request - either data about a successfully published article, or error.
 */
function rest_post_publish( $data ) {

	// Ensure there is a post ID provided in the data.
	$id = $data->get_param( 'id' );
	if ( empty( $id ) ) {
		return new WP_Error(
			'apple_news_no_post_id',
			esc_html__( 'No post ID was specified.', 'apple-news' ),
			[
				'status' => 400,
			]
		);
	}

	// Try to get the post by ID.
	$post = get_post( $id );
	if ( empty( $post ) ) {
		return new WP_Error(
			'apple_news_bad_post_id',
			esc_html__( 'No post was found with the given ID.', 'apple-news' ),
			[
				'status' => 404,
			]
		);
	}

	// Ensure the user can publish this type of post.
	$post_type = get_post_type_object( get_post_type( $post ) );
	if ( ! current_user_can( $post_type->cap->publish_posts ) ) {
		return new WP_Error(
			'apple_news_failed_cap_check',
			esc_html__( 'Your user account is not permitted to publish this post to Apple News.', 'apple-news' ),
			[
				'status' => 401,
			]
		);
	}

	// If this post is not owned by this user, ensure the user has the right to edit others' posts.
	if ( get_current_user_id() !== (int) $post->post_author
		&& ! current_user_can( $post_type->cap->edit_others_posts )
	) {
		return new WP_Error(
			'apple_news_failed_cap_check',
			esc_html__( 'Your user account is not permitted to publish this post to Apple News.', 'apple-news' ),
			[
				'status' => 401,
			]
		);
	}

	// Try to publish the article to the API.
	$action = new Push( Admin_Apple_News::$settings, $id );
	try {
		$action->perform();

		// Negotiate the message based on whether publish will happen asynchronously or not.
		if ( 'yes' === Admin_Apple_News::$settings->api_async ) {
			$message = __( 'Your article will be pushed shortly.', 'apple-news' );
			Admin_Apple_Notice::success( $message );
		} else {
			$message = __( 'Your article has been pushed successfully!', 'apple-news' );
		}

		// Return the success message in the JSON response also.
		return [
			'message' => $message,
		];
	} catch ( Action_Exception $e ) {
		// Add the error message to the list of messages to display to the user using normal means.
		Admin_Apple_Notice::error( $e->getMessage() );

		// Return the error message in the JSON response also.
		return new WP_Error(
			'apple_news_publish_failed',
			$e->getMessage()
		);
	}
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
