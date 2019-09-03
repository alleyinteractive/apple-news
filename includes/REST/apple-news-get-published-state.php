<?php
/**
 * This adds custom endpoints for perspective posts.
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

/**
 * Get API response.
 *
 * @param array $data data from query args.
 * @return array updated response.
 */
function get_published_state_response( $data ) {
	$response = [];

	if ( ! empty( get_current_user_id() ) ) {
		$response['publishState'] = \Admin_Apple_News::get_post_status( $data['id'] );
	}

	return $response;
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
			'/get-published-state/(?P<id>\d+)',
			[
				'methods'  => 'GET',
				'callback' => __NAMESPACE__ . '\get_published_state_response',
			]
		);
	}
);
