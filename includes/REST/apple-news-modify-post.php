<?php
/**
 * A generic function for handling publish/update/delete actions against the Apple News API.
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

use \Admin_Apple_News;
use \Admin_Apple_Notice;
use \Apple_Actions\Action_Exception;
use \Apple_Actions\Index\Delete;
use \Apple_Actions\Index\Push;
use \Apple_News;
use \WP_Error;

/**
 * Publish, update, or delete a post via the Apple News API given a post ID.
 *
 * @param int    $post_id   The post ID to modify.
 * @param string $operation The operation to perform. One of 'publish', 'update', 'delete'.
 *
 * @return array|WP_Error Response to the request - either data about a successful operation, or error.
 */
function modify_post( $post_id, $operation ) {

	// Ensure there is a post ID provided in the data.
	if ( empty( $post_id ) ) {
		$message = __( 'No post ID was specified.', 'apple-news' );
		Admin_Apple_Notice::error( $message );
		return new WP_Error(
			'apple_news_no_post_id',
			$message,
			[
				'status' => 400,
			]
		);
	}

	// Try to get the post by ID.
	$post = get_post( $post_id );
	if ( empty( $post ) ) {
		$message = __( 'No post was found with the given ID.', 'apple-news' );
		Admin_Apple_Notice::error( $message );
		return new WP_Error(
			'apple_news_bad_post_id',
			$message,
			[
				'status' => 404,
			]
		);
	}

	// Ensure the user is authorized to make changes to Apple News posts.
	if ( ! current_user_can(
		apply_filters(
			'apple_news_publish_capability',
			Apple_News::get_capability_for_post_type( 'publish_posts', $post->post_type )
		)
	) ) {
		$message = __( 'Your user account is not permitted to modify posts on Apple News.', 'apple-news' );
		Admin_Apple_Notice::error( $message );
		return new WP_Error(
			'apple_news_failed_cap_check',
			$message,
			[
				'status' => 401,
			]
		);
	}

	// Try to perform the action for the article against the API.
	switch ( $operation ) {
		case 'publish':
		case 'update':
			$action = new Push( Admin_Apple_News::$settings, $post_id );
			break;
		case 'delete':
			$action = new Delete( Admin_Apple_News::$settings, $post_id ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_delete
			break;
		default:
			$message = __( 'You specified an invalid API operation.', 'apple-news' );
			Admin_Apple_Notice::error( $message );
			return new WP_Error(
				'apple_news_bad_operation',
				$message
			);
	}
	try {
		$action->perform();

		// Set any additional notifications that might be required.
		if ( 'yes' === Admin_Apple_News::$settings->api_async ) {
			Admin_Apple_Notice::success( __( 'Your changes will be applied shortly.', 'apple-news' ) );
		} elseif ( 'delete' === $operation ) {
			Admin_Apple_Notice::success(
				sprintf(
					// translators: The title of the article.
					__( 'Article %s has been successfully deleted from Apple News!', 'apple-news' ),
					$post->post_title
				)
			);
		}

		return [
			'publishState' => Admin_Apple_News::get_post_status( $post_id ),
		];
	} catch ( Action_Exception $e ) {
		// Add the error message to the list of messages to display to the user using normal means.
		Admin_Apple_Notice::error( $e->getMessage() );

		// Return the error message in the JSON response also.
		return new WP_Error(
			'apple_news_operation_failed',
			$e->getMessage()
		);
	}
}
