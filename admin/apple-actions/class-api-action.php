<?php
/**
 * Publish to Apple News: \Apple_Actions\API_Action abstract class
 *
 * @package Apple_News
 * @subpackage Apple_Actions
 */

namespace Apple_Actions;

require_once __DIR__ . '/class-action.php';
require_once __DIR__ . '/class-action-exception.php';
require_once dirname( __DIR__, 2 ) . '/includes/apple-push-api/autoload.php';

use Apple_Actions\Action;
use Apple_Push_API\API;
use Apple_Push_API\Credentials;

/**
 * A base class that API-related actions can extend.
 */
abstract class API_Action extends Action {

	/**
	 * The API endpoint for all Apple News requests.
	 */
	const API_ENDPOINT = 'https://news-api.apple.com';

	/**
	 * Instance of the API class.
	 *
	 * @var API
	 * @access private
	 */
	private $api;

	/**
	 * The channel key for this action.
	 *
	 * @var string
	 * @access protected
	 */
	protected $channel_key = 'primary';

	/**
	 * Set the channel key for this action.
	 *
	 * @param string $channel_key The channel key ('primary' or 'secondary').
	 * @access public
	 */
	public function set_channel_key( string $channel_key ): void {
		$this->channel_key = $channel_key;
		// Reset API instance so it uses new credentials.
		$this->api = null;
	}

	/**
	 * Get the channel key for this action.
	 *
	 * @access public
	 * @return string
	 */
	public function get_channel_key(): string {
		return $this->channel_key;
	}

	/**
	 * Set the instance of the API class.
	 *
	 * @param API $api The instance of the API class.
	 * @access public
	 */
	public function set_api( $api ) {
		$this->api = $api;
	}

	/**
	 * Get the instance of the API class.
	 *
	 * @access protected
	 * @return API
	 */
	protected function get_api() {
		if ( is_null( $this->api ) ) {
			$this->api = new API( self::API_ENDPOINT, $this->fetch_credentials() );
		}

		return $this->api;
	}

	/**
	 * Fetch the current API credentials.
	 *
	 * @access private
	 * @return Credentials
	 */
	private function fetch_credentials() {
		$credentials = \Apple_News_Channels::get_credentials( $this->channel_key, $this->settings );
		return new Credentials( $credentials['key'], $credentials['secret'] );
	}

	/**
	 * Get the channel ID for the current channel.
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_channel_id(): string {
		$credentials = \Apple_News_Channels::get_credentials( $this->channel_key, $this->settings );
		return $credentials['channel'];
	}

	/**
	 * Check if the API configuration is valid.
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function is_api_configuration_valid() {
		$credentials = \Apple_News_Channels::get_credentials( $this->channel_key, $this->settings );
		if ( empty( $credentials['key'] )
			|| empty( $credentials['secret'] )
			|| empty( $credentials['channel'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the postmeta suffix for the current channel.
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_meta_suffix(): string {
		return \Apple_News_Channels::get_meta_suffix( $this->channel_key );
	}

	/**
	 * Resets the API postmeta for a given post ID.
	 *
	 * @param int    $post_id The post ID to reset.
	 * @param string $suffix  Optional. Meta key suffix. Defaults to current channel suffix.
	 */
	protected function delete_post_meta( $post_id, $suffix = null ): void {
		if ( null === $suffix ) {
			$suffix = $this->get_meta_suffix();
		}

		delete_post_meta( $post_id, 'apple_news_api_id' . $suffix );
		delete_post_meta( $post_id, 'apple_news_api_revision' . $suffix );
		delete_post_meta( $post_id, 'apple_news_api_created_at' . $suffix );
		delete_post_meta( $post_id, 'apple_news_api_modified_at' . $suffix );
		delete_post_meta( $post_id, 'apple_news_api_share_url' . $suffix );
		delete_post_meta( $post_id, 'apple_news_article_checksum' . $suffix );
	}
}
