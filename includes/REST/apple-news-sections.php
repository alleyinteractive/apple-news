<?php
/**
 * This adds custom endpoints for working with sections.
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

/**
 * Get API response.
 *
 * @return array An array of information about sections.
 */
function get_sections_response() {
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
			'/sections',
			[
				'methods'  => 'GET',
				'callback' => __NAMESPACE__ . '\get_sections_response',
			]
		);
	}
);
