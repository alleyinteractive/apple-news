<?php
/**
 * Publish to Apple News: \Apple_Actions\Index\Push class
 *
 * @package Apple_News
 * @subpackage Apple_Actions\Index
 */

namespace Apple_Actions\Index;

require_once plugin_dir_path( __FILE__ ) . '../class-api-action.php';
require_once plugin_dir_path( __FILE__ ) . 'class-export.php';

use \Admin_Apple_Notice;
use \Admin_Apple_Sections;
use \Apple_Actions\API_Action;

/**
 * A class to handle a push request from the admin.
 *
 * @package Apple_News
 * @subpackage Apple_Actions\Index
 */
class Push extends API_Action {

	/**
	 * Checksum for current article being exported.
	 *
	 * @var string
	 * @access private
	 */
	private $checksum;

	/**
	 * Current content ID being exported.
	 *
	 * @var int
	 * @access private
	 */
	private $id;

	/**
	 * Sections for the content being exported.
	 *
	 * @var array
	 * @access private
	 */
	private $sections;

	/**
	 * Current instance of the Exporter.
	 *
	 * @var Exporter
	 * @access private
	 */
	private $exporter;

	/**
	 * Constructor.
	 *
	 * @param \Apple_Exporter\Settings $settings A settings object containing settings at load time.
	 * @param int                      $id       The ID for the content object to be pushed.
	 */
	public function __construct( $settings, $id ) {
		parent::__construct( $settings );
		$this->id       = $id;
		$this->exporter = null;
	}

	/**
	 * Perform the push action.
	 *
	 * @param boolean $doing_async Optional. Whether the action is being performed asynchronously.
	 * @param int     $user_id     Optional. The ID of the user performing the action. Defaults to the current user ID.
	 * @access public
	 * @return boolean
	 * @throws \Apple_Actions\Action_Exception If the push fails.
	 */
	public function perform( $doing_async = false, $user_id = null ) {
		if ( 'yes' === $this->settings->get( 'api_async' ) && false === $doing_async ) {
			// Do not proceed if this is already pending publish.
			$pending = get_post_meta( $this->id, 'apple_news_api_pending', true );
			if ( ! empty( $pending ) ) {
				return false;
			}

			// Track this publish event as pending with the timestamp it was sent.
			update_post_meta( $this->id, 'apple_news_api_pending', time() );

			wp_schedule_single_event( time(), \Admin_Apple_Async::ASYNC_PUSH_HOOK, array( $this->id, get_current_user_id() ) );
		} else {
			return $this->push( $user_id );
		}
	}

	/**
	 * Generate a checksum against the article JSON with certain fields ignored.
	 *
	 * @param string $json    The JSON to turn into a checksum.
	 * @param array  $meta    Optional. Metadata for the article. Defaults to empty array.
	 * @param array  $bundles Optional. Any bundles that will be sent with the article. Defaults to empty array.
	 * @param bool   $force   Optional. Allows bypass of local cache for checksum.
	 *
	 * @return string The checksum for the JSON.
	 */
	private function generate_checksum( $json, $meta = [], $bundles = [], $force = false ) {
		// Use cached checksum, if it exists, and if force is false.
		if ( ! $force && ! empty( $this->checksum ) ) {
			return $this->checksum;
		}

		// Try to decode the JSON object.
		$json = json_decode( $json, true );
		if ( empty( $json ) ) {
			return '';
		}

		// Remove any fields from JSON that might change but not affect the article itself, like dates and plugin version.
		unset( $json['metadata']['dateCreated'] );
		unset( $json['metadata']['dateModified'] );
		unset( $json['metadata']['datePublished'] );
		unset( $json['metadata']['generatorVersion'] );

		// Add meta and bundles so we can checksum the whole thing.
		$json['checksum']['meta']    = $meta;
		$json['checksum']['bundles'] = $bundles;

		// Calculate the checksum as a hex value and cache it.
		$this->checksum = dechex( absint( crc32( wp_json_encode( $json ) ) ) );

		return $this->checksum;
	}

	/**
	 * Check if the post is in sync before updating in Apple News.
	 *
	 * @access private
	 * @param string $json    The JSON for this article to check if it is in sync.
	 * @param array  $meta    Optional. Metadata for the article. Defaults to empty array.
	 * @param array  $bundles Optional. Any bundles that will be sent with the article. Defaults to empty array.
	 * @return boolean
	 * @throws \Apple_Actions\Action_Exception If the post could not be found.
	 */
	private function is_post_in_sync( $json, $meta = [], $bundles = [] ) {
		$in_sync = true;

		// Ensure the post (still) exists. Async operations might result in this function being run against a non-existent post.
		$post = get_post( $this->id );
		if ( ! $post ) {
			throw new \Apple_Actions\Action_Exception( __( 'Could not find post with id ', 'apple-news' ) . $this->id );
		}

		// Compare checksums to determine whether the article is in sync or not.
		$current_checksum = get_post_meta( $this->id, 'apple_news_article_checksum', true );
		$new_checksum     = $this->generate_checksum( $json, $meta, $bundles );
		if ( empty( $current_checksum ) || $current_checksum !== $new_checksum ) {
			$in_sync = false;
		}

		/**
		 * Allows for custom logic to determine if a post is in sync or not.
		 *
		 * @since 2.0.2 Added the $post_id, $json, $meta, and $bundles parameters.
		 *
		 * @param bool   $in_sync Whether the current post is in sync or not.
		 * @param int    $post_id The ID of the post being checked.
		 * @param string $json    The JSON for the current article.
		 * @param array  $meta    Metadata for the current article.
		 * @param array  $bundles Any bundles that will be sent with the current article.
		 */
		return apply_filters( 'apple_news_is_post_in_sync', $in_sync, $this->id, $json, $meta, $bundles );
	}

	/**
	 * Get the post using the API data.
	 * Updates the current relevant metadata stored for the post.
	 *
	 * @access private
	 * @throws \Apple_Actions\Action_Exception If there was an error getting the article from the API.
	 */
	private function get() {
		// Ensure we have a valid ID.
		$apple_id = get_post_meta( $this->id, 'apple_news_api_id', true );
		if ( empty( $apple_id ) ) {
			throw new \Apple_Actions\Action_Exception( __( 'This post does not have a valid Apple News ID, so it cannot be retrieved from the API.', 'apple-news' ) );
		}

		// Get the article from the API.
		$result = $this->get_api()->get_article( $apple_id );
		if ( empty( $result->data->revision ) ) {
			throw new \Apple_Actions\Action_Exception( __( 'The API returned invalid data for this article since the revision is empty.', 'apple-news' ) );
		}

		// Update the revision.
		update_post_meta( $this->id, 'apple_news_api_revision', sanitize_text_field( $result->data->revision ) );
	}

	/**
	 * Push the post using the API data.
	 *
	 * @param int $user_id Optional. The ID of the user performing the push. Defaults to current user.
	 * @access private
	 * @throws \Apple_Actions\Action_Exception If unable to push.
	 */
	private function push( $user_id = null ) {
		if ( ! $this->is_api_configuration_valid() ) {
			throw new \Apple_Actions\Action_Exception( __( 'Your Apple News API settings seem to be empty. Please fill in the API key, API secret and API channel fields in the plugin configuration page.', 'apple-news' ) );
		}

		/**
		 * Should the post be skipped and not pushed to apple news.
		 *
		 * Default is false, but filterable.
		 */
		if ( apply_filters( 'apple_news_skip_push', false, $this->id ) ) {
			throw new \Apple_Actions\Action_Exception(
				sprintf(
					// Translators: Placeholder is a post ID.
					__( 'Skipped push of article %d due to the apple_news_skip_push filter.', 'apple-news' ),
					$this->id
				)
			);
		}

		/**
		 * The generate_article function uses Exporter->generate, so we MUST
		 * clean the workspace before and after its usage.
		 */
		$this->clean_workspace();

		// Get sections.
		$this->sections = Admin_Apple_Sections::get_sections_for_post( $this->id );

		// Generate the JSON for the article.
		list( $json, $bundles, $errors ) = $this->generate_article();

		// Process errors.
		$this->process_errors( $errors );

		// Sanitize the data before using since it's filterable.
		$json = $this->sanitize_json( $json );

		// Bundles should be an array of URLs.
		if ( ! empty( $bundles ) && is_array( $bundles ) ) {
			$bundles = array_map( 'esc_url_raw', $bundles );
		} else {
			$bundles = array();
		}

		// If there's an API ID, update, otherwise create.
		$remote_id = get_post_meta( $this->id, 'apple_news_api_id', true );
		$result    = null;

		do_action( 'apple_news_before_push', $this->id );

		// Populate optional metadata.
		$meta = array(
			'data' => array(),
		);

		// Set sections.
		if ( ! empty( $this->sections ) ) {
			sort( $this->sections );
			$meta['data']['links'] = array( 'sections' => $this->sections );
		}

		// Get the isPreview setting.
		$is_paid                = (bool) get_post_meta( $this->id, 'apple_news_is_paid', true );
		$meta['data']['isPaid'] = $is_paid;

		// Get the isPreview setting.
		$is_preview                = (bool) get_post_meta( $this->id, 'apple_news_is_preview', true );
		$meta['data']['isPreview'] = $is_preview;

		// Get the isHidden setting.
		$is_hidden                = (bool) get_post_meta( $this->id, 'apple_news_is_hidden', true );
		$meta['data']['isHidden'] = $is_hidden;

		// Get the isSponsored setting.
		$is_sponsored                = (bool) get_post_meta( $this->id, 'apple_news_is_sponsored', true );
		$meta['data']['isSponsored'] = $is_sponsored;

		// Get the maturity rating setting.
		$maturity_rating = get_post_meta( $this->id, 'apple_news_maturity_rating', true );
		if ( ! empty( $maturity_rating ) ) {
			$meta['data']['maturityRating'] = $maturity_rating;
		}

		// Ignore if the post is already in sync.
		if ( $this->is_post_in_sync( $json, $meta, $bundles ) ) {
			throw new \Apple_Actions\Action_Exception(
				sprintf(
					// Translators: Placeholder is a post ID.
					__( 'Skipped push of article %d because it is already in sync.', 'apple-news' ),
					$this->id
				)
			);
		}

		try {
			if ( $remote_id ) {
				// Update the current article from the API in case the revision changed.
				$this->get();

				// Get the current revision.
				$revision = get_post_meta( $this->id, 'apple_news_api_revision', true );
				$result   = $this->get_api()->update_article( $remote_id, $revision, $json, $bundles, $meta, $this->id );
			} else {
				$result = $this->get_api()->post_article_to_channel( $json, $this->get_setting( 'api_channel' ), $bundles, $meta, $this->id );
			}

			// Save the ID that was assigned to this post in by the API.
			update_post_meta( $this->id, 'apple_news_api_id', sanitize_text_field( $result->data->id ) );
			update_post_meta( $this->id, 'apple_news_api_created_at', sanitize_text_field( $result->data->createdAt ) );
			update_post_meta( $this->id, 'apple_news_api_modified_at', sanitize_text_field( $result->data->modifiedAt ) );
			update_post_meta( $this->id, 'apple_news_api_share_url', sanitize_text_field( $result->data->shareUrl ) );
			update_post_meta( $this->id, 'apple_news_api_revision', sanitize_text_field( $result->data->revision ) );

			// If it's marked as deleted, remove the mark. Ignore otherwise.
			delete_post_meta( $this->id, 'apple_news_api_deleted' );

			// Remove the pending designation if it exists.
			delete_post_meta( $this->id, 'apple_news_api_pending' );

			// Remove the async in progress flag.
			delete_post_meta( $this->id, 'apple_news_api_async_in_progress' );

			// Clear the cache for post status.
			delete_transient( 'apple_news_post_state_' . $this->id );

			// Update the checksum for the article JSON version.
			update_post_meta( $this->id, 'apple_news_article_checksum', $this->generate_checksum( $json, $meta, $bundles ) );

			do_action( 'apple_news_after_push', $this->id, $result );
		} catch ( \Apple_Push_API\Request\Request_Exception $e ) {

			// Remove the pending designation if it exists.
			delete_post_meta( $this->id, 'apple_news_api_pending' );

			// Remove the async in progress flag.
			delete_post_meta( $this->id, 'apple_news_api_async_in_progress' );

			$this->clean_workspace();

			if ( preg_match( '#WRONG_REVISION#', $e->getMessage() ) ) {
				throw new \Apple_Actions\Action_Exception( __( 'It seems like the article was updated by another call. If the problem persists, try removing and pushing again.', 'apple-news' ) );
			} else {
				throw new \Apple_Actions\Action_Exception( __( 'There has been an error with the API: ', 'apple-news' ) . $e->getMessage() );
			}
		}

		// Print success message.
		$post = get_post( $this->id );
		if ( $remote_id ) {
			Admin_Apple_Notice::success(
				sprintf(
					// translators: token is the post title.
					__( 'Article %s has been successfully updated on Apple News!', 'apple-news' ),
					$post->post_title
				),
				$user_id
			);
		} else {
			Admin_Apple_Notice::success(
				sprintf(
					// translators: token is the post title.
					__( 'Article %s has been pushed successfully to Apple News!', 'apple-news' ),
					$post->post_title
				),
				$user_id
			);
		}

		$this->clean_workspace();
	}

	/**
	 * Processes errors, halts publishing if needed.
	 *
	 * @param array $errors Array of errors to be processed.
	 * @access private
	 * @throws \Apple_Actions\Action_Exception If set to fail on component errors.
	 */
	private function process_errors( $errors ) {
		// Get the current alert settings.
		$component_alerts = $this->get_setting( 'component_alerts' );
		$json_alerts      = $this->get_setting( 'json_alerts' );

		// Initialize the alert message.
		$alert_message = '';

		// Get the current user id.
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// Build the component alert error message, if required.
		if ( ! empty( $errors[0]['component_errors'] ) ) {
			// Build an list of the components that caused errors.
			$component_names = implode( ', ', $errors[0]['component_errors'] );

			if ( 'warn' === $component_alerts ) {
				$alert_message .= sprintf(
					// translators: token is a list of component names.
					__( 'The following components are unsupported by Apple News and were removed: %s', 'apple-news' ),
					$component_names
				);
			} elseif ( 'fail' === $component_alerts ) {
				$alert_message .= sprintf(
					// translators: token is a list of component names.
					__( 'The following components are unsupported by Apple News and prevented publishing: %s', 'apple-news' ),
					$component_names
				);
			}
		}

		// Check for JSON errors.
		if ( ! empty( $errors[0]['json_errors'] ) ) {
			if ( ! empty( $alert_message ) ) {
				$alert_message .= '|';
			}

			// Merge all errors into a single message.
			$json_errors = implode( ', ', $errors[0]['json_errors'] );

			// Add these to the message.
			if ( 'warn' === $json_alerts ) {
				$alert_message .= sprintf(
					// translators: token is a list of errors.
					__( 'The following JSON errors were detected: %s', 'apple-news' ),
					$json_errors
				);
			} elseif ( 'fail' === $json_alerts ) {
				$alert_message .= sprintf(
					// translators: token is a list of errors.
					__( 'The following JSON errors were detected and prevented publishing: %s', 'apple-news' ),
					$json_errors
				);
			}
		}

		// See if we found any errors.
		if ( empty( $alert_message ) ) {
			return;
		}

		// Proceed based on component alert settings.
		if ( ( 'fail' === $component_alerts && ! empty( $errors[0]['component_errors'] ) )
			|| ( 'fail' === $json_alerts && ! empty( $errors[0]['json_errors'] ) ) ) {
			// Remove the pending designation if it exists.
			delete_post_meta( $this->id, 'apple_news_api_pending' );

			// Remove the async in progress flag.
			delete_post_meta( $this->id, 'apple_news_api_async_in_progress' );

			// Clean the workspace.
			$this->clean_workspace();

			// Throw an exception.
			throw new \Apple_Actions\Action_Exception( $alert_message );
		} elseif ( ( 'warn' === $component_alerts && ! empty( $errors[0]['component_errors'] ) )
			|| ( 'warn' === $json_alerts && ! empty( $errors[0]['json_errors'] ) ) ) {
				\Admin_Apple_Notice::error( $alert_message, $user_id );
		}
	}

	/**
	 * Clean up the workspace.
	 *
	 * @access private
	 */
	private function clean_workspace() {
		if ( is_null( $this->exporter ) ) {
			return;
		}

		$this->exporter->workspace()->clean_up();
	}

	/**
	 * Use the export action to get an instance of the Exporter. Use that to
	 * manually generate the workspace for upload, then clean it up.
	 *
	 * @access private
	 * @since 0.6.0
	 */
	private function generate_article() {

		$export_action  = new Export( $this->settings, $this->id, $this->sections );
		$this->exporter = $export_action->fetch_exporter();
		$this->exporter->generate();

		return array( $this->exporter->get_json(), $this->exporter->get_bundles(), $this->exporter->get_errors() );
	}

	/**
	 * Sanitize the JSON output based on whether HTML or markdown is used.
	 *
	 * @since 1.2.7
	 *
	 * @param string $json The JSON to be sanitized.
	 * @access private
	 * @return string
	 * @throws \Apple_Actions\Action_Exception If the JSON is invalid.
	 */
	private function sanitize_json( $json ) {
		/**
		 * Apple News format is complex and has too many options to validate otherwise.
		 * Let's just make sure the JSON is valid.
		 */
		$decoded = json_decode( $json );
		if ( ! $decoded ) {
			throw new \Apple_Actions\Action_Exception( __( 'The Apple News JSON is invalid and cannot be published.', 'apple-news' ) );
		} else {
			return wp_json_encode( $decoded );
		}
	}
}
