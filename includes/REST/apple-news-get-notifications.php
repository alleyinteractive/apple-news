<?php
/**
 * Apple News REST customizations: get-notifications endpoint
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

/**
 * Initialize this REST Endpoint.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'apple-news/v1',
			'/get-notifications',
			[
				'methods'  => 'GET',
				'callback' => [ 'Admin_Apple_Notice', 'get' ],
			]
		);
	}
);
