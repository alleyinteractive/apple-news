<?php
/**
 * Publish to Apple News: \Apple_Actions\Index\Delete class
 *
 * @package Apple_News
 * @subpackage Apple_Actions\Index
 */

namespace Apple_Actions\Index;

require_once dirname( __DIR__ ) . '/class-api-action.php';

use Apple_Actions\API_Action;

/**
 * A class to handle a delete request from the admin.
 *
 * @package Apple_News
 * @subpackage Apple_Actions\Index
 */
class Delete extends API_Action { // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_delete, Generic.Classes.OpeningBraceSameLine.ContentAfterBrace

	/**
	 * ID of the post to be deleted.
	 *
	 * @var int
	 * @access private
	 */
	private $id;

	/**
	 * Constructor.
	 *
	 * @param \Apple_Exporter\Settings $settings Settings in effect during this run.
	 * @param int                      $id The ID of the content to be deleted.
	 * @access public
	 */
	public function __construct( $settings, $id ) {
		parent::__construct( $settings );
		$this->id = $id;
	}

	/**
	 * Must be implemented when extending Action. Performs the action and returns
	 * errors if any, null otherwise.
	 *
	 * @since 0.6.0
	 *
	 * @access public
	 * @return object
	 * @throws \Apple_Actions\Action_Exception If the post fails to delete.
	 */
	public function perform() {
		return $this->delete();
	}

	/**
	 * Delete the post using the API data.
	 *
	 * @access private
	 * @return mixed
	 * @throws \Apple_Actions\Action_Exception If the post fails to delete.
	 */
	private function delete() {
		if ( ! $this->is_api_configuration_valid() ) {
			throw new \Apple_Actions\Action_Exception( esc_html__( 'Your Apple News API settings seem to be empty. Please fill the API key, API secret and API channel fields in the plugin configuration page.', 'apple-news' ) );
		}

		$remote_id = get_post_meta( $this->id, 'apple_news_api_id', true );
		if ( ! $remote_id ) {
			throw new \Apple_Actions\Action_Exception( esc_html__( 'This post has not been pushed to Apple News, cannot delete.', 'apple-news' ) );
		}

		try {
			/**
			 * Actions to be taken before an article is deleted via the API.
			 *
			 * @param string $remote_id The API ID of the article from Apple's servers.
			 * @param int    $post_id   The post ID from WordPress.
			 */
			do_action( 'apple_news_before_delete', $remote_id, $this->id );

			$this->get_api()->delete_article( $remote_id );

			// Delete the API references and mark as deleted.
			$this->delete_post_meta( $this->id );
			update_post_meta( $this->id, 'apple_news_api_deleted', time() );

			// Clear the cache for post status.
			delete_transient( 'apple_news_post_state_' . $this->id );

			/**
			 * Actions to be taken after an article is deleted via the API.
			 *
			 * @param string $remote_id The API ID of the article from Apple's servers.
			 * @param int    $post_id   The post ID from WordPress.
			 */
			do_action( 'apple_news_after_delete', $remote_id, $this->id );
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}
}
