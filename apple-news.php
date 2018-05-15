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
 *
 * Plugin Name: Publish to Apple News
 * Plugin URI:  http://github.com/alleyinteractive/apple-news
 * Description: Export and sync posts to Apple format.
 * Version:     1.3.0
 * Author:      Alley Interactive
 * Author URI:  https://www.alleyinteractive.com
 * Text Domain: apple-news
 * Domain Path: lang/
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Activate the plugin.
 */
function apple_news_activate_wp_plugin() {
	// Check for PHP version
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
	// Do something
	$settings = new Apple_Exporter\Settings;
	foreach ( $settings->all() as $name => $value ) {
		delete_option( $name );
	}
}

// WordPress VIP plugins do not execute these hooks, so ignore in that environment.
if ( ! defined( 'WPCOM_IS_VIP_ENV' ) || ! WPCOM_IS_VIP_ENV ) {
	register_activation_hook( __FILE__,   'apple_news_activate_wp_plugin' );
	register_uninstall_hook( __FILE__, 'apple_news_uninstall_wp_plugin' );
}

// Initialize plugin class
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
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	return get_plugin_data( plugin_dir_path( __FILE__ ) . '/apple-news.php' );
}

new Admin_Apple_News();
