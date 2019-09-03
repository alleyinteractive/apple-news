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
function get_settings_response( $data ) {
	if ( empty( get_current_user_id() ) ) {
		return [];
	}

	// Compile non-sensitive plugin settings into a JS-friendly format and return.
	$admin_settings = new \Admin_Apple_Settings();
	$settings       = $admin_settings->fetch_settings();
	return [
		'adminUrl'            => esc_url_raw( admin_url( 'admin.php?page=apple-news-options' ) ),
		'automaticAssignment' => ! empty( get_option( 'apple_news_section_taxonomy_mappings' ) ),
		'apiAsync'            => 'yes' === $settings->api_async,
		'apiAutosync'         => 'yes' === $settings->api_autosync,
		'apiAutosyncDelete'   => 'yes' === $settings->api_autosync_delete,
		'apiAutosyncUpdate'   => 'yes' === $settings->api_autosync_update,
		'enableCoverArt'      => 'yes' === $settings->enable_cover_art,
		'fullBleedImages'     => 'yes' === $settings->full_bleed_images,
		'htmlSupport'         => 'yes' === $settings->html_support,
		'postTypes'           => ! empty( $settings->post_types ) && is_array( $settings->post_types ) ? array_map( 'sanitize_text_field', $settings->post_types ) : [],
		'showMetabox'         => 'yes' === $settings->show_metabox,
		'useRemoteImages'     => 'yes' === $settings->use_remote_images,
	];
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
			'/get-settings',
			[
				'methods'  => 'GET',
				'callback' => __NAMESPACE__ . '\get_settings_response',
			]
		);
	}
);
