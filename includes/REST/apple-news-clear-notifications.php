<?php
/**
 * Apple News REST customizations: clear-notifications endpoint
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
			'/clear-notifications',
			[
				'methods'  => 'POST',
				'callback' => function( $data ) {
					$body          = json_decode( $data->get_body(), true );
					$notifications = ! empty( $body['toClear'] ) && is_array( $body['toClear'] )
						? $body['toClear']
						: [];
					return \Admin_Apple_Notice::clear( $notifications );
				},
			]
		);
	}
);
