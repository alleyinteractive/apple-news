<?php
/**
 * Publish to Apple News: \Apple_Actions\Index\Channel class
 *
 * @package Apple_News
 */

namespace Apple_Actions\Index;

require_once dirname( __DIR__ ) . '/class-api-action.php';

use Apple_Actions\API_Action;

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
				$channel = $this->get_api()->get_channel( $this->get_setting( 'api_channel' ) );
				set_transient( 'apple_news_channel', $channel, 300 );
			}
		}

		return ! empty( $channel ) ? $channel : null;
	}
}
