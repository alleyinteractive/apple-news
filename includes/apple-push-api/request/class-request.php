<?php
/**
 * Publish to Apple News: \Apple_Push_API\Request\Request class
 *
 * @package Apple_News
 * @subpackage Apple_Push_API
 */

namespace Apple_Push_API\Request;

use Apple_Push_API\Credentials;
use Apple_Push_API\MIME_Builder;
use WP_Error;

require_once dirname( __DIR__ ) . '/class-mime-builder.php';

/**
 * An object capable of sending signed HTTP requests to the Push API.
 *
 * @since 0.2.0
 */
class Request {

	/**
	 * Helper class used to build the MIME parts of the request.
	 *
	 * @var MIME_Builder
	 * @access private
	 * @since 0.2.0
	 */
	private $mime_builder;

	/**
	 * Whether or not we are debugging using a reverse proxy, like Charles.
	 *
	 * @var boolean
	 * @access private
	 * @since 0.2.0
	 */
	private $debug;

	/**
	 * The credentials that will be used to sign sent requests.
	 *
	 * @var Credentials
	 * @access private
	 * @since 0.2.0
	 */
	private $credentials;

	/**
	 * Default arguments passed to the WordPress HTTP API functions.
	 *
	 * @var array
	 * @access private
	 * @since 0.9.0
	 */
	private $default_args;

	/**
	 * Constructor.
	 *
	 * @param \Apple_Push_API\Credentials  $credentials  The credentials to connect to the API.
	 * @param boolean                      $debug        Optional. Whether to run the request in debug mode. Defaults to false.
	 * @param \Apple_Push_API\Mime_Builder $mime_builder Optional. An instance of the Mime_Builder class. Defaults to null.
	 * @access public
	 */
	public function __construct( $credentials, $debug = false, $mime_builder = null ) {
		$this->credentials  = $credentials;
		$this->debug        = $debug;
		$this->mime_builder = ! empty( $mime_builder ) ? $mime_builder : new MIME_Builder();

		/**
		 * Sets the default arguments for requests of any type.
		 *
		 * @param array $args Arguments that will be passed to wp_safe_remote_request.
		 */
		$this->default_args = apply_filters(
			'apple_news_request_args',
			[
				'reject_unsafe_urls' => true,
				'timeout'            => 3,
			]
		);
	}

	/**
	 * Sends a POST request with the given article and bundles.
	 *
	 * @param string     $url     The URL to post the request to.
	 * @param string     $article The content of the article.
	 * @param array      $bundles Optional. Any bundles that will be sent with the article. Defaults to an empty array.
	 * @param array|null $meta    Optional. Any additional metadata that will be sent with the article. Defaults to null.
	 * @param int|null   $post_id Optional. The post ID for the article being sent. Defaults to null.
	 *
	 * @access public
	 * @return mixed The response body from the API.
	 * @throws Request_Exception If the request fails.
	 * @since 0.2.0
	 */
	public function post( $url, $article, $bundles = [], $meta = null, $post_id = null ) {
		return $this->request(
			'POST',
			$url,
			[
				'article' => $article,
				'bundles' => $bundles,
				'meta'    => $meta,
				'post_id' => $post_id,
			]
		);
	}

	/**
	 * Sends a DELETE request for the given article and bundles.
	 *
	 * @param string $url The URL to send the request to.
	 *
	 * @access public
	 * @return mixed The response body from the API.
	 * @throws Request_Exception If the request fails.
	 * @since 0.2.0
	 */
	public function delete( $url ) {
		return $this->request( 'DELETE', $url );
	}

	/**
	 * Sends a GET request for the given article and bundles.
	 *
	 * @since 0.2.0
	 * @param string $url The URL to send the request to.
	 * @access public
	 * @return mixed
	 * @throws Request_Exception If the request fails.
	 */
	public function get( $url ) {
		return $this->request( 'GET', $url );
	}

	/**
	 * Parses the API response and checks for errors.
	 *
	 * @param array|WP_Error $response The response from the API.
	 * @param boolean        $json               Optional. Whether to return the response as decoded JSON or not. Defaults to true.
	 * @param string         $type               Optional. The post type of the content. Defaults to 'post'.
	 * @param array          $meta               Optional. Additional meta information sent with the request. Defaults to null.
	 * @param array          $bundles            Optional. Bundles sent with the request. Defaults to null.
	 * @param string         $article            Optional. The content of the article. Defaults to a blank string.
	 * @param string         $debug_mime_request Optional. Debug information about the MIME encoding. Defaults to a blank string.
	 *
	 * @access private
	 * @return mixed The response body from the API.
	 * @throws Request_Exception If the response is invalid.
	 * @since 0.2.0
	 */
	private function parse_response( $response, $json = true, $type = 'post', $meta = null, $bundles = null, $article = '', $debug_mime_request = '' ) {
		// Ensure we have an expected response type.
		if ( ( ! is_array( $response ) || ! isset( $response['body'] ) ) && ! is_wp_error( $response ) ) {
			if ( is_array( $response ) || is_object( $response ) ) {
				$response = wp_json_encode( $response );
			}
			throw new Request_Exception( esc_html( __( 'Invalid response:', 'apple-news' ) . $response ) );
		}

		// If debugging mode is enabled, send an email.
		$settings = get_option( 'apple_news_settings' );

		if ( ! empty( $settings['apple_news_enable_debugging'] )
			&& ! empty( $settings['apple_news_admin_email'] )
			&& 'yes' === $settings['apple_news_enable_debugging']
			&& 'get' !== $type ) {

			// Get the admin email.
			$admin_email = filter_var( $settings['apple_news_admin_email'], FILTER_VALIDATE_EMAIL );
			if ( empty( $admin_email ) ) {
				return; // TODO Fix inconsistent return value.
			}

			// Add the API response.
			$body  = esc_html__( 'API Response', 'apple-news' ) . ":\n";
			$body .= print_r( $response, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

			// Add the meta sent with the API request, if set.
			if ( ! empty( $meta ) ) {
				$body .= "\n\n" . esc_html__( 'Request Meta', 'apple-news' ) . ":\n\n" . print_r( $meta, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			}

			// Note image settings.
			$body .= "\n\n" . esc_html__( 'Image Settings', 'apple-news' ) . ":\n";
			if ( 'yes' === $settings['use_remote_images'] ) {
				$body .= esc_html__( 'Use Remote images enabled ', 'apple-news' );
			} elseif ( ! empty( $bundles ) ) {
					$body .= "\n" . esc_html__( 'Bundled images', 'apple-news' ) . ":\n";
					$body .= implode( "\n", $bundles );
			} else {
				$body .= esc_html__( 'No bundled images found.', 'apple-news' );
			}

			// Add the JSON for the post.
			$body .= "\n\n" . esc_html__( 'JSON', 'apple-news' ) . ":\n" . $article . "\n";

			// Add the MIME request.
			$body .= "\n\n" . esc_html__( 'MIME request', 'apple-news' ) . ":\n" . $debug_mime_request . "\n";

			/**
			 * Add headers (such as From:) to notification messages.
			 *
			 * See https://developer.wordpress.org/reference/functions/wp_mail/ for documentation and examples.
			 *
			 * @since 1.4.4
			 *
			 * @param string|array $headers     Optional. Additional headers.
			 */
			$headers = apply_filters( 'apple_news_notification_headers', '' );

			// Send the email.
			if ( ! empty( $body ) ) {
				wp_mail( // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
					$admin_email,
					esc_html__( 'Apple News Notification', 'apple-news' ),
					$body,
					$headers
				);
			}
		}

		// Check for errors with the request itself.
		if ( is_wp_error( $response ) ) {
			$string_errors  = '';
			$error_messages = $response->get_error_messages();
			if ( is_array( $error_messages ) && ! empty( $error_messages ) ) {
				$string_errors = implode( ', ', $error_messages );
			}
			throw new Request_Exception( esc_html( __( 'There has been an error with your request:', 'apple-news' ) . " $string_errors" ) );
		}

		// Check for errors from the API.
		$response_decoded = json_decode( $response['body'] );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new Request_Exception( esc_html( __( 'Unable to decode JSON from the response:', 'apple-news' ) ) );
		}
		if ( ! empty( $response_decoded->errors ) && is_array( $response_decoded->errors ) ) {
			$message  = '';
			$messages = [];
			foreach ( $response_decoded->errors as $error ) {
				// If there is a keyPath, build it into a string.
				$key_path = '';
				if ( ! empty( $error->keyPath ) && is_array( $error->keyPath ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					foreach ( $error->keyPath as $i => $path ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						if ( $i > 0 ) {
							$key_path .= "->$path";
						} else {
							$key_path .= $path;
						}
					}

					$key_path = " (keyPath $key_path)";
				}

				// Add the code, message and keyPath.
				$messages[] = sprintf(
					'%s%s%s%s',
					$error->code,
					( ! empty( $error->message ) ) ? ' - ' : '',
					( ! empty( $error->message ) ) ? $error->message : '',
					$key_path
				);
			}

			if ( ! empty( $messages ) ) {
				$message = implode( ', ', $messages );
			} else {
				$message = __( 'Unable to fetch information for the specified article. Was it deleted?', 'apple-news' );
			}

			if ( 'DUPLICATE_ARTICLE_FOUND' === $error->code ) {
				$message .= '.<br>' . sprintf(
					// translators: UUID of original article.
					__( 'Original UUID: %s', 'apple-news' ),
					sanitize_text_field( $error->value )
				);
			}

			throw new Request_Exception( esc_html( $message ) );
		}

		// Return the response in the desired format.
		return $json ? $response_decoded : $response['body'];
	}

	/**
	 * Parses the API response and checks for errors.
	 *
	 * @param string $article The article content.
	 * @param array  $bundles Optional. The bundles to be sent with the article. Defaults to an empty array.
	 * @param array  $meta Optional. Additional meta to be sent with the request. Defaults to an empty array.
	 * @param int    $post_id Optional. The post ID for the post being sent. Defaults to null.
	 *
	 * @access private
	 * @return string The content to be sent to the API.
	 * @throws Request_Exception If the content cannot be built.
	 * @todo The exporter has an abstracted article class. Should we have
	 *       something similar here? That way this method could live there.
	 *
	 * @since 0.2.0
	 */
	private function build_content( $article, $bundles = [], $meta = [], $post_id = null ) {
		$bundles = array_unique( $bundles );
		$content = '';

		/**
		 * Filters custom metadata for the article before being sent to Apple.
		 *
		 * @param array $meta    The Apple News Format metadata to be sent to Apple.
		 * @param int   $post_id The ID of the post being prepared.
		 */
		$meta = apply_filters( 'apple_news_api_post_meta', $meta, $post_id );

		if ( ! empty( $meta['data'] ) && is_array( $meta['data'] ) ) {
			$content .= $this->mime_builder->add_metadata( $meta );
		}

		$content .= $this->mime_builder->add_json_string( 'my_article', 'article.json', $article );
		foreach ( $bundles as $bundle ) {
			$content .= $this->mime_builder->add_content_from_file( $bundle );
		}
		$content .= $this->mime_builder->close();

		return $content;
	}

	/**
	 * A generic request method for handling requests to the API.
	 *
	 * @param string $verb The HTTP verb to use. One of GET, POST, DELETE.
	 * @param string $url  The URL against which to make the request.
	 * @param array  $data Optional. Data to send along with the request. Only applies to POST requests.
	 *
	 * @return mixed The parsed response from the API.
	 * @throws Request_Exception If the request fails.
	 */
	private function request( $verb, $url, $data = [] ) {
		// If this is a POST request, build the content.
		$content = 'POST' === $verb
			? $this->build_content( $data['article'], $data['bundles'], $data['meta'], $data['post_id'] )
			: null;

		// Build the request args.
		$args = [
			'headers' => [
				'Authorization' => $this->sign( $url, $verb, $content ),
			],
			'method'  => $verb,
		];

		// If this is a POST request, add the content to it.
		if ( 'POST' === $verb ) {
			$args['headers']['Content-Length'] = strlen( $content );
			$args['headers']['Content-Type']   = 'multipart/form-data; boundary=' . $this->mime_builder->boundary();
			$args['body']                      = $content;
			$args['timeout']                   = 30; // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
		}

		/**
		 * Allow filtering of the default arguments for the request.
		 *
		 * The verb will be dynamically inserted in the hook name, so this hook
		 * will support the following permutations:
		 *
		 *    apple_news_delete_args
		 *    apple_news_get_args
		 *    apple_news_post_args
		 *
		 * @param array $args    Arguments to be filtered.
		 * @param int   $post_id The post ID, if this is a POST request.
		 */
		$args = apply_filters(
			'apple_news_' . strtolower( $verb ) . '_args',
			wp_parse_args( $args, $this->default_args ),
			! empty( $data['post_id'] ) ? $data['post_id'] : 0
		);

		// Perform the request.
		$response = wp_safe_remote_request( esc_url_raw( $url ), $args );

		// Check for DATE_NOT_RECENT error and add a warning for it explicitly.
		if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) && false !== strpos( $response['body'], 'DATE_NOT_RECENT' ) ) {
			$response_body = json_decode( $response['body'], true );
			if ( ! empty( $response_body['errors'] ) && is_array( $response_body['errors'] ) ) {
				foreach ( $response_body['errors'] as $error ) {
					if ( ! empty( $error['code'] ) && 'DATE_NOT_RECENT' === $error['code'] ) {
						\Admin_Apple_Notice::error(
							__(
								'The date and time on your server does not match the date and time on the Apple server. All API requests will fail until you synchronize your server clock.',
								'apple-news'
							)
						);
						break;
					}
				}
			}
		}

		// NULL is a valid response for DELETE.
		if ( 'DELETE' === $verb && is_null( $response ) ) {
			return null;
		}

		// Parse the response.
		$response = $this->parse_response(
			$response,
			true,
			strtolower( $verb ),
			! empty( $data['meta'] ) ? $data['meta'] : null,
			! empty( $data['bundles'] ) ? $data['bundles'] : null,
			! empty( $data['article'] ) ? $data['article'] : '',
			'POST' === $verb ? $this->mime_builder->get_debug_content( $args ) : ''
		);

		return $response;
	}

	/**
	 * Signs the API request.
	 *
	 * @since 0.2.0
	 * @param string $url     The API URL that will be used.
	 * @param string $verb    The HTTP verb that will be used.
	 * @param string $content The content that will be sent.
	 * @access private
	 * @return string The signature string for use in signing API requests.
	 */
	private function sign( $url, $verb, $content = null ) {
		$current_date = gmdate( 'c' );

		$request_info = $verb . $url . $current_date;
		if ( 'POST' === $verb ) {
			$content_type  = 'multipart/form-data; boundary=' . $this->mime_builder->boundary();
			$request_info .= $content_type . $content;
		}

		$secret_key = base64_decode( $this->credentials->secret() );
		$hash       = hash_hmac( 'sha256', $request_info, $secret_key, true );
		$signature  = base64_encode( $hash );

		return 'HHMAC; key=' . $this->credentials->key() . '; signature=' . $signature . '; date=' . $current_date;
	}
}

/**
 * A class to handle exceptions on requests.
 *
 * @package Apple_News
 * @subpackage Apple_Push_API\Request
 */
class Request_Exception extends \Exception {} // phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
