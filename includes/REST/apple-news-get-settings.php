<?php
/**
 * A custom endpoint for getting settings.
 *
 * @package Apple_News
 */

namespace Apple_News\REST;

use Apple_Exporter\Settings;
use Apple_News\Admin\Automation;
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
			'/get-settings',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => __NAMESPACE__ . '\get_settings_response',
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
function get_settings_response(): WP_REST_Response|WP_Error {

	// Ensure Apple News is first initialized.
	$retval = \Apple_News::has_uninitialized_error();

	if ( is_wp_error( $retval ) ) {
		return $retval;
	}

	if ( empty( get_current_user_id() ) ) {
		return rest_ensure_response( [] );
	}

	// Compile non-sensitive plugin settings into a JS-friendly format and return.
	$admin_settings   = new \Admin_Apple_Settings();
	$settings         = $admin_settings->fetch_settings();
	$default_settings = ( new Settings() )->all();

	$response = [
		'adminUrl'            => esc_url_raw( admin_url( 'admin.php?page=apple-news-options' ) ),
		'automaticAssignment' => ! empty( Automation::get_automation_rules() ),
		'apiAsync'            => 'yes' === $settings->api_async,
		'apiAutosync'         => 'yes' === $settings->api_autosync,
		'apiAutosyncDelete'   => 'yes' === $settings->api_autosync_delete,
		'apiAutosyncUpdate'   => 'yes' === $settings->api_autosync_update,
		'fullBleedImages'     => 'yes' === $settings->full_bleed_images,
		'htmlSupport'         => 'yes' === $settings->html_support,
		'inArticlePosition'   => is_numeric( $settings->in_article_position ) ? (int) $settings->in_article_position : $default_settings['in_article_position'],
		'postTypes'           => ! empty( $settings->post_types ) && is_array( $settings->post_types ) ? array_map( 'sanitize_text_field', $settings->post_types ) : [],
		'showMetabox'         => 'yes' === $settings->show_metabox,
		'useRemoteImages'     => 'yes' === $settings->use_remote_images,
	];

	return rest_ensure_response( $response );
}
