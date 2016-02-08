<?php

namespace Apple_Actions\Index;

require_once plugin_dir_path( __FILE__ ) . '../class-api-action.php';
require_once plugin_dir_path( __FILE__ ) . 'class-export.php';

use Apple_Actions\API_Action as API_Action;

class Push extends API_Action {

	/**
	 * Current content ID being exported.
	 *
	 * @var int
	 * @access private
	 */
	private $id;

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
	 * @param Settings $settings
	 * @param int $id
	 */
	function __construct( $settings, $id ) {
		parent::__construct( $settings );
		$this->id       = $id;
		$this->exporter = null;
	}

	/**
	 * Perform the push action.
	 *
	 * @access public
	 * @param boolean $doing_async
	 * @return boolean
	 */
	public function perform( $doing_async = false ) {
		if ( 'yes' === $this->settings->get( 'api_async' ) && false === $doing_async ) {
			// Do not proceed if this is already pending publish
			$pending = get_post_meta( $this->id, 'apple_news_api_pending', true );
			if ( ! empty( $pending ) ) {
				return false;
			}

			// Track this publish event as pending with the timestamp it was sent
			update_post_meta( $this->id, 'apple_news_api_pending', time() );

			wp_schedule_single_event( time(), \Admin_Apple_Async::ASYNC_PUSH_HOOK, array( $this->id, get_current_user_id() ) );
		} else {
			return $this->push();
		}
	}

	/**
	 * Check if the post is in sync before updating in Apple News.
	 *
	 * @access private
	 * @return boolean
	 */
	private function is_post_in_sync() {
		$post = get_post( $this->id );

		if ( ! $post ) {
			throw new \Apple_Actions\Action_Exception( __( 'Could not find post with id ', 'apple-news' ) . $this->id );
		}

		$api_time = get_post_meta( $this->id, 'apple_news_api_modified_at', true );
		$api_time = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $api_time ) ) ) );
		$local_time = strtotime( $post->post_modified );

		$in_sync = $api_time >= $local_time;

		return apply_filters( 'apple_news_is_post_in_sync', $in_sync, $this->id, $api_time, $local_time );
	}

	/**
	 * Get the post using the API data.
	 * Updates the current relevant metadata stored for the post.
	 *
	 * @access private
	 */
	private function get() {
		// Ensure we have a valid ID.
		$apple_id = get_post_meta( $this->id, 'apple_news_api_id', true );
		if ( empty( $apple_id ) ) {
			throw new \Apple_Actions\Action_Exception( __( 'This post does not have a valid Apple News ID, so it cannot be retrieved from the API.', 'apple-news' ) );
		}

		// Get the article from the API
		$result = $this->get_api()->get_article( $apple_id );
		if ( empty( $result->data->revision ) ) {
			throw new \Apple_Actions\Action_Exception( __( 'The API returned invalid data for this article since the revision is empty.', 'apple-news' ) );
		}

		// Update the revision
		update_post_meta( $this->id, 'apple_news_api_revision', sanitize_text_field( $result->data->revision ) );
	}

	/**
	 * Push the post using the API data.
	 *
	 * @access private
	 */
	private function push() {
		if ( ! $this->is_api_configuration_valid() ) {
			throw new \Apple_Actions\Action_Exception( __( 'Your Apple News API settings seem to be empty. Please fill in the API key, API secret and API channel fields in the plugin configuration page.', 'apple-news' ) );
		}

		/**
		 * Should the post be skipped and not pushed to apple news.
		 *
		 * Default is false, but filterable.
		 */
		if ( apply_filters( 'apple_news_skip_push', false, $this->id ) ) {
			return;
		}

		// Ignore if the post is already in sync
		if ( $this->is_post_in_sync() ) {
			return;
		}

		// generate_article uses Exporter->generate, so we MUST clean the workspace
		// before and after its usage.
		$this->clean_workspace();
		list( $json, $bundles ) = $this->generate_article();

		// Validate the data before using since it's filterable.
		// JSON should just be a string.
		// Apple News format is complex and has too many options to validate otherwise.
		// Let's just make sure it's not doing anything bad and is the right data type.
		$json = sanitize_text_field( $json );

		// Bundles should be an array of URLs
		if ( ! empty( $bundles ) && is_array( $bundles ) ) {
			$bundles = array_map( 'esc_url_raw', $bundles );
		} else {
			$bundles = array();
		}

		try {
			// If there's an API ID, update, otherwise create.
			$remote_id = get_post_meta( $this->id, 'apple_news_api_id', true );
			$result    = null;

			do_action( 'apple_news_before_push', $this->id );

			if ( $remote_id ) {
				// Update the current article from the API in case the revision changed
				$this->get();

				// Get the current revision
				$revision = get_post_meta( $this->id, 'apple_news_api_revision', true );
				$result   = $this->get_api()->update_article( $remote_id, $revision, $json, $bundles );
			} else {
				$result = $this->get_api()->post_article_to_channel( $json, $this->get_setting( 'api_channel' ), $bundles );
			}

			// Save the ID that was assigned to this post in by the API.
			update_post_meta( $this->id, 'apple_news_api_id', sanitize_text_field( $result->data->id ) );
			update_post_meta( $this->id, 'apple_news_api_created_at', sanitize_text_field( $result->data->createdAt ) );
			update_post_meta( $this->id, 'apple_news_api_modified_at', sanitize_text_field( $result->data->modifiedAt ) );
			update_post_meta( $this->id, 'apple_news_api_share_url', sanitize_text_field( $result->data->shareUrl ) );
			update_post_meta( $this->id, 'apple_news_api_revision', sanitize_text_field( $result->data->revision ) );

			// If it's marked as deleted, remove the mark. Ignore otherwise.
			delete_post_meta( $this->id, 'apple_news_api_deleted' );

			// Remove the pending designation if it exists
			delete_post_meta( $this->id, 'apple_news_api_pending' );

			// Remove the async in progress flag
			delete_post_meta( $this->id, 'apple_news_api_async_in_progress' );

			do_action( 'apple_news_after_push', $this->id, $result );
		} catch ( \Apple_Push_API\Request\Request_Exception $e ) {

			// Remove the pending designation if it exists
			delete_post_meta( $this->id, 'apple_news_api_pending' );

			// Remove the async in progress flag
			delete_post_meta( $this->id, 'apple_news_api_async_in_progress' );

			$this->clean_workspace();

			if ( preg_match( '#WRONG_REVISION#', $e->getMessage() ) ) {
				throw new \Apple_Actions\Action_Exception( __( 'It seems like the article was updated by another call. If the problem persists, try removing and pushing again.', 'apple-news' ) );
			} else {
				throw new \Apple_Actions\Action_Exception( __( 'There has been an error with the API. Please make sure your API settings are correct and try again: ', 'apple-news' ) .  $e->getMessage() );
			}
		}

		$this->clean_workspace();
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

		$export_action = new Export( $this->settings, $this->id );
		$this->exporter = $export_action->fetch_exporter();
		$this->exporter->generate();

		return array( $this->exporter->get_json(), $this->exporter->get_bundles() );
	}
}
