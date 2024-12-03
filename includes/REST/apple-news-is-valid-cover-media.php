<?php
/**
 * Checks the validity of cover media settings
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

use Apple_Exporter\Components\Embed_Web_Video;
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
			'/is-valid-cover-media/',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => function ( $request ): WP_REST_Response {
					$type = $request['type'];
					$url  = $request['url'];

					// Assume URLs are OK but check for a few known cases where they are not.
					$valid = true;

					if ( 'video_url' === $type ) {
						$check = wp_check_filetype( $url );
						$valid = isset( $check['ext'] ) && 'mp4' === $check['ext'];
					}

					if ( 'embedwebvideo' === $type ) {
						$valid = preg_match( Embed_Web_Video::VIMEO_MATCH, $url )
							|| preg_match( Embed_Web_Video::YOUTUBE_MATCH, $url )
							|| preg_match( Embed_Web_Video::DAILYMOTION_MATCH, $url );
					}

					return rest_ensure_response( [ 'isValidCoverMedia' => $valid ] );
				},
				'permission_callback' => '__return_true',
				'args'                => [
					'type' => [
						'type'     => 'string',
						'required' => true,
					],
					'url'  => [
						'type'     => 'string',
						'required' => true,
						'format'   => 'uri',
					],
				],
			],
		);
	},
);
