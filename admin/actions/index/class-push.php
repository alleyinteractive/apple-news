<?php

namespace Actions\Index;

require_once plugin_dir_path( __FILE__ ) . '../class-api-action.php';
require_once plugin_dir_path( __FILE__ ) . 'class-export.php';

use Actions\API_Action as API_Action;

class Push extends API_Action {

	private $id;
	private $exporter;

	function __construct( $settings, $id ) {
		parent::__construct( $settings );
		$this->id       = $id;
		$this->exporter = null;

		// Maximum execution time is 5 minutes
		set_time_limit( 60 * 5 );
	}

	public function perform() {
		return $this->push();
	}

	private function is_post_in_sync() {
		$post = get_post( $this->id );

		if ( ! $post ) {
			throw new \Actions\Action_Exception( 'Could not find post with id ' . $this->id );
		}

		$api_time   = get_post_meta( $this->id, 'apple_export_api_modified_at', true );
		$api_time   = strtotime( $api_time );
		$local_time = strtotime( $post->post_modified );
		return $api_time >= $local_time;
	}

	/**
	 * Push the post using the API data.
	 */
	private function push() {
		if ( ! $this->is_api_configuration_valid() ) {
			throw new \Actions\Action_Exception( 'Your API settings seem to be empty. Please fill the API key, API
				secret and API channel fields in the plugin configuration page.' );
		}

		// Ignore if the post is already in sync
		if ( $this->is_post_in_sync() ) {
			return;
		}

		// generate_article uses Exporter->genearte, so we MUST clean the workspace
		// before and after its usage.
		$this->clean_workspace();
		list( $json, $bundles ) = $this->generate_article();

		try {
			// If there's an API ID, update, otherwise create.
			$remote_id = get_post_meta( $this->id, 'apple_export_api_id', true );
			$result    = null;
			if ( $remote_id ) {
				$revision = get_post_meta( $this->id, 'apple_export_api_revision', true );
				$result   = $this->get_api()->update_article( $remote_id, $revision, $json, $bundles );
			} else {
				$result = $this->get_api()->post_article_to_channel( $json, $this->get_setting( 'api_channel' ), $bundles );
			}

			// Save the ID that was assigned to this post in by the API
			update_post_meta( $this->id, 'apple_export_api_id', $result->data->id );
			update_post_meta( $this->id, 'apple_export_api_created_at', $result->data->createdAt );
			update_post_meta( $this->id, 'apple_export_api_modified_at', $result->data->modifiedAt );
			update_post_meta( $this->id, 'apple_export_api_share_url', $result->data->shareUrl );
			update_post_meta( $this->id, 'apple_export_api_revision', $result->data->revision );
			// If it's marked as deleted, remove the mark. Ignore otherwise.
			delete_post_meta( $this->id, 'apple_export_api_deleted' );
		} catch ( \Push_API\Request\Request_Exception $e ) {
			if ( preg_match( '#WRONG_REVISION#', $e->getMessage() ) ) {
				throw new \Actions\Action_Exception( 'It seems like the article was updated by another call. If the problem persist, try removing and pushing again.' );
			}

			throw new \Actions\Action_Exception( 'There has been an error with the API. Please make sure your API settings are correct and try again.' );
		} finally {
			$this->clean_workspace();
		}
	}

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
	 * @since 0.6.0
	 */
	private function generate_article() {
		$export_action = new Export( $this->settings, $this->id );
		$this->exporter = $export_action->fetch_exporter();
		$this->exporter->generate();

		$dir  = $this->exporter->workspace()->tmp_path();
		$json = file_get_contents( $dir . 'article.json' );

		$bundles = array();
		$files   = glob( $dir . '*', GLOB_BRACE );
		foreach ( $files as $file ) {
			if ( 'article.json' == basename( $file ) ) {
				continue;
			}

			$bundles[] = $file;
		}

		return array( $json, $bundles );
	}

}
