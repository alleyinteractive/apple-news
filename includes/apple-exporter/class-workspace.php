<?php
namespace Apple_Exporter;

/**
 * Manage the exporter's workspace.
 * For WordPress, this is entirely handled using meta fields
 * since the filesystem is unavailable on WordPress VIP and
 * potentially other enterprise WordPress hosts.
 *
 * @author  Federico Ramirez
 * @author	Bradford Campeau-Laurion
 * @since   0.2.0
 */
class Workspace {

	/**
	 * Meta key used to store the json content with the post.
	 *
	 * @var string
	 * @since 0.9.0
	 */
	const JSON_META_KEY = 'apple_news_api_json';

	/**
	 * Meta key used to store bundled assets with the post.
	 *
	 * @var string
	 * @since 0.9.0
	 */
	const BUNDLE_META_KEY = 'apple_news_api_bundle';

	/**
	 * Meta key used to store errors encountered with the post.
	 *
	 * @var string
	 * @since 0.9.0
	 */
	const ERRORS_META_KEY = 'apple_news_api_errors';

	/**
	 * Current ID of the content we are constructing a workspace for.
	 *
	 * @var int
	 * @since 0.9.0
	 */
	public $content_id;

	/**
	 * Initialize.
	 *
	 * @param int $content_id
	 * @since 0.2.0
	 */
	function __construct( $content_id ) {
		$this->content_id = $content_id;
	}

	/**
	 * Delete all bundle data from the post.
	 *
	 * @param int $content_id
	 * @since 0.2.0
	 */
	public function clean_up() {
		do_action( 'apple_news_before_clean_up' );
		delete_post_meta( $this->content_id, self::JSON_META_KEY );
		delete_post_meta( $this->content_id, self::BUNDLE_META_KEY );
		delete_post_meta( $this->content_id, self::ERRORS_META_KEY );
		do_action( 'apple_news_after_clean_up' );
	}

	/**
	 * Adds a source file to be included later in the bundle.
	 *
	 * @param string $filename
	 * @param string $source
	 * @since 0.9.0
	 */
	public function bundle_source( $filename, $source ) {
		add_post_meta( $this->content_id, self::BUNDLE_META_KEY, esc_url_raw( apply_filters( 'apple_news_bundle_source', $source, $filename, $this->content_id ) ) );
	}

	/**
	 * Stores the JSON file for this workspace to be included in the bundle.
	 *
	 * @param string $content
	 * @since 0.9.0
	 */
	public function write_json( $content ) {
		$json = apply_filters( 'apple_news_write_json', $content, $this->content_id );

		// JSON should be decoded before being stored.
		// Otherwise, stripslashes_deep could potentially remove valid characters
		// such as newlines (\n).s
		$decoded_json = json_decode( $json );
		if ( null === $decoded_json ) {
			// This is invalid JSON.
			// Store as an empty string.
			$decoded_json = '';
		}
		update_post_meta( $this->content_id, self::JSON_META_KEY, $decoded_json );
	}

	/**
	 * Gets the JSON content.
	 *
	 * @return string
	 * @since 0.9.0
	 */
	public function get_json() {
		$json = get_post_meta( $this->content_id, self::JSON_META_KEY, true );
		if ( ! empty( $json ) ) {
			$json = json_encode( $json );
		}
		return apply_filters( 'apple_news_get_json', $json, $this->content_id );
	}

	/**
	 * Gets any bundles.
	 *
	 * @return array
	 * @since 0.9.0
	 */
	public function get_bundles() {
		return apply_filters( 'apple_news_get_bundles', get_post_meta( $this->content_id, self::BUNDLE_META_KEY ), $this->content_id );
	}

	/**
	 * Logs errors encountered during publishing.
	 *
	 * @param string $key
	 * @param string $value
	 * @since 1.0.6
	 */
	public function log_error( $key, $value ) {
		// Get current errors
		$errors = get_post_meta( $this->content_id, self::ERRORS_META_KEY, true );

		// Initialize if needed
		if ( empty( $errors ) ) {
			$errors = array();
		}

		// Initialize the key if needed
		if ( empty( $errors[ $key ] ) ) {
			$errors[ $key ] = array();
		}

		// Log the error
		$errors[ $key ][] = $value;

		// Save the errors
		update_post_meta( $this->content_id, self::ERRORS_META_KEY, $errors );
	}

	/**
	 * Gets errors encountered during publishing.
	 *
	 * @return array
	 * @since 1.0.6
	 */
	public function get_errors() {
		return apply_filters( 'apple_news_get_errors', get_post_meta( $this->content_id, self::ERRORS_META_KEY ), $this->content_id );
	}
}
