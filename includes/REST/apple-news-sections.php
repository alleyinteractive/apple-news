<?php
/**
 * This adds custom endpoints for working with sections.
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

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
			'/sections',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => __NAMESPACE__ . '\get_sections_response',
				'permission_callback' => '__return_true',
			]
		);
	}
);

/**
 * Get API response.
 *
 * @return WP_REST_Response|WP_Error
 */
function get_sections_response(): WP_REST_Response|WP_Error {
	// Ensure Apple News is first initialized.
	$retval = \Apple_News::has_uninitialized_error();

	if ( is_wp_error( $retval ) ) {
		return $retval;
	}

	$sections = \Admin_Apple_Sections::get_sections();
	$response = [];

	if ( ! empty( $sections ) && ! empty( get_current_user_id() ) ) {
		foreach ( $sections as $section ) {
			$response[] = [
				'id'   => esc_html( 'https://news-api.apple.com/sections/' . $section->id ),
				'name' => esc_html( $section->name ),
			];
		}
	}

	return rest_ensure_response( $response );
}
