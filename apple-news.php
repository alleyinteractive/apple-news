<?php
/**
 * Entry point for the plugin.
 *
 * This file is read by WordPress to generate the plugin information in the
 * admin panel.
 *
 * @link    http://github.com/alleyinteractive/apple-news
 * @since   0.2.0
 * @package WP_Plugin
 */

/*
 * Plugin Name: Publish to Apple News
 * Plugin URI:  http://github.com/alleyinteractive/apple-news
 * Description: Export and sync posts to Apple format.
 * Version:     2.0.4
 * Author:      Alley
 * Author URI:  https://alley.co
 * Text Domain: apple-news
 * Domain Path: lang/
 */

require_once plugin_dir_path( __FILE__ ) . './includes/meta.php';

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Activate the plugin.
 */
function apple_news_activate_wp_plugin() {
	// Check for PHP version.
	if ( version_compare( PHP_VERSION, '5.3.6' ) < 0 ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( esc_html__( 'This plugin requires at least PHP 5.3.6', 'apple-news' ) );
	}
}

require plugin_dir_path( __FILE__ ) . 'includes/apple-exporter/class-settings.php';

/**
 * Deactivate the plugin.
 */
function apple_news_uninstall_wp_plugin() {
	$settings = new Apple_Exporter\Settings();
	foreach ( $settings->all() as $name => $value ) {
		delete_option( $name );
	}
}

// WordPress VIP plugins do not execute these hooks, so ignore in that environment.
if ( ! defined( 'WPCOM_IS_VIP_ENV' ) || ! WPCOM_IS_VIP_ENV ) {
	register_activation_hook( __FILE__, 'apple_news_activate_wp_plugin' );
	register_uninstall_hook( __FILE__, 'apple_news_uninstall_wp_plugin' );
}

// Initialize plugin class.
require plugin_dir_path( __FILE__ ) . 'includes/class-apple-news.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-admin-apple-news.php';

/**
 * Load plugin textdomain.
 *
 * @since 0.9.0
 */
function apple_news_load_textdomain() {
	load_plugin_textdomain( 'apple-news', false, plugin_dir_path( __FILE__ ) . '/lang' );
}
add_action( 'plugins_loaded', 'apple_news_load_textdomain' );

/**
 * Gets plugin data.
 * Used to provide generator info in the metadata class.
 *
 * @return array
 *
 * @since 1.0.4
 */
function apple_news_get_plugin_data() {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	return get_plugin_data( plugin_dir_path( __FILE__ ) . '/apple-news.php' );
}

new Admin_Apple_News();

/**
 * Reports whether an export is currently happening.
 *
 * @return bool True if exporting, false if not.
 * @since 1.4.0
 */
function apple_news_is_exporting() {
	return Apple_Actions\Index\Export::is_exporting();
}

/**
 * Check if Block Editor is active.
 * Must only be used after plugins_loaded action is fired.
 *
 * @return bool
 */
function apple_news_block_editor_is_active() {
	$active = true;

	// Gutenberg plugin is installed and activated.
	$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

	// Block editor since 5.0.
	$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

	if ( ! $gutenberg && ! $block_editor ) {
		$active = false;
	}

	if ( $active && apple_news_is_classic_editor_plugin_active() ) {
		$editor_option       = get_option( 'classic-editor-replace' );
		$block_editor_active = array( 'no-replace', 'block' );

		$active = in_array( $editor_option, $block_editor_active, true );
	}

	/**
	 * Overrides whether Apple News thinks the block editor is active or not.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $active Whether Apple News thinks the block editor is active or not.
	 */
	return apply_filters( 'apple_news_block_editor_is_active', $active );
}

/**
 * Check if Block Editor is active for a given post ID.
 *
 * @param int $post_id Optional. The post ID to check. Defaults to the current post ID.
 * @return bool
 */
function apple_news_block_editor_is_active_for_post( $post_id = 0 ) {

	// If get_current_screen is not defined, we can't get info about the view, so bail out.
	if ( ! function_exists( 'get_current_screen' ) || ! function_exists( 'use_block_editor_for_post' ) ) {
		return false;
	}

	// Only return true if we are on the post add/edit screen.
	$screen = get_current_screen();
	if ( empty( $screen->base ) || 'post' !== $screen->base ) {
		return false;
	}

	// If the post ID isn't specified, pull the current post ID.
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}

	// If the post ID isn't defined, bail out.
	if ( empty( $post_id ) ) {
		return false;
	}

	return use_block_editor_for_post( $post_id );
}

/**
 * Check if Classic Editor plugin is active.
 *
 * @return bool
 */
function apple_news_is_classic_editor_plugin_active() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
		return true;
	}

	return false;
}

/**
 * Given a user ID, a post ID, and an action, determines whether a user can
 * perform the action or not.
 *
 * @param int    $post_id The ID of the post to check.
 * @param string $action  The action to check. One of 'publish', 'update', 'delete'.
 * @param int    $user_id The user ID to check.
 *
 * @return bool True if the user can perform the action, false otherwise.
 */
