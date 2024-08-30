<?php
/**
 * Publish to Apple News: \Apple_Actions\Index\Channel class
 *
 * @package Apple_News
 */

namespace Apple_Actions\Index;

require_once dirname( __DIR__ ) . '/class-api-action.php';

use Admin_Apple_Notice;
use Apple_Actions\API_Action;
use Apple_Push_API\Request\Request_Exception;

/**
 * A class to handle a channel request from the admin.
 *
 * @package Apple_News
 */
class Channel extends API_Action {
	/**
	 * Get the channel data from Apple News.
	 *
	 * @return object|null An object containing the response from the API or null on failure.
	 */
	public function perform() {
		$channel = get_transient( 'apple_news_channel' );
		if ( false === $channel ) {
			if ( $this->is_api_configuration_valid() ) {
				try {
					$channel = $this->get_api()->get_channel( $this->get_setting( 'api_channel' ) );
				} catch ( Request_Exception $e ) {
					$channel = '';
				}
			}
		}

		set_transient( 'apple_news_channel', $channel, 300 );

		if ( '' === $channel ) {
			// Unable to get channel information. This likely means the user entered their API credentials incorrectly.
			Admin_Apple_Notice::error( __( 'Publish to Apple News error: Unable to get channel information. Please check your API credentials.', 'apple-news' ) );
		}

		return ! empty( $channel ) ? $channel : null;
	}
}
