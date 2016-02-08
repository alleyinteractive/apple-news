<?php
/**
 * Base plugin class with core plugin information and shared functionality
 * between frontend and backend plugin classes.
 *
 * @author  Federico Ramirez
 * @since   0.2.0
 */
class Apple_News {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 * @access protected
	 */
	protected $plugin_slug = 'apple_news';

	/**
	 * Plugin domain.
	 *
	 * @var string
	 * @access protected
	 */
	protected $plugin_domain = 'apple-news';

	/**
	 * Plugin version.
	 *
	 * @var string
	 * @access protected
	 */
	protected $version = '1.0.3';

	/**
	 * Extracts the filename for bundling an asset.
	 * This functionality is used in a number of classes that do not have a common ancestor.
	 *
	 * @var string
	 * @access protected
	 */
	public static function get_filename( $path ) {
		// Remove any URL parameters.
		// This is important for sites using WordPress VIP or Jetpack Photon.
		$url_parts = parse_url( $path );
		if ( empty( $url_parts['path'] ) ) {
			return '';
		}

		// Get the filename
		$filename = basename( $url_parts['path'] );

		// Remove any spaces and return the filename
		return str_replace( ' ', '', $filename );
	}
}
